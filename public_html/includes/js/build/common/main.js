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
        $(".nav-tabs a:first").tab("show");
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


