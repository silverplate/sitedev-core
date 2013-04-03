<?php

abstract class Core_Cms_Ext_Image extends Ext_Image
{
    /**
     * @param string $_path
     * @param string $_pathStartsWith
     * @param string $_uriStartsWith
     * @return App_Cms_Ext_Image
     */
    public static function factory($_path, $_pathStartsWith = null, $_uriStartsWith = null)
    {
        $file = parent::factory($_path, $_pathStartsWith, $_uriStartsWith);

        $appFile = new App_Cms_Ext_Image(
            $file->getPath(),
            $file->getPathStartsWith(),
            $file->getUriStartsWith()
        );

        $appFile->setSize($file->getSize());
        $appFile->setWidth($file->getWidth());
        $appFile->setHeight($file->getHeight());
        $appFile->setMime($file->getMime());

        return $appFile;
    }

    public static function concatUrl($_uri = null, $_host = null)
    {
        global $gHost;
        return parent::concatUrl($_uri, is_null($_host) ? $gHost : $_host);
    }
}