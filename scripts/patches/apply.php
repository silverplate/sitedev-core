<?php

require_once realpath(dirname(__FILE__) . '/../../library') . '/libs.php';
initSettings();

applyPatches();


function applyPatches()
{
    $nl = PHP_EOL;
    $folders = array(CORE_PATH . 'scripts/patches/', WD . 'scripts/patches/');
    $patches = array();

    foreach ($folders as $folder) {
        if (!is_dir($folder)) continue;
        $handle = opendir($folder);
        $entry = readdir($handle);

        while ($entry !== false) {
            if ($entry != '.' && $entry != '..') {
                $matches = array();

                preg_match(
                    '/^([0-9]{2,4}-[0-9]{2}-[0-9]{2})-([0-9]{2}-[0-9]{2})-([a-z0-9-]+)\.php$/',
                    $entry,
                    $matches
                );

                if ($matches && is_file($folder . $entry)) {
                    $file = new stdClass();
                    $file->path = $folder . $entry;
                    $file->filename = strtolower($entry);
                    $file->time = strtotime($matches[1] . ' ' . str_replace('-', ':', $matches[2]));
                    $patches[] = $file;
                }
            }

            $entry = readdir($handle);
        }

        closedir($handle);
    }

    if (count($patches) > 0) {
        usort($patches, 'sortPatches');
        $tbl = Ext_Db::get()->getPrefix() . 'patch';
        $exist = Ext_Db::get()->getList('SELECT filename FROM ' . $tbl);

        foreach ($patches as $patch) {
            if (!$exist || !in_array($patch->filename, $exist)) {
                echo $patch->filename;
                echo $nl;

                include_once($patch->path);

                Ext_Db::get()->execute('INSERT INTO ' . $tbl . Ext_Db::get()->getQueryFields(array(
                    'creation_time' => time(),
                    'filename' => $patch->filename
                ), 'insert'));
            }
        }

        echo $nl;
    }
}

function sortPatches(stdClass $_a, stdClass $_b)
{
    $a = $_a->time;
    $b = $_b->time;

    if ($a == $b)     return 0;
    else if ($a < $b) return -1;
    else              return 1;
}
