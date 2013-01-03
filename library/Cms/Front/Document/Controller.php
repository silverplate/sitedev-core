<?php

abstract class Core_Cms_Front_Document_Controller extends Core_Cms_Front_Page
{
    /**
     * @var Core_Cms_Front_Document
     */
    protected $_document;

    public function __construct(&$_document)
    {
        parent::__construct();
        $this->_document = $_document;
    }

    public function execute()
    {
        if ($this->_document) {
            if (!$this->getTitle()) {
                $this->setTitle($this->_document->getTitle());
            }

            if ($this->_document->getLang()) {
                $this->setRootAttr('xml:lang', $this->_document->getLang());
            }

            $key = App_Cms_Front_Document::getPri();
            $where = array('is_published' => 1);
            $ancestors = App_Cms_Front_Document::getAncestors($this->_document->getId());

            if ($ancestors) {
                unset($ancestors[$this->_document->getId()]);
            }

            if ($ancestors) {
                $where[] =
                    "(($key IN (" . App_Db::escape($ancestors) . ') AND apply_type_id IN (2, 3)) OR (' .
                    "$key = {$this->_document->getSqlId()} AND apply_type_id IN (1, 3)))";

            } else {
                $where[] = "($key = {$this->_document->getSqlId()} AND apply_type_id IN (1, 3))";
            }

            if (!is_null(App_Cms_User::getAuthGroup())) {
                $where[] = '(auth_status_id = 0 OR auth_status_id & ' . App_Cms_User::getAuthGroup() . ')';
            }

            $xml = array();

            foreach (App_Cms_Front_Data::getList($where) as $item) {
                if ($item->getControllerFile()) {
                    $controller = App_Cms_Front_Data::initController(
                        $item->getController(),
                        $item,
                        $this->_document
                    );

                } else {
                    $controller = new App_Cms_Front_Data_Controller(
                        $item,
                        $this->_document
                    );
                }

                $xml[] = $controller->getXml();
            }

            $this->setContent(array_merge($xml, $this->getContent()));
        }
    }
}
