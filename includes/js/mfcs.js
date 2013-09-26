$(function(){
	// Instantiate the bootstrap tooltip, popover, and modal plugins
	$("[rel='tooltip']").tooltip();
	$("[rel='popover']").popover();
	$("[rel='modal']").modal();

	$('div.filePreview a.previewLink').click(function(){
        var filePreview = $(this).closest('div')
        if(filePreview.hasClass('open')){
            // Closing
            filePreview.find('div').slideUp(function(){
                filePreview.removeClass('open');
            });
        }else{
            // Opening
            filePreview.addClass('open');
            filePreview.find('div').slideDown();
        }
    });

	// Make any file uploader div's live
	$('div.fineUploader').each(function(i,n){
		var $div               = $(n);
		var $form              = $div.closest('form');
		var uploadID           = $div.data('upload_id');
		var allowMultipleFiles = $div.data('multiple');
		var allowedExtentions  = $div.data('allowed_extensions').split(',');

		$div.fineUploader({
			request: {
				endpoint: siteRoot+"/includes/uploader.php",
				params: {
					engineCSRFCheck: csrfToken,
					uploadID: uploadID,
					multiple: allowMultipleFiles
				}
			},
			failedUploadTextDisplay: {
			mode: "custom",
				maxChars: 40,
				responseProperty: "error",
				enableTooltip: true
			},
			multiple: allowMultipleFiles,
			validation: {
				allowedExtensions: allowedExtentions,
			},
			text: {
				uploadButton: '<i class="icon-plus icon-white"></i> Select Files'
			},
			showMessage: function(message) {
				$div.find(".qq-upload-list").append('<li class="alert alert-error">'+message+'</li >');
			},
			classes: {
				success: "alert alert-success",
				fail: "alert alert-error"
			}
		}).on('submit',function(){
				var uploads_working = $form.data('uploads_working');
				var i = typeof(uploads_working) == 'undefined' ? 0 : parseInt(uploads_working);
				$form
					.data('uploads_working', ++i)
					.find(':submit').attr('disabled','disabled');
		}).on('complete cancel',function(){
				var i = parseInt($form.data('uploads_working'));
				i--;
				$form.data('uploads_working', i);
				if(i == 0) $form.find(':submit').removeAttr('disabled','disabled');
		});
	});

    // Reset the modal's UI when it's hidden
    $('#selectProjectsModal').on('hide', function (){
        var IDs = $('#currentProjectsLink').data('selected_projects');
        if(typeof(IDs) != 'string') IDs = IDs.toString();
        if(typeof(IDs) != 'array')  IDs = IDs.split(',');
        $('#selectProjectsModal :checkbox').each(function(i,n){
            var chkBox = $(n);
            var ID = $(n).val();
            chkBox.prop('checked', $.inArray(ID, IDs) !== -1 );
        });
    });

    // Lock form submit button on form submittion
	$('form').submit(function(){
		$(this).find(':submit').addClass('disabled').attr('readonly','readonly');
	});

	$(document)
        .on('click',  '.metadataObjectEditor', handler_setupMetadataModal)
        .on('change', '#searchFormSelect',     handler_setupSearchFormFields)
        .on('click',  '.metadataListAccordionToggle', handler_metadataListAccordionToggle)
        .on('submit', 'form[name=insertForm]', select_metadataMultiSelects)

    $('#metadataModal').bind('keypress keydown keyup', function(e){
       if(e.keyCode == 13) { e.preventDefault(); }
    });

    $("#objectListingTable").tablesorter(); 

});

function select_metadataMultiSelects() {
    $('.multiSelectContainer option').prop('selected', 'selected');
}

function handler_metadataListAccordionToggle() {
    event.preventDefault();
    event.stopImmediatePropagation();

    var currentValue = $(this).html();
    if (currentValue == "Show Metadata Forms") {
        $(this).html("Hide Metadata Forms");
    }
    else {
        $(this).html("Show Metadata Forms");
    }

}

function handler_setupSearchFormFields() {
    event.preventDefault();
    event.stopImmediatePropagation();

    var formID = $('#searchFormSelect').val();
    var url    = siteRoot+'/index.php?action=searchFormFields&formID='+formID+'&ajax=true';
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

function handler_setupMetadataModal() {
    event.preventDefault();
    event.stopImmediatePropagation();
    $("#metadataModal .modal-header h3").html($(this).attr("data-header"));

    var dataFieldName = $(this).attr("data-fieldname");
    var formID        = $(this).attr('data-formid');
    var url           = siteRoot+'/dataEntry/metadata.php?formID='+formID+'&ajax=true';

    $.ajax({
        type: "GET",
        url: url,
        dataType: "html",
        success: function(responseData) {
            $("#metadataModalBody").html(responseData);

             $("#metadataModalBody :submit").remove();
             $("#metadataModalBody header").remove();
             $("#metadataModalBody footer").remove();
             $("#metadataModalBody form").data("choicesform",formID);

             $('#metadataModal').modal('show');
        },
        error: function(jqXHR,error,exception) {
            $('#metadataModalBody').html("An Error has occurred: "+error);
        }
    }); 

}

function handler_displayMetadataFormModal(formID) {

    // event.preventDefault();
    // event.stopImmediatePropagation();

    var choicesForm = formID;//$(this).attr("data-formID");

    $("[data-choicesForm='"+choicesForm+"']").each(function() {
        console.log("here"); 
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
            data = data + "&submitForm=TRUE"
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
                $("#metadataModalBody").html("An Error has occurred: "+error);
            }
        });

    });
    
    $('#metadataModal').modal('hide');
    handler_displayMetadataFormModal(metadataFormID);

}

function saveSelectedProjects(){
    // Get all the IDs of selected projects
    var selectedProjectIDs   = [];
    var selectedProjectNames = [];
    $('#selectProjectsModal :checkbox:checked').each(function(i,n){
        selectedProjectIDs.push($(n).val());
        selectedProjectNames.push($(n).data('label'));
    });
    // And POST it to the server
    var postData = {
        engineCSRFCheck:  $(':input[name="engineCSRFCheck"]').val(),
        action:           'updateUserProjects',
        selectedProjects: selectedProjectIDs
    };
    $.post(siteRoot+'?ajax',postData,function(data){
        if(data.success){
            var newHTML = selectedProjectIDs.length
                ? selectedProjectNames.join(", ")
                : '<span style="color: #999; font-style: italic;">None Selected</span>';
            $('#currentProjectsLink')
                .html(newHTML)
                .data('selected_projects',selectedProjectIDs.join(','));
        }else{
            alert("An error occurred!\n\n(check the browser console for details)");
            if(typeof(console) != 'undefined') console.log("Error from AJAX call: "+data.errorMsg);
        }
        $('#selectProjectsModal').modal('hide');
    });
}

function addItemToID(id, item) {
    var theSelect = document.getElementById(id);

    if (item.value == "null") {
        return;
    }

    for (i = theSelect.length - 1; i >= 0; i--) {
        if (theSelect.options[i].value == item.value) {
            return;
        }
    }

    theSelect.options[theSelect.length] = new Option(item.text, item.value);
}

function removeFromList(id) {
    alert("here");
    var theSelect = document.getElementById(id);

       for (var selIndex = theSelect.length - 1; selIndex >= 0; selIndex--) 
       { 
            // Is this option selected? 
            if (theSelect.options[selIndex].selected) 
            { 
                // Delete the option in the first select box. 
                theSelect[selIndex] = null; 
            } 
        } 

}
