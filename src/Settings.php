<?php

/**
 * User: Malcolm Fitzgerald, stevewinter
 * Date: 2021-03-18
 * Time: 12:59
 */

namespace FMDataAPI;

use \Exception;

class Settings {

    const DATA_API_PARAMETERS = ['host', 'port', 'usingCognito','database', 'username', 'password', 'verify', 'locale', 'refreshToken', 'refreshTokenExpires'];

    protected $host;
    protected $port;
    protected $usingCognito;
    protected $database;
    protected $username;
    protected $password;
    protected $verify;
    protected $locale;
    protected $refreshToken;
    protected $refreshTokenExpires;
 
    /**
     * @param array $array
     *
     * @return Settings
     * @throws Exception
     */
    public static function CreateFromArray(array $array) {
        $settings = new static();
        foreach (static::DATA_API_PARAMETERS as $parameter) {
            if (!array_key_exists($parameter, $array)) {
                throw new Exception(sprintf('Missing parameter %s', $parameter));
            }

            $settings->$parameter = $array[$parameter];
        }

        return $settings;
    }

    /**
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getDatabase() {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * @return boolean
     */
    public function getDoNotVerify() {
        return $this->verify;
    }

    /**
     * @return boolean
     */
    public function getVerify() {
        return $this->verify;
    }

    /**
     * 
     * @return string
     */
    public function getRefreshToken() {
        return $this->refreshToken;
    }

    /**
     * 
     * @return unix time stamp
     */
    public function getRefreshTokenExpires() {
        return $this->refreshTokenExpires;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getUsingCognito() {
        return $this->usingCognito;
    }

}
