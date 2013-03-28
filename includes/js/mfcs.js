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