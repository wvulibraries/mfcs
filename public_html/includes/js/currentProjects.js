$(function(){
    var selectedProjectsModal = $('#selectProjectsModal');
    $('.projectToggle').click(function(){
        if(selectedProjectsModal.hasClass('hide')){
            selectedProjectsModal.removeClass('hide').fadeIn(600);
        } else {
            selectedProjectsModal.fadeOut(600).addClass('hide');
        }
    });
    // close modals
    $('.modal').click(function(){
        $(this).fadeOut(600).addClass('hide');
    });
    $('.modal .close, .modal .cancel').click(function(){
       $(this).parent().parent().fadeOut(600).addClass('hide');
    });
});


// Reset the modal's UI when it's hidden
$('#selectProjectsModal').on('hide', function (){
    var IDs = $('#currentProjectsLink').data('selected_projects');
    if(typeof(IDs) != 'string') IDs = IDs.toString();
    if(typeof(IDs) != 'array')  IDs = IDs.split(',');
    $('#selectProjectsModal :checkbox').each(function(i,n){
        var chkBox = $(n);
        var ID = $(n).val();
        chkBox.prop('checked', $.inArray(ID, IDs) !== -1 );
    });
});



function saveSelectedProjects(){
    // Get all the IDs of selected projects
    var selectedProjectIDs   = [];
    var selectedProjectNames = [];
    $('#selectProjectsModal :checkbox:checked').each(function(i,n){
        selectedProjectIDs.push($(n).val());
        selectedProjectNames.push($(n).data('label'));
    });
    // And POST it to the server
    var postData = {
        engineCSRFCheck:  $(':input[name="engineCSRFCheck"]').val(),
        action:           'updateUserProjects',
        selectedProjects: selectedProjectIDs
    };
    $.post(siteRoot+'?ajax',postData,function(data){
        if(data.success){
            var newHTML = selectedProjectIDs.length
                ? selectedProjectNames.join(", ")
                : '<span style="color: #999; font-style: italic;">None Selected</span>';
            $('#currentProjectsLink')
                .html(newHTML)
                .data('selected_projects',selectedProjectIDs.join(','));
        }else{
            alert("An error occurred!\n\n(check the browser console for details)");
            if(typeof(console) != 'undefined') console.log("Error from AJAX call: "+data.errorMsg);
        }
        $('#selectProjectsModal').modal('hide');
    });
}