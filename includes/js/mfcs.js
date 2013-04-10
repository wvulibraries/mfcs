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
    var selectedProjects = [];
    $('#selectProjects :checkbox:checked').each(function(i,n){
        selectedProjects.push($(n).val());
    });
    // And POST it to the server
    $.post('index.php',{
        engineCSRFCheck:  '{engine name="csrfGet"}',
        action:           'updateUserProjects',
        selectedProjects: selectedProjects
    },function(){ alert('DONE!') });
}