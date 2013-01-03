function documentDataUpdateBranch(_eleId, _documentId, _dataBlockId)
{
	showLoadingBar();

	var postBody = "id=" + _documentId;
	if (_dataBlockId) {
	    postBody += "&data_id=" + _dataBlockId;
	}

    $.post(
        "http-request-data.php",
        postBody,
        function (_response) {
            $("#" + _eleId).html(_response);

            $("#document-data-blocks").sortable({
                delay: 500,
                items: "div.document-data",
                update: itemSort
            });

            replaceTextareaCdata();
			hideLoadingBar();
        }
    );
}
