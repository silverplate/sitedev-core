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
    $patchesDir = realpath(WD . 'scripts/dumps');
    $dumpFile = date('Y-m-d') . '.sql';
    $dumpFilePath = $patchesDir . '/' . $dumpFile;
    $dumpArchivePath = $dumpFilePath . '.tgz';

} else {
    $dumpFilePath = realpath($argv[1]);
    $patchesDir = dirname($dumpFilePath);
}

if (is_file($dumpArchivePath)) {
    exec("tar -C $patchesDir -zxf $dumpArchivePath", $return);

    if (!empty($return)) {
        exit($return . $indent);
    }

}

if (is_file($dumpFilePath)) {
    exec("mysql $u$p$h$prt $d < $dumpFilePath", $return);

    if (empty($return)) {
        if (is_file($dumpArchivePath)) {
            unlink($dumpFilePath);
            exit($dumpArchivePath . $indent);

        } else {
            exit($dumpFilePath . $indent);
        }

    } else {
        exit($return . $indent);
    }

} else {
    exit('No file' . $indent);
}
