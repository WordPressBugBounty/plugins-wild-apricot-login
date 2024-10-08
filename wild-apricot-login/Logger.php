<?php

class WA_Logger
{
    public static function log($message)
    {
        @error_log($message);
    }
}