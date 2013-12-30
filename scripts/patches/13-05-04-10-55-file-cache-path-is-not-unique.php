<?php

require_once realpath(dirname(__FILE__) . '/../../../core/src') . '/libs.php';
initSettings();

fileCachePathIsNotUniquePatch();


function fileCachePathIsNotUniquePatch()
{
    $nl = PHP_EOL;
    $tbl = \Ext\File\Cache::getDataTable();
    $key = $tbl . '_file_path_unq';

    if (\Ext\Db::get()->getEntry("SHOW INDEX FROM `$tbl` WHERE Key_name = '$key'")) {
        $res = \Ext\Db::get()->execute("ALTER TABLE `$tbl` DROP INDEX `{$tbl}_file_path_unq`");
        echo $res ? 'Готово' : 'Ошибка';

    } else {
        echo 'Уже';
    }

    echo $nl . $nl;
}
