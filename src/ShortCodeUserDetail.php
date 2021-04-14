<?php

/**
 * Created by Apache Netbeans.
 * User: Malcolm Fitzgerald
 * Date: 2021/01/18
 * Time: 14:11
 */

namespace FMDataAPI;

use \Exception;
use FMDataAPI\AFLClient;

/**
 * Class ShortCodeTable
 *
 * @package FMDataAPI
 */
class ShortCodeUserDetail extends ShortCodeBase {

    protected $client_record;

    /**
     * ShortCodeUserDetail constructor.
     *
     * @param FileMakerDataAPI $api
     * @param Settings $settings
     */
    public function __construct(FileMakerDataAPI $api, Settings $settings) {
        parent::__construct($api, $settings);

        add_shortcode('FM-DATA-USER-DETAIL', [$this, 'retrieveClientRecord']);
    }

    /**
     * @param array $attr
     *
     * @return string
     */
    public function retrieveClientRecord(array $attr) {


        try {
            $afl = new AFLClient();

            $uuid = $afl->client_uuid();
            $attr = [
                'fields' => "id_client",
            ];

            if (empty($afl->client_uuid())) {
                return $this->connectionError();
            }

            $this->client_record = $this->api->findOneBy($afl->client_layout(), $this->client_query($uuid));

            if ($afl->client_id() !== $this->client_record['id_client']) {
                $s = '<h3>Access Denied</h3><div>Please <a href="https://adelphi.co.nz/contact-us">contact Adelphi Finance</a> directly or <a href="https://adelphi.co.nz/message-us">send us a message</a></div>';

                return $s;
            }

            update_user_meta(get_current_user_id(), 'client_' . $afl->client_id(), $this->client_record, false);

            return $this->formatClientRecord();
        } catch (Exception $e) {
            return $this->connectionError(true);
        }
    }

    protected function formatClientRecord() {
        if ($this->client_record['Contracts::getFoundCount'] > 0) {
            $contracts = $this->formatClientContracts();
        }
        return '<div id="clientData">'
                . '<div id="name" class="large strong">' . $this->client_record['Contracts::Firstname'] . " " . $this->client_record['Contracts::Surname'] . '</div>'
                . '<div id="address" class="">' . $this->client_record['Contracts::Address'] . "<br>" . $this->client_record['Contracts::City'] . '</div>'
                . '<div id="email" class="">' . $this->client_record['email'] . '</div>'
                . '<div id="clientId" class="strong">Account Number: ' . $this->client_record['id_client'] . '</div>'
                . '</div><div id="contractData">'
                . '<div id="contractCount" class="">Contracts: ' . $this->client_record['Contracts::getFoundCount'] . '</div>'
                . $contracts
                . '</div>';
        ;
    }

    protected function formatClientContracts() {

        $contractFields = [
            'Contracts::Inst. Due',
            'Contracts::Arrears',
            'Contracts::Total Due',
        ];
        $contractLabels = [
            'Contract',
            'Date',
            'Inst. Due*',
            'Arrears',
            'Total Due',
            'Active'
        ];
        $s = '<table><thead><tr class="head"><td class="inverse">&nbsp;</td>';
        foreach ($contractLabels as $field) {
            $s .= '<td class="center">' . $field . '</td>';
        }
        $s .= '</tr></thead>';
        $s .= '<tbody>';

        $i = $this->client_record['Contracts::getFoundCount'];
        foreach (array_reverse($this->client_record['portalData']['Contracts']) as $contract) {
            $i--;

            if (!empty($contract['Transactions::Date'])) {
                $us_date = explode("/", $contract['Transactions::Date']);
                $nz_date = implode("/", [$us_date[1], $us_date[0], $us_date[2]]);
            } else {
                $nz_date = '';
            }
            $s .= '<tr><td class="inverse center"><a href="/contract-statement/?&amp;cid=' . $i . '">&#10140;</a></td>'
                    . '<td class="center">' . $contract['Contracts::Contract'] . '</td><td class="center">' . $nz_date . '</td>';

            foreach ($contractFields as $field) {
                $s .= '<td class="rha">' . $this->formatCurrency(trim($contract[$field])) . '</td>';
            }

            $s .= '<td class="center">'; 
            $s .= $contract['Contracts::active_contract'] ? 'Yes' : 'No';
            
            $s .= '</td></tr>';
        }

        $s .= '</tbody>';
        $s .= '</table>';
        $s .= "* Instalment due";
        return '<div id="contractRecords">' . $s . '</div>';
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
     * Generate the query string required to obtain Client details from FileMaker Pro
     * @return array,Exception
     */
    protected function client_query(string $uuid) {

        try {
            return ['uniqueHash' => $uuid];
        } catch (Exception $ex) {
            return $ex;
        }
    }

}
