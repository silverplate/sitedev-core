<?php

namespace Core\Cms\Front\Document;

abstract class Controller extends \Core\Cms\Front\Page
{
    /**
     * @var \Core\Cms\Front\Document
     */
    protected $_document;

    /**
     * @var string
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_requestPieces;

    public function __construct(&$_document)
    {
        parent::__construct();
        $this->_document = $_document;

        $this->_request = trim(str_replace(
            $this->_document->getUri(),
            '/',
            $this->getUrl('path')
        ), '/');

        $this->_requestPieces = $this->_request
                              ? explode('/', $this->_request)
                              : array();
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

            $key = \App\Cms\Front\Document::getPri();
            $where = array('is_published' => 1);
            $ancestors = \App\Cms\Front\Document::getAncestors($this->_document->getId());

            if ($ancestors) {
                $ancestors = array_values(array_diff(
                    $ancestors,
                    array($this->_document->getId())
                ));
            }

            if ($ancestors) {
                $where[] =
                    "(($key IN (" . \Ext\Db::escape($ancestors) . ') AND apply_type_id IN (2, 3)) OR (' .
                    "$key = {$this->_document->getSqlId()} AND apply_type_id IN (1, 3)))";

            } else {
                $where[] = "($key = {$this->_document->getSqlId()} AND apply_type_id IN (1, 3))";
            }

            if (!is_null(\App\Cms\User::getAuthGroup())) {
                $where[] = '(ISNULL(auth_status_id) OR auth_status_id = 0 OR auth_status_id & ' . \App\Cms\User::getAuthGroup() . ')';
            }

            $xml = array();

            foreach (\App\Cms\Front\Data::getList($where) as $item) {
                if ($item->getControllerFile()) {
                    $controller = \App\Cms\Front\Data::initController(
                        $item->getController(),
                        $item,
                        $this->_document
                    );

                } else {
                    $controller = new \App\Cms\Front\Data\Controller(
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
