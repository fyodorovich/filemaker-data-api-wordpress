<?php

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

define("USERPOOL_ID", "us-west-2_NqkuZcXQY");
define("CLIENT_ID", "4l9rvl4mv5es1eep1qe97cautn");

class AWSCognitoAuthentication {

    /**
     * Authorise user with Amazon Cognito
     * @param string $username an email address
     * @param string $password
     * @return array Contains tokens needed for all other interactions
     */
    public static function getCognitoTokens(string $username, string $password) {
        $client = new CognitoIdentityProviderClient([
            'region' => 'us-west-2',
            'version' => 'latest',
            'credentials' => false,
        ]);
        $srp = new AwsCognitoAuthSRP($client, CLIENT_ID, USERPOOL_ID);

        $result = $srp->authenticateUser($username, $password);

        if (!$result) {
            throw new RuntimeException('Unable to obtain access token from AWS CognitoIdp.');
        }

        return $result->toArray();
    }

    /**
     * Returns all tokens from Amazon Cognito
     * @param type $refreshToken
     * @return array
     * @throws RuntimeException
     */
    public static function refreshCognitoTokens($refreshToken) {
        $client = new CognitoIdentityProviderClient([
            'region' => 'us-west-2',
            'version' => 'latest',
            'credentials' => false,
        ]);
        $srp = new AwsCognitoAuthSRP($client, CLIENT_ID, USERPOOL_ID);

        $result = $srp->refreshToken($refreshToken);

        if (!$result) {
            throw new RuntimeException('Unable to obtain access token from AWS CognitoIdp.');
        }

        return $result->toArray();
    }

}
