<?php

class Core_Model extends App_ActiveRecord
{
    /**
     * @var array[App_Cms_Ext_File]
     */
    protected $_files;

    /**
     * @var array[App_Cms_Ext_Image]
     */
    protected $_images;

    public function getTitle()
    {
        if (isset($this->title) && $this->title != '') {
            return $this->title;

        } else if (isset($this->name) && $this->name != '') {
            return $this->name;

        } else {
            return 'ID ' . $this->id;
        }
    }

    public function getDate($_name)
    {
        return !empty($this->$_name) ? Ext_Date::getDate($this->$_name) : false;
    }

    public function getXml($_node = null, $_xml = null, $_attrs = null)
    {
        $node = $_node ? $_node : Ext_String::dash($this->getTable());

        if (empty($_xml))         $xml = array();
        else if (is_array($_xml)) $xml = $_xml;
        else                      $xml = array($_xml);

        if (!key_exists('title', $xml)) {
            Ext_Xml::append($xml, Ext_Xml::cdata('title', $this->getTitle()));
        }

        $attrs = empty($_attrs) ? array() : $_attrs;

        if (!key_exists('id', $attrs)) {
            $attrs['id'] = $this->id;
        }

        return Ext_Xml::node($node, $xml, $attrs);
    }

    public function getBackOfficeXml($_xml = array(), $_attrs = array())
    {
        $attrs = $_attrs;

        if (
            !isset($attrs['is_published']) && (
                ($this->hasAttr('is_published') && $this->isPublished) ||
                ($this->hasAttr('status_id') && $this->statusId == 1)
            )
        ) {
            $attrs['is-published'] = 1;
        }

        return self::getXml('item', $_xml, $attrs);
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
                    $filePath = rtrim($this->getFilePath(), '/') . '/' . $item;

                    if ($item{0} != '.' && is_file($filePath)) {
                        $file = App_Cms_Ext_File::factory($filePath);

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
                if (
                    Ext_File::isImageExt($file->getExt()) &&
                    $file->getSize() > 0 &&
                    Ext_File::isImageExt(str_replace('image/', '', $file->getMime()))
                ) {
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
        Ext_File::chmod($path, 0777);
        Ext_File_Cache::delete($path);
    }
}
