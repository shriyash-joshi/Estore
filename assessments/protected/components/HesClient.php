<?php

include "Cryptographer.php";

class HesClient {

    var $handshakeUrl;
    var $loginUrl;
    var $createUserUrl;
    var $updateUserUrl;
    var $deleteUserUrl;
    var $listUserUrl;
    var $listDWYAResultsUrl;
    var $listResultsUrl;

    public function __construct($environment) {
        if ($environment === 'prod') {
            $apiRootUrl = 'https://api.keystosucceed.cn/';
            $extRootUrl = 'https://api.keystosucceed.cn/ext.php/';
        }
        
        if ($environment === 'staging') {
            $apiRootUrl = 'https://api.staging.humanesources.com/';
            $extRootUrl = 'https://api.staging.humanesources.com/ext.php/';
        }
        
        if (!isset($apiRootUrl)) {
            echo 'HesClient __construct received an invalid environment:' . $environment;
            exit();
        }
        
        $this->handshakeUrl = $apiRootUrl . 'login';
        $this->loginUrl = $extRootUrl;
        $this->createUserUrl = $apiRootUrl . 'sfGuardUserAPI.json';
        $this->updateUserUrl = $apiRootUrl . 'sfGuardUserAPI/';
        $this->deleteUserUrl = $apiRootUrl . 'sfGuardUserAPI/';
        $this->listUserUrl = $apiRootUrl . 'sfGuardUserAPI.json';
        $this->listDWYAResultsUrl = $apiRootUrl . 'asPortDWYAResultAPI.json';
        $this->listResultsUrl = $apiRootUrl . 'asPortResultAPI.json';
        $this->deleteResultUrl = $apiRootUrl . 'asPortResultAPI/';
    }

    public function getLoginUrl($accountId, $configId, $userId, $nonce, $prodId = null, $accountEncryptedHesStudentId = null) {
        $url = $this->loginUrl . '?accountId=' . $accountId . '&userId=' . $userId . '&nonce=' . $nonce;
        if (isset($prodId)) {
            $url .= '&prodId=' . $prodId;
        }
        if (isset($configId)) {
            $url .= '&configId=' . $configId;
        }
        if ($accountEncryptedHesStudentId !== null) {
            $url .= '&ssoStudentId=' . $accountEncryptedHesStudentId;
        }
        return $url;
    }

    public function powerOnSelfTest($stringToEncode, $accountKey) {
        try {
            $this->log("Power on self test\n");
            $this->log("text to encode: '" . $stringToEncode . "' length: " . strlen($stringToEncode));
            //encrypt and encode
            $outStringEncoded = Cryptographer::encrypt($stringToEncode, $accountKey, true);
            //decode and decrypt
            $clearText = Cryptographer::decrypt($outStringEncoded, $accountKey, true);
            $this->log("un-encoded: '" . $clearText . "' length: " . strlen($clearText));
            if ($stringToEncode !== $clearText) {
                throw new Exception("Fail: Decryption not exact match");
            } else {
                $this->log("Success: Decryption exact match");
            }
            $this->log("/////////////////////////////////////");
        } catch (Exception $e) {
            $this->log("Error: " . $e->getMessage());
            $this->log("/////////////////////////////////////");
        }
    }

    public function handshake($accountId, $password, $keyString) {
        try {
            $this->log("handshake test");
            $encryptedEncodedString = Cryptographer::encrypt($password, $keyString, true);
            $parameters = "accountId=" . $accountId . "&accountSsoPassword=" . $encryptedEncodedString;
            //echo $this->handshakeUrl;die;
            $postResponse = $this->sendPOST($this->handshakeUrl, $parameters);
            $responseArray = json_decode($postResponse, true);
            if (!isset($responseArray['nonce'])) {
                throw new Exception('Error in response to handshake response =' . $postResponse);
            }

            $nonce = $responseArray['nonce'];
            //echo $nonce;die;
            $doubleEncrypted = Cryptographer::encrypt($nonce, $keyString, true);
            $this->log("doubleEncrypted=" . $doubleEncrypted);
            return $doubleEncrypted;
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }

    public function createUser($accountId, $nonce, $nonEmptyInputValues) {
        try {
            $content = json_encode($nonEmptyInputValues);
            $parameters = "accountId=" . $accountId . "&nonce=" . $nonce . '&content=' . $content;
            $response = $this->sendPOST($this->createUserUrl, $parameters);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }

    public function listUser($accountId, $userId, $nonce) {
        try {
            $listUserUrl = $this->listUserUrl;
            $parameters = "id=" . $userId . "&accountId=" . $accountId . "&nonce=" . $nonce;
            $response = $this->sendGET($listUserUrl, $parameters);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }

    public function listResults($accountId, $userId, $nonce, $filters) {
        try {
            $parameters = "user_id=" . $userId . "&accountId=" . $accountId . "&nonce=" . $nonce;
            foreach ($filters as $key => $value) {
                $parameters .= "&$key=$value";
            }
            $response = $this->sendGET($this->listResultsUrl, $parameters);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }

    public function deleteResult($accountId, $resultId, $nonce) {
        try {
            $deleteUrl = $this->deleteResultUrl . $resultId . '.json';
            $parameters = "accountId=" . $accountId . "&nonce=" . $nonce;
            $response = $this->sendDELETE($deleteUrl, $parameters);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }

    public function deleteUser($accountId, $userId, $nonce) {
        try {
            $deleteUrl = $this->deleteUserUrl . $userId . '.json';
            $parameters = "accountId=" . $accountId . "&nonce=" . $nonce;
            $response = $this->sendDELETE($deleteUrl, $parameters);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }

    public function updateUser($userId, $accountId, $nonce, $nonEmptyInputValues) {
        try {
            $updateUrl = $this->updateUserUrl . $userId . '.json';
            $content = json_encode($nonEmptyInputValues);
            $parameters = "accountId=" . $accountId . "&nonce=" . $nonce . '&content=' . $content;
            $response = $this->sendPUT($updateUrl, $parameters);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }

    public function encryptMe($me, $keyString) {
        try {
            $encryptedEncodedString = Cryptographer::encrypt($me, $keyString, true);
            return $encryptedEncodedString;
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }

    protected function sendPOST($urlString, $parameters) {
        $line = "";
        $output = "";
        try {
            $ch = curl_init($urlString);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            $response = curl_exec($ch);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
            throw e;
        }
    }

    protected function sendGET($urlString, $parameters) {
        try {
            $ch = curl_init($urlString . "?" . $parameters);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
            throw e;
        }
    }

    protected function sendDELETE($urlString, $parameters) {
        try {
            $ch = curl_init($urlString);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
            throw e;
        }
    }

    protected function sendPUT($urlString, $parameters) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (Exception $e) {
            $this->log($e->getMessage());
            throw e;
        }
    }

    protected function log($message) {
        Yii::log($message, CLogger::LEVEL_INFO);
    }

}