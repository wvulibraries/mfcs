// Document Ready
// ===================================================================
$(function(){
    console.log('metadata file loaded');

    // create uniform selects using select 2
    $('select').addClass('form-control');

    // Metadata Instantiation
    $('.metadataObjectEditor').click(metadataModal);
});

function metadataModal(event){
    event.preventDefault();
    console.log('testing click');

    $("#metadataModal .modal-header h3").html($(this).attr("data-header"));
    $("#metadataModal").removeClass('hide').show();

    var dataFieldName = $(this).attr("data-fieldname");
    var formID        = $(this).attr('data-formid');
    var url           = siteRoot+'dataEntry/metadata.php?formID='+formID+'&ajax=true';

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
        }
    });
}