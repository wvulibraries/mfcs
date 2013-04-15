$(function(){
    $('div.filePreview a').click(function(){
        var filePreview = $(this).closest('div')
        if(filePreview.hasClass('open')){
            // Closing
            filePreview.find('div').slideUp(function(){
                filePreview.removeClass('open');
            });
        }else{
            // Opening
            filePreview.addClass('open');
            filePreview.find('div').slideDown();
        }
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
