var defaultModalBody;

// Document Ready
// ===================================================================
$(function(){
    defaultModalBody = $('#metadataModalBody').html();
    // create uniform selects using select 2
    $('select').addClass('form-control');

    $('.metadataObjectEditor').click(metadataModal);
    $('.close, .cancelButton').click(closeModal);
    $('.saveMetadata').click(submitMetadataModal);

    $('form[name=insertForm]').submit(function() {
        $('.multiSelectContainer option').prop('selected', 'selected');
    });

    $('#metadataModal').bind('keypress keydown keyup', function(e){
        if(e.keyCode == 13) { e.preventDefault(); }
        if(e.keyCode == 27) { $('.cancelButton').trigger('click'); }
    });

    $('.wysiwyg').removeClass('wysiwyg').parent().addClass('wysiwyg');
    $('.bgCloak').click(closeModal);
});

// MetaData Modals
// ===================================================================

function metadataModal(event){
    event.preventDefault();
    $("#metadataModal .modal-header h3").html($(this).attr("data-header"));
    $("#metadataModal").fadeIn(600).removeClass('hide').show();
    $('.bgCloak').show();
    $('html,body').addClass('modalBlockScroll');

    var dataFieldName = $(this).attr("data-fieldname");
    var formID        = $(this).attr('data-formid');
    var url           = siteRoot+'dataEntry/metadata.php?formID='+formID+'&ajax=true';

    $("#metadataModal form").data(formID);


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

            $('#metadataModal').show().removeClass('hide');
        },
        error: function(jqXHR,error,exception) {
            $('#metadataModalBody').html("An Error has occurred: "+error);
        },
    });
}

function submitMetadataModal() {
    var metadataFormID = 0;
    var insertForm = $('#metadataModalBody form[name="insertForm"]');

    $('#metadataModalBody section').prepend(' <div class="successMessage">Please wait while your change is processed.  Once processed this window will close. </div>');

    data           = insertForm.serialize() + "&ajax=true&submitForm=Submit";
    metadataFormID = insertForm.data("formid");
    var requestURL = insertForm.attr('action');

    $.ajax({
        type: "POST",
        url: requestURL,
        data: data,
        async: false,
        success: function(responseData) {
        },
        error: function(jqXHR,error,exception) {
            $("#metadataModalBody").html("An Error has occurred: "+error);
        }
    }).done(function() {
        $("#metadataModalBody").empty();
        $("#metadataModal").find($('button.close')).trigger('click');
    });
}


function closeModal(event){
    event.preventDefault();
    $('#metadataModal').fadeOut().addClass('hide');
    $('#metadataModalBody').empty();
    $('.bgCloak').hide();
    $('html,body').removeClass('modalBlockScroll');
}


// HELPER FUNCTIONS
// ===================================================================

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

function addToID(id, value, text) {
    var theSelect = document.getElementById(id);

    for (i = theSelect.length - 1; i >= 0; i--) {
        if (theSelect.options[i].value == value) {
            return;
        }
    }

    theSelect.options[theSelect.length] = new Option(text, value);
}

function removeFromList(id) {
    var theSelect = document.getElementById(id);

    for (var selIndex = theSelect.length - 1; selIndex >= 0; selIndex--) {
        // Is this option selected?
        if (theSelect.options[selIndex].selected) {
            // Delete the option in the first select box.
            theSelect[selIndex] = null;
        }
    }
}

// Document Ready
// ===================================================================
$(function(){
    // Show first tab on page load
    $(".nav-tabs a:first").tab("show");

    // form processing
    var objectSubmitBtn = $('#objectSubmitBtn');

    objectSubmitBtn.closest('form').submit(function(){
        var objectSubmitProcessing = $('#objectSubmitProcessing');
        if(objectSubmitProcessing.length){
            objectSubmitBtn.hide();
            objectSubmitProcessing.show();
        }
    });

    // File Preview Modals
    // ====================================================

    $('.fileModalPreview').find('a').click( function(){
        var targetURL = $(this).data('url');

        console.log(targetURL);

        $('#iFrameTarget').attr('src', targetURL);

        $('.imagePreviewModal').addClass('show');
        $('.bgCloak').show();
        $('body').addClass('filePreviewModalLives');
    });
});

function closeModal(){
    $('.modal').removeClass('show').hide();
    $('.bgCloak').hide();
    $('html,body').removeClass('modalBlockScroll');
}




// Document Ready
// ===================================================================
$(function(){
    // Instantiate the bootstrap tooltip, popover, and modal plugins
    $("[rel='tooltip']").tooltip();
    $("[rel='popover']").popover({ trigger:'click' });
    $("[rel='modal']").modal();

    $('.metadataListAccordionToggle').click(function(e){
        e.preventDefault();
        var accordionClass = $('.accordion-body');
        var thisAccordion  = $(this).parent().parent().next(accordionClass);
        if (thisAccordion.is(':visible')){
            $(this).html("<i class='fa fa-plus-square-o'></i> Metadata Forms");
        }
        else {
            $(this).html("<i class='fa fa-minus-square-o'></i> Metadata Forms");
        }
    });

    if($('.hasDescription').length){
        $('.hasDescription').parent().parent().addClass('panelHasDescription');
    }
});
// Document Ready
// ===================================================================
// Initializes and gives parameters to Fine Uploader divs
$(function(){
    // Make any file uploader div's live
    $('div.fineUploader').each(function(i,n){
        var $div               = $(n);
        var $form              = $div.closest('form');
        var uploadID           = $div.data('upload_id');
        var allowMultipleFiles = $div.data('multiple');
        var allowedExtentions  = $div.data('allowed_extensions').split(',');

        $div.fineUploader({
            request: {
                endpoint: siteRoot+"includes/uploader.php",
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
                uploadButton: '<div class="uploadText"> <i class="fa fa-upload fa-4x"></i> <br> Drag or Click Here <br> To Upload Files </div>',
                dropButton: 'HELP'
            },
            showMessage: function(message) {
                $div.find(".qq-upload-list").append('<li class="alert alert-danger">'+message+'</li >');
            },
            classes: {
                success: "alert alert-success",
                fail: "alert alert-danger"
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

    $('.fineUploader').parent().addClass('uploadFiles');
    $('.qq-upload-drop-area').html('<div class="uploadText"> <i class="fa fa-dropbox fa-4x"></i> <br><br> Drop Files Here </div>');


    // File Preview
    // =============================================================
    $('div.filePreview a.previewLink').click(function(){
        var filePreview = $(this).closest('div')
        if(filePreview.hasClass('open')){
            filePreview.removeClass('open').find('div').slideUp();
        }else{
            filePreview.addClass('open').find('div').slideDown();
        }
    });
});
