<?php

class TGNS {
	/**
	 * Ярлык страницы настроек.
	 *
	 * @var string
	 */
	public $page_slug;
	
	/**
	 * Токен бота.
	 *
	 * @var string
	 */
	public $token_bot;
	
	/**
	 * Идентификатор чата.
	 *
	 * @var integer
	 */
	public $chat_id;
	
	/**
	 * Имя файла лога.
	 *
	 * @var string
	 */
	public $name_logfile;
	
	/**
	 * Статус файла лога.
	 *
	 * @var string
	 */
	public $status_log;
	
	/**
	 * Путь к папке с плагином со слешем на конце.
	 *
	 * @var string
	 */
	public $path;
	
	/**
	 * Ссылка на папку с плагином со слешем на конце.
	 *
	 * @var string
	 */
	public $url;
	
	
	/**
	 * TGNS constructor.
	 */
	function __construct() {
		$this->init_path();
		$this->init_options();
		$this->init_hooks();
	}
	
	/**
	 * Инициализация основных путей и ссылок плагина.
	 */
	function init_path() {
		$this->path = plugin_dir_path( TGNS_PLUGIN_FILE );
		$this->url  = plugin_dir_url( TGNS_PLUGIN_FILE );
	}
	
	/**
	 * Hook into actions and filters.
	 */
	function init_hooks() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'phpmailer_init', array( $this, 'prepare_send_message' ) );
	}
	
	/**
	 * Инициализирует опции плагина.
	 */
	function init_options() {
		$options = get_option( 'tgns' );
		
		if ( $options ) {
			foreach ( $options as $name => $value ) {
				$this->{$name} = empty( $value ) ? null : $value;
			}
		}
	}
	
	/**
	 * Выводит предупреждение, если плагин не настроен.
	 */
	function admin_notices() {
		if ( ! $this->token_bot || ! $this->chat_id ) : ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    WP Telegram Notification Sender
                    <a href="<?php echo admin_url( 'options-general.php?page=tgns' ); ?>">нужно настроить</a>,
                    чтобы он мог отправлять сообщения в чат Telegram.
                </p>
            </div>
		<?php endif;
	}
	
	/**
	 * Регистрирует пункт меню и страницу настроек плагина.
	 */
	function register_menu() {
		// Добавление пункта меню в основное меню "Настройки".
		$this->page_slug = add_options_page( 'Telegram Notification Sender', 'Telegram Notification', 'manage_options',
			'tgns', array(
				$this,
				'render_options_page',
			) );
		
		// Создаем хук для зацепа скриптов и стилей только на этой странице.
		add_action( 'admin_print_styles-' . $this->page_slug, array( $this, 'enqueue_assets' ) );
	}
	
	/**
	 * Подключает JS и CSS.
	 */
	function enqueue_assets() {
		add_thickbox();
		wp_enqueue_style( 'tgns', $this->url . 'assets/tgns-admin.css' );
		wp_enqueue_script( 'tgns', $this->url . 'assets/tgns-admin.js', array( 'jquery-ui-tabs' ) );
	}
	
	/**
	 * Рендерит страницу настроек.
	 */
	function render_options_page() {
		$path = plugin_dir_path( TGNS_PLUGIN_FILE ) . 'includes/options-page-template.php';
		$path = apply_filters( 'tgns_options_page_template', $path );
		
		if ( file_exists( $path ) ) {
			include( $path );
		}
	}
	
	/**
	 * Регистрирует настройки.
	 */
	function register_setting() {
		// Регистрирует новую опцию и callback функцию для обработки значения опции при её сохранении в БД.
		register_setting( 'tgns_group', 'tgns', array( $this, 'sanitize_fields' ) );
		
		// Создает новый блок (секцию), в котором выводятся поля настроек.
		add_settings_section( 'tgns_section', '', '', $this->page_slug . '_main' );
		add_settings_section( 'tgns_section', '', '', $this->page_slug . '_log' );
		
		// Регистрация поля : Токен бота
		add_settings_field( 'field_token_bot', 'Токен бота', array( $this, 'make_field_token_bot' ),
			$this->page_slug . '_main', 'tgns_section' );
		
		// Регистрация поля : ID чата
		add_settings_field( 'field_chat_id', 'ID чата', array( $this, 'make_field_chat_id' ),
			$this->page_slug . '_main', 'tgns_section' );
		
		// Регистрация поля : Имя файла лога
		add_settings_field( 'field_name_log', 'Имя файла лога', array( $this, 'make_field_name_log' ),
			$this->page_slug . '_log', 'tgns_section' );
	}
	
	/**
	 * Выводит на экран поле "Токен бота".
	 */
	function make_field_token_bot() {
		$key = 'token_bot';
		$val = $this->{$key};
		
		printf( '
        <input type="password" name="tgns[%s]" value="%s" />
        <span class="dashicons dashicons-visibility"></span>', $key, esc_attr( $val ) );
	}
	
	/**
	 * Выводит на экран поле "ID чата".
	 */
	function make_field_chat_id() {
		$key = 'chat_id';
		$val = $this->{$key};
		
		printf( '
        <input type="password" name="tgns[%s]" value="%s" />
        <span class="dashicons dashicons-visibility"></span>', $key, esc_attr( $val ) );
	}
	
	/**
	 * Выводит на экран поле "Имя файла лога".
	 */
	function make_field_name_log() {
		$key = 'name_logfile';
		$val = $this->{$key};
		
		printf( '
        <input type="text" name="tgns[%s]" value="%s" />', $key, esc_attr( $val ) );
	}
	
	/**
	 * Очистка данных полей.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	function sanitize_fields( $options ) {
		foreach ( $options as $name => & $val ) {
			$val = strip_tags( $val );
		}
		
		return $options;
	}
	
	/**
	 * Возвращает ссылку для работы с ботом.
	 *
	 * @return string|boolean
	 */
	function get_tg_api_url() {
		return $this->token_bot ? "https://api.telegram.org/bot{$this->token_bot}/" : false;
	}
	
	/**
	 * Очищает сообщение от html тегов, лишних переносов строк и пробелов.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	function sanitaze_message( $content = '' ) {
		if ( $content ) {
			$content = wp_strip_all_tags( $content );
			$content = preg_replace( '/\n(\ |\t)+/', "\n", $content );
			$content = preg_replace( '/(?:\r?\n){3,}/', "\n\n", $content );
		}
		
		return $content;
	}
	
	/**
	 * Проверяет наличие необходимых данных для отправки сообщения в чат.
	 *
	 * @param \PHPMailer $phpmailer
	 */
	function prepare_send_message( & $phpmailer ) {
		$message = $this->sanitaze_message( $phpmailer->Body );
		
		if ( $this->token_bot && $this->chat_id && $message ) {
			$this->check_logfile();
			$this->send_message( $this->token_bot, $this->chat_id, $message );
		}
	}
	
	/**
	 * Отправляет сообщение в чат.
	 *
	 * @param string  $token   Токен бота.
	 * @param integer $chat_id Идентификатор чата.
	 * @param string  $message Сообщение.
	 *
	 * @return bool
	 */
	function send_message( $token, $chat_id, $message ) {
		
		// Параметры запроса.
		$params = array(
			'body' => array(
				'chat_id' => $chat_id,
				'text'    => $message,
			),
		);
		
		// URL запроса.
		$url = "https://api.telegram.org/bot{$token}/sendmessage";
		
		// Отправка сообщения боту.
		$response = wp_remote_post( $url, $params );
		
		// Проверка на ошибки.
		if ( is_wp_error( $response ) ) {
			$this->write_log( 'ОШИБКА: ' . $response->get_error_message() );
			
			return false;
		}
		
		// Ответы.
		$response_code = wp_remote_retrieve_response_code( $response );
		
		if ( 200 == $response_code ) {
			$this->write_log( 'УСПЕШНАЯ ОТПРАВКА в чат ' . $this->chat_id );
			
			return true;
		} else {
			$response_message = wp_remote_retrieve_response_message( $response );
			
			$this->write_log( "ОШИБКА: ответ - $response_code($response_message)" );
			
			return false;
		}
	}
	
	function check_logfile() {
		$path_log = $this->path . $this->name_logfile;
		
		if ( file_exists( $path_log ) ) {
			$this->status_log = true;
		} else {
			$status = file_put_contents( $path_log, '' );
			
			$this->status_log = $status === false ? false : true;
		}
	}
	
	/**
	 * Записывает сообщение в лог файл.
	 *
	 * @param string $message
	 */
	function write_log( $message ) {
		if ( $this->status_log ) {
			$path_log  = $this->path . $this->name_logfile;
			$timestamp = date( 'd/m/Y H:i:s' );
			$message   = "[{$timestamp}] {$message}\r\n";
			
			error_log( $message, 3, $path_log );
		}
	}
	
	/**
	 * Читает сообщение из лог файла.
	 *
	 * @return string
	 */
	function read_log() {
		$content = '';
		
		if ( $this->status_log ) {
			//var_dump(); die();
		}
		
		return $content;
	}
}