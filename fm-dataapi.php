<?php
/*
Plugin Name: FileMaker Data API Connector
Plugin URI: https://businessdatasystems.co.nz/wp-fm-data-api
Description: This plugin has been customised for Adelphi Finance
Version: 0.1.3 (03)
Author: Malcolm Fitzgerald, Steve Winter
Author URI: https://businessdatasystems.co.nz
License: GPL2 or later
*/

// Only run as part of WordPress
defined( 'ABSPATH' ) or die( 'Access denied - this plugin must be run as part of WordPress!' );
define('FM_DATA_API_SETTINGS', 'fm-dataapi_settings');
define('FM_DATA_API_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define('FM_DATA_API_BASENAME', plugin_basename( __FILE__ ) );

require_once FM_DATA_API_PLUGIN_DIR . '/src/Locales.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/FileMakerDataAPI.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Plugin.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Admin.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Settings.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeBase.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeField.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeTable.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeUserDetail.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeContractDetail.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/AFLClient.php';

new FMDataAPI\Plugin();

