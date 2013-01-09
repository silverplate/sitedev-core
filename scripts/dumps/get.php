<?php

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
    $dumpArchivePath = $dumpFilePath . '.tgz';

} else {
    $dumpFilePath = realpath($argv[1]);
    $patchesDir = dirname($dumpFilePath);
}

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
