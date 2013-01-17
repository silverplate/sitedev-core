<?php

abstract class Core_Cms_Back_Office
{
    /**
     * @var string
     */
    public static $uriStartsWith = '/cms/';

    public static function bootstrap()
    {
        header('Expires: Fri, 9 Feb 1980 00:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        self::auth();
    }

    /**
     * Авторизация в бэк-офисе системы управления.
     */
    public static function auth()
    {
        if (
            key_exists('auth_submit', $_POST) &&
            key_exists('auth_login', $_POST) &&
            key_exists('auth_password', $_POST)
        ) {
            $user = App_Cms_Back_User::auth(
                $_POST['auth_login'],
                $_POST['auth_password']
            );

            if ($user) {
                App_Cms_Session::get()->login(
                    $user->getId(),
                    !empty($_POST['auth_is_ip_match']),
                    empty($_POST['auth_life_span']) ? null : (int) $_POST['auth_life_span'],
                    empty($_POST['auth_timeout']) ? null : (int) $_POST['auth_timeout'],
                    empty($_POST['auth_is_remember_me']) ? null : time() + 60 * 60 * 24 * 356
                );

                App_Cms_Session::get()->setParam(
                    App_Cms_Session::ACT_PARAM_NEXT,
                    App_Cms_Session::ACT_LOGIN
                );

                App_Cms_Back_Log::log(
                    App_Cms_Back_Log::ACT_LOGIN,
                    array('user' => $user)
                );

            } else {
                App_Cms_Session::get()->setParam(
                    App_Cms_Session::ACT_PARAM_NEXT,
                    App_Cms_Session::ACT_LOGIN_ERROR
                );
            }

            if (empty($_GET['id'])) reload();
            else                    reload('?id=' . $_GET['id']);

        } else if (key_exists('auth_reminder_submit', $_POST)) {
            $users = empty($_POST['auth_email']) ? false : App_Cms_Back_User::getList(
                array('email' => $_POST['auth_email'], 'status_id' => 1),
                array('limit' => 1)
            );

            if ($users) {
                $user = current($users);
                App_Cms_Session::get()->setParam(
                    App_Cms_Session::ACT_PARAM_NEXT,
                    App_Cms_Session::ACT_REMIND_PWD
                );

                App_Cms_Back_Log::log(
                    App_Cms_Back_Log::ACT_REMIND_PWD,
                    array('user' => $user)
                );

                $user->remindPassword();

            } else {
                App_Cms_Session::get()->setParam(
                    App_Cms_Session::ACT_PARAM_NEXT,
                    App_Cms_Session::ACT_REMIND_PWD_ERROR
                );
            }

            reload();

        } else if (
            key_exists('r', $_GET) ||
           (key_exists('e', $_GET) && App_Cms_Session::get()->isLoggedIn())
        ) {
            if (App_Cms_Session::get()->isLoggedIn()) {
                App_Cms_Back_Log::log(
                    App_Cms_Back_Log::ACT_LOGOUT,
                    array('user' => App_Cms_Back_User::getById(
                        App_Cms_Session::get()->getUserId()
                    ))
                );
                App_Cms_Session::get()->logout();
            }

            if (key_exists('r', $_GET)) {
                $user = empty($_GET['r'])
                      ? false
                      : App_Cms_Back_User::load($_GET['r'], 'reminder_key');

                if ($user && $user->changePassword() === 0) {
                    App_Cms_Session::get()->setParam(
                        App_Cms_Session::ACT_PARAM_NEXT,
                        App_Cms_Session::ACT_CHANGE_PWD
                    );

                    App_Cms_Back_Log::log(
                        App_Cms_Back_Log::ACT_CHANGE_PWD,
                        array('user' => $user)
                    );

                } else {
                    App_Cms_Session::get()->setParam(
                        App_Cms_Session::ACT_PARAM_NEXT,
                        App_Cms_Session::ACT_CHANGE_PWD_ERROR
                    );
                }

            } else {
                App_Cms_Session::get()->setParam(
                    App_Cms_Session::ACT_PARAM_NEXT,
                    App_Cms_Session::ACT_LOGOUT
                );
            }

            reload();

        } else {
            $param = App_Cms_Session::get()->getParam(
                App_Cms_Session::ACT_PARAM_NEXT
            );

            App_Cms_Session::get()->setParam(
                App_Cms_Session::ACT_PARAM,
                $param ? $param : App_Cms_Session::ACT_START
            );

            App_Cms_Session::get()->setParam(
                App_Cms_Session::ACT_PARAM_NEXT,
                App_Cms_Session::ACT_CONTINUE
            );
        }
    }

    /**
     * Сортировка списка.
     *
     * @param string $_class Название класса объекты, которого нужно сортировать.
     */
    public static function ajaxSort($_class)
    {
        if (!empty($_POST['items'])) {
            $key = $_class::getPri();
            $newSortOrder = array();

            for ($i = 0; $i < count($_POST['items']); $i++) {
                $newSortOrder[$_POST['items'][$i]] = $i;
            }

            $currentSortOrder = array();
            $objects = $_class::getList(array($key => $_POST['items']));

            foreach ($objects as $item) {
                $currentSortOrder[] = $item->sortOrder;
            }

            foreach ($objects as $item) {
                $newItemSortOrder = $currentSortOrder[$newSortOrder[$item->getId()]];

                if ($newItemSortOrder) {
                    $item->updateAttr('sort_order', $newItemSortOrder);
                }
            }

            $_class::clearApcList();

            App_Cms_Back_Log::logModule(
                App_Cms_Back_Log::ACT_MODIFY,
                null,
                'Сортировка'
            );
        }
    }

    public static function ajaxTreeOutput($_className)
    {
        global $gOpenBranches;

        $data = $_POST;
        $parentId = empty($data['parent_id']) ? null : $data['parent_id'];

        $selectedIds = isset($data['selected_ids']) &&
                       is_array($data['selected_ids'])
                     ? $data['selected_ids']
                     : array();

        $currentId = isset($data['current_object_id'])
                   ? $data['current_object_id']
                   : '';

        $page = new App_Cms_Page();
        $page->setTemplate(TEMPLATES . 'back/http-requests.xsl');
        $page->setRootName('http-request');

        if (isset($data['type'])) {
            $page->setRootAttr('type', 'tree_' . $data['type']);
        }

        if (isset($data['module_name'])) {
            $page->setRootAttr('module-name', $data['module_name']);
        }

        if (isset($data['field_name'])) {
            $page->setRootAttr('field-name', $data['field_name']);
        }

        if ($parentId) {
            $page->setRootAttr('parent-id', $parentId);
        }

        if ($currentId) {
            $page->setRootAttr('current-object-id', $currentId);
        }

        foreach ($selectedIds as $i) {
            $page->addContent(Ext_Xml::cdata('selected', $i));
        }

        $gOpenBranches = $selectedIds
                       ? $_className::getMultiAncestors($selectedIds)
                       : array();

        $cookieBranchName = 'back-tree';

        if (isset($data['module_name'])) {
            $cookieBranchName .= '-' . $data['module_name'];
        }

        if (isset($data['field_name'])) {
            $cookieBranchName .= '-' . $data['field_name'];
        }

        if (!empty($_COOKIE[$cookieBranchName])) {
            foreach (explode('|', $_COOKIE[$cookieBranchName]) as $item) {
                if (!in_array($item, $gOpenBranches)) {
                    $gOpenBranches[] = $item;
                }
            }
        }

        if (isset($data['type']) && $data['type'] == 'single' && !$parentId) {
            $obj = new $_className;
            $obj->isPublished = 1;

            if ('integer' == $obj->getPrimaryKey()->getType()) {
                $obj->id = 0;
            }

            if ($obj->hasAttr('title')) {
                $obj->title = 'Нет';

            } else if ($obj->hasAttr('name')) {
                $obj->name = 'Нет';
            }

            $xml = $obj->getBackOfficeXml(
                self::ajaxGetBranchXml($_className, $parentId, $currentId)
            );

        } else {
            $xml = self::ajaxGetBranchXml($_className, $parentId, $currentId);
        }

        $page->addContent($xml);

        header('Content-type: text/html; charset=utf-8');
        $page->output();
    }

    public static function ajaxGetBranchXml($_className, $_parentId, $_excludeId)
    {
        global $gOpenBranches;

        $result = '';
        $where = array('parent_id' => empty($_parentId) ? null : $_parentId);

        if ($_excludeId) {
            $where[] = $_className::getPri() . ' != ' . Ext_Db::escape($_excludeId);
        }

        $list = $_className::getList($where);

        foreach ($list as $item) {
            if (
                $item->isChildren($_excludeId) &&
                in_array($item->id, $gOpenBranches)
            ) {
                $result .= $item->getBackOfficeXml(
                    self::ajaxGetBranchXml($_className, $item->id, $_excludeId)
                );

            } else if ($item->isChildren($_excludeId)) {
                $result .= $item->getBackOfficeXml(null, array('has-children' => 'true'));

            } else {
                $result .= $item->getBackOfficeXml();
            }
        }

        return $result;
    }
}
