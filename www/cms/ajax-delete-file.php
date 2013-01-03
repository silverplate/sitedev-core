<?php

require 'prepend.php';
$result = 0;

global $g_user;

if (!empty($g_user) && isset($_POST['f']) && is_file($_POST['f'])) {
    Ext_File::deleteFile($_POST['f']);

    $dir = dirname($_POST['f']);

    if (Ext_File::isDirEmpty($dir)) {
        Ext_File::deleteDir($dir);
    }

	$result = 1;
}

header('Content-type: text/html');
echo $result;
