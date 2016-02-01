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
});




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
