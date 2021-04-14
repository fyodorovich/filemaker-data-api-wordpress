<?php

/**
 * Created by Apache Netbeans
 * User: Malcolm Fitzgerald
 * Date: 28/01/2020
 * Time: 13:51
 */

namespace FMDataAPI;

use \Exception;
use FMDataAPI\AFLClient;

/**
 * Class ShortCodeContractDetail
 *
 * @package FMDataAPI
 */
class ShortCodeContractDetail extends ShortCodeBase {

    protected $client_record;
    protected $contract_record;
    protected $transaction_record;

    /**
     * ShortCodeTable constructor.
     *
     * @param FileMakerDataAPI $api
     * @param Settings $settings
     */
    public function __construct(FileMakerDataAPI $api, Settings $settings) {
        parent::__construct($api, $settings);

        add_shortcode('FM-DATA-CONTRACT-DETAIL', [$this, 'retrieveContract']);
    }

    /**
     * @param array $attr
     *
     * @return string
     */
    public function retrieveContract() {

        $contract_index = filter_input(INPUT_GET, 'cid', FILTER_SANITIZE_NUMBER_INT);

        $afl = new AFLClient();

        $uuid = $afl->client_uuid();

        if (empty($afl->client_uuid())) {
            return $this->connectionError();
        }

        $loginId = $afl->client_id();

        $attr = [
            'fields' => "id_client",
        ];

        $this->client_record = get_user_meta(get_current_user_id(), 'client_' . $afl->client_id());

        if ($afl->client_id() !== $this->client_record[0]['id_client']) {
            return 'Access Denied';
        }

        $this->contract_record = $this->client_record[0]['portalData']['Contracts'][$contract_index];

        try {
            $this->transaction_record = $this->api->find($afl->transaction_layout(), $this->transaction_query($this->contract_record["Contracts::Contract"]), (int) $this->contract_record["Transactions::getFoundCount"]);
            return $this->formatContractRecord();
        } catch (Exception $e) {
            error_log($e->message);
            return $this->connectionError(true);
        }
    }

    protected function formatContractRecord() {
        if ($this->contract_record['Transactions::getFoundCount'] > 0) {
            $transactions = $this->formatTransactionRecords();
        } else {
            $transactions = "No transactions recorded.";
        }
        # at this point we will record the access
        $this->logPageView();

        $nz_date = $this->fmDate2nzDate($this->contract_record["Transactions::Date"]);

        $contractStatus = '<div id="contractStatus"><table style="margin-top: 3em; margin-bottom: 2em;"><tbody>'
                . '<tr><td>Instalment due this period</td><td class="rha">' . $this->formatCurrency($this->contract_record['Contracts::Inst. Due'], true) . '</td></tr>'
                . '<tr><td>Arrears</td><td class="rha">' . $this->formatCurrency($this->contract_record['Contracts::Arrears'], true) . '</td></tr>'
                . '<tr><td>Arrears Charges</td><td class="rha">' . $this->formatCurrency($this->contract_record['Contracts::Arr. Charges'], true) . '</td></tr>'
                . '<tr><td>Transactions</td><td class="rha">' . $this->formatCurrency($this->contract_record['Contracts::Transactions'], true) . '</td></tr>'
                . '<tr><td>Total</td><td class="rha">' . $this->formatCurrency($this->contract_record['Contracts::Total Due'], true) . '</td></tr>'
                . '</tbody></table>'
                . '<hr><div>An Early Settlement Fee is payable on full early repayment</div></div>'
        ;

        return '<hr><div style="float:right"> Contract ' . $this->contract_record["Contracts::Contract"] . '</div><div class="center">Adelphi Finance Ltd</div><hr>'
                . '<div class="center">Statement of Loan Account<div style="float:right"> Dated ' . date('d M Y') . '</div></div>'
                . '<div id="clientData" style="padding-top:2em;">'
                . '<div id="name" class="large bold">' . $this->client_record[0]['Contracts::Firstname'] . " " . $this->client_record[0]['Contracts::Surname'] . '</div>'
                . '<div id="address" class="">' . $this->client_record[0]['Contracts::Address'] . "<br>" . $this->client_record[0]['Contracts::City'] . '</div>'
                . '<div id="email" class="">' . $this->client_record[0]['email'] . '</div>'
                . '<div id="clientId" class="">Client ID # ' . $this->client_record[0]['id_client'] . '</div>'
                . '<div id="transactionCount" class="">Transactions: ' . $this->contract_record['Transactions::getFoundCount'] . '</div>'
                . '</div>'
                . '<div id="transactions" style="padding-top:2em;">' . $transactions . '</div>'
                . $contractStatus
        ;
    }

