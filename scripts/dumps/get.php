<?php

/**
 * Скрипт для автоматического создания дампа БД проекта. Скрипт берет настройки
 * БД от текущего сервера, создает дамп, архивирует его и размещает
 * по адресу ~/scripts/dumps/YYYY-MM-DD.sql.tgz.
 *
 * Запускать следует так: $ php get.php
 *
 * Дополнительно в скрипт можно передать путь к дампу (не архиву), если нужно
 * если назвать файл не в YYYY-MM-DD формате: $ php get.php ~/backup.sql
 * В результате получится архив ~/backup.sql.tgz.
 *
 * Полученный архив можно переписать в локальную версию проекта и загрузить
 * через запуск скрипта set.php.
 */

require_once realpath(dirname(__FILE__) . '/../../../core/library') . '/libs.php';
require_once SETS . 'project.php';

$d = App_Db::get()->getDatabase();
$u = ' -u' . App_Db::get()->getUser();
$p = ' -p' . App_Db::get()->getPassword();
$prt = App_Db::get()->getPort() ? ' -P' . App_Db::get()->getPort() : '';
$h = ' -h' . App_Db::get()->getHost();

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

$dumpArchivePath = $dumpFilePath . '.tgz';

if (!is_dir($patchesDir)) {
    Ext_File::createDir($patchesDir);
}

if (is_dir($patchesDir)) {
    exec("mysqldump $u$p$h$prt $d > $dumpFilePath", $return);

    if (empty($return)) {
        exec("tar -C $patchesDir -czf $dumpArchivePath $dumpFile", $return);

        if (empty($return)) {
            unlink($dumpFilePath);
            exit($dumpArchivePath . $indent);
        }
    }

    if (!empty($return)) {
        exit($return . $indent);
    }

} else {
    exit('Invalid path' . $indent);
}
