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
	$dumpFilePath = $argv[1];
	$patchesDir = dirname($dumpFilePath);
}

if (is_dir($patchesDir)) {
	exec("mysqldump -u{$conf['user']} -p{$conf['pass']} -h{$conf['host']} {$conf['dbname']} > $dumpFilePath", $return);

	if (empty($return)) {
	    exec("tar -C $patchesDir -czf $dumpArchivePath $dumpFile", $return);

        if (empty($return)) {
            unlink($dumpFilePath);
    		exit($dumpArchivePath . "\n");
    	}
	}

	if (!empty($return)) {
		exit($return . "\n");
	}

} else {
	exit("invalid path\n");
}
