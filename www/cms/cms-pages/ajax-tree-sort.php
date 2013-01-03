<?php

require('../prepend.php');

global $gCustomUrls;
$data = $_POST;

if (!empty($data['branches'])) {
	$changed = array();
	$parent = array();
	$objects = App_Cms_Front_Document::getList();

	foreach ($data['branches'] as $i) {
		$order = $newOrder = array();

		for ($j = 0; $j < count($data['branch_' . $i]); $j++) {
			$id = $data['branch_' . $i][$j];
			if (!isset($objects[$id])) {
			    return false;
			}

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
			array_push($changed, $id);
		}
	}

	foreach ($objects as $i) {
		if (isset($parent[$i->getId()])) {
			$isRoot = $i->folder != '/'
			       || $parent[$i->getId()] == '';

			$isUnique = App_Cms_Front_Document::checkUnique(
		        $parent[$i->getId()],
		        $i->folder,
		        $i->getId()
	        );

			$isNotCustomUrl = empty($gCustomUrls) ||
			                  !in_array(trim($objects[$i->getId()]->uri, '/'), $gCustomUrls);

			if ($isRoot && $isUnique && $isNotCustomUrl) {
			    $parentId = empty($parent[$i->getId()])
			              ? null
			              : $parent[$i->getId()];

				$objects[$i->getId()]->parentId = $parentId;
				$changed[] = $i->getId();
			}
		}
	}

	foreach (array_unique($changed) as $i) {
		$objects[$i]->update();
	}

	App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_MODIFY, null, 'Сортировка');
}
