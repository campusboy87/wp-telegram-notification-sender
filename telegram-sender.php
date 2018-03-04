<?php
/**
 * Plugin Name: WP Telegram Notification Sender
 * Description: Отправляет письма в Телеграмм
 * Version:     0.2
 * Plugin URI:  https://github.com/campusboy87/WP-Telegram-Sender
 * Author:      Campusboy
 * Author URI:  https://wp-plus.ru/
 * License:     GPL v2 or later
 */

defined( 'ABSPATH' ) || die();

define( 'TGNS_PLUGIN_FILE', __FILE__ );

require dirname( __FILE__ ) . '/includes/class-tgns.php';

/*
 * Инициализация плагина.
 */
function tgns_init() {
	new TGNS();
}

/*
 * Выполняет действия при активации плагина.
 */
function tgns_activate() {
	$default_option = array(
		'name_logfile' => wp_generate_uuid4() . '.log',
	);
	
	add_option( 'tgns', $default_option, '', 'no' );
}

add_action( 'plugins_loaded', 'tgns_init', 9999 );
register_activation_hook( TGNS_PLUGIN_FILE, 'tgns_activate' );