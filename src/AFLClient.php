<?php

/**
 * Created by Apache Netbeans
 * User: Malcolm Fitzgerald
 * Date: 2021/01/18
 * Time: 14:25
 */

namespace FMDataAPI;

use \WP_Http;
use \Exception;

class AFLClient extends ShortCodeBase {

    /**
     * Name of FMP layout to target for client data
     * @var String
     */
    protected $clientLayout = 'WebData_Client';

    /**
     * Names of FMP fields to access for client data
     * @var array
     */
    protected $clientFields = ['id_client', 'email', 'Contracts::Firstname', 'Contracts::Firstname', 'Contracts::Surname', 'Contracts::Address', 'Contracts::City', 'Contracts::getFoundCount'];

    /**
     * Name of FMP layout to target for contract data
     * @var String
     */
    protected $contractLayout = 'WebData_Contract';

    /**
     * Names of FMP fields to access for contract data
     * @var array
     */
    protected $contractFields = ['Contract', 'Client', 'Inst. Due', 'Arrears', 'Arr. Charges', 'Transactions', 'Total Due'];

    /**
     * Name of FMP layout to target for transaction data
     * @var String
     */
    protected $transactionLayout = 'WebData_Transactions';

    /**
     * Names of FMP fields to access for transaction data
     * @var array
     */
    protected $transactionFields = ['Contract', 'Date', 'TX Code', 'DRCR', 'Amount', 'Details', 'Balance', 'Debit', 'Credit'];

    /**
     * uuid of current user
     * @var string
     */
    protected $current_user_uuid;

    /**
     * login of current user
     * This is the Client ID in FMP
     * @var int
     */
    protected $current_user_login;

    protected $current_user_data;


    public function __construct() {
        $this->current_user_data = get_userdata( get_current_user_id() );
        $this->current_user_login = $this->current_user_data->user_login;
        $this->current_user_uuid = $this->get_wp_user_uuid(get_current_user_id());
    }

    /**
     * Get the value of usermeta::wpcf-uuid for the current user
     * That is the UUID used in the ID field of the client table in FileMaker Pro
     * @return string
     */
    private function get_wp_user_uuid( int $user_id) {

        if ($user_id === 0 || empty($user_id)) {
            error_log('WARNING: Access to this page without a user login. IP: ' . $_SERVER['REMOTE_ADDR']);
            return '';
        }

        return get_metadata('user', $user_id, 'wpcf-uuid', 1);
    }

    /**
     * Get the UUID needed to identify the client in FMP
     * @return string
     */
    public function client_uuid() {
        return $this->current_user_uuid;
    }

    /**
     * Get the Client ID needed to identify contracts for this client in FMP
     * We don't want to display this information very much because these IDs are serial numbers
     * @return type
     */
    public function client_id() {
        return $this->current_user_login ;
    }

    public function client_layout() {
        return $this->clientLayout;
    }

    public function contract_layout() {
        return $this->contractLayout;
    }

    public function transaction_layout() {
        return $this->transactionLayout;
    }

}
