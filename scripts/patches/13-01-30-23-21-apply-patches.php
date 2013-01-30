<?php

require_once realpath(dirname(__FILE__) . '/../../library') . '/libs.php';
initSettings();

$nl = PHP_EOL;
$prfx = Ext_Db::get()->getPrefix();

$res = Ext_Db::get()->execute("
    CREATE TABLE IF NOT EXISTS `{$prfx}patch` (
        `{$prfx}patch_id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `filename` VARCHAR(255) NOT NULL,
        `creation_time` INT UNSIGNED NOT NULL,
        PRIMARY KEY (`{$prfx}patch_id`),
        UNIQUE INDEX `{$prfx}path_filename_unq` (`filename` ASC)
    ) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8
");

echo $res ? 'Готово' : 'Ошибка';
echo $nl . $nl;
