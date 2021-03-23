<?php

/**
 * User: Malcolm Fitzgerald
 * Date:18/01/2021
 * Time: 14:25
 */

namespace FMDataAPI;

use \WP_Http;
use \Exception;
use \ClarisCloudAuth;

class FileMakerDataAPI {

    /** @var Settings */
    private $settings;

    /** @var string */
    private $baseURI;

    /** @var string */
    private $token;
    private $cache = [];
    private $retried = false;

    public function __construct(Settings $settings) {
        $this->settings = $settings;
        $this->setBaseURL($settings->getHost(), $settings->getDatabase());
    }

    /**
     * @param $layout
     * @param bool|object $class
     * 
     * @return array
     * @throws Exception
     */
    public function findAll($layout) {
        $this->setOrFetchToken();

        // by default API only returns 100 records at a time, so we need to keep getting records till we run out
        $offset = 1;
        $retrieved = 100;
        $results = [];

        while ($retrieved == 100) {
            $uri = $this->baseURI . sprintf('layouts/%s/records?_offset=%s', $layout, $offset);
            $records = $this->performFMRequest('GET', $uri, []);
            $retrieved = count($records);
            $offset += 100;

            $results = array_merge($results, $records);
        }

        return $results;
    }

    /**
     * @param $layout
     * @param $query
     *
     * @return array|mixed
     * @throws Exception
     */
    public function findOneBy($layout, $query) {
        $records = $this->find($layout, $query);

        if (empty($records)) {
            return [];
        }

        return $records[0];
    }

    /**
     * @param string $layout
     * @param array $query
     * @param bool $class
     *
     * @return array
     *
     * @throws Exception
     */
    public function find(string $layout, array $query, int $limit = null) {


        $queryHash = md5(
                serialize($query . $limit)
        );

        if (array_key_exists($queryHash, $this->cache)) {
            return $this->cache[$queryHash];
        }
        try {

            $this->setOrFetchToken();
        } catch (\Exception $e) {
            error_log($e->message);
            mail(get_option('admin_email'), 'FM Data API Token Set/Fetch Failed', print_r($e));
            return 'Connection could not be Authorised';
        }

        if (isset($limit) && $limit > 100) {

            $body = json_encode([
                'query' => [$query],
                'limit' => $limit
            ]);
        } else {
            $body = json_encode([
                'query' => [$query]
            ]);
        }
        $uri = $this->baseURI . sprintf('layouts/%s/_find', $layout);
        try {
            $records = $this->performFMRequest("POST", $uri, ['body' => $body]);
        } catch (\Exception $e) {
            error_log($e->message);
            mail(get_option('admin_email'), 'FM Data Request Failed', print_r($e));
            return 'Data Request Error';
        }
        $this->cache[$queryHash] = $records;

        return $records;
    }

    public function storedQuery($query) {
        $queryHash = md5(
                serialize($query)
        );
        if (array_key_exists($queryHash, $this->cache)) {
            return $this->cache[$queryHash];
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return array
     * @throws Exception
     */
    private function performFMRequest($method, $uri, $options) {
        $params = [
            'method' => $method,
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $this->token),
                'Content-Type' => 'application/json'
            ]
        ];

        if ($this->settings->getDoNotVerify()) {
            $params['sslverify'] = false;
        }

        $request = new WP_Http();
        $response = $request->request($uri, array_merge($params, $options));

        if ($response) {
            $responseArray = json_decode($response['body'], true);
            $responseCode = $responseArray['messages'][0]['code'];

            switch ($responseCode) {
                case 0:
                    return $this->flattenRecords($responseArray['response']['data']);
                case 401:
                    return [];
                case 952:
                    if (!$this->retried) {
                        $this->retried = true;
                        $this->fetchToken();
                        $this->performFMRequest($method, $uri, $options);
                    }
                    break;
            }

            throw new Exception($responseArray['messages'][0]['message'], $responseArray['messages'][0]['code']);
        }

        throw new Exception('No response received from FileMaker are you sure the settings are correct?');
    }

    private function flattenRecords(array $records) {
        $resp = [];
        foreach ($records as $record) {
            $resp[] = array_merge([
                'portalData' => $record['portalData'],
                'recordId' => $record['recordId'],
                'modId' => $record['modId'],
                    ], $record['fieldData']);
        }

        return $resp;
    }

    private function setBaseURL($host, $database) {
        $this->baseURI = ('http' == substr($host, 4) ? $host : 'https://' . $host) .
                ('/' == substr($host, -1) ? '' : '/') .
                'fmi/data/v2/databases/' .
                $database . '/';
    }

    /**
     * @return string
     * @throws Exception
     */
    private function setOrFetchToken() {
        if (!empty($_SESSION['fm-data-api-token'])) {
            return $this->token = $_SESSION['fm-data-api-token'];
        }

        return $this->fetchToken();
    }

    /**
     * Get a token for use with Bearer TOKEN
     * @return string
     * @throws Exception
     */
    public function fetchToken() {

        if ($this->settings->getUsingCognito()) {
            return $this->fetchCognitoAPIToken();
        }

        return $this->fetchOnPremToken();
    }

    /**
     * Get Token for Claris Cloud server
     * @return string API Token for Claris FM Cloud
     */
    private function fetchCognitoAPIToken() {
        $cca = new \ClarisCloudAuth();
        $cca->setHost($this->settings->getHost());
        $cca->setDatabase($this->settings->getDatabase());
        $cca->setUsername($this->settings->getUsername());
        $cca->setPassword($this->settings->getPassword());
        $apiToken = $cca->dataLogin();
        $this->token = $apiToken['api_token']; 
        $_SESSION['fm-data-api-token'] = $this->token;
        session_write_close();
        //other plugins can restart a session again via session_start()
        // see https://core.trac.wordpress.org/ticket/47320
        return $this->token;
    }

    /**
     * Get Token for On Premise Server
     * @return string ID token
     * @throws Exception
     */
    private function fetchOnPremToken() {
        $params = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("{$this->settings->getUsername()}:{$this->settings->getPassword()}"),
                'Content-Type' => 'application/json'
            ]
        ];

        if ($this->settings->getDoNotVerify()) {
            $params['sslverify'] = false;
        }

        $request = new WP_Http();
        $response = $request->request($this->baseURI . 'sessions', $params);

        if (is_a($response, 'WP_Error')) {
            throw new Exception(sprintf(': %s', $response->get_error_message()));
        }

        if ($response) {
            $responseObj = json_decode($response['body'], false);
            $responseCode = $responseObj->messages[0]->code;

            if ($responseCode == '0') {
                $this->token = $responseObj->response->token;
                $_SESSION['fm-data-api-token'] = $this->token;

                session_write_close();
                //other plugins can restart a session again via session_start()
                // see https://core.trac.wordpress.org/ticket/47320

                return $this->token;
            }

            throw new Exception($responseObj->messages[0]->message, $responseObj->messages[0]->code);
        }

        throw new Exception('No response received from FileMaker are you sure the settings are correct?');
    }

}
