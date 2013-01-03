<?php

class Core_Model extends App_ActiveRecord
{
    /**
     * @var array[App_File]
     */
    protected $_files;

    /**
     * @var array[App_Image]
     */
    protected $_images;

    public function getTitle()
    {
        if (isset($this->title) && $this->title)    return $this->title;
        else if (isset($this->name) && $this->name) return $this->name;
        else                                        return 'ID ' . $this->id;
    }

    public function getDate($_name)
    {
        return !empty($this->$_name) ? Ext_Date::getDate($this->$_name) : false;
    }

    public function getXml($_node = null, $_xml = null, array $_attrs = null)
    {
        $node = $_node ? $_node : Ext_String::dash($this->getTable());

        if (empty($_xml))         $xml = array();
        else if (is_array($_xml)) $xml = $_xml;
        else                      $xml = array($_xml);

        $attrs = empty($_attrs) ? array() : $_attrs;

        $attrs['id'] = $this->id;
        Ext_Xml::append($xml, Ext_Xml::cdata('title', $this->getTitle()));

        return Ext_Xml::node($node, $xml, $attrs);
    }

    public function getBackOfficeXml($_xml = array(), $_attrs = array())
    {
        $attrs = $_attrs;

        if (
            !isset($attrs['is_published']) && (
                ($this->hasAttribute('is_published') && $this->isPublished) ||
                ($this->hasAttribute('status_id') && $this->statusId == 1)
            )
        ) {
            $attrs['is-published'] = 1;
        }

        return $this->getXml('item', $_xml, $attrs);
    }

    public function getFiles()
    {
        if (is_null($this->_files)) {
            $this->_files = array();

            if (
                method_exists($this, 'getFilePath') &&
                $this->getFilePath() &&
                is_dir($this->getFilePath())
            ) {
                $handle = opendir($this->getFilePath());

                while (false !== $item = readdir($handle)) {
                    $filePath = $this->getFilePath() . '/' . $item;

                    if ($item{0} != '.' && is_file($filePath)) {
                        $file = App_File::factory($filePath);

                        $this->_files[
                            Ext_String::toLower($file->getFilename())
                        ] = $file;
                    }
                }

                closedir($handle);
            }
        }

        return $this->_files;
    }

    public function getFileByFilename($_filename)
    {
        $files = $this->getFiles();

        return $files && key_exists($_filename, $files)
             ? $files[$_filename]
             : false;
    }

    public function getFileByName($_name)
    {
        foreach ($this->getFiles() as $file) {
            if ($_name == $file->getName()) {
                return $file;
            }
        }

        return false;
    }

    public function getFile($_name)
    {
        $file = $this->getFileByName($_name);

        if (!$file) {
            $file = $this->getFileByFilename($_name);
        }

        return $file;
    }

    public function getImages()
    {
        if (is_null($this->_images)) {
            $this->_images = array();

            foreach ($this->getFiles() as $key => $file) {
                if ($file->isImage()) {
                    $this->_images[$key] = $file;
                }
            }
        }

        return $this->_images;
    }

    public function getIlluByFilename($_filename)
    {
        $files = $this->getImages();

        return $files && key_exists($_filename, $files)
             ? $files[$_filename]
             : false;
    }

    public function getIlluByName($_name)
    {
        foreach ($this->getImages() as $file) {
            if ($_name == $file->getName()) {
                return $file;
            }
        }

        return false;
    }

    public function getIllu($_name)
    {
        $illu = $this->getIlluByName($_name);

        if (!$illu) {
            $illu = $this->getIlluByFilename($_name);
        }

        return $illu;
    }

    public function resetFiles()
    {
        $this->_files = null;
        $this->_images = null;
    }

    public function cleanFileCache()
    {
        foreach ($this->getFiles() as $file) {
            Ext_File_Cache::delete($file->getPath());
        }
    }

    public function uploadFile($_filename, $_tmpName, $_newName = null)
    {
        $filename = is_null($_newName)
                  ? Ext_File::normalizeName($_filename)
                  : $_newName . '.' . Ext_File::computeExt($_filename);

        $path = $this->getFilePath() . $filename;

        Ext_File::deleteFile($path);
        Ext_File::createDir($this->getFilePath());

        move_uploaded_file($_tmpName, $path);
        @chmod($path, 0777);

        Ext_File_Cache::delete($path);
    }
}
