<?php

namespace Expirenza\src;

use InvalidArgumentException;

/**
 * Class SmsHelper for sending messages via EXPIRENZA SMS API Service
 *
 * @package Expirenza\src
 *
 * @property string $apiKey
 * @property array $errors
 * @property bool $debug
 */
class SmsHelper
{
    const API_URL = 'https://sms.cifr.us/api/send';

    protected $apiKey;
    protected $errors;
    protected $debug;

    public function __construct($apiKey = null, $debug = false)
    {

        if (empty($apiKey)) {
            throw new InvalidArgumentException('api key is empty');
        }

        $this->apiKey = $apiKey;
        $this->debug = $debug;
    }

    /**
     * @param string $phone
     * @param string $text
     * @param string $extId
     * @param int $timeout
     * @return bool
     */
    public function send($phone, $text, $timeout = 5, $extId = null)
    {
        if (empty($phone) || empty($text)) {
            return false;
        }

        if (mb_strlen($text) > 350) {
            throw new InvalidArgumentException('Your text so long (limit 350 symbols)');
        }

        /**
         * Base clear phone number to adapt E.164 format
         */
        $phone = str_replace(' ', '', $phone);
        $phone = str_replace('-', '', $phone);
        $phone = str_replace('_', '', $phone);
        $phone = str_replace('(', '', $phone);

        $url         = self::API_URL;

        $postfields  = [
            'key'       => $this->apiKey,
            'number'    => $phone,
            'text'      => $text,
            'extId'     => $extId
        ];

        /**
         * Initialize CURL
         */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,$timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));

        try {
            /**
             * Check if debug mode
             */
            if ($this->debug == false) {
                $server_output = curl_exec($ch);
                $this->_server_response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $json = json_decode($server_output);

                /**
                 * Parse response from API
                 */
                if (is_object($json)) {
                    if ($json->result == true) {
                        return true;
                    } else {
                        $this->errors[] = $json->message;
                    }
                } else {
                    $this->errors[] = 'Can not detect reason. Response is not an object';
                }

                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get current API key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Get errors if exist
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
