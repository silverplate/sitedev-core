<?php

require_once realpath(dirname(__FILE__) . '/../../library') . '/libs.php';
initSettings();

$nl = PHP_EOL;

$res = Ext_Db::get()->execute('
    ALTER TABLE `' . App_Cms_User::getTbl() . '`
    MODIFY `passwd` CHAR(60) NOT NULL
');

echo $res ? 'Готово' : 'Ошибка';
echo $nl;

$res = Ext_Db::get()->execute('
    ALTER TABLE `' . App_Cms_Back_User::getTbl() . '`
    MODIFY `passwd` CHAR(60) NOT NULL
');

echo $res ? 'Готово' : 'Ошибка';
echo $nl . $nl;
