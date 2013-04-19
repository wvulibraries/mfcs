$(function(){
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

    $(document)
        .on('click', '.metadataObjectEditor', handler_setupMetadataModal)


});

function handler_setupMetadataModal() {
    event.preventDefault();
    event.stopImmediatePropagation();
    $("#metadataModal .modal-header h3").html($(this).attr("data-header"));

    var dataFieldName = $(this).attr("data-fieldname");
    var url           = siteRoot+'/dataEntry/metadata.php?formID='+$(this).attr('data-formID')+'&amp;ajax=true';

    $.ajax({
        type: "GET",
        url: url,
        dataType: "html",
        success: function(responseData) {
            $("#metadataModalBody").html(responseData);

             $("#metadataModalBody :submit").remove();
             $("#metadataModalBody header").remove();
             $("#metadataModalBody footer").remove();
        },
        error: function(jqXHR,error,exception) {
            $('#metadataModalBody').html("An Error has occurred: "+error);
        }
    });

}

function handler_displayMetadataFormModal(formID) {

    event.preventDefault();
    event.stopImmediatePropagation();

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

    $("#metadataModalBody form").each(function() {

        data = $(this).serialize();

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
    handler_displayMetadataFormModal($(this).attr("data-formid"));

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
