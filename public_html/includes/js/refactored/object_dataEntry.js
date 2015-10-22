// Document Ready
// ===================================================================
$(function(){
    // form processing
    var objectSubmitBtn = $('#objectSubmitBtn');

    objectSubmitBtn.closest('form').submit(function(){
        var objectSubmitProcessing = $('#objectSubmitProcessing');
        if(objectSubmitProcessing.length){
            objectSubmitBtn.hide();
            objectSubmitProcessing.show();
        }
    });

    // alert window
    setTimeout(removeFormAlert, 15000);
});

// Form Alert Function
// ===================================================================
function removeFormAlert(){
    var formAlert = $('.formAlert');
    if(formAlert.is(':visible')){
        formAlert.hide();
    }
}