    protected function formatTransactionRecords() {

        $transactionFields = ['TX Code', 'Details', 'Debit', 'Credit', 'Balance'];
        $transactionLabels = ['Date', 'Code', 'Reference', 'Debit', 'Credit', 'Balance'];

        $s = '<table class="w3-table w3-bordered rh-border-true"><thead><tr class="head">';
        foreach ($transactionLabels as $field) {
            $s .= '<td class="center">' . $field . '</td>';
        }
        $s .= '</tr></thead>';
        $s .= '<tbody>';

        $i = 0;
        foreach ($this->transaction_record as $transaction) {
            $i++;
            $nz_date = $this->fmDate2nzDate($transaction['Date']);
            $s .= '<tr><td>' . $nz_date . '</td>';
            foreach ($transactionFields as $field) {
                if (in_array($field, ['Amount', 'Debit', 'Credit'])) {
                    $s .= '<td class="rha">' . $this->formatCurrency(trim($transaction[$field])) . '</td>';
                } elseif (in_array($field, ['Balance'])) {
                    $s .= '<td class="rha">' . $this->formatCurrency(trim($transaction[$field]), true) . '</td>';
                } else {
                    $s .= '<td>' . trim($transaction[$field]) . '</td>';
                }
            }
            $s .= '</tr>';
        }

        $s .= '</tbody>';
        $s .= '</table>';

        return $s;
    }

    protected function formatCurrentContractState() {


        $s = '<table class="w3-table w3-bordered"><thead><tr class="head">';
        foreach ($contractLabels as $field) {
            $s .= '<td class="center">' . $field . '</td>';
        }
        $s .= '</tr></thead>';
        $s .= '<tbody>';

        $i = 0;
        foreach ($this->contract_record as $transaction) {

            $nz_date = $this->fmDate2nzDate($transaction['Date']);
            $s .= '<tr><td>' . $nz_date . '</td>';
            foreach ($contractFields as $field) {
                if (in_array($field, ['Amount', 'Balance', 'Debit', 'Credit'])) {
                    $s .= '<td class="rha">' . $this->formatCurrency(trim($transaction[$field])) . '</td>';
                } else {
                    $s .= '<td>' . trim($transaction[$field]) . '</td>';
                }
            }
            $s .= '</tr>';
        }

        $s .= '</tbody>';
        $s .= '</table>';

        return $s;
    }

    /**
     * @param array $attr
     *
     * @return string
     *
     * @throws Exception
     */
    private function performTableQuery(array $attr) {
        $query = $this->parseQueryToJSON($attr['query']);
        $records = $this->api->find($attr['layout'], $query);

        return $this->generateTable($records, $attr);
    }

    /**
     * @param string $queryString
     *
     * @return array
     */
    private function parseQueryToJSON(string $queryString) {
        $reformattedQuery = html_entity_decode(
                str_replace("'", '"', $queryString)
        );

        return json_decode($reformattedQuery, true);
    }

    /**
     * @param array $records
     * @param array $attr
     *
     * @return string
     */
    private function generateTable(array $records, array $attr) {
        $fields = explode('|', $attr['fields']);
        $types = array_key_exists('types', $attr) ? explode('|', $attr['types']) : [];

        $html = '<table>';
        $html .= array_key_exists('labels', $attr) ? $this->generateHeaderRow($attr['labels']) : $this->generateHeaderRow($attr['fields']);
        $html .= '<tbody>';


        foreach ($records as $record) {
            $link = array_key_exists('id-field', $attr) && array_key_exists('detail-url', $attr) ? str_replace('*id*', $record[$attr['id-field']], $attr['detail-url']) : '';

            $html .= '<tr>';
            foreach ($fields as $id => $field) {
                $type = array_key_exists($id, $types) ? $types[$id] : null;
                $html .= sprintf('<td%2$s>%1$s</td>', $this->outputField($record, trim($field), $type, $link), $type === 'Currency' ? ' class="rha"' : '');
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * @param $labels
     *
     * @return string
     */
    private function generateHeaderRow($labels) {
        $labels = explode('|', $labels);
        $html = '<thead><tr>';
        foreach ($labels as $label) {
            $html .= trim(
                    sprintf('<th>%s</th>', $label)
            );
        }
        $html .= '</tr></thead>';

        return $html;
    }

    /**
     * Generate the query string required to obtain contract details from FileMaker Pro
     * @param uuid to identify client
     * @param contract id to identify contract
     * @return empty string,'array','Exception
     */
    protected function contract_query(string $uuid, string $contract) {

        try {
            return ['uniqueHash' => $uuid, 'Contract' => $contract];
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * Generate the query string required to obtain transaction details from FileMaker Pro
     * @param contract id to identify contract
     * @return empty string,'array','Exception
     */
    protected function transaction_query(string $contract) {

        try {
            return ['Contract' => $contract];
        } catch (Exception $ex) {
            return $ex;
        }
    }

    protected function logPageView() {


        $user = wp_get_current_user();
        $user_email = $user->user_email;
        $user_login = $user->user_login;
        $obj_id = get_queried_object_id();
        $permalink = get_permalink($obj_id);
        $msg = "Client #$user_login viewed contract #" . $this->contract_record["Contracts::Contract"];
        $context = array(
            '_action' => 'viewContract',
            '_userID' => $user->ID,
            '_userLogin' => $user->user_login,
            '_contractID' => $this->contract_record["Contracts::Contract"],
            '_permalink' => $permalink,
            '_postID' => $obj_id,
        );
        if (array_key_exists('cid', $_GET) && array_key_exists('print', $_GET)) {
            $print_option = filter_input(INPUT_GET, 'print', FILTER_SANITIZE_STRING);
            if (in_array($print_option, ['pdf', 'print'])) {
                $context['_output'] = $print_option;
            }
        }

        if ($user_email != get_option('admin_email')) {
            apply_filters('simple_history_log', $msg, $context);
        }
    }

}