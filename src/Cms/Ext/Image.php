<?php

namespace Core\Cms\Ext;

abstract class Image extends \Ext\Image
{
    /**
     * @param string $_path
     * @param string $_pathStartsWith
     * @param string $_uriStartsWith
     * @return \App\Cms\Ext\Image
     */
    public static function factory($_path, $_pathStartsWith = null, $_uriStartsWith = null)
    {
        $file = parent::factory($_path, $_pathStartsWith, $_uriStartsWith);

        $appFile = new \App\Cms\Ext\Image(
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
