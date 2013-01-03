function getRandom()
{
	return Math.round(Math.random() * Math.random() * 100000);
}

function openWindow(_url, _width, _height, _name)
{
	var w = _width ? _width : 600;
	var h = _height ? _height : 400;

	window.open(
	    _url,
	    _name ? _name : "back-" + getRandom(),
	    "width=" + w + ",height=" + h + ",location=no,status=yes,resizable=yes,scrollbars=yes,titlebar=yes"
    );
}

function showFormGroup(_name)
{
	if (formGroups) {
		for (var i = 0; i < formGroups.length; i++) {
			changeElementVisibility("form-group-" + formGroups[i], formGroups[i] == _name);

			var tabEle = document.getElementById("form-group-" + formGroups[i] + "-tab");

			if (tabEle) {
				var className = "";

				if (i == 0) className += (className != "" ? " " : "") + "first";
				if (formGroups[i] == _name) className += (className != "" ? " " : "") + "selected";

				tabEle.className = className;
			}
		}

		setCookie("form-group", _name);
	}
}

function changeElementVisibility(_id, _isVisible)
{
	var element = document.getElementById(_id);

	if (element) {
		element.style.display = _isVisible ||
		                        (_isVisible == null && element.style.display != "block") ? "block" : "none";
	}
}


/**
 * Files
 */

function getFormFileAddingLink(_name)
{
	return document.getElementById("add-form-files-" + _name);
}

function getFormFileContainer(_name)
{
	return document.getElementById("form-files-" + _name);
}

function getFormFileCount(_name)
{
	return getFormFileContainer(_name).getElementsByTagName("tr").length;
}

function addFormFileInputs(_name)
{
	var table = document.createElement("table");
	table.className = "form-files";
	table.setAttribute("id", "form-files-" + _name);

	getFormFileAddingLink(_name).parentNode.appendChild(table);
	addFormFileInput(_name);
}

function addFormFileInput(_name)
{
	var table = getFormFileContainer(_name);
	var tr, removeEle, inputEle;

	if (navigator.userAgent.indexOf("MSIE") != -1) {
		tr = table.insertRow();

	} else {
		tr = document.createElement("tr");
		getFormFileContainer(_name).appendChild(tr);
	}

	if (navigator.userAgent.indexOf("MSIE") != -1) {
		removeEle = tr.insertCell();

	} else {
		removeEle = document.createElement("td");
		tr.appendChild(removeEle);
	}

	removeEle.className = "system";
	removeEle.onclick = function () {
	    removeFormFileInput(_name, removeEle);
	};

	removeEle.innerHTML = "&times;";

	if (navigator.userAgent.indexOf("MSIE") != -1) {
		inputEle = tr.insertCell();

	} else {
		inputEle = document.createElement("td");
		tr.appendChild(inputEle);
	}

	inputEle.innerHTML = '<input type="file" name="' + _name + '[]" class="file" multiple="true" />';
}

function removeFormFileInput(_name, _element)
{
	_element.parentNode.parentNode.removeChild(_element.parentNode);

	if (getFormFileCount(_name) == 0) {
		getFormFileContainer(_name).parentNode.removeChild(getFormFileContainer(_name));
	}
}

function deleteFile(_ele, _path)
{
	if (confirm("Удалить файл немедленно?")) {
		showLoadingBar();

        $.post(
            "/cms/ajax-delete-file.php",
            "f=" + _path,
            function (_response) {
                if (_response == "1") {
                    var file = _ele.parentNode;
                    var parent = file.parentNode;
                    parent.removeChild(file);

                    var hasChild = false;
                    for (var i = 0; i < parent.childNodes.length; i++) {
                        if (parent.childNodes[i].nodeType == 1) {
                            hasChild = true;
                            break;
                        }
                    }

                    if (!hasChild) {
                        parent.parentNode.removeChild(parent);
                    }
                }

                hideLoadingBar();
            }
        );
	}
}

function itemSort(_event, _ui)
{
	var inputs = _ui.item.parent().find("input[type = 'hidden']");
	var postBody = "";

	for (var i = 0; i < inputs.length; i++) {
        postBody += "&items[]=" + inputs[i].value;
	}

	if (postBody) {
		showLoadingBar();
        $.post("ajax-sort.php", postBody.substr(1), hideLoadingBar);
	}
}


/**
 * Список элементов из таблицы с тройным
 * составным первичным ключом
 */

var tripleLinkCounter = 0;

function addTripleLink(_name)
{
    showLoadingBar();

    var containerEle = document.getElementById(_name);
    if (containerEle) {
        var count = containerEle.getElementsByTagName("table").length;

        $.post(
            "ajax-" + _name + ".php",
            "name=" + _name + "&position=" + tripleLinkCounter + "&count=" + count,
            function (_response) {
                if (_response) {
                    var ele = document.createElement("ins");
                    ele.innerHTML = _response;
                    containerEle.appendChild(ele);
                    tripleLinkCounter++;
                }

                hideLoadingBar();
            }
        );
    }
}

function deleteTripleLink(_button)
{
    var ele = getParentElement(_button.parentNode, "ins");
    if (!ele) {
        ele = getParentElement(_button.parentNode, "table");
    }

    if (ele) {
        removeElement(ele);
    }
}

var tripleLinkCounter = 0;

function addTripleLink(_name)
{
    showLoadingBar();

    var containerEle = document.getElementById(_name);
    if (containerEle) {
        var count = containerEle.getElementsByTagName("table").length;

        $.post(
            "ajax-" + _name + ".php",
            "name=" + _name + "&position=" + tripleLinkCounter + "&count=" + count,
            function (_response) {
                if (_response) {
                    var ele = document.createElement("ins");
                    ele.innerHTML = _response;
                    containerEle.appendChild(ele);
                    tripleLinkCounter++;
                }

                hideLoadingBar();
            }
        );
    }
}

function deleteTripleLink(_button)
{
    var ele = getParentElement(_button.parentNode, "ins");

    if (!ele) {
        ele = getParentElement(_button.parentNode, "table");
    }

    if (ele) {
        removeElement(ele);
    }
}


/**
 * Формы
 */

function replaceTextareaCdata()
{
    var elems = document.getElementsByTagName("textarea");
    for (var i = 0; i < elems.length; i++) {
        elems[i].value = elems[i].value
                                 .replace(/&lt;!\[CDATA\[/gim, "<![CDATA[")
                                 .replace(/\]\]&gt;/gim, "]]>");
    }
}
