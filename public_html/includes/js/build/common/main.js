// Document Ready
// ===================================================================
$(function(){
    var projects = CurrentProjects; // init the dipslay object
    projects.init();
    $('.projectToggle').click(projects.toggleLogic);
    $('.menuModal .close, .menuModal .cancel').click(projects.closeModal);
    $('.submitProjects').click(projects.saveSelectedProjects);
    $('.bgCloak').click(projects.closeModal);

    // alert window for non matching current project
    setTimeout(removeFormAlert, 15000);

    $(window).on("scroll", function(){
        var scrollPos = $(window).scrollTop();
        if (scrollPos >= 50) {
           removeFormAlert();
        }
    });

    $(document).keyup(function(e) {
        if (e.keyCode == 27) { // escape key maps to keycode `27`
            if($('.projectToggle').hasClass('active')){
                projects.closeModal();
            }
        }
    });
});

// Fake JS Class
// ===================================================================
CurrentProjects = {
    init: function(){
        this.displayCurrentProjects(JSON.parse(userCurrentProjects));
    },

    closeModal:function(){
        $('#selectProjectsModal').fadeOut(600).addClass('hide');
        CurrentProjects.resetCheckBoxes(JSON.parse(userCurrentProjects));
        $('.bgCloak').hide();
        $('html,body').removeClass('modalBlockScroll');
        $('.projectToggle').removeClass('active');
    },

    displayNoProjects:function(){
        console.log('no projects to dipslay');
        $('.selectedProjects .tags').html('<li class="label label-warning"> No Projects Selected </li>');
    },

    displayCurrentProjects:function(projects){
        if(projects.length == "0"){
            this.displayNoProjects();
        }
        else{
            var output = "";
            $.each(projects, function(index,object){
                output += "<li><div class='label label-default'>"+object+"</div></li>";
            });
            $('.selectedProjects .tags').html(output);
        }
    },

    resetCheckBoxes:function(projects){
        $('#selectProjectsModal :checkbox').each(function(i,n){
            var chkBox = $(n);
            var ID = $(n).val();
            if(typeof projects[ID] != 'undefined'){
                chkBox.prop('checked', true);
            }
            else {
                chkBox.prop('checked', false);
            }
        });
    },

    toggleLogic:function(){
        var selectedProjectsModal = $('#selectProjectsModal');
        if(selectedProjectsModal.hasClass('hide')){
            selectedProjectsModal.removeClass('hide').fadeIn(600);
            $('.bgCloak').show();
            $('html,body').addClass('modalBlockScroll');
            $('.projectToggle').toggleClass('active')
        }
        else {
            CurrentProjects.closeModal();
        }
    },

    saveSelectedProjects:function(){
        // Get all the IDs of selected projects
        var selectedProjectIDs   = [];
        var selectedProjectNames = [];
        var viewObject           = {};
        $('#selectProjectsModal :checkbox:checked').each(function(i,n){
            selectedProjectIDs.push($(n).val());
            selectedProjectNames.push($(n).data('label'));
            var item = $(n).val();
            viewObject[item] = $(n).data('label');
        });

        // close the window
        CurrentProjects.closeModal();
        CurrentProjects.resetCheckBoxes(viewObject);

        // And POST it to the server
        var postData = {
            engineCSRFCheck:  $(':input[name="engineCSRFCheck"]').val(),
            action:           'updateUserProjects',
            selectedProjects: selectedProjectIDs
        };
        $.post(siteRoot+'?ajax',postData,function(data){
            if(data.success){
                if(viewObject.length == "0"){
                    CurrentProjects.displayNoProjects();
                } else {
                    CurrentProjects.displayCurrentProjects(viewObject);
                }
            }else{
                alert("An error occurred!\n\n(check the browser console for details)");
                if(typeof(console) != 'undefined') console.log("Error from AJAX call: "+data.errorMsg);
            }
            $('#selectProjectsModal').modal('hide');
        });
    },

}

// Form Alert Function
// ===================================================================

function removeFormAlert(){
    var formAlert = $('.formAlert');
    if(formAlert.is(':visible')){
        formAlert.hide();
    }
}
function previewFile(linkObj,url){
	var $link     = $(linkObj);
	var $modal    = $('#filePreviewModal');
	var linkLabel = $link.text();
	var filename  = $link.closest('.btn-group').siblings('.filename').text();
	var iFrame    = $modal.find('iframe.filePreview')[0];

	// Create a ucfirst() version of the linkLabel
	var typeLabel = linkLabel.charAt(0).toUpperCase() + linkLabel.substr(1).toLowerCase();

	$modal.modal('hide');
	iFrame.src = 'about:blank';
	$modal.find('h3').html( filename+' - '+typeLabel );
	iFrame.src = url;
	$modal.find('a.previewDownloadLink')[0].href = url+'&download=1';
	$modal.modal('show');
}


// Document Ready
// ===================================================================
$(function(){
    $("#objectListingTable").tablesorter();

    $('form').submit(function(){
        $(this).find(':submit').addClass('disabled').attr('readonly','readonly');
    });

    // add style stuff
    $('.page-header').parent().addClass('main').wrapInner('<div class="widthContainer"></div>');

    // Current Project Tags
    $(window).on("scroll", function() {
        var scrollPos = $(window).scrollTop();
        if (scrollPos <= 50) {
            $(".tags").fadeIn();
        } else {
            $(".tags").fadeOut();
        }
    });

    // shelf list controls
    $('.shelfList').hide();
    $('.expandShelfList').click(function(){
        $(this).parent().next().slideToggle('fast').toggleClass('active');
        if($(this).parent().next().hasClass('active')){
            $(this).removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
        } else {
            $(this).removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
        }
    });

    // Navigation Toggles
    $('.toggleNav, .main-nav .close').click(function() {
        $('.main-nav').toggle('slide', {direction:'right'});
    });

    $(document).keyup(function(e) {
         if (e.keyCode == 27) { // escape key maps to keycode `27`
            $('.main-nav').hide('slide', {direction:'right'});

            if($('.modal').hasClass('show')){
                $('.bgCloak').hide();
                $('html,body').removeClass('modalBlockScroll');
                $('.modal').removeClass('show').hide();
            }
        }
    });

    // add event listeners
    $(document)
        .on('change', '#searchFormSelect',                   handler_setupSearchFormFields)
        .on('change', '#paginationPageDropdownID',           handler_jumpToPage)
        .on('change', '#paginationRecordsPerPageDropdownID', handler_setPaginationPerPage)
        .on('submit', '#jumpToIDNOForm',                     handler_jumpToIDNO);


    // index page equal heights
    var indexElments = $('.createIndex');
    if(indexElments.length){
        var resizeTimer;
        var elementsToSize = indexElments.find($('.text'));
        resizeElms(elementsToSize);
        $(window).on('resize', function(e) {
            elementsToSize.height('auto');
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                resizeElms(elementsToSize);
            }, 150);
        });
    }

    // Navigation Tabs
    if($('.nav-tabs').length){
        $(".nav-tabs a").first().tab("show");
    }

    // add required to fields
    $('.requiredField').append("<span> * </span>");
});

function resizeElms(elms){
    var maxHeight = Math.max($(elms[0]).height(),$(elms[1]).height(),$(elms[2]).height());
    $(elms).height(maxHeight);
}

// Helper Functions
// ===================================================================

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
