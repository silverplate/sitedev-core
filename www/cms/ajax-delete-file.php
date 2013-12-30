<?php

require_once 'prepend.php';
$result = 0;

if (\App\Cms\Back\User::get() && !empty($_POST['f']) && is_file($_POST['f'])) {
    \Ext\File::deleteFile($_POST['f']);
    $dir = dirname($_POST['f']);

    if (\Ext\File::isDirEmpty($dir)) {
        \Ext\File::deleteDir($dir);
    }

    $result = 1;
}

header('Content-type: text/html');
echo $result;
