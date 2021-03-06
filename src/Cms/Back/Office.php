<?php

namespace Core\Cms\Back;

abstract class Office
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
            $user = \App\Cms\Back\User::auth(
                $_POST['auth_login'],
                $_POST['auth_password']
            );

            if ($user) {
                \App\Cms\Session::get()->login(
                    $user->getId(),
                    !empty($_POST['auth_is_ip_match']),
                    empty($_POST['auth_life_span']) ? null : (int) $_POST['auth_life_span'],
                    empty($_POST['auth_timeout']) ? null : (int) $_POST['auth_timeout'],
                    empty($_POST['auth_is_remember_me']) ? null : time() + 60 * 60 * 24 * 356
                );

                \App\Cms\Session::get()->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    \App\Cms\Session::ACT_LOGIN
                );

                \App\Cms\Back\Log::log(
                    \App\Cms\Back\Log::ACT_LOGIN,
                    array('user' => $user)
                );

            } else {
                \App\Cms\Session::get()->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    \App\Cms\Session::ACT_LOGIN_ERROR
                );
            }

            if (empty($_GET['id'])) reload();
            else                    reload('?id=' . $_GET['id']);

        } else if (key_exists('auth_reminder_submit', $_POST)) {
            $users = empty($_POST['auth_email']) ? false : \App\Cms\Back\User::getList(
                array('email' => $_POST['auth_email'], 'status_id' => 1),
                array('limit' => 1)
            );

            if ($users) {
                $user = current($users);
                \App\Cms\Session::get()->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    \App\Cms\Session::ACT_REMIND_PWD
                );

                \App\Cms\Back\Log::log(
                    \App\Cms\Back\Log::ACT_REMIND_PWD,
                    array('user' => $user)
                );

                $user->remindPassword();

            } else {
                \App\Cms\Session::get()->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    \App\Cms\Session::ACT_REMIND_PWD_ERROR
                );
            }

            reload();

        } else if (
            key_exists('r', $_GET) ||
           (key_exists('e', $_GET) && \App\Cms\Session::get()->isLoggedIn())
        ) {
            if (\App\Cms\Session::get()->isLoggedIn()) {
                \App\Cms\Back\Log::log(
                    \App\Cms\Back\Log::ACT_LOGOUT,
                    array('user' => \App\Cms\Back\User::getById(
                        \App\Cms\Session::get()->getUserId()
                    ))
                );
                \App\Cms\Session::get()->logout();
            }

            if (key_exists('r', $_GET)) {
                $user = empty($_GET['r'])
                      ? false
                      : \App\Cms\Back\User::load($_GET['r'], 'reminder_key');

                if ($user && $user->changePassword() === 0) {
                    \App\Cms\Session::get()->setParam(
                        \App\Cms\Session::ACT_PARAM_NEXT,
                        \App\Cms\Session::ACT_CHANGE_PWD
                    );

                    \App\Cms\Back\Log::log(
                        \App\Cms\Back\Log::ACT_CHANGE_PWD,
                        array('user' => $user)
                    );

                } else {
                    \App\Cms\Session::get()->setParam(
                        \App\Cms\Session::ACT_PARAM_NEXT,
                        \App\Cms\Session::ACT_CHANGE_PWD_ERROR
                    );
                }

            } else {
                \App\Cms\Session::get()->setParam(
                    \App\Cms\Session::ACT_PARAM_NEXT,
                    \App\Cms\Session::ACT_LOGOUT
                );
            }

            reload();

        } else {
            $param = \App\Cms\Session::get()->getParam(
                \App\Cms\Session::ACT_PARAM_NEXT
            );

            \App\Cms\Session::get()->setParam(
                \App\Cms\Session::ACT_PARAM,
                $param ? $param : \App\Cms\Session::ACT_START
            );

            \App\Cms\Session::get()->setParam(
                \App\Cms\Session::ACT_PARAM_NEXT,
                \App\Cms\Session::ACT_CONTINUE
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

            \App\Cms\Back\Log::logModule(
                \App\Cms\Back\Log::ACT_MODIFY,
                null,
                'Сортировка'
            );
        }
    }

    public static function ajaxTreeOutput($_className, $_isSelfExclude = true)
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

        $page = new \App\Cms\Page();
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
            $page->addContent(\Ext\Xml::cdata('selected', $i));
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

            if ($obj->hasAttr('title')) {
                $obj->title = 'Нет';

            } else if ($obj->hasAttr('name')) {
                $obj->name = 'Нет';
            }

            $xml = $obj->getBackOfficeXml(
                self::ajaxGetBranchXml(
                    $_className,
                    $parentId,
                    $_isSelfExclude ? $currentId : null
                )
            );

        } else {
            $xml = self::ajaxGetBranchXml(
                $_className,
                $parentId,
                $currentId,
                $_isSelfExclude ? $currentId : null
            );
        }

        $page->addContent($xml);

        header('Content-type: text/html; charset=utf-8');
        $page->output();
    }

    public static function ajaxGetBranchXml($_className,
                                            $_parentId,
                                            $_excludeId = null)
    {
        global $gOpenBranches;

        $result = '';
        $where = array('parent_id' => empty($_parentId) ? null : $_parentId);

        if ($_excludeId) {
            $where[] = $_className::getPri() . ' != ' . \Ext\Db::escape($_excludeId);
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

    /**
     * Сортировка и перемещение элементов в дереве. В отличие от реализации
     * в разделе управления страницами (~/core/cms/cms-pages/ajax-tree-sort.php)
     * метод не проверяет уникальность служебного имени (name) при перемещении.
     *
     * @param string $_class
     * @return boolean
     */
    public static function ajaxTreeSort($_class)
    {
        $data = $_POST;

        if (!empty($data['branches'])) {
            $changed = array();
            $parent = array();
            $objects = $_class::getList();

            foreach ($data['branches'] as $i) {
                $order = array();
                $newOrder = array();

                for ($j = 0; $j < count($data['branch_' . $i]); $j++) {
                    $id = $data['branch_' . $i][$j];
                    if (!isset($objects[$id])) return false;

                    $parent[$id] = $i;
                    $newOrder[$id] = $j + 1;
                }

                $k = 0;

                foreach ($objects as $j) {
                    if (in_array($j->getId(), $data['branch_' . $i])) {
                        $order[++$k] = $j->sortOrder;
                    }
                }

                for ($j = 0; $j < count($data['branch_' . $i]); $j++) {
                    $id = $data['branch_' . $i][$j];
                    $objects[$id]->sortOrder = $order[$newOrder[$id]];
                    $changed[] = $id;
                }
            }

            foreach ($objects as $i) {
                $id = $i->id;

                if (isset($parent[$id])) {
                    $objects[$id]->parentId = empty($parent[$id]) ? null : $parent[$id];
                    $changed[] = $id;
                }
            }

            foreach (array_unique($changed) as $i) {
                $objects[$i]->update();
            }

            \App\Cms\Back\Log::LogModule(
                \App\Cms\Back\Log::ACT_MODIFY,
                null,
                'Сортировка'
            );

            return !empty($changed);
        }

        return false;
    }
}
