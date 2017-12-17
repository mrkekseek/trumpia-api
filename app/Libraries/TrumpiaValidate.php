<?php

namespace App\Libraries;

class TrumpiaValidate
{
    protected $maxLength = 500;
    protected $supportedCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@!“#$%&‘()*+,-.?/:;<=>';

    static public function message($text)
    {
        if ( ! empty($text)) {
            $text = str_replace(['[$FirstName]', '[$LastName]'], '', $text);
            $check = true;
            for ($i = 0, $count = strlen($text); $i < $count; $i++) {
                if (strpos(self::supportedCharacters, $text[$i]) === false) {
                    $check = false;
                }
            }

            if ( ! empty($check)) {
                return true;
            }
        }

        return false;
    }

    static public function messageLength($text, $company, $length = self::maxLength)
    {
        $optout = ' Txt STOP to OptOut';
        $realLength = strlen($text) + 2 + strlen($company) + stelen($optout);
        return $realLength <= $length;
    }
}