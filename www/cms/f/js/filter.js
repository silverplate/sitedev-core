function dateFilterFromDate(_stringDateFrom, _stringDateTill)
{
    if (_stringDateFrom) {
        var fromDate = _stringDateFrom.split("-");
        calendarSet("filter-from", fromDate[0], fromDate[1] - 1, fromDate[2]);

        if (_stringDateTill) {
            var tillDate = _stringDateTill.split("-");
            calendarSet("filter-till", tillDate[0], tillDate[1] - 1, tillDate[2]);

        } else {
            var now = new Date();
            calendarSet("filter-till", now.getFullYear(), now.getMonth(), now.getDate());
        }

    } else {
        calendarClear("filter-from");
        calendarClear("filter-till");
    }
}

function dateFilterGetDate(_name)
{
    var cal = getCalendar(_name);
    return cal.getInputEle().value ? cal.getDate() : false;
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

function filterUpdate(_eleName, _isFormSubmit, _isSortable)
{
    var formEle = document.getElementById("filter");
    var ele = document.getElementById(_eleName);

    if (formEle && ele) {
        showLoadingBar();

        if (_isFormSubmit) {
            setCookie("filter-page", "");

            if ($("[name = 'filter_from']").length != 0) {
                var from = dateFilterGetDate("filter-from");
                var till = dateFilterGetDate("filter-till");

                if (from && till) {
                    setCookie("filter-from", from.toISOString());
                    setCookie("filter-till", till.toISOString());

                } else {
                    setCookie("filter-from", "");
                    setCookie("filter-till", "");
                }
            }

            $(".filter-input").each(function() {
                setCookie(
                    "filter-" + this.id.replace("filter-", ""),
                    this.value
                );
            });

            $(".filter-switcher").each(function() {
                var value = "";
                var name = this.id.replace("is-filter-", "");

                if (this.checked) {
                    var input = $(formEle).find("[name = 'filter_" + name + "']");

                    if (input.length == 0) {
                        input = $(formEle).find("[name = 'filter_" + name + "[]']");
                    }

                    if (input.length > 0) {
                        if (input.length > 1) {
                            input.each(function() {
                                if (this.checked) {
                                    value += (value != "" ? "|" : "") + this.value;
                                }
                            });

                        } else if (input.checked) {
                            value = input.value;
                        }
                    }
                }

                setCookie("is-filter-" + name, this.checked ? 1 : "");
                setCookie("filter-" + name, value);
            });
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
