<?php
namespace WisdmAd;

if (!class_exists('WisdmAd\WisdmAdPackage')) {
    class WisdmAdPackage
    {
        private $events=[];
        // Hold an instance of the class
        private static $instance = null;
     
        private function __construct()
        {
            add_action('admin_init', array($this,'processJsonFile'));
            add_action('admin_notices', array($this,'wdmEventsNotice'));
            add_action('admin_enqueue_scripts', array( $this,'loadCustomWpAdminStyleScripts'));
            add_action('wp_ajax_wdm_dismissed_notice_handler', array($this,'ajaxNoticeHandler'));
            add_action('wdm_ad_schedule_hook', array($this,'wdmAdScheduleHookCallback'), 10, 2);
        }
        
        public static function getInstance()
        {
            if (self::$instance == null) {
                self::$instance = new WisdmAdPackage();
            }
            return self::$instance;
        }

        public function processJsonFile()
        {
            if ($this->isJsonFileUpdated()) {
                $this->readEvents();
                if (!empty($this->events)) {
                    foreach ($this->events as $event) {
                        if (!empty($event['id'])) {
                            $this->setCron($event);
                        }
                    }
                    $this->jsonReadComplete();
                }
            }
        }

        private function isJsonFileUpdated()
        {
            $wdm_ad_json_hash = get_option('wdm_ad_json_hash', 0);

            if ($this->isFirstTimeJsonRead($wdm_ad_json_hash)) {
                return true;
            }

            if ($this->isHashUpdated($wdm_ad_json_hash)) {
                return true;
            }

            return false;
        }

        private function readEvents()
        {
            $eventJsonPath = plugin_dir_path(__FILE__) . 'WisdmAd/wdm_events.json';
            if (file_exists($eventJsonPath)) {
                $str = file_get_contents($eventJsonPath);
                $this->events = json_decode($str, true);
            }
        }

        private function jsonReadComplete()
        {
            $hash = file_get_contents($this->getHashFilePath());
            if ($hash) {
                update_option('wdm_ad_json_hash', $hash);
            }
        }

        private function isFirstTimeJsonRead($wdm_ad_json_hash)
        {
            // Checks if the system is reading json for the first time
            return empty($wdm_ad_json_hash);
        }

        private function isHashUpdated($wdm_ad_json_hash)
        {
            $hash_path = $this->getHashFilePath();
            
            if (!file_exists($hash_path)) {
                return false;
            }

            if ($wdm_ad_json_hash != file_get_contents($hash_path)) {
                return true;
            }

            return false;
        }

        private function getHashFilePath()
        {
            return plugin_dir_path(__FILE__) . 'WisdmAd/event_hash.txt';
        }

        public function wdmEventsNotice()
        {
            if ($wdmad = $this->getPersistentTransient('wdm-ad-package')) {
                echo $wdmad;
            }
        }

        private function setCron($event_data)
        {
            if (!$this->isValidEvent($event_data)) {
                return;
            }
            $start_date = new \DateTime($event_data['start'], new \DateTimeZone('America/New_York'));
            $end_date = new \DateTime($event_data['end'] . ' 23:59:59', new \DateTimeZone('America/Los_Angeles'));
             
            $client_timezone = $this->getClientTimeZone();

            $start_date->setTimezone(new \DateTimeZone($client_timezone));
            $end_date->setTimezone(new \DateTimeZone($client_timezone));
                
            $local_current_time_stamp = current_time('timestamp');

            $local_ad_start_time_stamp = $start_date->getTimestamp();
            $local_ad_end_time_stamp = $end_date->getTimestamp();

            // If ad has not started
            if ($local_current_time_stamp < $local_ad_start_time_stamp) {
                $expiration = $local_ad_end_time_stamp - $local_ad_start_time_stamp;
            } else {
                $expiration = $local_ad_end_time_stamp - $local_current_time_stamp;
            }

            if ($local_current_time_stamp < $local_ad_end_time_stamp) {
                $args = array('event_data' => $event_data, 'expiration_time' => $expiration );
                if (!wp_next_scheduled('wdm_ad_schedule_hook')) {
                    wp_schedule_single_event($local_ad_start_time_stamp, 'wdm_ad_schedule_hook', $args);
                }
            }
        }

        private function isValidEvent($event_data = array())
        {
            

            if (empty($event_data['end'])   || empty($event_data['start']) ||
                empty($event_data['title']) || empty($event_data['link'])  ||
                empty($event_data['descr'])) {
                return false;
            }

            return true;
        }

        private function getClientTimeZone()
        {
            $client_timezone = get_option('timezone_string');

            if ($client_timezone) {
                return $client_timezone;
            }

            $client_gmt_offset = get_option('gmt_offset');

            if ($client_gmt_offset == 0 || empty($client_gmt_offset)) {
                return 'UTC';
            }

            if ($client_gmt_offset > 0) {
                return '+'. $client_gmt_offset;
            }

            return $client_gmt_offset;
        }

        public function loadCustomWpAdminStyleScripts()
        {
            wp_register_style('wdm_add_wp_admin_css', plugins_url('WisdmAd/wdmstyle.css', __FILE__), false, '1.0.0');
            wp_register_script('wdm_add_wp_admin_js', plugins_url('WisdmAd/wdm.js', __FILE__), array('jquery'), false, true);
            wp_enqueue_style('wdm_add_wp_admin_css');
            wp_enqueue_script('wdm_add_wp_admin_js');
        }

        public function ajaxNoticeHandler()
        {
            if (!empty($_POST['nonce'])) {
                delete_option('wdm-ad-package');
            }
        }

        public function wdmAdScheduleHookCallback($event_data, $expiration_time)
        {
            if ($event_data) {
                $html = $this->getAdHtml($event_data);
                $this->setPersistentTransient('wdm-ad-package', $expiration_time, $html);
            }
        }

        private function getAdHtml($event_data)
        {
            $html ='<div class="notice is-dismissible notice-wdmad" data-nonce="'. wp_create_nonce('wdm-ad-nonce') .'"><div class="wdmadouterdiv">
                    <p class="wdmadicon"><img src="' . plugins_url('WisdmAd/'.$event_data['icon'], __FILE__).'" /></p>
                    <div class="wdmadmessagediv">
                        <p class="wdmadtitle">'.$event_data['title'] .'</p>
                        <p class="wdmadmsg">'. $event_data['descr'] .'</p>
                    </div>
                    <div class="wdmactiondiv"><a target="_blank" class="button wdmactionbut" href="'. $event_data['link'] .'">Shop Now</a></div>
                </div>
            </div>
            ';
            return $html;
        }

        private function setPersistentTransient($transient, $time, $value = '')
        {
            if ($time == 0) {
                $timeOut = 0;
            } else {
                $timeOut = current_time('timestamp')+$time;
            }
            $data = array(
            'timeout' => $timeOut,
            'value' => json_encode($value),
            );
            update_option($transient, $data);
        }

        private function getPersistentTransient($transient)
        {
            $cache = get_option($transient);
            if (!empty($cache)) {
                if ($cache['timeout'] != 0 && (empty($cache['timeout']) || current_time('timestamp') > $cache['timeout'])) {
                    return false; // Cache is expired
                }
                return json_decode($cache['value']);   
            }
        }
    }
}
