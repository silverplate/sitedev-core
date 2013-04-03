<?php

abstract class Core_Cms_Back_Office_NavFilter
{
    protected $_class;
    protected $_page;
    protected $_perPage;
    protected $_selectedId;
    protected $_isOpen = false;
    protected $_isSortable = false;

    /**
     * @var array[App_Cms_Back_Office_NavFilter_Element]
     */
    protected $_elements;

    public function __construct($_class)
    {
        $this->_class = $_class;
        $this->setPerPage(20);
    }

    public function setPage($_page)
    {
        $this->_page = (int) $_page;

        if ($this->_page == 0) {
            $this->_page = 1;
        }
    }

    public function getPage()
    {
        return $this->_page;
    }

    public function setPerPage($_perPage)
    {
        $this->_perPage = (int) $_perPage;
    }

    public function getPerPage()
    {
        return $this->_perPage;
    }

    public function getOffset()
    {
        return ($this->getPage() - 1) * $this->getPerPage();
    }

    public function setSelectedId($_id = null)
    {
        $this->_selectedId = $_id;
    }

    public function getSelectedId()
    {
        return $this->_selectedId;
    }

    public function isOpen($_isOpen = null)
    {
        if ($_isOpen !== null) {
            $this->_isOpen = (boolean) $_isOpen;
        }

        return $this->_isOpen;
    }

    public function isSortable($_isSortable = null)
    {
        if ($_isSortable !== null) {
            $this->_isSortable = (boolean) $_isSortable;
        }

        return $this->_isSortable;
    }

    public function run()
    {
        if (!empty($_POST['filter_selected_id'])) {
            $this->setSelectedId($_POST['filter_selected_id']);
        }

        if (!empty($_COOKIE['filter-is-open'])) {
            $this->isOpen(true);
        }

        if (isset($_POST['page'])) {
            $this->setPage($_POST['page']);

        } else if (isset($_COOKIE['filter-page'])) {
            $this->setPage($_COOKIE['filter-page']);

        } else {
            $this->setPage(1);
        }

        foreach ($this->_elements as $el) {
            $el->run();
        }
    }

    public function getSqlWhere()
    {
        $where = array();

        foreach ($this->_elements as $el) {
            $where = array_merge($where, $el->getSqlWhere());
        }

        return $where;
    }

    public function getSqlParams()
    {
        $params = array(
            'limit' => $this->getPerPage(),
            'offset' => $this->getOffset()
        );

        if ($this->isSortable()) {
            $params['limit']++;

            if ($params['offset'] > 0) {
                $params['offset']--;
                $params['limit']++;
            }
        }

        return $params;
    }

    public function filter()
    {
        $where = $this->getSqlWhere();
        $params = $this->getSqlParams();

        $result = array(
            'items' => call_user_func_array(
                array($this->_class, 'getList'),
                array($where, $params)
            ),
            'total' => call_user_func_array(
                array($this->_class, 'getCount'),
                array($where)
            ),
            'only_sort_items' => array()
        );

        if (
            count($result['items']) == 0 &&
            $result['total'] > 0 &&
            $this->getPage() != 1
        ) {
            $this->setPage(1);
            $this->filter();
        }

        if (
            $this->isSortable() && (
                $this->getPage() > 1 ||
                count($result['items']) > $this->getPerPage()
            )
        ) {
            if (count($result['items']) - 2 == $this->getPerPage()) {
                $isFirst = true;
                $isLast = true;

            } else if ($this->getOffset() != $params['offset']) {
                $isFirst = true;
                $isLast = false;

            } else {
                $isFirst = false;
                $isLast = true;
            }

            reset($result['items']);

            if ($isFirst) {
                $result['only_sort_items'][] = current($result['items'])->id;
            }

            if ($isLast) {
                $result['only_sort_items'][] = end($result['items'])->id;
                reset($result['items']);
            }
        }

        return $result;
    }

    public function addElement(Core_Cms_Back_Office_NavFilter_Element $_el)
    {
        $this->_elements[] = $_el;
    }

    public function getXml()
    {
        $xml = '';

        foreach ($this->_elements as $el) {
            Ext_Xml::append($xml, $el->getXml());
        }

        return Ext_Xml::node(
            'local-navigation',
            $xml,
            array(
                'type' => 'filter',
                'is-open' => $this->isOpen(),
                'is-sortable' => $this->isSortable()
            )
        );
    }

    public function output()
    {
        $result = $this->filter();

        $page = new App_Cms_Page();
        $page->setRootName('http-request');
        $page->setRootAttr('type', 'filter');
        $page->setTemplate(TEMPLATES . 'back/http-requests.xsl');

        if ($this->getSelectedId()) {
            $page->setRootAttr('selected-id', $this->getSelectedId());
        }

        if ($result['items']) {
            foreach ($result['items'] as $item) {
                $page->addContent($item->getBackOfficeXml());
            }

            $page->addContent(Ext_Xml::node('list-navigation', null, array(
                'page' => $this->getPage(),
                'per-page' => $this->getPerPage(),
                'total' => $result['total']
            )));
        }

        header('Content-type: text/html; charset=utf-8');
        $page->output();
    }
}