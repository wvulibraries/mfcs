
$(function() {
	// Grab commonly used IDs
	var formPreview  = $('#formPreview');
	var fieldAdd     = $('#fieldAdd');
	var formSettings = $('#formSettings');
	var fieldTab     = $('#fieldTab');
	var leftPanel    = $('#leftPanel');

	// Blank all panes when changing tabs
	fieldTab.on("click", "a", function() {
		$('li', formPreview).removeClass("well");
		showFieldSettings(); // blank the Field Settings pane
	});

	$(".draggable li", fieldAdd)
		// Make field types draggable, linked to preview pane
		.draggable({
			connectToSortable: "#formCreator ul.sortable",
			helper: "clone",
			revert: "invalid"})
		// Add new field on click as well as drag
		.click(function() {
			event.preventDefault();

			$(this).clone().appendTo(formPreview);
			addNewField($("li:last",formPreview));
			sortableForm();
		});

	// Delete icon binding
	formPreview.on("click", ".fieldPreview i.icon-remove", function() {
		if (confirm("Are you sure you want to remove this field?")) {
			var thisLI = $(this).parent().parent();

			// If I'm a fieldset, move any fields that are within me
			if ($(this).parent().next().children(":input[name^=type_]").val() == 'fieldset') {
				thisLI.after($(this).next().find("li"));
			}
			// Delete this li
			thisLI.remove();

			if ($("#formSettings_formMetadata").not(":checked")) {
				// Enable/disable Production Form setting based on whether an idno field exists
				if ($(":input[name^=type_][value=idno]",formPreview).length == 0) {
					$("#formSettings_formProduction").prop({
						checked:  false,
						disabled: true,
						title:    "This form needs an ID Number field.",
					});
				}
				else {
					$("#formSettings_formProduction").removeAttr("disabled").removeAttr("title");
				}
			}
		}
	});

	// Re-order nesting on load
	// This loops through <li> and finds all the fieldsets, then loops through matching all <li> that have
	// the same fieldset name and moves them inside it
	$(".fieldValues :input[name^='type_'][value='fieldset']").each(function() {
		var fieldset = $(this).closest("li").prop("id");
		$(".fieldValues :input[name^='fieldset_'][value='"+$(this).siblings(":input[name^='fieldset_']").val()+"']").each(function() {
			if (fieldset != $(this).closest("li").prop("id")) {
				$(this).closest("li").detach().appendTo($("#"+fieldset+" ul"));
			}
		});
	});

	// Make the preview pane sortable -- sort order determines position
	sortableForm();

	// Set all the black magic bindings
	fieldSettingsBindings();
	formSettingsBindings();
	modalBindings();

	// Form submit handler
	$("form[name=submitForm]").submit(function(e) {
		// e.preventDefault();

		// Calculate position of all fields
		$(".fieldValues :input[name^=position_]").each(function(index) {
			$(this).val(index);
		});

		// Create a multidimentional object to store field info
		var obj = {};
		$(":input",formSettings).each(function() {
			var form = $(this).prop("name").split("_");

			if ($(this).prop("type") == "checkbox") {
				obj[ form[1] ] = $(this).prop("checked");
			}
			else {
				obj[ form[1] ] = $(this).val();
			}
		});
		// Convert object to JSON and add it to a hidden form field
		$(":input[name=form]", this).val(JSON.stringify(obj));

		// Create a multidimentional object to store field info
		var obj = {};
		$(".fieldValues :input").each(function() {
			var field = $(this).prop("name").split("_");

			if (!obj[ field[1] ]) {
				obj[ field[1] ] = {};
			}

			if ($(this).is('[id^="choicesOptions_"]') || $(this).is('[id^="allowedExtensions_"]')) {
				obj[ field[1] ][ field[0] ] = {};
				obj[ field[1] ][ field[0] ] = $(this).val().split("%,%");
			}
			else {
				obj[ field[1] ][ field[0] ] = $(this).val();
			}
		});

		// Remove fieldsets from submission
		for (var i in obj) {
			if (obj[i]['type'] == 'fieldset' || obj[i]['type'] == 'Field Set') {
				delete obj[i];
			}
		};

		// Convert object to JSON and add it to a hidden form field
		$(":input[name=fields]", this).val(JSON.stringify(obj));
	});

	// Click through each field and then back to add field tab on page load to update form preview
	$("li",formPreview).click();
	$("li:last a",fieldTab).click();

	// Make the left panel fixed if the viewport is big enough to hold the content
	$(window).scroll(function() {
		var left          = $('#leftPanel');
		var leftParent    = left.closest('.row-fluid');
		var leftParentTop = leftParent.offset().top - 55;
		var leftHeight    = $('.tab-content',leftPanel).outerHeight() + $('#fieldTab').outerHeight() + 170;

		// Is the window big enough?
		if ($(window).height() > leftHeight) {
			// Yes - should we fix it?
			if($(window).scrollTop() >= leftParentTop){
				left.addClass('fix');
				left.css("width",left.parent().width());
			}else{
				left.removeClass('fix');
			}
		}
		else {
			// No - make sure it's not currently fixed
			left.removeClass('fix');
		}
	}).scroll();

	$(window).resize(function() {
		if (leftPanel.hasClass("fix")) {
			leftPanel.css("width",leftPanel.parent().width());
		}
		else {
			leftPanel.css("width",'auto');
		}
	});

	$('#progressModal').modal('hide');

	// Enable the submit button and hide thenoJavaScriptWarning
	$(':submit').removeAttr('disabled');
});