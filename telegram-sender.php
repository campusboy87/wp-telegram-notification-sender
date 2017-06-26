<?php
/**
 * Plugin Name: Telegram Sender
 * Description: Отправляет письма в Телеграмм
 * Version:     0.2
 * Plugin URI:  https://
 * Author:      Campusboy
 * Author URI:  https://wp-plus.ru/
 * License:     GPL v2 or later
 */

defined( 'ABSPATH' ) or die();

define( 'TGS_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define( 'TGS_PLUGIN_ASSETS_URL', plugins_url( '/assets', __FILE__ ) );

include TGS_PLUGIN_DIR . '/includes/tgs-helpers.class.php';
include TGS_PLUGIN_DIR . '/includes/tgs-admin.class.php';

add_action( 'phpmailer_init', 'tgs_send_message_callback' );
function tgs_send_message_callback( & $phpmailer ) {
	
	if ( empty( $phpmailer->Body ) ) {
		TGS_Helpers::write_log( 'ОШИБКА: сообщение пустое.' );
		
		return false;
	}
	
	new TGS_Request( $phpmailer->Body );
}


class TGS_Request {
	public $token_bot;
	public $chat_id;
	public $content_raw;
	public $content;
	public $response;
	
	/**
	 * TGS_Request constructor.
	 *
	 * @param null|string $content контент сообщения
	 */
	function __construct( $content = null ) {
		$this->token_bot   = TGS_Helpers::get_option( 'token_bot' );
		$this->chat_id     = TGS_Helpers::get_option( 'chat_id' );
		$this->content_raw = $content;
		
		$this->sanitaze_message();
		$this->send_message();
	}
	
	/**
	 * Очищает сообщение от html тегов, лишних переносов строк и пробелов
	 *
	 * @since 0.2
	 */
	function sanitaze_message() {
		$text = wp_strip_all_tags( $this->content_raw );
		$text = preg_replace( '/\n(\ |\t)+/', "\n", $text );
		$text = preg_replace( '/(?:\r?\n){3,}/', "\n\n", $text );
		
		$this->content = $text;
	}
	
	/**
	 * Отправка сообщения в чат
	 *
	 * @return bool|WP_Error|array
	 */
	function send_message() {
		if ( ! $this->token_bot || ! $this->chat_id || ! $this->content ) {
			TGS_Helpers::write_log( 'ОШИБКА - Не указаны обязательные параметры для отправки сообщения.' );
			
			return false;
		}
		
		// Параметры запроса
		$params = array(
			'body' => array(
				'chat_id' => $this->chat_id,
				'text'    => $this->content,
			),
		);
		
		// URL запроса
		$url = "https://api.telegram.org/bot{$this->token_bot}/sendmessage";
		
		// Отправка сообщения боту
		$this->response = wp_remote_post( $url, $params );
		
		// Проверка на ошибки
		if ( is_wp_error( $this->response ) ) {
			$error_message = $this->response->get_error_message();
			TGS_Helpers::write_log( "ОШИБКА: $error_message" );
			TGS_Helpers::write_log( "ОШИБКА: $url" );
			
			return false;
		}
		
		// Ответы
		$response_code    = wp_remote_retrieve_response_code( $this->response );
		$response_message = wp_remote_retrieve_response_message( $this->response );
		$response_body    = wp_remote_retrieve_body( $this->response );
		
		if ( 200 == $response_code ) {
			TGS_Helpers::write_log( 'УСПЕШНАЯ ОТПРАВКА в чат ' . $this->chat_id );
			
			return true;
		} else {
			TGS_Helpers::write_log(
				"ОШИБКА.
				Код: $response_code.
				Статус: $response_message.
				Ответ сервера: " . print_r( $response_body, true )
			);
			
			return false;
		}
	}
	
}

// Инициализация плагина
function tgs_init() {
	new TGS_Admin();
}

add_action( 'plugins_loaded', 'tgs_init', 9999 );
