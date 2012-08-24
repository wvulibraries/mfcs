function init() {

	$('#commonSecurityGroupsToggle').click(function() {
		$('#commonSecurityGroupsList').toggle();
	});

	$('#commonSecurityGroupsList a').click(function() {
		$(':input[name=name_insert]').val($(this).html());
		$(':input[name=type_insert]').val('group');
		$('#commonSecurityGroupsList').hide();
	})

}
