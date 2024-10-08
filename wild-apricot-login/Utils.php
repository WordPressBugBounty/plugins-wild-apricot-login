<?php

class WA_Utils
{
    const SANITIZER_PREFIX = 'sanitize';

    public static function sanitizeString($value, $maxLength = 255, $keepTags = false)
    {
        $filtered = substr((string)$value, 0, $maxLength);
        $filtered = wp_check_invalid_utf8($filtered);

        if (strpos($filtered, '<') !== false)
        {
            $filtered = wp_pre_kses_less_than($filtered);

            if (!$keepTags)
            {
                $filtered = wp_strip_all_tags($filtered, true);
            }
        }
        else
        {
            $filtered = trim(preg_replace('/[\r\n\t ]+/', ' ', $filtered));
        }

        return !empty($filtered) ? $filtered : '';
    }

    public static function sanitizeKey($value, $maxLength = 255)
    {
        return sanitize_key( trim( substr($value, 0, $maxLength) ) );
    }

    public static function sanitizeInt($value)
    {
        return (int)$value;
    }

    public static function sanitizeEmail($value, $maxLength = 255)
    {
        return sanitize_email( trim( substr($value, 0, $maxLength) ) );
    }

    public static function sanitizeArray($value, $type)
    {
        $type = ucfirst(strtolower($type));

        if (!is_array($value))
        {
            return array( call_user_func(array(__CLASS__, self::SANITIZER_PREFIX . $type), $value) );
        }

        if (empty($value)) { return $value; }

        $output = array();

        foreach ($value as $key => $item)
        {
            $output[$key] = call_user_func(array(__CLASS__, self::SANITIZER_PREFIX . $type), $item);
        }

        return $output;
    }

    public static function isNotEmptyString($value)
    {
        return is_string($value) && !empty($value);
    }

    public static function isNotEmptyArray($value)
    {
        return is_array($value) && count($value) > 0;
    }
}