<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 28/07/2018
 * Time: 13:51
 */

namespace FMDataAPI;

use \Exception;

/**
 * Class ShortCodeContractDetail
 *
 * @package FMDataAPI
 */
class ShortCodeContractDetail extends ShortCodeBase
{
    /**
     * ShortCodeTable constructor.
     *
     * @param FileMakerDataAPI $api
     * @param Settings $settings
     */
    public function __construct(FileMakerDataAPI $api, Settings $settings)
    {
        parent::__construct($api, $settings);

        add_shortcode('FM-DATA-CONTRACT-DETAIL', [$this, 'retrieveTableContent']);
    }

    /**
     * @param array $attr
     *
     * @return string
     */
    public function retrieveTableContent(array $attr)
    {
        if(!$this->validateAttributesOrExit(['layout', 'fields'], $attr)) {
            return '';
        }

        try {
            if(array_key_exists('query', $attr)) {
                return $this->performTableQuery($attr);
            }

            $records = $this->api->findAll($attr['layout']);

            return $this->generateTable($records, $attr);
        } catch (Exception $e) {
            return 'Unable to load records.';
        }
    }

    /**
     * @param array $attr
     *
     * @return string
     *
     * @throws Exception
     */
    private function performTableQuery(array $attr)
    {
        $query = $this->parseQueryToJSON($attr['query']);
        $records = $this->api->find($attr['layout'], $query);

        return $this->generateTable($records, $attr);
    }

    /**
     * @param string $queryString
     *
     * @return array
     */
    private function parseQueryToJSON(string $queryString)
    {
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
    private function generateTable(array $records, array $attr)
    {
        $fields = explode('|', $attr['fields']);
        $types = array_key_exists('types', $attr)
            ? explode('|', $attr['types'])
            : [];

        $html = '<table>';
        $html .= array_key_exists('labels', $attr)
            ? $this->generateHeaderRow($attr['labels'])
            : $this->generateHeaderRow($attr['fields']);
        $html .= '<tbody>';


        foreach($records as $record) {
            $link = array_key_exists('id-field', $attr) && array_key_exists('detail-url', $attr)
                ? str_replace('*id*', $record[$attr['id-field']], $attr['detail-url'])
                : '';

                $html .= '<tr>';
            foreach($fields as $id => $field) {
                $type = array_key_exists($id, $types) ? $types[$id] : null;
                $html .= sprintf('<td%2$s>%1$s</td>',$this->outputField($record, trim($field), $type, $link), $type === 'Currency' ? ' class="rha"':'');
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
    private function generateHeaderRow($labels)
    {
        $labels = explode('|', $labels);
        $html = '<thead><tr>';
        foreach($labels as $label) {
            $html .= trim(
                sprintf('<th>%s</th>', $label)
            );
        }
        $html .= '</tr></thead>';

        return $html;
    }
}