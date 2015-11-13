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

    // Navigation Toggles
    $('.toggleNav, .main-nav .close').click(function() {
        $('.main-nav').toggle('slide');
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
        var elementsToSize = indexElments.find('span');
        resizeElms(elementsToSize);
        $(window).on('resize', function(e) {
            elementsToSize.height('auto');
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                resizeElms(elementsToSize);
            }, 250);
        });
    }

    // Navigation Tabs
    if($('.nav-tabs').length){
        $(".nav-tabs a:first").tab("show");
    }
});

function resizeElms(elms){
    console.log(elms)
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


