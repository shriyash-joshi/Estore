<?php
class ERForms_Validation {

    public static function text($type, $value) {
        switch ($type) {
            case 'email':
            case 'user_email':
            case 'url': if (method_exists('ERForms_Validation', $type)) {
                    $method = $type;
                    return ERForms_Validation::$method($value);
                }
        }
        return true;
    }

    public static function email($value) {
        $value = trim($value);
        if (empty($value))
            return true;
        return (filter_var($value, FILTER_VALIDATE_EMAIL)) ? true : false;
    }

    public static function maxlength($value, $len) {
        if (function_exists('mb_strlen')) {
            return (mb_strlen($value) <= $len) ? true : false;
        } else {
            return (strlen($value) <= $len) ? true : false;
        }
    }

    public static function required($value) {
        if (is_array($value))
            return empty($value) ? false : true;
        else
            return (trim($value) == '') ? false : true;
    }

    public static function is_file_uploaded($param_name) {
        if (empty($_FILES)) {
            return false;
        }

        $file = isset($_FILES[$param_name]) ? $_FILES[$param_name] : false;
        if (empty($file))
            return false;

        if (!file_exists($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        return true;
    }

    public static function minlength($value, $len) {
        if (function_exists('mb_strlen')) {
            return (mb_strlen($value) > $len) ? true : false;
        } else {
            return (strlen($value) > $len) ? true : false;
        }
    }

    public static function url($value) {
        $value = trim($value);
        if (empty($value))
            return true;
        return (filter_var($value, FILTER_VALIDATE_URL)) ? true : false;
    }

    public static function verify_file_type($allowed, $FILE, $param_name) {
        $filename = $FILE['name'];
        $allowed = array_map('strtolower', $allowed); // To avoid case sensitive
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return false;
        }
        return true;
    }

    public static function user_email($value) {
        return self::email($value);
    }

    public static function date($value, $pattern = 'm/d/Y') {
        if (empty($value))
            return true;
        
        $dt = DateTime::createFromFormat($pattern, $value);
        if (empty($dt)) {
            return false;
        }
        $timestamp = $dt->getTimestamp();
        if (empty($timestamp))
            return false;
        return true;
    }

    public static function maxDate($value, $maxDate, $pattern = 'm/d/Y') {
        $dt = DateTime::createFromFormat('!' . $pattern, $value);
        if (empty($dt))
            return true;
        $timestamp = $dt->getTimestamp();
        if (empty($timestamp))
            return true;

        $max_dt = DateTime::createFromFormat('!m/d/Y', $maxDate);
        if (empty($max_dt))
            return true;
        $maxTimestamp = $max_dt->getTimeStamp();
        return $timestamp > $maxTimestamp ? false : true;
    }

    public static function minDate($value, $minDate, $pattern = 'm/d/Y') {
        $dt = DateTime::createFromFormat('!' . $pattern, $value);
        if (empty($dt))
            return true;
        $timestamp = $dt->getTimestamp();
        if (empty($timestamp))
            return true;

        $min_dt = DateTime::createFromFormat('!m/d/Y', $minDate);
        if (empty($min_dt))
            return true;

        $minTimestamp = $min_dt->getTimestamp();
        return $timestamp < $minTimestamp ? false : true;
    }

    public static function is_unique($value, $name, $form_id, $submission_id = 0) {
        return erforms()->submission->is_unique_value($value, $name, $form_id, $submission_id);
    }

    public static function number($value) {
        return is_numeric($value);
    }

}