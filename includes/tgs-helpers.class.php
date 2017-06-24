<?php

/**
 * Class TGS_Helpes - Вспомогательные методы
 */
class TGS_Helpers {

	/**
	 * Возвращает массив опций или значение определенной опции
	 *
	 * @param null|string $key имя метаполя опции
	 *
	 * @return array|false
	 */
	public static function get_option( $key = null ) {
		$options = get_option( 'tgs_options' );

		if ( $key ) {
			return isset( $options[ $key ] ) ? $options[ $key ] : null;
		}

		return $options;
	}

	/**
	 * Возвращает ссылку для работы с ботом
	 *
	 * @since 0.1
	 *
	 * @return string|boolean
	 */
	public static function get_tg_api_url(){
		$token_bot_api =  self::get_option( 'token_bot' );
		return $token_bot_api ? "https://api.telegram.org/bot{$token_bot_api}/" : false;
	}

	/**
	 * Записывает сообщение в лог файл
	 *
	 * @since 0.1
	 *
	 * @param string $message
	 */
	public static function write_log( $message = '' ) {
		$timestamp    = date( 'd/m/Y H:i:s' );
		$message      = "[{$timestamp}] {$message}\r\n";
		$path_logfile = TGS_PLUGIN_DIR . '/log.log';
		error_log( $message, 3, $path_logfile );
	}
}
