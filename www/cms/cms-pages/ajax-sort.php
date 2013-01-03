<?php

require('../prepend.php');

$data = $_POST;

if (isset($data['items']) && $data['items']) {
    $new_sort_order = array();
    for ($i = 0; $i < count($data['items']); $i++) {
        $new_sort_order[$data['items'][$i]] = $i;
    }

    $current_sort_order = array();
    $objects = App_Cms_Front_Data::GetList(array(App_Cms_Front_Data::GetPri() => $data['items']));
    foreach ($objects as $item) {
        array_push($current_sort_order, $item->sortOrder);
    }

    foreach ($objects as $item) {
        $new_item_sort_order = $current_sort_order[$new_sort_order[$item->GetId()]];
        if ($new_item_sort_order) {
            $item->updateAttr('sort_order', $new_item_sort_order);
        }
    }

    App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_MODIFY, null, 'Сортировка');
}
