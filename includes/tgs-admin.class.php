<?php

class TGS_Admin {
	public $page_slug;

	function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'plugin_settings' ) );
	}

	/**
	 * Регистрирует пункт меню и страницу настроек плагина
	 *
	 * @since 0.1
	 */
	function register_menu() {
		// Добавление пункта меню в основное меню "Настройки"
		$this->page_slug = add_options_page(
			'Telegram Sender',
			'Telegram Sender',
			'manage_options',
			'tgs',
			array( $this, 'render_options_page' )
		);

		// Создаем хук для зацепа скриптов и стилей только на этой странице
		add_action( 'admin_print_styles-' . $this->page_slug, array( $this, 'load_assets' ) );
	}

	/**
	 * Подключает JS и CSS
	 *
	 * @since 0.1
	 */
	function load_assets() {
		add_thickbox();
		wp_enqueue_style( 'tgs-admin', TGS_PLUGIN_ASSETS_URL . '/css/tgs-admin.css' );
		wp_enqueue_script( 'tgs-admin', TGS_PLUGIN_ASSETS_URL . '/js/tgs-admin.js', array( 'jquery' ) );
	}

	/**
	 * Рендерит страницу настроек
	 *
	 * @since 0.1
	 */
	function render_options_page() {
		?>
        <div class="wrap tgs">

            <h2><?php echo get_admin_page_title(); ?></h2>

            <!-- Блок со справочной информацией -->
            <div class="info">
                <h4>Инструкция</h4>
                <ol>
                    <li>
                        Создайте своего бота (<a href="https://tlgrm.ru/docs/bots#botfather" target="_blank">советы по
                            созданию бота</a>) у официального бота Телеграмм <a href="https://t.me/BotFather"
                                                                                target="_blank">@BotFather</a>.
                    </li>
                    <li>
                        Получите от него токен и сохраните здесь в соответствующем поле.
                    </li>
                    <li>
                        Начните диалог со своим ботом, нажав кнопку <b>/start</b>, а затем напишите ему любой текст.
                    </li>
                    <li>
                        Теперь <a href="#TB_inline?width=400&height=400&inlineId=test-token-and-chat"
                                  class="thickbox check-chat">узнайте ID чата</a> и сохраните в соответствующее поле.
                    </li>
                </ol>

                <h4>Справочник</h4>
                <ul>
                    <li><a href="https://core.telegram.org/bots/api" target="_blank"><i>Официальная документация Telegram</i></a></li>
                    <li><a href="https://tlgrm.ru/docs/bots/api" target="_blank"><i>Переведенная документация Telegram</i></a></li>
                </ul>

                <!-- Модальное окно с результатами проверки токена и чата -->
                <div id="test-token-and-chat" style="display:none;">
                    <div class="result">
                        Загрузка результата...
                    </div>
                </div>

            </div>

            <form action="<?php echo admin_url( 'options.php' ); ?>" method="POST">
				<?php
				// Cкрытые защитные поля
				settings_fields( 'tgs_options_group' );

				// Cекции с настройками (опциями)
				do_settings_sections( $this->page_slug . '_tab_1' );

				// Кнопка сохранения
				submit_button();
				?>
            </form>
        </div>
		<?
	}

	/**
	 * Регистрирует настройки.
	 * Настройки будут храниться в массиве.
	 *
	 * @since 0.1
	 */
	function plugin_settings() {
		// Регистрирует новую опцию и callback функцию для обработки значения опции при её сохранении в БД.
		register_setting( 'tgs_options_group', 'tgs_options', array( $this, 'sanitize_fields' ) );

		// Создает новый блок (секцию), в котором выводятся поля настроек.
		add_settings_section( 'tgs_section', 'Основные настройки', '', $this->page_slug . '_tab_1' );

		// Регистрация поля : Токен бота
		add_settings_field(
			'field_token_bot',
			'Токен бота',
			array( $this, 'make_field_token_bot' ),
			$this->page_slug . '_tab_1',
			'tgs_section'
		);

		// Регистрация поля : ID чата
		add_settings_field(
			'field_chat_id',
			'ID чата',
			array( $this, 'make_field_chat_id' ),
			$this->page_slug . '_tab_1',
			'tgs_section'
		);
	}

	/**
	 * Поле Поле "Токен бота"
	 *
	 * @since 0.1
	 */
	function make_field_token_bot() {
		$key = 'token_bot';
		$val = TGS_Helpers::get_option( $key );
		printf( '
        <input type="password" name="tgs_options[%s]" value="%s" />
        <span class="dashicons dashicons-visibility"></span>',
			$key,
			esc_attr( $val )
		);
	}

	/**
	 * Поле "ID чата"
	 *
	 * @since 0.1
	 */
	function make_field_chat_id() {
		$key = 'chat_id';
		$val = TGS_Helpers::get_option( $key );
		printf( '
        <input type="password" name="tgs_options[%s]" value="%s" />
        <span class="dashicons dashicons-visibility"></span>',
			$key,
			esc_attr( $val )
		);
	}

	/**
	 * Очистка данных полей
	 *
	 * @since 0.1
	 *
	 * @param array $options
	 * @return array
	 */
	function sanitize_fields( $options ) {
		foreach ( $options as $name => & $val ) {
			$val = strip_tags( $val );
		}

		return $options;
	}
}
