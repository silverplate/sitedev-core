<?php

require_once realpath(dirname(__FILE__) . '/../../libs') . '/libs.php';
require_once SETS . 'project.php';

$conf = parse_url(DB_CONNECTION_STRING);
$conf['dbname'] = trim($conf['path'], '/');

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
        exit($return . "\n");
    }

}

if (is_file($dumpFilePath)) {
    exec("mysql -u{$conf['user']} -p{$conf['pass']} -h{$conf['host']} {$conf['dbname']} < $dumpFilePath", $return);

	if (empty($return)) {
	    if (is_file($dumpArchivePath)) {
            unlink($dumpFilePath);
            exit($dumpArchivePath . "\n");

        } else {
            exit($dumpFilePath . "\n");
        }

	} else {
		exit($return . "\n");
	}

} else {
	exit("no file\n");
}
