var filePrevModal;

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
        filePrevModal = $(this).data('target');

        $('#'+filePrevModal).addClass('show');
        $('.bgCloak').show();
        $('body').addClass('filePreviewModalLives');
    });
});

function closeModal(){
    $('#'+filePrevModal).removeClass('show').hide();
    $('.bgCloak').hide();
}



