function init() {

	$(":input[name^='formType']").each(function() {
		var name = $(this).attr('name').split("_");
		var id = name[name.length-1];

		toggleGroupName(id);
		$(this).change(function() {
			toggleGroupName(id);
		})
	});

	$(":input[name^='parentForm']").each(function() {
		var name = $(this).attr('name').split("_");
		var id = name[name.length-1];

		toggleLocation(id);
		$(this).change(function() {
			toggleLocation(id);
		})
	});

}

function toggleGroupName(id) {

	if ($(":input[name='formType_"+id+"']").val() == 'record') {
		$(":input[name='groupName_"+id+"']").val('').attr('disabled',true);
	}
	else {
		$(":input[name='groupName_"+id+"']").removeAttr('disabled');
	}

}

function toggleLocation(id) {

	if ($(":input[name='parentForm_"+id+"']").val() == 0) {
		$(":input[name='insertLocation_"+id+"']").val('').attr('disabled',true);
	}
	else {
		$(":input[name='insertLocation_"+id+"']").removeAttr('disabled');
	}

}
