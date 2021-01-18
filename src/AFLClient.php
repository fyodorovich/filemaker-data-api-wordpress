<?php

/**
 * Created by Netbeans.
 * User: Malcolm Fitzgerald
 * Date: 2021/01/18
 * Time: 14:25
 */

namespace FMDataAPI;

use \WP_Http;
use \Exception;

class AFLClient {

    public function __construct() {
        $this->setBaseURL($settings->getServer(), $settings->getDatabase());
    }

    /**
     * Get the value of usermeta::wpcf-uuid for the current user
     * That is the UUID used in the ID field of the client table in FileMaker Pro
     * @return string
     */
    public function client_uuid() {
        $current_user_id = get_current_user_id();

        if ($current_user_id === 0) {
            error_log('WARNING: Access to this page without a user login. IP: ' . $_SERVER['REMOTE_ADDR']);
            return '';
        }

        return get_metadata('user', $current_user_id, 'wpcf-uuid', 1);
    }

    /**
     * Generate the query string required to obtain Client details from FileMaker Pro
     * @return empty string|array|Exception
     */
    public function client_query() {

        try {
            $user_uuid = $this->client_uuid() ;
            
            return ['uniqueHash' => $user_uuid];

        } catch (Exception $ex) {
            return $ex;
        }
    }

}
