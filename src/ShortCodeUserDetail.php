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
            $query = $afl->client_query();
            if (empty($query['uniqueHash'])) {
                return '';
            }

            $records = $this->api->find($this->clientLayout, $query );

            return $this->generateTable($records, $attr);
        } catch (Exception $e) {
            return 'Unable to load records.';
        }
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

}
