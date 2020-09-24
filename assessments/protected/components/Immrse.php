<?php

class Immrse {

    public $pin;
    public $secret;
    public $domain;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $studentPin;
    public $career;
    public $careerUrl;

    public function __construct() {
        if(filter_input(INPUT_SERVER, 'HTTP_HOST') == 'shop.univariety.com'){
            $this->pin = '358472';
            $this->secret = 'fa72077c78dsfsdfuuasdaawlomcvla';
            $this->domain = 'https://experience.immrse.in/';
            $this->careerUrl = 'http://univariety.immrse.in/career/cp/';
        } else {
            $this->pin = '358472';
            $this->secret = 'fa72077c78dsfsdfuuasdaawlomcvla';
            $this->domain = 'http://imdev.immrse.in/';
            $this->domain = 'https://experience.immrse.in/';
            $this->careerUrl = 'http://univariety.immrse.in/career/cp/';
        }
        
    }

    public function signup() {
        $url = 'api/v1.0/signup';
        $postParams = ['first_name' => $this->first_name, 'last_name' => $this->last_name, 'email' => $this->email, 'mobile' => $this->phone];

        $return = $this->sendCurlRequest($url, true, $postParams);
        return $return;
    }

    public function signin() {
        $url = 'api/v1.0/signin';
        $postParams = ['pin' => $this->studentPin];

        $return = $this->sendCurlRequest($url, true, $postParams);
        return $return;
    }

    public function getCareers() {
        $url = 'api/v1.0/getAllCareers';

        $return = $this->sendCurlRequest($url, true, []);
        return $return;
    }

    public function getUsage() {
        $url = 'api/v1.0/getUsage';

        $return = $this->sendCurlRequest($url, true, []);
        return $return;
    }

    public function sendCurlRequest($url, $post = true, $params = []) {
        $key = OpenSslEncrypt::getInstance($this->secret)->encrypt($this->pin . '|' . time());
        $headers = array(
            'Content-Type:application/x-www-form-urlencoded',
            'Channel-Partner:' . $key
        );

        $ch = curl_init($this->domain . $url);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (count($params) > 0) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        $return = json_decode($response, true);

        return $return;
    }

    public function getEncryptionCareer() {
        $key = '';
        if ($this->career && $this->studentPin) {
            $params = $this->career . '|' . $this->studentPin . '|' . $this->pin . '|' . time();
            $key = OpenSslEncrypt::getInstance($this->secret)->encrypt($params);
            
            return $this->careerUrl.$key;
        }
        return $key;
    }

}
