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
    $.post('index.php?ajax',{
        engineCSRFCheck:  $(':input[name="engineCSRFCheck"]').val(),
        action:           'updateUserProjects',
        selectedProjects: selectedProjectIDs
    },function(data){
        if(data.success){
            alert('Success!');
            var newHTML = selectedProjectIDs.length
                ? selectedProjectNames.join(", ")
                : '<span style="color: #999; font-style: italic;">None Selected</span>';
            $('#currentProjectsLink')
                .html(newHTML)
                .data('selected_projects',selectedProjectIDs);
            $('#selectProjectsModal').modal('hide')
        }else{
            alert("An error occurred!\n\n(check the browser console for details)")
            if(typeof(console) != 'undefined') console.log("Error from AJAX call: "+data.errorMsg);
        }
    });
}