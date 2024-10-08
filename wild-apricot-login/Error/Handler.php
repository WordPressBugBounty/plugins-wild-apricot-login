<?php

class WA_Error_Handler
{
    const STOP_ON_ERROR = false;

    public static function handleError($errorCode, $errorMessage, $suppressStopping = false)
    {
        WA_Logger::log($errorCode . ': ' . $errorMessage);

        if (self::STOP_ON_ERROR && !$suppressStopping)
        {
            die($errorMessage);
        }

        return new WP_Error($errorCode, $errorMessage);
    }
}