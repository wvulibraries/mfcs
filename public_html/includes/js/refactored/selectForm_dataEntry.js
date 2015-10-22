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
            $(this).html("Show Metadata Forms");
        }
        else {
            $(this).html("Hide Metadata Forms");
        }
    });
});