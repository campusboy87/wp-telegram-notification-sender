jQuery('document').ready(function ($) {

    var $tgsBox = $('.tgs');

    $tgsBox.on('click', '.dashicons-visibility', function () {
        $(this)
            .prev()
            .attr("type", "text");

        $(this)
            .removeClass('dashicons-visibility')
            .addClass('dashicons-hidden');
    });

    $tgsBox.on('click', '.dashicons-hidden', function () {
        $(this)
            .prev()
            .attr("type", "password");

        $(this)
            .removeClass('dashicons-hidden')
            .addClass('dashicons-visibility');
    });

    $tgsBox.on('click', '.check-chat', function (e) {
        e.preventDefault();

        var modalBox = $('#test-token-and-chat');
        var resultBox = $('.result', modalBox);
        var tokenBot = $('[name="tgs_options[token_bot]"]').val();

        resultBox.text('Загрузка результата...');

        if (tokenBot) {

            var tgApiUrl = 'https://api.telegram.org/bot' + tokenBot + '/getUpdates';

            $.ajax({
                url: tgApiUrl,
                complete: function (data) {
                    console.log(data);

                    if ( data.responseJSON.ok ) {
                        var messages = data.responseJSON.result;

                        if (messages.length) {
                            var listMessages = '<div class="item-mess">';
                                listMessages += '<div class="f">Имя пользователя</div>';
                                listMessages += '<div class="u">Ник пользователя</div>';
                                listMessages += '<div class="i">ID чата</div>';
                                listMessages += '<div class="t">Присланный текст</div>';
                                listMessages += '</div>';

                            messages.forEach(function (item, i, messages) {
                                var first_name = item.message.chat.first_name;
                                var username = item.message.chat.username;
                                var id = item.message.chat.id;
                                var text = item.message.text;
                                listMessages += '<div class="item-mess">';
                                listMessages += '<div class="f">' + first_name + '</div>';
                                listMessages += '<div class="u">' + username + '</div>';
                                listMessages += '<div class="i">' + id + '</div>';
                                listMessages += '<div class="t">' + text + '</div>';
                                listMessages += '</div>';
                            });

                            resultBox.html(listMessages);
                        } else {
                            resultBox.text('Подключение к боту успешное, но сообщений не найдено.');
                        }

                    } else {
                        resultBox.text('Ошибка запроса. Код ошибки: ' + data.status + '. Описание: ' + data.statusText);
                    }

                }
            });
        } else {
            resultBox.text('Без токена узнать ID чата невозможно.');
        }
    });


});