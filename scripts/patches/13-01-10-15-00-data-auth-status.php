<?php

require_once realpath(dirname(__FILE__) . '/../../library') . '/libs.php';
initSettings();

$nl = PHP_EOL;

$res = Ext_Db::get()->execute('
    ALTER TABLE `' . App_Cms_Front_Data::getTbl() . '`
    MODIFY `auth_status_id` SMALLINT UNSIGNED NULL
');

echo $res ? 'Готово' : 'Ошибка';
echo $nl . $nl;
