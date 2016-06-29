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