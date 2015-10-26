$(function(){

	$('div.filePreview a.previewLink').click(function(){
		var filePreview = $(this).closest('div')
		if(filePreview.hasClass('open')){
			filePreview.removeClass('open').find('div').slideUp();
		}else{
			filePreview.addClass('open').find('div').slideDown();
		}
	});

	$('form').submit(function(){
		$(this).find(':submit').addClass('disabled').attr('readonly','readonly');
	});

	$(document)
		.on('change', '#searchFormSelect',                   handler_setupSearchFormFields)
		.on('change', '#paginationPageDropdownID',           handler_jumpToPage)
		.on('change', '#paginationRecordsPerPageDropdownID', handler_setPaginationPerPage)
		.on('submit', '#jumpToIDNOForm',                     handler_jumpToIDNO);

	$('#metadataModal').bind('keypress keydown keyup', function(e){
		if(e.keyCode == 13) { e.preventDefault(); }
	});

	$("#objectListingTable").tablesorter();
});

function handler_jumpToIDNO() {
	event.preventDefault();
	event.stopImmediatePropagation();

	var idno   = $('#jumpToIDNO').val();
	var formID = $('#jumpToIDNO').data("formid");
	var url    = siteRoot+"?ajax=TRUE&action=paginationJumpToIDNO&idno="+idno+"&formID="+formID;

	window.location.href=url;
}

function queryObj() {
	var result = {}, queryString = location.search.slice(1),
		re = /([^&=]+)=([^&]*)/g, m;

	while (m = re.exec(queryString)) {
		result[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
	}

	return result;
}

function select_metadataMultiSelects() {
	$('.multiSelectContainer option').prop('selected', 'selected');
}

function handler_jumpToPage() {
	event.preventDefault();
	event.stopImmediatePropagation();

	var page = $(this).val();
	var url  = window.location.pathname+"?listType="+queryObj()['listType']+"&formID="+queryObj()['formID']+"&page="+page;

	window.location.href=url;
}

function handler_setPaginationPerPage() {
	event.preventDefault();
	event.stopImmediatePropagation();

	var perPage = $(this).val();
	var url = siteRoot+'index.php?action=paginationPerPage&perPage='+perPage+'&ajax=true';

	$.ajax({
		type: "GET",
		url: url,
		dataType: "html",
		success: function(responseData) {
			window.location.reload();
		},
		error: function(jqXHR,error,exception) {
		}
	});
}

function handler_setupSearchFormFields() {
	event.preventDefault();
	event.stopImmediatePropagation();

	var formID = $('#searchFormSelect').val();
	var url    = siteRoot+'index.php?action=searchFormFields&formID='+formID+'&ajax=true';
	$.ajax({
		type: "GET",
		url: url,
		dataType: "html",
		success: function(responseData) {
			$("#formFieldsOptGroup").html(responseData);
		},
		error: function(jqXHR,error,exception) {
		}
	});
}

function handler_displayMetadataFormModal(formID) {
	var choicesForm = formID;//$(this).attr("data-formID");

	$("[data-choicesForm='"+choicesForm+"']").each(function() {

		var dataFieldName = $(this).attr("data-fieldname");
		var url           = siteRoot+'?ajax&action=selectChoices&formID='+$(this).attr("data-formid")+"&fieldName="+dataFieldName;

		$.ajax({
			type: "GET",
			url: url,
			dataType: "html",
			success: function(responseData) {
				$("[data-fieldname='"+dataFieldName+"']").html(responseData);
			},
			error: function(jqXHR,error,exception) {
				$('#'+target).html("An Error has occurred: "+error);
			}
		});
	});

}

function submitMetadataModal() {

	var metadataFormID = 0;

	$("#metadataModalBody form").each(function() {

		data           = $(this).serialize();
		metadataFormID = $(this).data("choicesform");

		if ($(this).attr("name") == "insertForm") {
			data = data + "&submitForm=Submit"
		}
		else if ($(this).attr("name") == "updateForm") {
			data = data + "&updateEdit=Update";
		}

		$.ajax({
			type: "POST",
			url: $(this).attr("action")+"&ajax=true",
			dataType: "html",
			data: data,
			async:   false,
			success: function(responseData) {
				console.log(responseData);
			},
			error: function(jqXHR,error,exception) {
				console.log("An Error has occurred: "+error);
				$("#metadataModalBody").html("An Error has occurred: "+error);
			}
		});
	});

	$('#metadataModal').modal('hide');
	handler_displayMetadataFormModal(metadataFormID);
}

