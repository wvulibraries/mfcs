// Global Variable
// ===================================================================
var globalFieldID;

// Document Ready
// ===================================================================
$(function(){
	// Grab commonly used IDs
	var formPreview  = $('#formPreview');
	var fieldAdd     = $('#fieldAdd');
	var formSettings = $('#formSettings');
	var fieldTab     = $('#fieldTab');
	var leftPanel    = $('#leftPanel');
	var testStart = performance.now();

	// helper functions
    sortableForm();
    fieldSettingsBindings();
	formSettingsBindings();
	modalBindings();
	applyLabelName();


	// Blank all panes when changing tabs
	fieldTab.on("click", "a", function() {
		$('li', formPreview).removeClass("well");
		showFieldSettings(); // blank the Field Settings pane
	});

	// Click and Draggable form fields.
	$(".draggable li", fieldAdd)
		.draggable({
			connectToSortable: "#formCreator ul.sortable",
			helper: "clone",
			revert: "invalid"})
		.click(function() {
			event.preventDefault();
			$(this).clone().appendTo(formPreview);
			addNewField($("li:last",formPreview));
			sortableForm();
	});

	// Deleted The field
	formPreview.on("click", ".fieldPreview i.icon-remove", function() {
		if (confirm("Are you sure you want to remove this field?")) {
			var thisLI = $(this).parent().parent();

			if ($(this).parent().next().children(":input[name^=type_]").val() == 'fieldset') {
				thisLI.after($(this).next().find("li"));
			}

			thisLI.remove();

			if ($("#formSettings_formMetadata").not(":checked")) {
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

	// Form submit handler
	$("form[name=submitForm]").submit(function(e) {
		// Undo Bindings
		$("#formPreview").find('[data-bind]').unbind('change', setOriginalValues);
		$('#fieldSettings_form').find("[data-bindmodel]").unbind('change keyup', bindToHiddenForm);

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

	// Enable the submit button and hide thenoJavaScriptWarning
	$(':submit').removeAttr('disabled');
});


// Create For Nav Sumbissions
// ===================================================================
$(function() {
	var groupingsPreview = $("#GroupingsPreview");

	// Blank all panes when changing tabs
	$("#groupingTab").on("click", "a", function() {
		groupingsPreview.find("li").removeClass("well");
		showSettings(); // blank the Settings pane
	});

	// Make draggable, linked to preview pane
	$("#groupingsAdd .draggable li").draggable({
		connectToSortable: "#navigation ul.sortable",
		helper: "clone",
		revert: "invalid",
		cancel: ".noDrag",
	}).disableSelection();

	// Add new on click as well as drag
	$("#groupingsAdd").on("click", "li", function(event) {
		event.preventDefault();

		if (!$(this).hasClass("noDrag")) {
			$(this).clone().appendTo(groupingsPreview);
			addNew(groupingsPreview.find("li:last"));
			sortableNav();
		}
	});

	// Delete form handlers
	$('#deleteFormBtn-Cancel').click(function(){
		$("a[href='#formCreator']").click();
	});
	$('#deleteFormBtn-Submit').click(function(e){
		if(prompt("Last chance!\nAre you sure you want to permanently delete this form?\n\nAnything but 'yes' will cancel").toLowerCase() == 'yes'){
			$('#deleteFormFrm').submit();
		}
	});

	groupingsPreview
		// Delete icon binding
		.on("click", ".groupingPreview i.icon-remove", function() {
			if (confirm("Are you sure you want to remove this grouping?")) {
				var thisLI = $(this).parent().parent();

				// If I'm a grouping, move any groupings that are within me
				if ($(this).parent().next().children(":input[name^=nav_type_]").val() == 'grouping') {
					thisLI.after($(this).next().find("li"));
				}
				// Delete this li
				thisLI.remove();
			}
		})
		// Disable links in preview
		.on("click", "a", function(event) {
			event.preventDefault();
		});

	// Re-order nesting on load
	// This loops through <li> and finds all the fieldsets, then loops through matching all <li> that have
	// the same grouping name and moves them inside it
	$(".groupingValues :input[name^='nav_type_'][value='grouping']").each(function() {
		var grouping = $(this).parents("li").prop("id");
		$(".groupingValues :input[name^='nav_grouping_'][value='"+$(this).siblings(":input[name^='nav_grouping_']").val()+"']").each(function() {
			if (grouping != $(this).parents("li").prop("id")) {
				$(this).parents("li").detach().appendTo($("#"+grouping+" ul"));
			}
		});
	});

	sortableNav();
	settingsBindings();

	// Click through each field and then back to add field tab on page load to update form preview
	groupingsPreview.find("li").click();
	$("#groupingTab li:first a").click();

	$("form[name=submitNavigation]").submit(function(event) {
		// event.preventDefault();

		// Calculate position of all fields
		$(".groupingValues :input[name^=nav_position_]").each(function(index) {
			$(this).val(index);
		});

		// Create a multidimentional object to store field info
		var obj = {};
		$(".groupingValues :input").each(function() {
			var grouping = $(this).prop("name").split("_");

			if (!obj[ grouping[2] ]) {
				obj[ grouping[2] ] = {};
			}

			obj[ grouping[2] ][ grouping[1] ] = $(this).val();
		});

		// Remove groupings from submission
		for (var i in obj) {
			if (obj[i]['type'] == 'grouping') {
				delete obj[i];
			}
		};

		// Convert object to JSON and add it to a hidden form field
		$(":input[name=groupings]", this).val(JSON.stringify(obj));

		$("#groupingsSettings :input").prop("disabled", true);
		groupingsPreview.find(":input").prop("disabled", true);
	});
});




// Helper Functions
// ===================================================================
function applyLabelName(){
	$('.fieldLabels').each(function(){
		var label = $(this).parent().parent().next().find($('input[name^="name"]')).val();
		$(this).html(label);
	});
}

function sortableForm() {
	$("#formCreator ul.sortable").sortable({
		connectWith: "#formCreator ul.sortable",
		revert: true,
		placeholder: "highlight",
		update: function(event, ui) {
			// Only perform this if it's a brand new field
			if ($(ui.item).hasClass("ui-draggable")) {
				// Block fieldsets within fieldsets
				if ($(ui.item).text() == 'Field Set' && $(ui.item).parent().attr("id") != "formPreview") {
					$(ui.item).remove();
				}
				// Convert text to preview
				addNewField(ui.item);
			}
			$(ui.item).parents("li").click();
			$(ui.item).click();
			sortableForm();
		}
	});
	calculatePosition();
}

function calculatePosition(){
	$('#formCreator ul.sortable').children('li').each(function(index){
		$(this).attr('data-position', index);
		$(this).find("[data-bind='position']").val(index);
	});
}

// This function creates the form view
// It allows you to see the fields, drag and drop
// It also selects what field options show when a field
// Is selected from the list.

function showFieldSettings(fullID) {
	// Create jQuery shortcuts (code optimization)
	var fieldSettings_form          = $("#fieldSettings_form");
	var fieldSettings_fieldset_form = $("#fieldSettings_fieldset_form");

	if (fullID === undefined) {
		$("#noFieldSelected").show();
		fieldSettings_fieldset_form.hide();
		fieldSettings_form.hide();
	}
	else {
		var id       = fullID;
		var type     = $("#type_"+id).val();
		var fieldset = $("#fieldset_"+id);
		var opts;
		var tmp;
		var i;

		// Hide the nothing selected error and show the form
		$("#noFieldSelected").hide();
		if (type == "fieldset") {
			fieldSettings_fieldset_form.show();
			fieldSettings_form.hide();
		}
		else {
			fieldSettings_fieldset_form.hide();
			fieldSettings_form.show();
			fieldSettings_form.find('.dataSettings').children().hide();
			fieldSettings_form.find('.default').show();

			// Create jQuery shortcuts (code optimization)
			var fieldSettings_name                 = $("#fieldSettings_name");
			var fieldSettings_options_required     = $("#fieldSettings_options_required");
			var fieldSettings_options_duplicates   = $("#fieldSettings_options_duplicates");
			var fieldSettings_options_displayTable = $("#fieldSettings_options_displayTable");
			var fieldSettings_options_readonly     = $("#fieldSettings_options_readonly");
			var fieldSettings_options_disabled     = $("#fieldSettings_options_disabled");

			if (type == 'idno') {
				fieldSettings_name.prop("readonly", true).val("idno").keyup();
				fieldSettings_options_required.prop({
					checked:  true,
					disabled: true,
				}).change();
				fieldSettings_options_duplicates.prop({
					checked:  true,
					disabled: true,
				}).change();
				fieldSettings_options_displayTable.prop({
					checked:  true,
					disabled: true,
				}).change();
				fieldSettings_options_readonly.prop("disabled", true);
				fieldSettings_options_disabled.removeAttr("checked").change().prop("disabled", true);
			}
			else if (type == 'file') {
				fieldSettings_options_displayTable.removeAttr("checked").change().prop("disabled", true);
			}
			else {
				fieldSettings_name.removeAttr("readonly");
				fieldSettings_options_required.removeAttr("disabled");
				fieldSettings_options_duplicates.removeAttr("disabled");
				fieldSettings_options_readonly.removeAttr("disabled");
				fieldSettings_options_disabled.removeAttr("disabled");
				fieldSettings_options_displayTable.removeAttr("disabled");
			}

			// Show optional fields
			switch(type) {
				case 'idno':
					$("#fieldSettings_container_idno").parent().show();
					break;

				case 'text':
					$("#fieldSettings_container_externalUpdate").parent().show();
					$("#fieldSettings_container_range").parent().show();
					$("#fieldSettings_range_step").parent().hide();
					$('#fieldSettings_range_format').parent().removeClass('span4').addClass('span6');
					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="characters">Characters</option><option value="words">Words</option>');
					break;

				case 'textarea':
					$("#fieldSettings_container_range").parent().show();
					$("#fieldSettings_range_step").parent().hide();
					$('#fieldSettings_range_format').parent().removeClass('span4').addClass('span6');
					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="characters">Characters</option><option value="words">Words</option>');
					break;

				case 'radio':
				case 'checkbox':
				case 'select':
				case 'multiselect':
					$("#fieldSettings_container_choices").parent().show();
					break;

				case 'number':
					$("#fieldSettings_container_range").parent().show();
					$("#fieldSettings_range_step").parent().show();
					$('#fieldSettings_range_format').parent().removeClass('span6').addClass('span4');

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="value">Value</option><option value="digits">Digits</option>');
					break;

				case 'wysiwyg':
					$("#fieldSettings_container_placeholder").hide();
					break;

				case 'file':
					$("#fieldSettings_container_file_allowedExtensions").show();
					$("#fieldSettings_container_file_options").show();
					$("#fieldSettings_container_value").hide();
					$("#fieldSettings_container_placeholder").hide();
					break;

				default:
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();
					break;
			}

			var fieldHelp = $("#help_"+id).val();
			if(fieldHelp !== ''){
				var n = fieldHelp.indexOf('|');
				var fieldHelpType  = fieldHelp.slice(0,n);
				var fieldHelpValue = fieldHelp.slice(n+1);
				$("#fieldSettings_help_type").val(fieldHelpType).change();
				switch(fieldHelpType){
					case 'text':
						// de-escape HTML-breaking characters
						fieldHelpValue = fieldHelpValue.replace(/&#34;/g, '"');
						fieldHelpValue = fieldHelpValue.replace(/&#39;/g, "'");
						fieldHelpValue = fieldHelpValue.replace(/&#62;/g, '>');
						fieldHelpValue = fieldHelpValue.replace(/&#60;/g, '<');
						$("#fieldSettings_help_text").val(fieldHelpValue).keyup();
						break;
					case 'html':
						// de-escape HTML-breaking characters
						fieldHelpValue = fieldHelpValue.replace(/&#34;/g, '"');
						fieldHelpValue = fieldHelpValue.replace(/&#39;/g, "'");
						$("#fieldSettings_help_html").val(fieldHelpValue).keyup();
						break;
					case 'web':
						$("#fieldSettings_help_url").val(fieldHelpValue).keyup();
						break;
				}
			}else{
				$("#fieldSettings_help_type").val('').change();
			}

			var choicesOptions_val = $("#choicesOptions_"+id).val();
			if (choicesOptions_val !== undefined) {
				var fieldSettings_choices_manual = $("#fieldSettings_choices_manual");
				opts                             = choicesOptions_val.split("%,%");
				tmp                              = '';
				// Update left panel
				for (i = 0; i < opts.length; i++) {
					tmp += addChoice(opts[i],$("#choicesDefault_"+id).val());
				}
				fieldSettings_choices_manual.html(tmp).find("input[name=fieldSettings_choices_text]").keyup();
			}

			var allowedExtensions_val = $("#allowedExtensions_"+id).val();
			if (allowedExtensions_val !== undefined) {
				var fieldSettings_file_allowedExtensions = $("#fieldSettings_file_allowedExtensions");
				opts                                     = allowedExtensions_val.split("%,%");
				tmp                                      = '';

				fieldSettings_file_allowedExtensions.html('');
				for (i = 0; i < opts.length; i++) {
					tmp += addAllowedExtension(opts[i]);
				}
				fieldSettings_file_allowedExtensions.append(tmp);
				fieldSettings_file_allowedExtensions.find(":input[name=fieldSettings_allowedExtension_text]:first").keyup();
			}

			if (type != 'fieldset') {
				var parentFieldset = fieldset.parents("li").parents("li");
				if (parentFieldset.length > 0) {
					var parentFieldsetID = parentFieldset.prop("id").split("_")[1];
					fieldset.val($("#fieldset_"+parentFieldsetID).val());
				}
				else {
					fieldset.val('');
				}
			}
			else {
				$("#fieldSettings_fieldset").val(fieldset.val());
			}

			// Do I show the 'Variables' link?
			if(-1 != $.inArray(type, ['idno','text','textarea','date','time','wysiwyg'])){
				$('#fieldVariablesLink').show();
			}else{
				$('#fieldVariablesLink').hide();
			}

			// bind functionality of form
			enableChoiceFunctionality();
		}
	}
}

function fieldSettingsBindings(){
	var choicesFields = {};
	var formPreview   = $("#formPreview");
	formPreview.children('li').removeClass('activeField');

    // Setup Form Bindings
    $('#fieldSettings_form').find("[data-bindmodel]").bind('change keyup', bindToHiddenForm);

	// Select a field to change settings
	formPreview.on("click", "li", function(event) {
		event.stopPropagation();
		var id = $(this).data('id');
		globalFieldID = id;

		formPreview.find('[data-bind]').bind('change', setOriginalValues);

		if(!$(this).hasClass('activeField')){
			formPreview.find('.activeField').removeClass('activeField');
			$(this).addClass('activeField');
			$("#fieldTab a[href='#fieldSettings']").tab("show");
			showFieldSettings(id);
			setInitialBind();
		}
	});


}  // end function

function setInitialBind(){
    var id = globalFieldID;
    if (typeof id == 'undefined') {
        return;
    }
    else {
        var parentObj  = $("#formPreview").find("[data-id='"+ id +"']");
        var hiddenForm = parentObj.find('.fieldValues');
        hiddenForm.find('[data-bind]').change();
        $("#formPreview").find('[data-bind]').unbind('change', setOriginalValues);
    }
}

function setOriginalValues(){
    var id          = globalFieldID;
    var bindObj     = $(this).data('bind');
    var value      = $(this).is("input[type=checkbox]") ? evaluateCheck($(this)) : $(this).val();
    var bindToInput = $('#fieldSettings_form').find("[data-bindmodel='" + bindObj + "']");

    // Modifications for inputs and selects need to be done here same with checks
    if(bindToInput.is('input[type=text],textarea')){
        bindToInput.val(value);
    }
    else if(bindToInput.is("input[type=checkbox]")) {
		if(value == "true"){
			bindToInput.prop('checked', true);
		}
		else {
			bindToInput.prop('checked', false);
		}
    }
    else if(bindToInput.is('select')) {
        bindToInput.find('option[value="' + value + '"]').prop('selected', true);
    }

    if(bindToInput.is($("#fieldSettings_choices_type"))){
		if(value == 'manual'){
			$('#fieldSettings_container_choices').find('.manual_choices').show();
			$('#fieldSettings_container_choices').find('.form_choices').hide();
		} else {
			$('#fieldSettings_container_choices').find('.manual_choices').hide();
			$('#fieldSettings_container_choices').find('.form_choices').show();
		}
	}
}

function bindToHiddenForm(){
	var id = globalFieldID;
	if (typeof id == 'undefined') {
        return;
    }
    else {
		var inputObj   = $(this).data('bindmodel');
		var value      = $(this).is("input[type=checkbox]") ? evaluateCheck($(this)) : $(this).val();
		var parentObj  = $("#formPreview").find("[data-id='"+ id +"']");
		var label      = $("#formPreview_"+id).find('.fieldLabels');
		var hiddenForm = parentObj.find('.fieldValues');
		hiddenForm.find("[data-bind='"+ inputObj +"']").val(value);

		if(inputObj == 'name'){
			label.html(value);
		}

		if(inputObj == 'choicesType'){
			if(value == 'manual'){
				$('#fieldSettings_container_choices').find('.manual_choices').show();
				$('#fieldSettings_container_choices').find('.form_choices').hide();
			} else {
				$('#fieldSettings_container_choices').find('.manual_choices').hide();
				$('#fieldSettings_container_choices').find('.form_choices').show();
			}
		}

		if(inputObj == 'choicesOptions'){
			console.log('test -- ' + $(this));
		}
	}
}

function evaluateCheck(object){
	return (object.is(':checked') ? true : false);
}

function formSettingsBindings() {
	$("#formTitle").on("click", function() {
		$("#fieldTab a[href='#formSettings']").click();
		$("#formSettings_formTitle").focus();
	});
	$("#formSettings_formTitle").keyup(function() {
		$("#formTitle").html($(this).val());
	}).keyup();

	$("#formDescription").on("click", function() {
		$("#fieldTab a[href='#formSettings']").click();
		$("#formSettings_formDescription").focus();
	});
	$("#formSettings_formDescription").keyup(function() {
		$("#formDescription").html($(this).val());
	}).keyup();

	$("#formSettings_formMetadata").change(function() {
		var fieldAdd = $('#fieldAdd');
		var idnoType = $("#formPreview").find("input[name^=type_][value=idno]");

		if ($(this).is(":checked")) {
			$("#formSettings_linkTitle_container").show();

			if (idnoType.length === 0) {
				$("#formSettings_formProduction").removeAttr("disabled").removeAttr("title");
				fieldAdd.find("li:contains('ID Number')").hide();
				fieldAdd.find("li:contains('Paragraph Text')").hide();
				fieldAdd.find("li:contains('Radio')").hide();
				fieldAdd.find("li:contains('Checkboxes')").hide();
				fieldAdd.find("li:contains('Dropdown')").hide();
				fieldAdd.find("li:contains('Multi-Select')").hide();
				fieldAdd.find("li:contains('File Upload')").hide();
				fieldAdd.find("li:contains('WYSIWYG')").hide();
				fieldAdd.find("li:contains('Field Set')").parent().hide().prev().hide();
			}
			else {
				if (confirm("Enabling this will remove any existing ID Number fields. Do you want to continue?")) {
					idnoType.parent().parent().remove();
					$("#formSettings_formProduction").removeAttr("disabled").removeAttr("title");
					fieldAdd.find("li:contains('ID Number')").hide();
					fieldAdd.find("li:contains('Paragraph Text')").hide();
					fieldAdd.find("li:contains('Radio')").hide();
					fieldAdd.find("li:contains('Checkboxes')").hide();
					fieldAdd.find("li:contains('Dropdown')").hide();
					fieldAdd.find("li:contains('Multi-Select')").hide();
					fieldAdd.find("li:contains('File Upload')").hide();
					fieldAdd.find("li:contains('WYSIWYG')").hide();
					fieldAdd.find("li:contains('Field Set')").parent().hide().prev().hide();
				}
				else {
					$(this).removeAttr('checked');
				}
			}
		}
		else {
			$("#formSettings_linkTitle_container").hide();

			fieldAdd.find("li:contains('ID Number')").show();
			fieldAdd.find("li:contains('Paragraph Text')").show();
			fieldAdd.find("li:contains('Radio')").show();
			fieldAdd.find("li:contains('Checkboxes')").show();
			fieldAdd.find("li:contains('Dropdown')").show();
			fieldAdd.find("li:contains('Multi-Select')").show();
			fieldAdd.find("li:contains('File Upload')").show();
			fieldAdd.find("li:contains('WYSIWYG')").show();
			fieldAdd.find("li:contains('Field Set')").parent().show().prev().show();

			if (idnoType.length === 0) {
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
	}).change();
}




function formSettingsBindings() {
	$("#formTitle").on("click", function() {
		$("#fieldTab a[href='#formSettings']").click();
		$("#formSettings_formTitle").focus();
	});
	$("#formSettings_formTitle").keyup(function() {
		$("#formTitle").html($(this).val());
	}).keyup();

	$("#formDescription").on("click", function() {
		$("#fieldTab a[href='#formSettings']").click();
		$("#formSettings_formDescription").focus();
	});
	$("#formSettings_formDescription").keyup(function() {
		$("#formDescription").html($(this).val());
	}).keyup();

	$("#formSettings_formMetadata").change(function() {
		var fieldAdd = $('#fieldAdd');
		var idnoType = $("#formPreview").find("input[name^=type_][value=idno]");

		if ($(this).is(":checked")) {
			$("#formSettings_linkTitle_container").show();

			if (idnoType.length === 0) {
				$("#formSettings_formProduction").removeAttr("disabled").removeAttr("title");
				fieldAdd.find("li:contains('ID Number')").hide();
				fieldAdd.find("li:contains('Paragraph Text')").hide();
				fieldAdd.find("li:contains('Radio')").hide();
				fieldAdd.find("li:contains('Checkboxes')").hide();
				fieldAdd.find("li:contains('Dropdown')").hide();
				fieldAdd.find("li:contains('Multi-Select')").hide();
				fieldAdd.find("li:contains('File Upload')").hide();
				fieldAdd.find("li:contains('WYSIWYG')").hide();
				fieldAdd.find("li:contains('Field Set')").parent().hide().prev().hide();
			}
			else {
				if (confirm("Enabling this will remove any existing ID Number fields. Do you want to continue?")) {
					idnoType.parent().parent().remove();
					$("#formSettings_formProduction").removeAttr("disabled").removeAttr("title");
					fieldAdd.find("li:contains('ID Number')").hide();
					fieldAdd.find("li:contains('Paragraph Text')").hide();
					fieldAdd.find("li:contains('Radio')").hide();
					fieldAdd.find("li:contains('Checkboxes')").hide();
					fieldAdd.find("li:contains('Dropdown')").hide();
					fieldAdd.find("li:contains('Multi-Select')").hide();
					fieldAdd.find("li:contains('File Upload')").hide();
					fieldAdd.find("li:contains('WYSIWYG')").hide();
					fieldAdd.find("li:contains('Field Set')").parent().hide().prev().hide();
				}
				else {
					$(this).removeAttr('checked');
				}
			}
		}
		else {
			$("#formSettings_linkTitle_container").hide();

			fieldAdd.find("li:contains('ID Number')").show();
			fieldAdd.find("li:contains('Paragraph Text')").show();
			fieldAdd.find("li:contains('Radio')").show();
			fieldAdd.find("li:contains('Checkboxes')").show();
			fieldAdd.find("li:contains('Dropdown')").show();
			fieldAdd.find("li:contains('Multi-Select')").show();
			fieldAdd.find("li:contains('File Upload')").show();
			fieldAdd.find("li:contains('WYSIWYG')").show();
			fieldAdd.find("li:contains('Field Set')").parent().show().prev().show();

			if (idnoType.length === 0) {
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
	}).change();
}

function modalBindings() {
	$("#formTypeSelector")
		.modal({
			show:     false,
			keyboard: false,
			backdrop: "static"
		})
		.on("click", "button:contains('Metadata')", function() {
			// Select Metadata form
			$("#formSettings_formMetadata").prop("checked", true).change();
			// Hide modal
			$("#formTypeSelector").modal("hide");
		})
		.on("click", "button:contains('Object')", function() {
			var fieldAdd                         = $('#fieldAdd');
			var formPreviewWell                  = $("#formPreview .well");
			var fieldSettings_label              = $("#fieldSettings_label");
			var fieldSettings_options_sortable   = $("#fieldSettings_options_sortable");
			var fieldSettings_options_searchable = $("#fieldSettings_options_searchable");

			// Add IDNO field and select options
			fieldAdd.find("li:contains('ID Number')").click();
			fieldSettings_label.val('IDNO').keyup();
			fieldSettings_options_sortable.prop("checked", true).change();
			fieldSettings_options_searchable.prop("checked", true).change();

			// Add Title field and select options
			fieldAdd.find("li:contains('Single Line Text')").click();
			$("#fieldSettings_name").val('title').keyup();
			fieldSettings_label.val('Title').keyup();
			$("#fieldSettings_options_required").prop("checked", true).change();
			$("#fieldSettings_options_duplicates").prop("checked", true).change();
			fieldSettings_options_sortable.prop("checked", true).change();
			fieldSettings_options_searchable.prop("checked", true).change();
			$("#fieldSettings_options_displayTable").prop("checked", true).change();

			// Click through each field and then back to add field tab to update form preview
			$("#formPreview li").click();
			$("#fieldTab li:last a").click();

			// Deselect object form
			$("#formSettings_formMetadata").removeAttr("checked").change();

			// Hide modal
			$("#formTypeSelector").modal("hide");
		});
}

function addNewField(item) {
	// Remove class to designate this is not new for next time
	$(item).removeClass("ui-draggable");

	// Preserve type
	var type = $("a", item).text();

	// Assign an id to new li
	var newID = 0;
	$("#formPreview li").each(function() {
		if ($(this)[0] !== $(item)[0]) {
			var thisID = $(this).attr("id").split("_");
			if (newID <= thisID[1]) {
				newID = parseInt(thisID[1])+1;
			}
		}
	});

	$(item).attr({
			'id': "formPreview_" + newID,
			'data-id': newID,
		}).html('<div class="fieldPreview">'+newFieldPreview(newID,type)+'</div><div class="fieldValues">'+newFieldValues(newID,type)+'</div>');

	// Display settings for new field
	$("#formPreview_"+newID).click();

	if ($("#formSettings_formMetadata").not(":checked")) {
		// Enable/disable Production Form setting based on whether an idno field exists
		if ($("#formPreview").find("input[name^=type_][value=idno]").length === 0) {
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

function newFieldPreview(id,type) {
	var output;
	output = '<i class="icon-remove"></i>';

	if (type == 'Field Set' || type == 'fieldset') {
		output += '<fieldset><legend></legend><ul class="unstyled sortable"></ul></fieldset>';
	}
	else {
		output += '<div class="control-group"><label class="control-label fieldLabels">Untitled</label><div class="controls">';

		switch(type) {
			case 'ID Number':
			case 'idno':
			case 'Single Line Text':
			case 'text':
				output += '<input type="text">';
				break;

			case 'Paragraph Text':
			case 'textarea':
				output += '<textarea></textarea>';
				break;

			case 'Radio':
			case 'radio':
				output += '<label class="radio"><input type="radio">First Choice</label><label class="radio"><input type="radio">Second Choice</label>';
				break;

			case 'Checkboxes':
			case 'checkbox':
				output += '<label class="checkbox"><input type="checkbox">First Choice</label><label class="checkbox"><input type="checkbox">Second Choice</label>';
				break;

			case 'Dropdown':
			case 'select':
				output += '<select></select>';
				break;

			case 'Number':
			case 'number':
				output += '<input type="number">';
				break;

			case 'Email':
			case 'email':
				output += '<input type="email">';
				break;

			case 'Phone':
			case 'tel':
				output += '<input type="tel">';
				break;

			case 'Date':
			case 'date':
				output += '<input type="date">';
				break;

			case 'Time':
			case 'time':
				output += '<input type="time">';
				break;

			case 'Website':
			case 'url':
				output += '<input type="url">';
				break;

			case 'Multi-Select':
			case 'multiselect':
				output += '<select multiple></select><br><select></select>';
				break;

			case 'WYSIWYG':
			case 'wysiwyg':
				output += '<img src="../includes/img/wysiwyg.png">';
				break;

			case 'File Upload':
			case 'file':
				output += '<input type="file" disabled>';
				break;

			default:
				break;
		}
		output += ' <span class="fa fa-question-circle helpPreview" style="display: none; cursor: pointer;"></span>';
		output += ' <span class="fa fa-question-circle helpPreviewModal" style="display: none; cursor: pointer;" title="Click to view help" onclick="$(\'#fieldHelpModal\').modal(\'show\')"></span>';
		output += '</div></div>';
	}
	return output;
}

function newFieldValues(id,type,vals) {
	var output = "";

	if (vals === undefined) {
		vals = {};
        vals.validation = determineValidation(type);
	}

    vals.type = determineType(type);
    type = vals.type;

    var help = vals.help;
    if(typeof help !== "undefined"){
	    if(help.length > 0){
				var helpValue = help.split("|")[1];
				var helpType  = help.split("|")[0];
				vals.help     = helpValue;
				vals.helpType = helpType;
	    }
	}

    var defaultHiddenFormFields = ['name','position', 'type', 'label', 'value', 'placeholder', 'id', 'class', 'style',
    'help', 'helpType', 'required', 'duplicates', 'readonly', 'disabled', 'disabledInsert', 'disabledUpdate', 'publicRelease',
    'sortable', 'searchable', 'displayTable', 'hidden', 'validation', 'validationRegex', 'access', 'fieldset' ];

    output += createHiddenFields(defaultHiddenFormFields, id, vals);

    // handle additional form information based on field added
	switch(type) {
		case 'idno':
            var idnoHiddenFields = ['managedBy', 'idnoFormat'];
            output += createHiddenFields(idnoHiddenFields, id, vals);
			output += '<input type="hidden" id="startIncrement_'+id+'" name="startIncrement_'+id+'"   data-bind="startIncrement_'+id+'"    value="'+((vals.startIncrement !== undefined)?vals.startIncrement:'1')+'">';
			output += '<input type="hidden" id="idnoConfirm_'+id+'"    name="idnoConfirm_'+id+'"      data-bind"idnoConfirm_'+id+'"        value="false">';  // why is this hard coded
			break;

		case 'text':
            var textHiddenFields = ['externalUpdateForm', 'externalUpdateField', 'min', 'max', 'step', 'format'];
            output += createHiddenFields(textHiddenFields, id, vals);
			break;

		case 'textarea':
		case 'number':
            var textHiddenFields = ['min', 'max', 'step', 'format'];
            output += createHiddenFields(textHiddenFields, id, vals);
			break;

		case 'radio':
		case 'checkbox':
		case 'select':
		case 'multiselect':
            var choiceHiddenFields = ['choicesType', 'choicesNull', 'choicesDefault', 'choicesForm', 'choicesField', 'choicesFieldDefault'];
            output += createHiddenFields(choiceHiddenFields, id, vals);
			output += '<input type="hidden" id="choicesOptions_'+id+'" name="choicesOptions_'+id+'" data-bind="choicesOptions" value="'+((vals.choicesOptions !== undefined)?vals.choicesOptions:'First Choice%,%Second Choice')+'">';
			break;

		case 'file':
            var fileHiddenFields = [
                'bgProcessing', 'multipleFiles', 'combine', 'ocr', 'convert', 'convertHeight', 'convertWidth', 'watermark', 'watermarkImage',
                'watermarkLocation', 'border', 'borderHeight', 'borderWidth', 'borderColor', 'thumbnail', 'convertAudio', 'bitRate', 'audioFormat', 'convertVideo',
                'videoHeight', 'videoWidth', 'videobitRate', 'aspectRatio', 'videoFormat', 'videothumbnail', 'videoThumbFrames', 'videoThumbHeight',
                'videoThumbWidth', 'videoFormatThumb'];


             // default values
            output += '<input type="hidden" id="allowedExtensions_'+id+'" name="allowedExtensions_'+id+'"     data-bind="allowedExtensions_'+id+'"    value="'+((vals.allowedExtensions !== undefined)?vals.allowedExtensions:'tif%,%tiff,mp4')+'">';
			output += '<input type="hidden" id="allowedExtensions_'+id+'" name="allowedExtensions_'+id+'"     data-bind="allowedExtensions_'+id+'"    value="'+((vals.allowedExtensions !== undefined)?vals.allowedExtensions:'tif%,%tiff,mp4')+'">';
			output += '<input type="hidden" id="convertResolution_'+id+'" name="convertResolution_'+id+'"     data-bind="convertResolution_'+id+'"    value="'+((vals.convertResolution !== undefined)?vals.convertResolution:'192')+'">';
			output += '<input type="hidden" id="convertFormat_'+id+'"     name="convertFormat_'+id+'"         data-bind="convertFormat_'+id+'"        value="'+((vals.convertFormat !== undefined)?vals.convertFormat:'JPG')+'">';
			output += '<input type="hidden" id="thumbnailHeight_'+id+'"   name="thumbnailHeight_'+id+'"       data-bind="thumbnailHeight_'+id+'"      value="'+((vals.thumbnailHeight !== undefined)?vals.thumbnailHeight:'150')+'">';
			output += '<input type="hidden" id="thumbnailWidth_'+id+'"    name="thumbnailWidth_'+id+'"        data-bind="thumbnailWidth_'+id+'"       value="'+((vals.thumbnailWidth !== undefined)?vals.thumbnailWidth:'150')+'">';
			output += '<input type="hidden" id="thumbnailFormat_'+id+'"   name="thumbnailFormat_'+id+'"       data-bind="thumbnailFormat_'+id+'"      value="'+((vals.thumbnailFormat !== undefined)?vals.thumbnailFormat:'JPG')+'">';
			break;

		default:
			break;
	}

	return output;
}

function determineValidation(type){
    switch (type) {
        case 'Number':
        case 'number':
            return "integer";
            break;
        case 'Email':
        case 'email':
            return "emailAddr";
            break;
        case 'Phone':
        case 'tel':
            return "phoneNumber";
            break;
        case 'Date':
        case 'date':
            return "date";
            break;
        case 'Website':
        case 'url':
            return "url";
            break;
        default:
            return;
            break;
    }
}

function determineType(type){ switch(type) {
        case 'ID Number':
        case 'idno':
            return 'idno';
            break;

        case 'Single Line Text':
        case 'text':
            return 'text';
            break;

        case 'Paragraph Text':
        case 'textarea':
            return 'textarea';
            break;

        case 'Radio':
        case 'radio':
            return 'radio';
            break;

        case 'Checkboxes':
        case 'checkbox':
            return 'checkbox';
            break;

        case 'Dropdown':
        case 'select':
            return 'select';
            break;

        case 'Number':
        case 'number':
            return 'number';
            break;

        case 'Email':
        case 'email':
            return 'email';
            break;

        case 'Phone':
        case 'tel':
            return 'tel';
            break;

        case 'Date':
        case 'date':
            return 'date';
            break;

        case 'Time':
        case 'time':
            return 'time';
            break;

        case 'Website':
        case 'url':
            return 'url';
            break;

        case 'Multi-Select':
        case 'multiselect':
            return 'multiselect';
            break;

        case 'WYSIWYG':
        case 'wysiwyg':
            return 'wysiwyg';
            break;

        case 'File Upload':
        case 'file':
            return 'file';
            break;

        case 'Field Set':
        case 'fieldset':
            return 'fieldset';
            break;

        default:
            return;
            break;
    }
}

function createHiddenFields(fieldArray,id, vals){
    // Loops through the array to add the hidden fields with out manually adding all of them.  If there isn't a value already determined
    // The value will come from manually declaring it or from the data-bind setup.
    output = "";
    $.each(fieldArray, function(index, value) {
        var field = value + "_" + id;
        var hiddenValues = ((vals[value] !== undefined) ? vals[value]: '');
        output += '<input type="hidden" id="'+field+'" name="'+field+'" data-bind="'+value+'" value="'+hiddenValues+'"/>';
    });
    return output;
}

function addChoice(val,def) {
	if (val === undefined) {
		return '<div class="input-prepend input-append" data-itemtype="choice">'+
					'<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
					'<input name="fieldSettings_choices_text" type="text">'+
					'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
					'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
				'</div>';
	}
	else if (def === undefined) {
		return '<div class="input-prepend input-append" data-itemtype="choice">'+
					'<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
					'<input name="fieldSettings_choices_text" type="text" value="'+val+'">'+
					'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
					'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
				'</div>';
	}
	return '<div class="input-prepend input-append" data-itemtype="choice">'+
				'<button name="default" class="btn'+(val==def?" active":"")+'" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
				'<input name="fieldSettings_choices_text" type="text" value="'+val+'">'+
				'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
				'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
			'</div>';
}

function addAllowedExtension(val) {

	if (val === undefined) {
		val = '';
	}

	return '<div class="row-fluid input-append" data-itemtype="extension">'+
				'<input name="fieldSettings_allowedExtension_text" type="text" value="'+val+'">'+
				'<button name="add" class="btn" type="button" title="Add an extension."><i class="icon-plus"></i></button>'+
				'<button name="remove" class="btn" type="button" title="Remove this extension."><i class="icon-remove"></i></button>'+
			'</div>';
}

function enableChoiceFunctionality(){
	$('.input-append').find('button').click(function(){
		var state = $(this).attr('name');
		var type  = $(this).parent().data('itemtype');

		if(state == "add"){
			if(type == "choice"){
				$(this).parent().after(addChoice());
			}
			else {
				$(this).parent().after(addAllowedExtension());
			}
			// EVENT LISTENER Recouple
			$('.input-append').find('button').unbind('click');
			enableChoiceFunctionality();
		}
		else if(state == "remove"){
			$(this).parent().remove();
		}
	});
}

// /*
//  * Navigation Creator Functions
//  */

function sortableNav() {
	$("#navigation ul.sortable").sortable({
		connectWith: "#navigation ul.sortable",
		revert: true,
		placeholder: "highlight",
		update: function(event, ui) {

			// Only perform this if it's a brand new field
			if ($(ui.item).hasClass("ui-draggable")) {
				// Block groupings within groupings
				if ($(ui.item).text() == 'New Grouping' && $(ui.item).parent().attr("id") != "GroupingsPreview") {
					$(ui.item).remove();
				}

				// Convert text to preview
				addNew(ui.item);
			}

			$(ui.item).parents("li").click();
			$(ui.item).click();

			sortableNav();
		}
	});
}

function settingsBindings() {
	var groupingsPreview = $("#GroupingsPreview");

	// Select an option to change settings
	$("#GroupingsPreview").on("click", "li", function(event) {
		event.stopPropagation();
		if (!$(this).hasClass("well")) {
			groupingsPreview.find(".well").removeClass("well");
			$(this).addClass("well well-small");
			showSettings($(this).prop("id"));
		}
	});

	$("#groupingsSettings_grouping").keyup(function() {
		var groupingsPreviewWell = groupingsPreview.find(".well");
		var id                   = groupingsPreviewWell.prop("id").split("_")[1];
		var val                  = $(this).val();
		var groupingPreview      = groupingsPreviewWell.find(".groupingPreview");
		var before               = groupingPreview.find("i");
		var after                = groupingPreview.find("ul");
		var contents             = groupingPreview.contents();

		// remove old label
		contents.slice(contents.index(before)+1, contents.index(after)).remove();

		// add new label
		after.before(val);

		groupingsPreviewWell.find('input[name^=nav_grouping_]').val(val);
		$("#nav_label_"+id).val(val);
	});

	$("#groupingsSettings_label").keyup(function() {
		var groupingsPreviewWell = groupingsPreview.find(".well");
		var id                   = groupingsPreviewWell.prop("id").split("_")[1];
		var val                  = $(this).val();

		groupingsPreviewWell.find("a").text(val);
		$("#nav_label_"+id).val(val);
	});

	$("#groupingsSettings_url").keyup(function() {
		var groupingsPreviewWell = groupingsPreview.find(".well");
		var id                   = groupingsPreviewWell.prop("id").split("_")[1];
		var val                  = $(this).val();

		groupingsPreviewWell.find("a").prop("href", val);
		$("#nav_url_"+id).val(val);
	});
}

function showSettings(fullID) {
	// Hide all fields
	$("#groupingsSettings").children().hide();

	// Select the Settings tab
	$("#groupingTab a[href='#groupingsSettings']").tab("show");

	if (fullID === undefined) {
		// Show a warning about having nothing selected
		$("#noGroupingSelected").show();
	}
	else {
		var id       = fullID.split("_")[1];
		var type     = $("#nav_type_"+id).val();
		var grouping = $("#nav_grouping_"+id);

		// Show the form
		if (type == "grouping") {
			$("#groupingsSettings_container_grouping").show();
			$("#groupingsSettings_grouping").val(grouping.val()).keyup();
		}
		else {
			switch(type) {
				case 'logout':
				case 'link':
					$("#groupingsSettings_container_label").show();
					$("#groupingsSettings_container_url").show();
					break;

				case 'export':
				case 'objectForm':
				case 'metadataForm':
					$("#groupingsSettings_container_label").show();
					break;
			}

			if (type == 'objectForm' || type == 'metadataForm') {
				$("#groupingsSettings_label").prop('disabled', true);
			}
			else {
				$("#groupingsSettings_label").removeAttr("disabled");
			}

			if (type != 'grouping') {
				var parentGrouping = grouping.parents("li").parents("li");

				if (parentGrouping.length > 0) {
					var parentGroupingID = parentGrouping.prop("id").split("_")[1];
					grouping.val($("#nav_grouping_"+parentGroupingID).val());
				}
				else {
					grouping.val('');
				}
			}
			else {
				$("#groupingsSettings_grouping").val(grouping.val());
			}

			$("#groupingsSettings_label").val($("#nav_label_"+id).val()).keyup();
			$("#groupingsSettings_url").val($("#nav_url_"+id).val()).keyup();
		}

	}
}

function addNew(item) {
	// Remove class to designate this is not new for next time
	$(item).removeClass("ui-draggable");

	// Preserve type
	var type = $("a", item).text();
	var vals = {};

	// If data-type attribute exists, use that for type
	if ($(item).data("type")) {
		vals.type   = type = $(item).data("type");
		vals.label  = $("a", item).text();
	}

	if ($(item).data("formid")) {
		vals.formID = $(item).data("formid");
	}

	// Assign an id to new li
	var newID = 0;
	$("#GroupingsPreview li").each(function() {
		if ($(this)[0] !== $(item)[0]) {
			var thisID = $(this).attr("id").split("_");
			if (newID <= thisID[1]) {
				newID = parseInt(thisID[1])+1;
			}
		}
	});

	$(item)
		.attr("id","GroupingsPreview_"+newID)
		.html('<div class="groupingPreview">'+newGroupingPreview(type)+'</div><div class="groupingValues">'+newGroupingValues(newID,type,vals)+'</div>');

	// Display settings for new field
	$("#GroupingsPreview_"+newID).click();
}

function newGroupingPreview(type) {
	var output;

	output = '<i class="icon-remove"></i>';

	if (type == 'New Grouping' || type == 'grouping') {
		output += '<ul class="unstyled sortable"></ul>';
	}
	else {
		output += '<a href="#">[Link]</a>';
	}

	return output;
}

function newGroupingValues(id,type,vals) {
	var output;

	if (vals === undefined) {
		vals = {};
	}

	switch(type) {
		case 'New Grouping':
		case 'grouping':
			type = vals.type = 'grouping';
			break;

		case 'Log Out':
		case 'logout':
			type = vals.type = 'logout';
			break;

		case 'Export Link':
		case 'export':
			type = vals.type = 'export';
			break;

		case 'Link':
		case 'link':
			type = vals.type = 'link';
			break;

		default:
			break;
	}

    var groupFields = ['nav_position', 'nav_type', 'nav_label', 'nav_url', 'nav_grouping'];
    output = createHiddenFields(groupFields, id, vals);

	switch(type) {
		case 'objectForm':
		case 'metadataForm':
			output += '<input type="hidden" id="nav_formID_'+id+'" name="nav_formID_'+id+'" value="'+((vals.formID !== undefined)?vals.formID:'')+'">';
			break;

		default:
			break;
	}

	return output;
}
