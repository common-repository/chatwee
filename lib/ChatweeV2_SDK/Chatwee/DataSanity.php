<?php
class ChatweeV2_DataSanity{

    public static function sanitizeList($array) {
        for ($i=0; $i<count($array); $i++) {
            $array[$i] = sanitize_text_field($array[$i]);
        }
        return $array;
    }

    public static function validateListAgainstValues($array, $against) {
        for ($i=0; $i<count($array); $i++) {
            if (!in_array($array[$i], $against)) {
                return false;
            }
        }
        return true;
    }

    public static function sanitizeScript($input){
        $input = trim($input);
        $input = wp_check_invalid_utf8($input);
        return strip_tags($input, '<script>');
    }

    public static function validateTag($string) {
        $exp = "/^<script src=[\'\"]?https:\/\/([a-z]+\.)?chatwee-api\.com\/(v2)?\/script\/[0-9a-f]{24}\.js[\'\"]><\/script>$/";
        $noOfMatches = preg_match_all($exp, $string);
        if($noOfMatches == 1)
            return true;
        return false;
    }

    public static function validateUrl($url) {
        if (filter_var($url, FILTER_VALIDATE_URL) !== false){
            return true;
        }
        return false;
    }

    public static function validateApiKey($text) {
        if (strlen($text) == 24) {
            return true;
        }
        return false;
    }

    public static function validateCookie($input) {
        if (strlen($input) == 32) {
            return true;
        }
        return false;
    }

    public static function validateNumber($input){
        return is_numeric($input);
    }
}