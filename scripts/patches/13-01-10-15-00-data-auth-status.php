<?php

require_once realpath(dirname(__FILE__) . '/../../library') . '/libs.php';
require_once SETS . 'project.php';

$nl = PHP_EOL;

$res = App_Db::get()->execute('
    ALTER TABLE `' . DB_PREFIX . 'front_data`
    MODIFY `auth_status_id` SMALLINT UNSIGNED NULL
');

echo $res ? 'Готово' : 'Ошибка';
echo $nl . $nl;
