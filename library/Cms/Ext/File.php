<?php

abstract class Core_Cms_Ext_File extends Ext_File
{
    public static function getUrl($_uri = null, $_host = null)
    {
        global $gHost;
        return parent::concatUrl($_uri, is_null($_host) ? $gHost : $_host);
    }
}
