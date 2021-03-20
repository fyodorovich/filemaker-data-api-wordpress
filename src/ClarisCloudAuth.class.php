<?php

/**
 * This version of the class is in the Push CSV to Data API Project
 */
use GuzzleHttp\Client;

/**
 * A class to obtain an API Token for use with Data API on
 * Claris FileMaker Cloud
 *
 * This is a two step process:
 *      get an ID token from Cognito
 *      get an API token from the database we want to talk to
 *
 * A Cognito Auth requires a username/password
 * This can be passed when creating a new object
 * or it can be done afterwards
 *
 * $cca = new ClarisCloudAuth($u,$p);
 * $apiToken = $cca->login($host, $database)
 *
 * or
 *
 * $cca = new ClarisCloudAuth();
 * $cca->id_token($u,$p)
 * $apiToken = $cca->login($host, $database)
 *
 * or
 *
 * $cca = new ClarisCloudAuth();
 * $cca->setUsername($u);
 * $cca->setPassword($p);
 * $cca->id_token();
 * $apiToken = $cca->login($host, $database)
 *

 * With the API token you can start to chat with the Data API
 * passing the header: Authorization: Bearer $apiToken
 *
 */
class ClarisCloudAuth {

    /**
     * ID tokens are supplied by Amazon Cognito
     * @var string
     */
    private $id_token;
    private $token_expires_at = 3600;

    /**
     * Refresh tokens are supplied by Amazon Cognito
     * @var string
     */
    private $refresh_token = 'eyJjdHkiOiJKV1QiLCJlbmMiOiJBMjU2R0NNIiwiYWxnIjoiUlNBLU9BRVAifQ.VbwZosWervY5v3VwcsfESf7H147YYIqLFh7OBBOKUzMJx09D-sjdFpy5eKhzkDOSDvg-WzgtioC3EWvnneYHXcBKG0O_32fStXWfceuPT-JXeEsNyhIu9pE2OBp6itGeYbDw2yo5BQzXqKvpv4TtQNwt0X_J8K9pQ0vdTDxzK-ZGQgdtiok7vY5vmaQ15UpvZteAIo6DC3wBPMdQDTWlSLiGoHn2bjg1ECxSuLYZzqqoJqD_N_6-XM9xj5WqPO_NE4_ZJn1CsIv3zVo3DWLbl1Op8pZPH9i-fDPcJ1HO8PJH-0Om2aDH7qioGJ8ZuLB5ZMRY8fCvXY5Rj_3ajA-Ung.56KZLMrnpj7Ycp70.FKHuM33Q0dXVAQ_u4VW8Fkzlst5xbM_k-A0yZekakrfFUf4_adviyFlDW0wAnO-VmPLgJ4ucX11WxsIUve64hQGNg7r2dEm_jIBVyFo7YRJN6pIIgwAkkj2edWYi5ro6LdgZG4cGhEZ_W41ULsZ74SHJVUPKIg1BpCrQNY-zHrvyhsrGM2RKdrS5aHnRFMqUPOu1VmL_S-9PLltxLbA9Wwf9cJETmFDE7PjpjCzFbYZ_gh72UBniLInifogFiKt5drPd8K2dk7qRZ2epTNzQ7mSyETfY92oDl_omRfb2IDAQa3OiZN2tWIL6KqR787G-1YqWSpehE8Le5aTAgR3XXJRnIcEys5ymbeNluc3rNXUxzPxKNGtHT7CMeAsbeBup6136Dl6qietqS-mirXkxz-ROKrhEaxL6x5NccRD8ntvna3TcTtjSfHbSfDBTAFtGWRDw8YIsoQG9Qu8wQqdYAx7Vhw8gFzkRzzzBZT3jrdoBP1zIac2UEvtsU_gF2VlxFwkdP9P2G7UG40r3bH7bM881SpKzxq-iM08MdLi7zSmdyH54bdctVWvk9NBc3VIZ7F5yf4ZvBBuLoaxa3h7pU_bSfYOSUXBj5_zV6E0djISaYmIKWoAXRk9etH6eSQUEwF9dQTy2FuiPx9jHlfFMHBtXJJ-te-BZYN-Tfn6_qwj8luDPz--Rk2Xs0I5SZqKLKmN76rNnbTR038Ks6Wbaolz2h-_Hu4P7Y1_vKXPAnI7Bg8stu1DxrnUF4kOdF1e9mNXRdON5LZ8lOYQ9UuVYR3I2BW_5WWGonM-hnc9FIfuUkZcV1HhOVkaEPVbpZULgZmupekPSnz5nX1SiDfXg9UURjXwCutF8lVvAsig3Ta005UlI25DGE5RZ8ba1sBmQPPmQibuKWjs72SZZLA8qg4LF0ClOOE-V2mSf2tSyPtJxh0SHNamRKXSrn9MWQ4uj9PFuR--ulWf1JgiZmFrWdn2lV8ARf12pMfYgcC9BYpV0s0HK7rwvX9nEHlyn_pTVOGHM9baVJtaGbRtXf1kYDUc000PNZtdnC-7TbX0CRpzCZfcCRKOSCzbwZEBZO0wzOq9aAdj1YTXf7Z6hxmmVL8AipYqwyuRaMum9XAP8QebnyIOHtUPzCTIASiF25iz-RVtDWDbUhyXXIcnSDVHjQrQbRx2iSwCiU3xeie1_Iz92FC0H8-iCGZb9tHDpQRsYjM8VVJ0wZwNDOlsqLkMxsjb3BJIu9zpcD3u8T8hVtacBGNzx94zrCgUppBpl66rkzHigSq-y8nEfcqBKqK0V-p9-Jyag0bv--Ih7lNqYeilDSAbHzRakKroA8Yw.XxbhZGla8dP_POpebMaB5g';
    private $refresh_token_expires_at = 1646637606;

