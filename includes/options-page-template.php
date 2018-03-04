<?php
$link_modal = http_build_query( array(
	'width'    => 400,
	'height'   => 400,
	'inlineId' => 'test-token-and-chat',
) );
?>
    <div class="wrap tgns">

        <h2><?php echo get_admin_page_title(); ?></h2>

        <form action="<?php echo admin_url( 'options.php' ); ?>" method="POST">

            <div id="tabs">
                <div class="nav-tab-wrapper">
                    <ul class="tabs-item">
                        <li><a href="#main-tab" class="nav-tab nav-tab-active">Основные настройки</a></li>
                        <li><a href="#log-tab" class="nav-tab">Логи</a></li>
                        <li><a href="#info-tab" class="nav-tab">Помощь</a></li>
                    </ul>
                </div>

                <div id="main-tab">
					<?php
					// Cекции с основными настройками (опциями)
					do_settings_sections( $this->page_slug . '_main' );
					?>
                </div>

                <div id="log-tab">
					<?php
					// Cекции с логами
					do_settings_sections( $this->page_slug . '_log' );
					?>
                </div>

                <div id="info-tab">
                    <!-- Блок со справочной информацией -->
                    <div class="info">
                        <h4>Инструкция</h4>
                        <ol>
                            <li>
                                Создайте своего бота (<a href="https://tlgrm.ru/docs/bots#botfather" target="_blank">советы
                                    по
                                    созданию бота</a>) у официального бота Телеграмм <a href="https://t.me/BotFather"
                                                                                        target="_blank">@BotFather</a>.
                            </li>
                            <li>
                                Получите от него токен и сохраните здесь в соответствующем поле.
                            </li>
                            <li>
                                Начните диалог со своим ботом, нажав кнопку <b>/start</b>, а затем напишите ему любой
                                текст.
                            </li>
                            <li>
                                Теперь <a href="#TB_inline?<?php echo $link_modal; ?>"
                                          class="thickbox check-chat">узнайте ID чата</a> и сохраните в соответствующее
                                поле.
                            </li>
                        </ol>

                        <h4>Справочник</h4>
                        <ul>
                            <li><a href="https://core.telegram.org/bots/api" target="_blank"><i>Официальная документация
                                        Telegram</i></a></li>
                            <li><a href="https://tlgrm.ru/docs/bots/api" target="_blank"><i>Переведенная документация
                                        Telegram</i></a></li>
                        </ul>

                        <!-- Модальное окно с результатами проверки токена и чата -->
                        <div id="test-token-and-chat" style="display:none;">
                            <div class="result">
                                Загрузка результата...
                            </div>
                        </div>

                    </div>
                </div>
            </div>
			
			
			<?php
			// Cкрытые защитные поля
			settings_fields( 'tgns_group' );
			
			// Кнопка сохранения
			submit_button();
			?>
        </form>
    </div>
<?php
