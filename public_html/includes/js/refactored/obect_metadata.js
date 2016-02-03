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

function submitMetadataModal() {
    var metadataFormID = 0;
    $("#metadataModalBody form").each(function() {

        data           = $(this).serialize();
        metadataFormID = $(this).data("choicesform");

        if ($(this).attr("name") == "insertForm") {
            data = data + "&submitForm=Submit"
        }
        // else if ($(this).attr("name") == "updateForm") {
        //     data = data + "&updateEdit=Update";
        // }

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


function closeModal(event){
    event.preventDefault();
    $('#metadataModal').fadeOut().addClass('hide');
    $('#metadataModalBody').html(defaultModalBody);
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
