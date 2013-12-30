<?php

/**
 * Скрипт предназначен для автоматической загрузки дампа БД
 * на основании настроек проекта на текущем сервере.
 *
 * Запускать следует так: $ php set.php
 * Или так: $ php set.php ~/backup.sql[.tgz]
 *
 * Если запустить скрипт без параметров, то будет осуществленна попытка
 * применить дамп по адресу ~/scripts/dumps/YYYY-MM-DD.sql[.tgz],
 * где YYYY-MM-DD - текущая дата. Файл в таком формате получается автоматически
 * путем запуска скрипта get.php.
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
    $patchesDir = realpath(WD . 'scripts/dumps');
    $dumpFile = date('Y-m-d') . '.sql';
    $dumpFilePath = $patchesDir . '/' . $dumpFile;

} else {
    $dumpFilePath = $argv[1];
    $dumpFile = basename($dumpFilePath);
    $patchesDir = dirname($dumpFilePath);
}

$dumpArchivePath = $dumpFilePath . '.tgz';

if (is_file($dumpArchivePath)) {
    exec("tar -C $patchesDir -zxf $dumpArchivePath", $return);

    if (!empty($return)) {
        exit($return . $indent);
    }

}

if (is_file($dumpFilePath)) {
    exec("mysql $u$p$h$prt $d < $dumpFilePath", $return);

    if (empty($return)) {
        if (is_file($dumpArchivePath)) {
            unlink($dumpFilePath);
            exit($dumpArchivePath . $indent);

        } else {
            exit($dumpFilePath . $indent);
        }

    } else {
        exit($return . $indent);
    }

} else {
    exit('No file' . $indent);
}
