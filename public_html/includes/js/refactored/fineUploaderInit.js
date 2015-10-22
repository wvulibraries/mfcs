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
});
