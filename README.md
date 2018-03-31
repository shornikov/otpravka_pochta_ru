# otpravka_pochta_ru
PHP-класс основных методов для работы с сервисом "Отправка" почты РФ.
Сервис находится по адресу otpravka.pochta.ru, требует договора с почтой РФ, регистрации и ключей пользователя.
Предназначен для автоматической выдачи ШПИ, формирования документов и передачи данных в почту РФ.

Класс otpravka реализует бОльшую часть методов сервиса и нормализует ошибки.

ЗЫ: так уж получилось что в коде под block понимается "партия".

Пример использования - получаем номера отправлений в партии

<?php
include "otpravka.php";

$otpravka = new otpravka("-=accessToken=-", "-=passwordToken=-");
$res = $otpravka->getBlockInfoExtended($partiakNumber);

if ($res == false) {
    print "error".PHP_EOL;
    print_r ($otpravka->lastError());
} else {
    foreach ($res as $rec) {
        print $rec['id');
    }
}
