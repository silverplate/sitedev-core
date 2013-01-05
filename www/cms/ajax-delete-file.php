<?php

require 'prepend.php';
$result = 0;

if (App_Cms_Back_User::get() && !empty($_POST['f']) && is_file($_POST['f'])) {
    Ext_File::deleteFile($_POST['f']);
    $dir = dirname($_POST['f']);

    if (Ext_File::isDirEmpty($dir)) {
        Ext_File::deleteDir($dir);
    }

	$result = 1;
}

header('Content-type: text/html');
echo $result;
