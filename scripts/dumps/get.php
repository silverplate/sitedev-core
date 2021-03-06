<?php

/**
 * Скрипт для автоматического создания дампа БД проекта. Скрипт берет
 * настройки БД от текущего сервера, создает дамп и размещает
 * по адресу ~/scripts/dumps/YYYY-MM-DD.sql.
 *
 * Запускать следует так: $ php get.php
 *
 * Дополнительно в скрипт можно передать путь к дампу, если нужно
 * назвать файл не в YYYY-MM-DD формате: $ php get.php ~/backup.sql
 *
 * Полученный файл можно переписать в локальную версию проекта и загрузить
 * через запуск скрипта set.php.
 */

require_once realpath(dirname(__FILE__) . '/../../src') . '/libs.php';
initSettings();

$d = \Ext\Db::get()->getDatabase();
$u = ' -u' . \Ext\Db::get()->getUser();

$password = \Ext\Db::get()->getPassword();
$p = $password ? ' -p' . $password : '';

$prt = \Ext\Db::get()->getPort() ? ' -P' . \Ext\Db::get()->getPort() : '';
$h = ' -h' . \Ext\Db::get()->getHost();

$indent = PHP_EOL . PHP_EOL;
$return = null;

if (empty($argv[1])) {
    $patchesDir = WD . 'scripts/dumps';
    $dumpFile = date('Y-m-d') . '.sql';
    $dumpFilePath = $patchesDir . '/' . $dumpFile;

} else {
    $dumpFilePath = $argv[1];
    $dumpFile = basename($dumpFilePath);
    $patchesDir = dirname($dumpFilePath);
}

// $dumpArchivePath = $dumpFilePath . '.tgz';

if (!is_dir($patchesDir)) {
    \Ext\File::createDir($patchesDir);
}

if (is_dir($patchesDir)) {
    exec("mysqldump $u$p$h$prt $d > $dumpFilePath", $return);

//     if (empty($return)) {
//         exec("tar -C $patchesDir -czf $dumpArchivePath $dumpFile", $return);

//         if (empty($return)) {
//             unlink($dumpFilePath);
//             exit($dumpArchivePath . $indent);
//         }
//     }

//     if (!empty($return)) {
//         exit($return . $indent);
//     }

    if (empty($return)) {
        echo $dumpFilePath . $indent;
        exit();

    } else {
        exit($return . $indent);
    }

} else {
    exit('Invalid path' . $indent);
}
