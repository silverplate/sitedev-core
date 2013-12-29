<?php

abstract class Core_Cms_Cache
{
    /**
     * @var array[Core_Cms_Cache_Section]
     */
    protected $_sections = array();

    /**
     * @var Core_Cms_Cache_Section
     */
    protected $_section;

    protected $_isAble;
    protected $_isQueryImportant;
    protected $_time;
    protected $_path;
    protected $_category;
    protected $_queryIgnore;
    protected $_uri;
    protected $_file;
    protected $_sectionTime;
    protected $_isSectionQueryImportant;

    public function __construct($_path, $_category = null, $_uri = null)
    {
        $this->_isAble = true;
        $this->_isQueryImportant = false;
        $this->_time = 30;
        $this->_path = rtrim($_path, '/') . '/';
        $this->_category = $_category;
        $this->_queryIgnore = array('delete-cache', 'no-cache');
        $this->getUri($_uri);

        if (key_exists('delete-cache', $_GET)) {
            $this->deletePage();
        }
    }

    public function getUri($_uri = null)
    {
        if (is_null($this->_uri)) {
            $this->_uri = \Ext\File::parseUrl();
            $this->_uri['path_info'] = pathinfo($this->_uri['path']);

            if (!empty($this->_uri['query']) && $this->_queryIgnore) {
                $query = '';

                foreach (explode('&', $this->_uri['query']) as $item) {
                    $pair = explode('=', $item);

                    if (!in_array($pair[0], $this->_queryIgnore)) {
                        $query .= ('' == $query ? '' : '&') . $item;
                    }
                }

                $this->_uri['query'] = $query;
            }
        }

        return $this->_uri;
    }

    public function getRequestPath()
    {
        $request = $this->getUri();
        return $request['path'];
    }

    public function getRequestQuery()
    {
        $request = $this->getUri();
        return empty($request['query']) ? false : $request['query'];
    }

    public function setSection(Core_Cms_Cache_Section &$_obj)
    {
        $this->_sections[$_obj->getUri()] = $_obj;
    }

    /**
     * @return Core_Cms_Cache_Section
     */
    public function getSection()
    {
        if (is_null($this->_section)) {
            if ($this->_sections) {
                if (isset($this->_sections[$this->getRequestPath()])) {
                    $this->_section = $this->_sections[$this->getRequestPath()];

                } else {
                    foreach ($this->_sections as $item) {
                        if (
                            $item->IsWhole() &&
                            strpos($this->getRequestPath(), $item->getUri()) === 0
                        ) {
                            $this->_section = $item;
                        }
                    }
                }
            }

            if (is_null($this->_section)) {
                $this->_section = false;
            }
        }

        return $this->_section;
    }

    public function getSectionTime()
    {
        if (is_null($this->_sectionTime)) {
            $this->_sectionTime = $this->getSection()
                                ? $this->getSection()->getTime()
                                : $this->_time;
        }

        return $this->_sectionTime;
    }

    public function getSectionQueryImportant()
    {
        if (is_null($this->_isSectionQueryImportant)) {
            $this->_isSectionQueryImportant = $this->getSection()
                                            ? $this->getSection()->isQueryImportant()
                                            : $this->_isQueryImportant;
        }

        return $this->_isSectionQueryImportant;
    }

    public function isAvailable()
    {
        return $this->_isAble &&
               !$_POST &&
               !array_intersect(array_keys($_GET), $this->_queryIgnore) &&
               $this->getSectionTime();
    }

    public function getFile()
    {
        if (is_null($this->_file)) {
            $this->_file = $this->_path;
            if ($this->_category) {
                $this->_file .= 'g_' . $this->_category . '/';
            }

            $path = pathinfo($this->getRequestPath());

            if (
                isset($path['basename']) &&
                $path['basename'] == 'index.html'
            ) {
                $this->_file .= $path['dirname'] . '/';

            } else if (
                isset($path['basename']) &&
                isset($path['extension'])
            ) {
                $this->_file .= $path['dirname'] . '/' .
                                \Ext\File::computeName($path['basename']) . '/';
            } else {
                $this->_file .= $this->getRequestPath();
            }

            if (
                $this->getSectionQueryImportant() &&
                $this->getRequestQuery()
            ) {
                $query = str_replace(
                    array('&', '=', '[', ']', '"', '\''),
                    '-',
                    \Ext\String::translit(urldecode($this->getRequestQuery()))
                );

            } else {
                $query = false;
            }

            $this->_file  = str_replace('//', '/', $this->_file);
            $this->_file .=  $query && $this->getSectionQueryImportant()
                          ? "-q-$query.html"
                          : 'index.html';
        }

        return $this->_file;
    }

    public function isCache()
    {
        return is_file($this->getFile()) &&
               time() - filemtime($this->getFile()) < $this->getSectionTime() * 60;
    }

    public function __toString()
    {
        return self::isCache() ? file_get_contents($this->getFile()) : false;
    }

    public function set($_content)
    {
        \Ext\File::createDir(dirname($this->getFile()));
        \Ext\File::write($this->getFile(), $_content);
    }

    public function deletePage()
    {
        if (is_file($this->getFile())) {
            unlink($this->getFile());
            $path = dirname($this->getFile());

            if (\Ext\File::isDirEmpty($path)) {
                \Ext\File::deleteDir($path);
            }
        }
    }

    public function emptyPage()
    {
        return \Ext\File::deleteDir(dirname($this->getFile()), false, true);
    }

    public function emptyCache()
    {
        return \Ext\File::deleteDir($this->_path, false);
    }
}
