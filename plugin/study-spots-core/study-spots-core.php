<?php
/**
 * Plugin Name:       Study Spots Core
 * Description:       Content model, roles, review workflow and blocks for Study Spots SJ.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Vraj Patel
 * Text Domain:       study-spots
 */

defined( 'ABSPATH' ) || exit;

define( 'SS_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SS_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'SS_CORE_VERSION', '1.0.0' );

require_once SS_CORE_DIR . 'includes/content-model.php';
require_once SS_CORE_DIR . 'includes/roles.php';
require_once SS_CORE_DIR . 'includes/submissions.php';
require_once SS_CORE_DIR . 'includes/blocks.php';
require_once SS_CORE_DIR . 'includes/admin.php';
require_once SS_CORE_DIR . 'includes/seed.php';
require_once SS_CORE_DIR . 'includes/demo.php';

register_activation_hook( __FILE__, function () {
	ss_register_content_model();
	ss_register_roles();
	flush_rewrite_rules();
} );
