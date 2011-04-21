function init() {

	$(":input[name^='formType']").each(function() {

		var name = $(this).attr('name').split("_");
		var id = name[name.length-1];

		toggleWritableGroupName(id);
		$(this).change(function() {
			toggleWritableGroupName(id);
		})
			
	})

}

function toggleWritableGroupName(id) {
	
	if ($(":input[name='formType_"+id+"']").val() == 'record') {
		$(":input[name='groupName_"+id+"']").val('').attr('disabled',true);
	}
	else {
		$(":input[name='groupName_"+id+"']").removeAttr('disabled');
	}

}
