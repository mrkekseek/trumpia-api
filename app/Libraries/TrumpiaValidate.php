<?php

namespace App\Libraries;

class TrumpiaValidate
{
    const MAX_LENGTH = 500;
    const SUPPORTED_CHARACTERS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@!“#$%&‘'’()*+,-.?/:;<=> ";

    static public function companyName($name) {
        if (strlen($name) <= 32) {
            return true;
        }
            
        return false;
    }

    static public function message($text)
    {
        $text = str_replace(['[$FirstName]', '[$LastName]', '[$Link]'], '', $text);
        for ($i = 0, $count = strlen($text); $i < $count; $i++) {
            if (strpos(self::SUPPORTED_CHARACTERS, $text[$i]) === false) {
               return false;
            }
        }

        return true;
    }

    static public function messageLength($text, $company, $length = null)
    {
        $length = empty($length) ? self::MAX_LENGTH : $length;
        $optout = ' Txt STOP to OptOut';
        $realLength = strlen($text) + 2 + strlen($company) + strlen($optout);

        return $realLength <= $length;
    }

    static public function phone($phone)
    {
        if (strlen($phone) != 10) {
            return false;
        }

        if (strpos($phone, '1') === 0 || strpos($phone, '0') === 0) {
            return false;
        }

        return is_numeric($phone);
    }
}