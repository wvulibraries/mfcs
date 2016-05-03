// Document Ready Stuff
$(function(){
    var moveObjectsToForm;
    var selectedItemsArray = [];
    var lastSearchForm = $('.searchedFormId').data('formid');

    // Watch the Objects to see if they have been checked
    $('.moveObjectCheckbox').change(function(){
        var value = $(this).val();
        var indexValue = selectedItemsArray.indexOf(value);

        if($(this).is(':checked')){
            if(indexValue == -1){
                selectedItemsArray.push(value);
            } else {
                selectedItemsArray.splice(indexValue, 1);
            }
        } else {
            selectedItemsArray.splice(indexValue, 1);
        }

        $('#selectedObjectIDs').val(selectedItemsArray.join());
    });


    // Check / Uncheck All Objects
    $('.selectAllObjects').click(function(){
        $('.moveObjectCheckbox').prop('checked', true).change();
    });

    $('.removeAllObjects').click(function(){
        $('.moveObjectCheckbox').prop('checked', false).change();
    });

    // get SelectForm Info
    $('.selectForm select').change(function(){
       moveObjectsToForm = $(this).val();
    });

    if(isInt(lastSearchForm)){
      $.ajax({
           dataType:'html',
           type: 'get',
           url:'/data/object/move/getCompatibleForms.php',
           data:{ formID: lastSearchForm },

           success:function(){
               console.log('success');
           },

           error: function (jqXHR, textStatus, errorThrown) {
               console.log(textStatus + ': ' + errorThrown);
           },

           complete:function(data){
              $('.compatibleForms').html(data.responseText);
              $('#performMove').removeClass('hidden');
           },
       });
    }


    $('#performMove').submit(function(e){
        e.preventDefault();

        if(selectedItemsArray.length == 0 || (isBlank(moveObjectsToForm)) ){
            $('#formAlert').removeClass('hide');
            $('.submit').removeClass('disabled').attr('readonly', false);
            return false;
        } else {
           var objects = selectedItemsArray.join();

           $.ajax({
                dataType:'json',
                url:'/data/object/move/completeMove/',
                data:{
                    objects: objects,
                    formID: moveObjectsToForm
                },

                success:function(){
                    console.log('success');
                },

                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus + ': ' + errorThrown);
                },

                complete:function(data){
                    console.log(data);
                },
            });
        }

    });
});

function isInt(value) {
  if (isNaN(value)) {
    return false;
  }
  var x = parseFloat(value);
  return (x | 0) === x;
}
