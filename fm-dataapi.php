<?php
/*
Plugin Name: FileMaker Cloud Data API Connector
Plugin URI: https://businessdatasystems.co.nz/wp-fm-data-api
Description:  Data Connector to Adelphi Finance. REQUIRED for MY.ADELPHI.CO.NZ.
Version: 0.1.4 (00)
Author: Malcolm Fitzgerald, Steve Winter
Author URI: https://businessdatasystems.co.nz
License: Commercial Copyright and portions GPL2 or later
*/

// Only run as part of WordPress
defined( 'ABSPATH' ) or die( 'Access denied - this plugin must be run as part of WordPress!' );
define('FM_DATA_API_SETTINGS', 'budasy_cfmc_dapi_settings');
define('FM_DATA_API_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define('FM_DATA_API_BASENAME', plugin_basename( __FILE__ ) );

require_once FM_DATA_API_PLUGIN_DIR . '/vendor/autoload.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Locales.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/AwsCognitoAuthSRP.class.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/AwsCognitoAuthentication.class.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ClarisCloudAuth.class.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/FileMakerDataAPI.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Plugin.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Admin.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Settings.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeBase.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeField.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeTable.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/AFLClient.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeUserDetail.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeContractDetail.php';

new FMDataAPI\Plugin();