    /**
     * API token is supplied by Claris FileMaker Cloud
     * @var array [<databaseName> => <apiToken>,<databaseName> => <apiToken>]
     */
    private $api_token;
    private $token_type = 'Bearer';

    /**
     * Host and User Credentials for the host
     * @var string
     */
    private $host = '';
    private $version = 'vLatest';
    private $username = '';
    private $password = '';

    /**
     * Database that is the target of the query
     * @var string
     */
    private $database = '';

    /**
     *
     * @var array
     */
    private $known_errors = [
        '212' => 'Invalid Account: Occurs when the token is invalid',
        '400' => 'Bad request: Occurs when the server cannot process the request because it is incomplete or invalid.',
        '401' => 'Unauthorized: Occurs when the client is not authorized to access the API. If this error occurs when attempting to log in to a database session, then there is a problem with the specified user account or password. If this error occurs with other calls, the access token is not specified or it is not valid.',
        '403' => 'Forbidden: Occurs when the client is authorized, but the call attempts an action that is forbidden for a different reason.',
        '404' => 'Not found: Occurs if the call uses a URL with an invalid URL schema. Check the specified URL for syntax errors.',
        '405' => 'Method not allowed: Occurs when an incorrect HTTP method is used with a call.',
        '415' => 'Unsupported media type: Occurs if the required header is missing or is not correct for the request:',
        '500' => 'FileMaker error: Includes FileMaker error messages and error codes. See "FileMaker error codes" in FileMaker Pro Help.'
    ];

    /**
     *
     * Define the parameters in a config file
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username = null, string $password = null) {
        if (!empty($username)) {
            $this->setUsername($username);
        }
        if (!empty($password)) {
            $this->setPassword($password);
        }

        if (!empty($this->username) && !empty($this->password)) {
            $this->getIdToken();
        }
    }

    /**
     * Set the username properties needed to authenticate with Cognito
     * @param string $username
     */
    public function setUsername(string $username) {
        if ($this->username !== $username) {
            $this->username = $username;
        }
    }

    /**
     * Set the password properties needed to authenticate with Cognito
     * @param string $password
     */
    public function setPassword(string $password) {
        if ($this->password !== $password) {
            $this->password = $password;
        }
    }

    /**
     * Obtain the account name and password properties
     * @return array 
     */
    public function getAccount() {
        return ["username" => $this->username, "password" => $this->password];
    }

    /**
     * Obtain the host and database properties
     * @return array
     */
    public function getTarget() {
        return ["host" => $this->host, "database" => $this->database];
    }

