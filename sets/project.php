<?php

date_default_timezone_set('Etc/GMT-4');

ini_set('default_charset', 'utf-8');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('error_reporting', E_ALL);
ini_set('magic_quotes_gpc', 0);


// PHP APC кэширование

global $gIsApc;

$gIsApc = true;


// Почта

global $gAdminEmails, $gMail, $gBackOfficeMail;

$gAdminEmails = array('support@sitedev.ru');

$gMail = array(
    'from' => array(
        'email' => 'support@sitedev.ru',
//         Будет добавлено в ~/sets/project.php
//         'name' => 'Название сайта'
    ),

    'subject' => array(
//         Будет добавлено в ~/sets/project.php
//         'append' => 'Название сайта'
    ),

    'signature' => array(
        'text' => "\n\n\n--\nСлужба поддержки\nsupport@sitedev.ru"
    ),

//     'bcc' => array(
//         'support@sitedev.ru'
//     )
);

$gMail['signature']['html'] = nl2br($gMail['signature']['text']);

$gBackOfficeMail = $gMail;
$gBackOfficeMail['subject']['append'] = 'СУ';


// Загрузка файлов

global $gMaxUploadFilesize, $gAmountMaxUploadFilesize;

$gMaxUploadFilesize = 1.5;
$gAmountMaxUploadFilesize = (int) ini_get('upload_max_filesize');
