<?php

/**.
 * User: Malcolm Fitzgerald
 * Date: 21/03/2021
 * Time: 12:39
 */

namespace FMDataAPI;

class Plugin {

    /** @var Admin */
    protected $admin;

    /** @var array */
    protected $shortcodes;

    public function __construct() {
        add_action('init', [$this, 'fmDataApiRegisterSession']);

        $settings = get_option(FM_DATA_API_SETTINGS, Admin::fmDataApiDefaultOptions());
        $api = new FileMakerDataAPI($settings);
        
        // error_log(print_r($settings), true );
        
        $this->admin = new Admin();
        $this->shortcodes = [
            new ShortCodeField($api, $settings),
            new ShortCodeTable($api, $settings),
            new ShortCodeUserDetail($api, $settings),
            new ShortCodeContractDetail($api, $settings),
        ];
    }

    public function fmDataApiRegisterSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

}
