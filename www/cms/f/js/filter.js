function dateFilterFromDate(_stringDateFrom, _stringDateTill)
{
	if (_stringDateFrom) {
		var fromDate = _stringDateFrom.split("-");
		calendarSet("filter-from", fromDate[0], fromDate[1] - 1, fromDate[2]);
	}

	if (_stringDateTill) {
		var tillDate = _stringDateTill.split("-");
		calendarSet("filter-till", tillDate[0], tillDate[1] - 1, tillDate[2]);

	} else {
		var now = new Date();
		calendarSet("filter-till", now.getFullYear(), now.getMonth(), now.getDate());
	}
}

function dateFilterGetDate(_name)
{
	return getCalendar(_name).getDate();
}

function hideFilter()
{
	changeElementVisibility("filter", false);
	changeElementVisibility("filter-link", true);
	setCookie("filter-is-open", "");
}

function showFilter()
{
	changeElementVisibility("filter", true);
	changeElementVisibility("filter-link", false);
	setCookie("filter-is-open", 1);
}

function filterUpdate(_eleName, _isFormSubmit, _isSortable, _isDate)
{
	var formEle = document.getElementById("filter");
	var ele = document.getElementById(_eleName);
	var from, till;

	if (_isDate) {
		from = dateFilterGetDate("filter_from");
		till = dateFilterGetDate("filter_till");
	}

	if (formEle && ele && (!_isDate || (from && till))) {
		showLoadingBar();

		if (_isDate) {
			setCookie("filter-from", from.toGMTString());
			setCookie("filter-till", till.toGMTString());
		}

		if (_isFormSubmit) {
			setCookie("filter-page", "");
		}

		var inputs = new Array("title", "name", "email");

		for (var i = 0; i < inputs.length; i++) {
			var input = document.getElementById("filter-" + inputs[i]);
			setCookie("filter-" + inputs[i], input ? input.value : "");
		}

		var inputs = new Array("users", "sections", "actions", "type", "group");

		for (var i = 0; i < inputs.length; i++) {
			var checkEle = document.getElementById("is-filter-" + inputs[i]);
			if (checkEle) {
				var value = "";
				if (checkEle.checked) {
					var input = formEle.elements["filter_" + inputs[i]];
					if (!input) input = formEle.elements["filter_" + inputs[i] + "[]"];

					if (input && input.length > 0) {
						for (var j = 0; j < input.length; j++) {
							if (input[j].checked) {
								value += (value != "" ? "|" : "") + input[j].value;
							}
						}
					}
				}

				setCookie("is-filter-" + inputs[i], checkEle.checked ? 1 : "");
				setCookie("filter-" + inputs[i], value);
			}
		}

        $.post(
            "ajax-filter.php",
            $(formEle).serialize(),
            function(_response) {
                ele.innerHTML = _response;

                if (_response.indexOf("Нет") != 0 && _isSortable) {
                    $(ele).sortable({update: itemSort});
                }

                hideLoadingBar();
            }
        );
	}
}

function filterUpdateNav(_page, _isSortable)
{
	showLoadingBar();

	var formEle = document.getElementById("filter");
	var ele = document.getElementById("filter-content");
	var selectedEle = formEle.elements["filter_selected_id"];

	setCookie("filter-page", _page);

    var postBody = $(formEle).serialize() + "&page=" + _page;

    if (selectedEle) {
        postBody += "&filter_selected_id=" + selectedEle.value;
    }

    $.post(
        "ajax-filter.php",
        postBody,
        function(_response) {
            ele.innerHTML = _response;

            if (_response.indexOf("Нет") != 0 && _isSortable) {
                $(ele).sortable({update: itemSort});
            }

            hideLoadingBar();
        }
    );
}
