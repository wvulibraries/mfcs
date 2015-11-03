// Document Ready
// ===================================================================
$(function(){
    var projects = CurrentProjects; // init the dipslay object
    projects.init();
    $('.projectToggle').click(projects.toggleLogic);
    $('.modal .close, .modal .cancel').click(projects.closeModal);
    $('.submitProjects').click(projects.saveSelectedProjects);

    // alert window for non matching current project
    setTimeout(removeFormAlert, 15000);

    $(window).on("scroll", function(){
        var scrollPos = $(window).scrollTop();
        if (scrollPos >= 50) {
           removeFormAlert();
        }
    });

    // $('html, body').css({
    //     'overflow': 'hidden',
    //     'height': '100%'
    // });
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
        }
        else {
            CurrentProjects.closeProjectsModal();
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