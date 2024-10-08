<?php

class WA_Http_Client
{
    const TIMEOUT = 10;
    const DEBUG = false;

    public function sendRequest($url, $args)
    {
        $args['user-agent'] = 'WP - Wild Apricot Login (' . WA_Utils::sanitizeString(home_url()) . ')';
        $args['sslverify'] = false;
        $args['timeout'] = self::TIMEOUT;

        $response = wp_remote_request($url, $args);

        if (WA_Utils::isNotEmptyArray($response))
        {
            if (self::DEBUG)
            {
                WA_Logger::log('wa_integration_http_request_url: ' . @json_encode($url));
                WA_Logger::log('wa_integration_http_request_params: ' . @json_encode($args));
                WA_Logger::log('wa_integration_http_request_response: ' . @json_encode($response));
            }

            if (WA_Utils::isNotEmptyArray($response['response']) && WA_Utils::sanitizeInt($response['response']['code']) == 403)
            {
                WA_Error_Handler::handleError('wa_integration_http_error', 'Your IP address has been flagged for unauthorized access.');
            }
        }

        return $response;
    }
}