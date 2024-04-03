<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class Text
{
    public static function removeSpecialChars($string)
    {
        $string = str_replace(' ', '_', $string);
        return preg_replace('/[^A-Za-z0-9_.]/', '', $string);
    }

    public static function formatPhoneNumber($phoneNumber)
    {
        if (Str::startsWith($phoneNumber, '08')) {
            return '628' . substr($phoneNumber, 2);
        }

        return $phoneNumber;
    }

    public static function generateIdCardNumber()
    {
        $randomNumber = mt_rand(1000, 9999);
        $getUnix = time();

        return $getUnix . $randomNumber;
    }

    public static function generateUrlMap(string $lat, string $lng)
    {
        return "https://www.google.com/maps?q={$lat},{$lng}";
    }
}