    /**
     * Obtain the ID token from Amazon cognito
     * The ID token can be passed to FM Cloud to generate an API token
     * the username and password can be set using setAccount()
     * @param string $username Optional. email account of user
     * @param string $password Optional. password of user
     * @return string
     * @throws Exception
     */
    public function id_token(string $username = null, string $password = null) {

        try {
            $this->getIdToken($username, $password);
        } catch (Exception $e) {
            throw $e;
        }

        return $this->id_token;
    }

    /**
     * Test for presence of username and password
     * @param string $username
     * @param string $password
     * @throws Exception
     */
    private function requireAccount(string $username, string $password) {
        $this->setUsername($username);
        $this->setPassword($password);

        if (empty($this->username) || empty($this->password)) {
            throw new Exception("Username or Password empty. Use SetAccount() or pass the account details with this function call.");
        }
    }

    /**
     * Obtain ID token required for login
     * @throws Exception
     */
    private function getIdToken(string $username = null, string $password = null) {
        if ($this->isIdTokenCurrent() && $this->isIdTokenPresent()) {
            return;
        }
        $this->id_token = '';
        $this->token_expires_at = 0;
        try {
            if ($this->isRefreshTokenCurrent() && $this->isRefreshTokenPresent()) {
                $this->refreshIdToken();
            } else {
                $this->authenticateWithCognito($username, $password);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtain the ID token
     * @return string 
     */
    public function getCurrentIdToken() {
        $this->getIdToken();
        return $this->id_token;
    }

    /**
     * Use the stored Refresh token to obtain an ID token
     * @return string
     * @throws Exception
     */
    private function refreshIdToken() {
        try {
            $tokens = AWSCognitoAuthentication::refreshCognitoTokens($this->refresh_token);
            $this->setTokens($tokens);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtain a full set of tokens from Cognito
     *
     * @param string $username
     * @parame string $password
     * @throws Exception
     */
    private function authenticateWithCognito(string $username = null, string $password = null) {
        $this->requireAccount($username, $password);
        try {
            $tokens = AWSCognitoAuthentication::getCognitoTokens($this->username, $this->password);
            $this->setTokens($tokens);
            $this->setRefreshTokens($tokens);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Assign tokens to properties
     * @param array $tokens
     */
    private function setTokens($tokens) {
        $this->id_token = $tokens['AuthenticationResult']['IdToken'];
        $this->token_expires_at = time() + $tokens['AuthenticationResult']['ExpiresIn'];
        $this->token_type = $tokens['AuthenticationResult']['TokenType'];
    }

    /**
     * Assign refresh tokens to properties
     * @param array $tokens
     */ private function setRefreshTokens($tokens) {
        $this->refresh_token = $tokens['AuthenticationResult']['RefreshToken'];
        $this->refresh_token_expires_at = time() + 31536000; // use for one year
    }

    /**
     * Set the Cloud Instance
     * @param string $host
     */
    public function setHost(string $host) {
        $this->host = $host;
    }

    /**
     * Check the host property
     * @param string $host
     * @throws Exception
     */
    private function requireHost(string $host = null) {
        if (!empty($host)) {
            $this->host = $host;
        }
        if (empty($this->host)) {
            throw new Exception("Host is not defined");
        }
    }

    /**
     * Set the default API version
     * options are 1 | 2 | Latest
     * default is Latest
     * @param string $version
     */
    public function setVersion(string $version) {
        if ($version === "1" || $version === "2") {
            $this->version = $version;
        } else {
            $this->version = "Latest";
        }
    }

    /**
     * Set the Database
     * @param string $database
     */
    public function setDatabase(string $database) {
        $this->database = $database;
    }

    /**
     * Check database property
     * @param string $database
     * @throws Exception
     */
    private function requireDatabase(string $database = null) {
        if (!empty($database)) {
            $this->database = $database;
        }
        if (empty($this->database)) {
            throw new Exception("Host is not defined");
        }
    }

    /**
     * Build URL needed for Cloud Instance
     * The prefix is built from known variables upto and including "/databases/XYZ"
     * @return string URL to Cloud Instance
     */
    private function getDataAuthURI() {
        return 'https://' . $this->host . '/fmi/data/' . $this->version . '/databases/' . $this->database . '/sessions';
    }

    private function getAdminAuthURI() {
        return 'https://' . $this->host . '/fmi/admin/api/v2/user/auth';
    }

    private function requireCurrentToken() {
        if (!$this->isIdTokenCurrent() || !$this->isIdTokenPresent()) {
            try {
                $this->getIdToken();
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * Returns true when the token is current
     * A token only lasts for one hour
     * If the id_token is current it can be used to obtain an API token
     * @return bool
     */
    private function isIdTokenCurrent() {
        return $this->token_expires_at > time();
    }

    /**
     * Returns true when we have an ID token
     * The token may have expired
     * @return bool
     */
    private function isIdTokenPresent() {
        return !empty($this->id_token);
    }

    /**
     * Returns true when the refresh token is current
     * A refresh token lasts for one year
     * If the refresh_token is current it can be used to obtain an ID token
     * @return bool
     */
    private function isRefreshTokenCurrent() {
        return $this->refresh_token_expires_at > time();
    }

    /**
     * Returns true when we have an Refresh token
     * The token may have expired
     * @return bool
     */
    private function isRefreshTokenPresent() {
        return !empty($this->refresh_token);
    }

    /**
     * Determine whether we have an API Token
     * If the API token is present and the ID token is current
     * we can use the API token.
     * @return bool
     */
    private function isApiTokenPresent() {
        return !empty($this->api_token[$this->database]['api_token']) && (time() < $this->api_token[$this->database]['expires_at'] );
    }

    /**
     * Get API Token for a database
     * @param string $database
     * @return array [api_token, expires_at]
     */
    public function getApiToken($database) {
        return $this->api_token[$database];
    }

    /**
     * Return the Refresh Token
     * @return string
     */
    public function getRefreshToken() {
        return $this->refresh_token;
    }

    /**
     * Login to a database to obtain an API Token for use by Data API
     * an API Token is database specific.
     * You cannot use the token to target a different database even when the user/password are the same
     * 
     * The login method is a part of this class because it allows us to reauthenticate the token
     * without intervention from the user.
     * @param type $host
     * @param type $database
     * @return string API Token
     * @throws RuntimeException
     */
    public function dataLogin(string $host = null, string $database = null) {

        try {
            $this->requireCurrentToken();
            $this->requireHost($host);
            $this->requireDatabase($database);
        } catch (Exception $e) {
            throw $e;
        }

        $uri = $this->getDataAuthURI('/sessions');
        $client = new Client();
        $method = 'POST';
        $options = ['headers' => [
                'Authorization' => "FMID $this->id_token",
                "Content-Type" => 'application/json'
        ]];
        $response = $client->request($method, $uri, $options);

        if (!$response || $response->getStatusCode() != 200) {
            throw new RuntimeException('Unable to obtain api token from Filemaker cloud.');
        }

        $jsonRes = json_decode($response->getBody(), true);
        $this->api_token[$this->database] = ['api_token' => $jsonRes['response']['token'], 'expires_at' => time() + 3600];
        return $this->api_token[$this->database];
    }

    /**
     * Login to obtain an API Token for use by Admin API
     * You cannot use the token to for data API even when the user/password are the same
     * 
     * The login method is a part of this class because it allows us to reauthenticate the token
     * without intervention from the user.
     * @param type $host
     * @return string API Token
     * @throws RuntimeException
     */
    public function adminLogin(string $host = null) {

        try {
            $this->requireCurrentToken();
            $this->requireHost($host);
        } catch (Exception $e) {
            throw $e;
        }

        $uri = $this->getAdminAuthURI();
        $client = new Client();
        $method = 'POST';
        $options = ['headers' => [
                'Authorization' => "FMID $this->id_token",
                "Content-Type" => 'application/json'
        ]];
        $response = $client->request($method, $uri, $options);

        if (!$response || $response->getStatusCode() != 200) {
            throw new RuntimeException('Unable to obtain api token from Filemaker cloud.');
        }

        $jsonRes = json_decode($response->getBody(), true);
        $this->api_token[$host] = ['api_token' => $jsonRes['response']['token'], 'expires_at' => time() + 3600];
        return $this->api_token[$host];
    }

}
