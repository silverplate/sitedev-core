var treeIconPlus = new Image(9, 9);
treeIconPlus.src = "/cms/f/icon-plus.gif";

var treeIconMinus = new Image(9, 9);
treeIconMinus.src = "/cms/f/icon-minus.gif";

function treeLoad(_updateEleId, _moduleName, _fieldName, _parentId, _type)
{
	showLoadingBar();

	var postBody = "module_name=" + _moduleName
	             + "&field_name=" + _fieldName
	             + "&type=" + _type;

	if (_parentId) {
	    postBody += "&parent_id=" + _parentId;
	}

	if (_type != "list") {
		var currentObjectEle = document.getElementById("current-object-id");
		if (currentObjectEle && currentObjectEle.value) {
		    postBody += "&current_object_id=" + currentObjectEle.value;
		}
	}

	var selectedIds = eval("formTreeValues_" + _fieldName);
	if (selectedIds) {
		for (var i = 0; i < selectedIds.length; i++) {
			postBody += "&selected_ids[]=" + selectedIds[i];
		}
	}

    $.post(
        "ajax-tree.php",
        postBody,
        function(_response) {
            $("#" + _updateEleId).html(_response);

            if (_type == "list") {
                $("#tree-list").sortable({
                    delay: 500,
/*
                    opacity: 0.3,
                    placeholder: "sort-placeholer",
                    forcePlaceholderSize: true,
 */
                    items: "div.sort-item",
                    update: function() {
                        updateTree(
                            document.getElementById(_updateEleId),
                            _fieldName
                        );
                    }
                });

                $( "#tree-list" ).disableSelection();
            }

            hideLoadingBar();
        }
    );
}

function updateTree(_ele, _fieldName)
{
	treeBranches = new Array();
	getTreeBranchIds("tree-list", _ele, _fieldName);

	if (treeBranches.length > 0) {
		showLoadingBar();
		var postBody = "";
		var j;

		for (var i = 0; i < treeBranches.length; i++) {
			postBody += "&branches[]=" + treeBranches[i][0];
			for (j = 0; j < treeBranches[i][1].length; j++) {
				postBody += "&branch_" + treeBranches[i][0] + "[]=" + treeBranches[i][1][j];
			}
		}

        $.post("ajax-tree-sort.php", postBody.substr(1), hideLoadingBar);
    }
}

function getTreeBranchIds(_parentEleId, _ele, _fieldName)
{
	var parentId = _parentEleId == "tree-list" ? "" : _parentEleId;
	var child;
	var idEle;
	var subItems;

	for (var i = 0; i < _ele.childNodes.length; i++) {
		child = _ele.childNodes[i];

		if (child.nodeName == "DIV" && child.className == "sort-item") {
			idEle = document.getElementById(child.getAttribute("id") + "-id");

			if (idEle) {
				addTreeBranchId(parentId, idEle.getAttribute("value"));
				subItems = document.getElementById(_fieldName + "-" + idEle.getAttribute("value"));

				if (subItems) {
					getTreeBranchIds(idEle.getAttribute("value"), subItems, _fieldName);
				}
			}
		}
	}
}

function addTreeBranchId(_branchId, _id)
{
	var isBranch = false;
	for (var i = 0; i < treeBranches.length; i++) {
		if (treeBranches[i][0] == _branchId) {
			treeBranches[i][1][treeBranches[i][1].length] = _id;
			isBranch = true;
			break;
		}
	}

	if (!isBranch) {
		treeBranches[treeBranches.length] = new Array(_branchId, new Array(_id));
	}
}

function treeCollapse(_obj, _moduleName, _fieldName, _parentId, _type)
{
	var updateEleId = _fieldName + "-" + _parentId;
	var ele = document.getElementById(updateEleId);

	if (ele.innerHTML == "") {
		ele.style.display = "block";
		treeLoad(updateEleId, _moduleName, _fieldName, _parentId, _type);

	} else {
		ele.style.display = ele.style.display == "block" ? "none" : "block";
	}

	treeImageRoll(_obj.getElementsByTagName("img")[0], ele);

	var cookieName = "back-tree-" + _moduleName + "-" + _fieldName;
	if (ele.style.display == "block") saveIntoCookieList(cookieName, _parentId, null);
	else removeFromCookieList(cookieName, parentId, null);
}

function treeImageRoll(_img, _ele)
{
	_img.src = _ele.style.display == "block" ? treeIconMinus.src : treeIconPlus.src;
}

function treeSwitcher(_name)
{
	var eleOpenBtn = document.getElementById(_name + "-tree-open-btn");
	var eleContainer = document.getElementById(_name + "-tree-container");
	eleOpenBtn.style.display = eleOpenBtn.style.display == "none" ? "block" : "none";
	eleContainer.style.display = eleOpenBtn.style.display == "none" ? "block" : "none";
}
