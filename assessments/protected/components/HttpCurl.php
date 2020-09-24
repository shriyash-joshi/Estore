<?php

class HttpCurl {

    private $session;           // Contains the cURL handler for a session
    private $url;               // URL of the session
    private $options = array(); // Populates curl_setopt_array
    public $error_code;         // Error code returned as an int
    public $error_string;       // Error message returned as a string
    public $info;               // Returned after request (elapsed time, etc)
    public $multi_info;
    public $multi_content;
    public $multi_error;

    function __construct($url = '') {

        if (!function_exists('curl_init'))
            trigger_error('cURL Class - PHP was not built with --with-curl, rebuild PHP to use cURL.');
        $this->set_defaults();
        if ($url)
            $this->create($url);
    }

    // Return a get request results
    public function get($url = '', $options = array()) {
        // If a URL is provided, create new session
        if (!empty($url)) {
            $this->set_defaults();
            $this->create($url);
        }

        // Add in the specific options provided
        $this->options($options);
        return $this->execute();
    }

    /*
     * returns multiple get requests using curl_multi_exec
     */

    public function get_multi($urls = array(), $options = array()) {
        return $this->getMulti($urls, $options);
    }

    public function getMulti($urls = array(), $options = array()) {
        if (!is_array($urls) || empty($urls))
            return FALSE;

        $this->multi_info = array();
        $this->multi_content = array();
        $this->multi_error = array();
        $handles = array();
        $curl_multi = curl_multi_init();

        foreach ($urls as $index => $url) {
            $handles[$index] = $this->create($url);
            $this->options($options);
            if (!isset($this->options[CURLOPT_TIMEOUT]))
                $this->options[CURLOPT_TIMEOUT] = 45;
            if (!isset($this->options[CURLOPT_RETURNTRANSFER]))
                $this->options[CURLOPT_RETURNTRANSFER] = TRUE;
            if (!isset($this->options[CURLOPT_FOLLOWLOCATION]))
                $this->options[CURLOPT_FOLLOWLOCATION] = TRUE;
            if (!isset($this->options[CURLOPT_USERAGENT]))
                $this->options[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; rv:20.0) Gecko/20100101 Firefox/20.0";
            if (!isset($this->options[CURLOPT_AUTOREFERER]))
                $this->options[CURLOPT_AUTOREFERER] = TRUE;
            if (!isset($this->options[CURLOPT_CONNECTTIMEOUT]))
                $this->options[CURLOPT_CONNECTTIMEOUT] = 30;
            if (!isset($this->options[CURLOPT_MAXREDIRS]))
                $this->options[CURLOPT_MAXREDIRS] = 10;
            if (!isset($this->options[CURLOPT_HEADER]))
                $this->options[CURLOPT_HEADER] = FALSE;
            if (!isset($this->options[CURLOPT_SSL_VERIFYPEER]))
                $this->options[CURLOPT_SSL_VERIFYPEER] = FALSE;
            if (!isset($this->options[CURLOPT_FAILONERROR]))
                $this->options[CURLOPT_FAILONERROR] = FALSE;
            if (!isset($this->options[CURLOPT_ENCODING]))
                $this->options[CURLOPT_ENCODING] = '';

            curl_setopt_array($handles[$index], $this->options);
            curl_multi_add_handle($curl_multi, $handles[$index]);
        }

        $active = NULL;
        do {
            $mrc = curl_multi_exec($curl_multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($curl_multi) == -1)
                usleep(100);
            do {
                $mrc = curl_multi_exec($curl_multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($urls as $index => $url) {
            if (curl_error($handles[$index]) == "") {
                $this->multi_content[$index] = curl_multi_getcontent($handles[$index]);
                $handle = curl_multi_info_read($curl_multi, $handles[$index]);
                $this->multi_info[$index] = curl_getinfo($handle['handle']);
            } else {
                $this->multi_error[$index] = curl_error($handles[$index]);
            }
            // Remove and close the handle
            @curl_multi_remove_handle($curl_multi, $handles[$index]);
            @curl_close($handles[$index]);
        }
        curl_multi_close($curl_multi);
        return $this->multi_content;
    }

    public function getMultiContent($index = null) {
        if (isset($this->multi_content[$index]))
            return $this->multi_content[$index];
        return null;
    }

    // Send a post request on its way with optional parameters (and get output)
    // $url = '', $params = array(), $options = array()
    //   or
    // $parays = array()
    public function post() {
        $url = '';
        $params = array();
        $options = array();
        // How many parameters have been passed?
        switch (count($args = func_get_args())) {
            // If they have JUST passed post parameters
            default:
            case 1:
                $advance_mode = TRUE;
                $params = $args[0];
                break;
            // They have passed several (up to 3) parameters
            case 2:
            case 3:
                $advance_mode = FALSE;
                if (isset($args[0]))
                    $url = $args[0];
                if (isset($args[1]))
                    $params = $args[1];
                if (isset($args[2]))
                    $options = $args[2];
                break;
        }

        // If a URL is provided, create new session
        if (!empty($url))
            $this->create($url);

        // If its an array (instead of a query string) then format it correctly
        if (is_array($params)) {
            $params = http_build_query($params);
        }

        // Add in the specific options provided
        $this->options($options);

        $this->options[CURLOPT_POST] = TRUE;
        $this->options[CURLOPT_POSTFIELDS] = $params;

        // We are in simple mode, they have only called this method, so return the output
        if (!$advance_mode) {
            return $this->execute();
        }
    }

    public function set_cookies($params = array()) {
        if (is_array($params))
            $params = http_build_query($params);
        $this->option(CURLOPT_COOKIE, $params);
        return $this;
    }

    public function http_login($username = '', $password = '') {
        $this->option(CURLOPT_USERPWD, $username . ':' . $password);
        return $this;
    }

    public function proxy($url = '', $port = 3128) {
        //$this->option(CURLOPT_HTTPPROXYTUNNEL, TRUE);
        $this->option(CURLOPT_PROXY, $url);
        $this->option(CURLOPT_PROXYPORT, $port);
        return $this;
    }

    public function proxy_login($username = '', $password = '') {
        $this->option(CURLOPT_PROXYUSERPWD, $username . ':' . $password);
        return $this;
    }

    public function options($options = array()) {
        // Merge options in with the rest - done as array_merge() does not overwrite numeric keys
        foreach ($options as $option_code => $option_value)
            $this->option($option_code, $option_value);
        unset($option_code, $option_value);

        // Set all options provided
        curl_setopt_array($this->session, $this->options);
        return $this;
    }

    public function option($code, $value) {
        $this->options[$code] = $value;
        return $this;
    }

    // Start a session from a URL
    public function create($url) {
        if (!preg_match('!^\w+://! i', $url))
            $url = 'http://' . $url; // If no a protocol in URL, add http://
        $this->url = $url;
        $this->session = curl_init($this->url);
        return $this->session;
    }

    // End a session and return the results
    public function execute() {
        // Set two default options, and merge any extra ones in
        if (!isset($this->options[CURLOPT_TIMEOUT]))
            $this->options[CURLOPT_TIMEOUT] = 45;
        if (!isset($this->options[CURLOPT_RETURNTRANSFER]))
            $this->options[CURLOPT_RETURNTRANSFER] = TRUE;
        if (!isset($this->options[CURLOPT_FOLLOWLOCATION]))
            $this->options[CURLOPT_FOLLOWLOCATION] = TRUE;
        if (!isset($this->options[CURLOPT_USERAGENT]))
            $this->options[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; rv:20.0) Gecko/20100101 Firefox/20.0";
        if (!isset($this->options[CURLOPT_AUTOREFERER]))
            $this->options[CURLOPT_AUTOREFERER] = TRUE;
        if (!isset($this->options[CURLOPT_CONNECTTIMEOUT]))
            $this->options[CURLOPT_CONNECTTIMEOUT] = 15;
        if (!isset($this->options[CURLOPT_MAXREDIRS]))
            $this->options[CURLOPT_MAXREDIRS] = 4;
        if (!isset($this->options[CURLOPT_HEADER]))
            $this->options[CURLOPT_HEADER] = FALSE;
        if (!isset($this->options[CURLOPT_SSL_VERIFYPEER]))
            $this->options[CURLOPT_SSL_VERIFYPEER] = FALSE;
        if (!isset($this->options[CURLOPT_FAILONERROR]))
            $this->options[CURLOPT_FAILONERROR] = FALSE;
        if (!isset($this->options[CURLOPT_ENCODING]))
            $this->options[CURLOPT_ENCODING] = '';

        $this->options();
        $return = curl_exec($this->session);

        // Request failed
        if ($return === FALSE) {
            $this->error_code = curl_errno($this->session);
            $this->error_string = curl_error($this->session);
            curl_close($this->session);
            $this->session = NULL;
            return $return;
            // Request successful
        } else {
            $this->info = curl_getinfo($this->session);
            curl_close($this->session);
            $this->session = NULL;
            return $return;
        }
    }

    private function set_defaults() {
        $this->info = array();
        $this->options = array();
        $this->error_code = 0;
        $this->error_string = '';
        $this->multi_content = array();
        $this->multi_info = array();
    }

}
