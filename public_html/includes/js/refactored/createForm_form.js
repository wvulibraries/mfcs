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
	//$('.addFieldNav').click();// trigger click to open left pane
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
	// added for performance.  Labels the element in the form preview
	// Other method was added through clickthrough and was bad for performance
	// 31 ms versus 5299.128ms
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

		var id       = fullID.split("_")[1];
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

			// Hide all but the common fields
			fieldSettings_form.children().not(".noHide").hide();

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
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();
					$("#fieldSettings_container_idno").show();
					break;

				case 'text':
					$("#fieldSettings_container_externalUpdate").show();
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();

					$("#fieldSettings_container_range").show();
					$("#fieldSettings_range_step").parent().hide();

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="characters">Characters</option><option value="words">Words</option>');
					break;

				case 'textarea':
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();

					$("#fieldSettings_container_range").show();
					$("#fieldSettings_range_step").parent().hide();

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="characters">Characters</option><option value="words">Words</option>');
					break;

				case 'radio':
				case 'checkbox':
				case 'select':
				case 'multiselect':
					$("#fieldSettings_container_value").hide();
					$("#fieldSettings_container_placeholder").hide();
					$("#fieldSettings_container_choices").show();
					break;

				case 'number':
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();

					$("#fieldSettings_container_range").show();
					$("#fieldSettings_range_step").parent().show();

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="value">Value</option><option value="digits">Digits</option>');
					break;

				case 'wysiwyg':
					$("#fieldSettings_container_value").show();
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

			// Fieldset types
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
		}

		// coupleBinding('[data-bindname]', id);
	}
}

function coupleBinding(obj, dataID) {
	dataID = parseInt(dataID);
	$('.bindingData').show();
    if(dataID || dataID === 0){
	    $(obj).each(function () {
			var dataName = $(this).data('bindname');
			var model    = $('[data-model]');
			var binder   = $('[data-bind]');

	        $(this).attr('data-model', dataName + "_" + dataID);
	        dataBind(model, binder, true); // set initial state to get data
	    });
	}
}

function dataBind(model, binder, initialState) {
    // initial grabbing of data
    if (typeof initialState !== 'undefined' && initialState) {
        binder.each(function () {
            var dataModel = $(this).data('bind');
            var value = $(this).val();
            var dataBinder = $('[data-model=\"' + dataModel + '\"]');

            if ($(this).is('input[type=checkbox]')) {
                value = evaluateCheck($(this));
            }

            bindValues(value, dataBinder);
        });
    }
    model.each(function () {
        $(this).bind("keyup change", function (e) {
            var dataModel = $(this).data('model');
            var value = $(this).val();
            var dataBinder = $('[data-bind=\"' + dataModel + '\"]');

            if ($(this).is('input[type=checkbox]')) {
                value = evaluateCheck($(this));
            }

            if($(this).is('[data-model^="name"')){
            	applyLabelName();
            }

            bindValues(value, dataBinder);
        });
    });
    $('.bindingData').hide();
}


function evaluateCheck(object){
	return (object.is(':checked') ? true : false);
}

function bindValues(value, binder) {
    if(binder.is("input, textarea")) {
        binder.val(value);
    }
    else if(binder.is("input[type=checkbox]")) {
		if(value == "true"){
			binder.prop('checked', true);
		}
		else {
			binder.prop('checked', false);
		}
    }
    else if(binder.is('select')) {
        binder.find('option[value="' + value + '"]').prop('selected', true);
    }

    if(binder.not('select,input,textarea')){
        binder.html(value);
    }
}

// function fieldSettingsBindings(){
// 	var choicesFields = {};
// 	var formPreview   = $("#formPreview");
// 	formPreview.children('li').removeClass('activeField');
// 	// Select a field to change settings
// 	formPreview.on("click", "li", function(event) {
// 		console.log("Being called from " + arguments.callee.caller.toString());

// 		event.stopPropagation();
// 		console.log('click deteced');
// 		var li = $(this);
// 		if(!li.hasClass('activeField')){
// 			formPreview.find('.activeField').removeClass('activeField');
// 			li.addClass('activeField');
// 			$("#fieldTab a[href='#fieldSettings']").tab("show");
// 			showFieldSettings(li.attr("id"));
// 		}
// 	});
// }  // end function

function fieldSettingsBindings() {
	var choicesFields = {};

	// Create jQuery shortcuts (code optimization)
	var formPreview = $("#formPreview");

	// Select a field to change settings
	formPreview.on("click", "li", function(event) {
		event.stopPropagation();
		var li = $(this);
		if (!li.hasClass("well")) {
			formPreview.find(".well").removeClass("well");
			li.addClass("well well-small");
			$("#fieldTab a[href='#fieldSettings']").tab("show");
			showFieldSettings(li.attr("id"));
		}
	});

	$("#fieldSettings_name").keyup(function() {
		var formPreviewWell               = formPreview.find(".well");
		var id                            = formPreviewWell.prop("id").split("_")[1];
		var val                           = $(this).val();
		var formSettings_objectTitleField = $('#formSettings_objectTitleField');

		var option = formSettings_objectTitleField.find("option[value='"+$("#name_"+id).val()+"']");
		if (option.length > 0) {
			option.val(val);
		}
		else if ($("#type_"+id+"[value=text]").length > 0) {
			formSettings_objectTitleField.append('<option value="'+val+'">'+$("#label_"+id).val()+'</option>');
		}

		formPreviewWell.find(".control-group > .controls > :input").prop('name', val);
		$("#name_"+id).val(val);
	});

	$("#fieldSettings_label").keyup(function() {
		var formPreviewWell               = formPreview.find(".well");
		var id                            = formPreviewWell.prop("id").split("_")[1];
		var val                           = $(this).val();
		var nameVal                       = $("#name_"+id).val();
		var formSettings_objectTitleField = $('#formSettings_objectTitleField');

		var option = formSettings_objectTitleField.find("option[value='"+nameVal+"']");
		if (option.length > 0) {
			option.text(val);
		}
		else if ($("#type_"+id+"[value=text]").length > 0) {
			formSettings_objectTitleField.append('<option value="'+nameVal+'">'+val+'</option>');
		}

		formPreviewWell.find(".control-group > label").text(val);
		$("#label_"+id).val(val);
	});

	$("#fieldSettings_value").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		formPreviewWell.find(".control-group > .controls > :input").val(val);
		$("#value_"+id).val(val);
	});

	$("#fieldSettings_placeholder").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		formPreviewWell.find(".control-group > .controls > :input").prop('placeholder', val);
		$("#placeholder_"+id).val(val);
	});

	$("#fieldSettings_id").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		formPreviewWell.find(".control-group > label").prop('for', val);
		formPreviewWell.find(".control-group > .controls > :input").prop('id', val);
		$("#id_"+id).val(val);
	});

	$("#fieldSettings_class").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		formPreviewWell.find(".control-group > .controls > :input").prop('class', val);
		$("#class_"+id).val(val);
	});

	$("#fieldSettings_style").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		formPreviewWell.find(".control-group > .controls > :input").prop('style', val);
		$("#style_"+id).val(val);
	});

	$("#fieldSettings_choices_type").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var val             = $(this).val();

		formPreviewWell.find(".fieldValues > :input[name^=choicesType_]").val(val);
		if (val == 'manual') {
			$("#fieldSettings_choices_manual").show().find("input[name=fieldSettings_choices_text]:first").keyup();
			$("#fieldSettings_choices_form").hide();
		}
		else if (val == 'form') {
			$("#fieldSettings_choices_manual").hide();
			$("#fieldSettings_choices_form").show();
			formPreviewWell.find(".control-group > .controls > :input").html('');
		}
	}).change();

	$("#fieldSettings_help_type").change(function() {
		var formPreviewWell = formPreview.find(".well");
		if(!formPreviewWell.length) return;

		var id                       = formPreviewWell.prop("id").split("_")[1];
		var val                      = $(this).val();
		var $fieldSettings_help_type = $("#fieldSettings_help_type");
		var $fieldSettings_help_text = $("#fieldSettings_help_text");
		var $fieldSettings_help_html = $("#fieldSettings_help_html");
		var $fieldSettings_help_url  = $("#fieldSettings_help_url");
		var $helpPreview             = formPreviewWell.find('.helpPreview');
		var $helpPreviewModal        = formPreviewWell.find('.helpPreviewModal');

		$fieldSettings_help_text.hide().val('');
		$fieldSettings_help_html.hide().val('');
		$fieldSettings_help_url.hide().val('');
		$helpPreview.hide().tooltip('destroy').popover('destroy');
		$helpPreviewModal.hide();
		switch(val){
			case '':
				$helpPreview.hide();
				$("#help_"+id).val('');
				break;
			case 'text':
				$fieldSettings_help_text.show().focus();
				$helpPreview.show();
				$("#help_"+id).val($fieldSettings_help_type.val()+'|');
				break;
			case 'html':
				$fieldSettings_help_html.show().focus();
				$helpPreview.show();
				$("#help_"+id).val($fieldSettings_help_type.val()+'|');
				break;
			case 'web':
				$fieldSettings_help_url.show().focus();
				$helpPreviewModal.show();
				$("#help_"+id).val($fieldSettings_help_type.val()+'|');
				break;
		}
	}).change();
	$("#fieldSettings_help_text").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		// Escape HTML-breaking characters
		val = val.replace(/"/g, '&#34;');
		val = val.replace(/'/g, '&#39;');
		val = val.replace(/>/g, '&#62;');
		val = val.replace(/</g, '&#60;');

		$("#help_"+id).val($('#fieldSettings_help_type').val()+'|'+val);
		formPreviewWell.find('.helpPreview').tooltip('destroy').tooltip({
			placement: 'right',
			title: $(this).val()
		});
	});
	$("#fieldSettings_help_html").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		// Escape HTML-breaking characters
		val = val.replace(/"/g, '&#34;');
		val = val.replace(/'/g, '&#39;');

		$("#help_"+id).val($('#fieldSettings_help_type').val()+'|'+val);
		formPreviewWell.find('.helpPreview').popover('destroy').popover({
			placement: 'right',
			trigger: 'click',
			html: true,
			content: $(this).val()
		});
	});
	$("#fieldSettings_help_url").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();
		$("#help_"+id).val($('#fieldSettings_help_type').val()+'|'+val);
		$("#fieldHelpModalURL").attr('src', val);
	});

	$("#fieldSettings_choices_null").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var clickState      = $(this).prop('checked');
		$("#choicesNull_"+id).val(clickState);
		if($("#fieldSettings_choices_type").val() == 'manual'){
			$('#fieldSettings_choices_manual').find('input[name=fieldSettings_choices_text]:first').keyup();
		}else{
			if(clickState){
				formPreviewWell.find(".control-group > .controls > :input").append('<option value="">Make a selection</option>');
			}else{
				formPreviewWell.find(".control-group > .controls > :input").html('');
			}

		}
	});
	$("#fieldSettings_choices_manual")
		.on("click","button[name=default]",function() {
			var formPreviewWell = formPreview.find(".well");
			var id              = formPreviewWell.prop("id").split("_")[1];
			var val;
			var vals = [];
			var text;

			switch ($("#type_"+id).val()) {
				case 'select':
					if ($(this).hasClass("active")) {
						formPreviewWell.find(".control-group > .controls > :input").val('');
						$("#choicesDefault_"+id).val('');
					}
					else {
						val = $(this).siblings(":input").val();

						formPreviewWell.find(".control-group > .controls > :input").val(val);
						$("#choicesDefault_"+id).val(val);
					}
					$("#fieldSettings_choices_manual button[name=default]").not(this).removeClass("active");
					break;

				case 'radio':
					if ($(this).hasClass("active")) {
						formPreviewWell.find(".control-group > .controls > :input").removeAttr('checked');
						$("#choicesDefault_"+id).val('');
					}
					else {
						val  = $(this).siblings(":input").val();
						text = $(this).text();

						formPreviewWell.find(".controls label").each(function() {
							if (text == val) {
								$(":input", this).prop('checked', true);
							}
						});
						formPreviewWell.find(".fieldValues > :input[name^=choicesDefault_]").val(val);
					}
					$("#fieldSettings_choices_manual button[name=default]").not(this).removeClass("active");
					break;

				case 'checkbox':
					text = $(this).text();
					val  = $(this).siblings(":input").val();
					vals = [];

					if ($(this).hasClass("active")) {
						formPreviewWell.find(".controls label").each(function() {
							if (text == val) {
								$(":input", this).removeAttr('checked');
							}
						});
					}
					else {
						formPreviewWell.find(".controls label").each(function() {
							if (text == val) {
								$(":input",this).prop('checked', true);
							}
						});
					}

					formPreviewWell.find(".control-group > .controls > :input:checked").each(function() {
						vals.push($(this).parent().text());
					});

					$("#choicesDefault_"+id).val('').val(vals.join("%,%"));
					break;

				case 'multiselect':
					if ($(this).hasClass("active")) {
						formPreviewWell.find(".control-group > .controls > :input:last").val('');
						$("#choicesDefault_"+id).val('');
					}
					else {
						val = $(this).siblings(":input").val();

						formPreviewWell.find(".control-group > .controls > :input:last").val(val);
						$("#choicesDefault_"+id).val(val);
					}
					$("#fieldSettings_choices_manual button[name=default]").not(this).removeClass("active");
					break;

			}
		})
		.on("click","button[name=add]",function() {
			$(this).parent().after(addChoice());
		})
		.on("click","button[name=remove]",function() {
			if ($(this).parent().siblings().length === 0) {
				$(this).siblings("button[name=add]").click();
			}
			$(this).parent().remove();

			var formPreviewWell = formPreview.find(".well");
			var id              = formPreviewWell.prop("id").split("_")[1];
			var val             = $(this).val();
			var vals            = [];
			var tmp;
			var controls;
			var i;

			// Change value in hidden field
			$("#fieldSettings_choices_manual").find("input[name=fieldSettings_choices_text]").each(function() {
				vals.push($(this).val());
			});
			$("#choicesOptions_"+id).val(vals.join("%,%"));

			switch ($("#type_"+id).val()) {
				case 'select':
					var input = formPreviewWell.find(".control-group > .controls > :input");
					tmp   = '';

					// Set options in preview pane
					for (i = 0; i < vals.length; i++) {
						tmp += '<option value="'+vals[i]+'">'+vals[i]+'</option>';
					}
					input.html(tmp);
					break;

				case 'radio':
					controls = formPreviewWell.find(".controls");
					tmp      = '';

					controls.html('');
					for (i = 0; i < vals.length; i++) {
						tmp += '<label class="radio"><input type="radio" name="'+$("#name_"+id).val()+'">'+vals[i]+'</label>';
					}
					controls.append(tmp);
					break;

				case 'checkbox':
					controls = formPreviewWell.find(".controls");
					tmp      = '';

					controls.html('');
					for (i = 0; i < vals.length; i++) {
						tmp += '<label class="checkbox"><input type="checkbox" name="'+$("#name_"+id).val()+'">'+vals[i]+'</label>';
					}
					controls.append(tmp);
					break;

				case 'multiselect':
					var lastInput = formPreviewWell.find(".control-group > .controls > :input:last");
					tmp       = '';

					lastInput.html('');
					for (i = 0; i < vals.length; i++) {
						tmp += '<option value="'+vals[i]+'">'+vals[i]+'</option>';
					}
					lastInput.append(tmp);
					break;
			}
		})
		.on("keyup","input[name=fieldSettings_choices_text]",function() {
			var formPreviewWell = formPreview.find(".well");
			var id              = formPreviewWell.prop("id").split("_")[1];
			var val             = $(this).val();
			var vals            = [];
			var tmp;
			var controls;
			var i;

			// Change value in hidden field
			$("#fieldSettings_choices_manual").find("input[name=fieldSettings_choices_text]").each(function() {
				vals.push($(this).val());
			});
			$("#choicesOptions_"+id).val(vals.join("%,%"));

			if ($("#fieldSettings_choices_type").val() == 'manual') {
				switch ($("#type_"+id).val()) {
					case 'select':
						var input = formPreviewWell.find(".control-group > .controls > :input");
						tmp   = '';

						// Set options in preview pane
						if($('#fieldSettings_choices_null').prop('checked')){
							tmp += '<option value="">Make a selection</option>';
						}
						for (i = 0; i < vals.length; i++) {
							tmp += '<option value="'+vals[i]+'">'+vals[i]+'</option>';
						}
						input.html(tmp);
						break;

					case 'radio':
						controls = formPreviewWell.find(".controls");
						tmp      = '<div class="checkboxList">';

						for (i = 0; i < vals.length; i++) {
							tmp += '<label class="radio"><input type="radio" name="'+$("#name_"+id).val()+'">'+vals[i]+'</label>';
						}
						controls.html(tmp+'</div>');
						break;

					case 'checkbox':
						controls = formPreviewWell.find(".controls");
						tmp      = '<div class="checkboxList">';

						for (i = 0; i < vals.length; i++) {
							tmp += '<label class="checkbox"><input type="checkbox" name="'+$("#name_"+id).val()+'">'+vals[i]+'</label>';
						}
						controls.html(tmp+'</div>');
						break;

					case 'multiselect':
						var lastInput = formPreviewWell.find(".control-group > .controls > :input:last");
						tmp       = '';

						lastInput.html('');
						for (i = 0; i < vals.length; i++) {
							tmp += '<option value="'+vals[i]+'">'+vals[i]+'</option>';
						}
						lastInput.append(tmp);
						break;
				}
			}
		});

	$("#fieldSettings_choices_form")
		.on("change","#fieldSettings_choices_formSelect",function() {
			var formPreviewWell = formPreview.find(".well");
			var id              = formPreviewWell.prop("id").split("_")[1];
			var val             = $(this).val();

			if (choicesFields[val] === undefined) {
				var options;
				choicesFields[null] = options;

				$.ajax({
					url: "../includes/getFormFields.php",
					async: false
				}).always(function(data) {
					var obj = JSON.parse(data);

					$.each(obj, function(I, field) {
						var options;
						$.each(field, function(i, f) {
							options += '<option value="'+f.name+'">'+f.label+'</option>';
						});
						choicesFields[I] = options;
					});
				});
			}

			$("#choicesForm_"+id).val(val).change();
			$("#fieldSettings_choices_fieldSelect").html(choicesFields[val]).change();
		})
		.on("change","#fieldSettings_choices_fieldSelect",function() {
			var formPreviewWell = formPreview.find(".well");
			var id              = formPreviewWell.prop("id").split("_")[1];

			$("#choicesField_"+id).val($(this).val());
		});

	$("#fieldSettings_choices_fieldDefault").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#choicesFieldDefault_"+id).val($(this).val());
	});

	$("#fieldSettings_externalUpdate_formSelect").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		if (choicesFields[val] === undefined) {
			choicesFields[val] = '';

			$.ajax({
				url: "../includes/getFormFields.php",
				async: false
			}).always(function(data) {
				var obj = JSON.parse(data);

				$.each(obj, function(I, field) {
					var options = '';
					$.each(field, function(i, f) {
						options += '<option value="'+f.name+'">'+f.label+'</option>';
					});
					choicesFields[I] = options;
				});
			});
		}

		$("#externalUpdateForm_"+id).val(val).change();
		$("#fieldSettings_externalUpdate_fieldSelect").html(choicesFields[val]).change();
	});

	$("#fieldSettings_externalUpdate_fieldSelect").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#externalUpdateField_"+id).val($(this).val());
	});

	$("#fieldSettings_options_required").change(function() {
		var checked         = $(this).is(":checked");
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		formPreviewWell.find(".control-group > .controls > :input").prop('required', checked);
		$("#required_"+id).val(checked);
	});

	$("#fieldSettings_options_duplicates").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#duplicates_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_options_readonly").change(function() {
		var checked         = $(this).is(":checked");
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		formPreviewWell.find(".control-group > .controls > :input").prop('readonly', checked);
		$("#readonly_"+id).val(checked);
	});

	$("#fieldSettings_options_disabled").change(function() {
		var checked         = $(this).is(":checked");
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		if ($("#type_"+id).val() != 'file') {
			formPreviewWell.find(".control-group > .controls > :input").prop('disabled', checked);
		}
		$("#disabled_"+id).val(checked);
	});

	$("#fieldSettings_options_disabled_insert").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#disabledInsert_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_options_disabled_update").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#disabledUpdate_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_options_publicRelease").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#publicRelease_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_options_sortable").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#sortable_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_options_searchable").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#searchable_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_options_displayTable").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#displayTable_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_options_hidden").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#hidden_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_validation").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#validation_"+id).val($(this).val());
		if ($(this).val() == 'regexp') {
			$("#fieldSettings_validationRegex").show().focus();
		}
		else {
			$("#fieldSettings_validationRegex").hide().val('').keyup();
		}
	});

	$("#fieldSettings_validationRegex").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#validationRegex_"+id).val($(this).val());
	});

	$("#fieldSettings_range_min").change(function() {
		var fieldSettings_range_min = $('#fieldSettings_range_min');
		var fieldSettings_range_max = $('#fieldSettings_range_max');
		var formPreviewWell         = formPreview.find(".well");
		var id                      = formPreviewWell.prop("id").split("_")[1];

		$("#min_"+id).val($(this).val());
		if (parseInt(fieldSettings_range_min.val()) > parseInt(fieldSettings_range_max.val())) {
			fieldSettings_range_max.val(fieldSettings_range_min.val()).change();
		}
	});

	$("#fieldSettings_range_max").change(function() {
		var fieldSettings_range_min = $('#fieldSettings_range_min');
		var fieldSettings_range_max = $('#fieldSettings_range_max');
		var formPreviewWell         = formPreview.find(".well");
		var id                      = formPreviewWell.prop("id").split("_")[1];

		$("#max_"+id).val($(this).val());
		if (parseInt(fieldSettings_range_min.val()) > parseInt(fieldSettings_range_max.val())) {
			fieldSettings_range_min.val(fieldSettings_range_max.val()).change();
		}
	});

	$("#fieldSettings_range_step").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#step_"+id).val($(this).val());
	});

	$("#fieldSettings_range_format").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#format_"+id).val($(this).val());
	});

	$("#fieldSettings_idno_managedBy").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		$("#managedBy_"+id).val(val);
		if ($("#type_"+id).val() == 'idno') {
			if (val == "system") {
				$("#fieldSettings_options_readonly").prop("checked",true).change();
				$("#fieldSettings_container_idno_format").show();
				$("#fieldSettings_container_idno_startIncrement").show();
			}
			else if (val == "user") {
				$("#fieldSettings_options_readonly").removeAttr("checked").change();
				$("#fieldSettings_container_idno_format").hide();
				$("#fieldSettings_container_idno_startIncrement").hide();
			}
		}
	});

	$("#fieldSettings_idno_format").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#idnoFormat_"+id).val($(this).val());

		if ($('#submitForm').find('input[name=id]').val() !== '') {
			$("#fieldSettings_container_idno_confirm").removeClass('hidden');
		}
	});

	$("#fieldSettings_idno_startIncrement").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#startIncrement_"+id).val($(this).val());

		if ($('#submitForm').find('input[name=id]').val() !== '') {
			$("#fieldSettings_container_idno_confirm").removeClass('hidden');
		}
	});

	$("#fieldSettings_idno_confirm").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#idnoConfirm_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_file_allowedExtensions")
		.on("click","button[name=add]",function() {
			var $parent = $(this).parent();
			$parent.after(addAllowedExtension());
			$parent.next().find('input').focus();
		})
		.on("click","button[name=remove]",function() {
			if ($(this).parent().siblings().length === 0) {
				$(this).siblings("button[name=add]").click();
				$('#allowedExtensionsAlert').show();
			}
			$(this).parent().remove();

			var formPreviewWell = formPreview.find(".well");
			var id              = formPreviewWell.prop("id").split("_")[1];
			var val             = $(this).val();
			var vals            = [];

			$("#fieldSettings_file_allowedExtensions").find("input[name=fieldSettings_allowedExtension_text]").each(function() {
				vals.push($(this).val());
			});
			$("#allowedExtensions_"+id).val(vals.join("%,%"));
		})
		.on("keyup",":input[name=fieldSettings_allowedExtension_text]",function() {
			var formPreviewWell = formPreview.find(".well");
			var id              = formPreviewWell.prop("id").split("_")[1];
			var val             = $(this).val();
			var vals            = [];

			if(val !== '') $('#allowedExtensionsAlert').hide();
			$("#fieldSettings_file_allowedExtensions").find("input[name=fieldSettings_allowedExtension_text]").each(function() {
				vals.push($(this).val());
			});
			$("#allowedExtensions_"+id).val(vals.join("%,%"));
		})
		.on("change",":input[name=fieldSettings_allowedExtension_text]",function() {
			var noVal = true;
			$("#fieldSettings_file_allowedExtensions").find("input[name=fieldSettings_allowedExtension_text]").each(function() {
				if($(this).val()) noVal = false;
			});
			if(noVal){
				$('#allowedExtensionsAlert').show();
			}else{
				$('#allowedExtensionsAlert').hide();
			}
		});

	$("#fieldSettings_file_options_bgProcessing").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#bgProcessing_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_file_options_multipleFiles").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#multipleFiles_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_file_options_combine").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var newState        = $(this).is(":checked");
		var $multipleFiles  = $("#fieldSettings_file_options_multipleFiles");
		$("#combine_"+id).val(newState);
		if(newState){
			if(!$multipleFiles.is(":checked")) $multipleFiles.click();
			$multipleFiles.attr('disabled','disabled');
		}else{
			$multipleFiles.removeAttr('disabled').click();
		}
	});

	$("#fieldSettings_file_options_ocr").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#ocr_"+id).val($(this).is(":checked"));
	});

	$("#fieldSettings_file_options_convert").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#convert_"+id).val($(this).is(":checked"));

		if ($(this).is(":checked")) {
			$("#fieldSettings_container_file_convert").show();
		}
		else {
			$("#fieldSettings_container_file_convert").hide();
		}
	});

	$("#fieldSettings_file_convert_height").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#convertHeight_"+id).val($(this).val());
	});

	$("#fieldSettings_file_convert_width").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#convertWidth_"+id).val($(this).val());
	});

	$("#fieldSettings_file_convert_resolution").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#convertResolution_"+id).val($(this).val());
	});

	$("#fieldSettings_file_convert_format").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#convertFormat_"+id).val($(this).val());
	});

	$("#fieldSettings_file_convert_watermark").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var checked         = $(this).is(":checked");

		formPreviewWell.find(".fieldValues > :input[name^=watermark_]").val(checked);

		if (checked) {
			$(this).parent().next().show();
		}
		else {
			$(this).parent().next().hide();
		}
	}).change();

	$("#fieldSettings_file_watermark_image").change(function() {
		var formPreviewWell = formPreview.find(".well");

		formPreviewWell.find(".fieldValues > :input[name^=watermarkImage_]").val($(this).val());
	}).change();

	$("#fieldSettings_file_watermark_location").change(function() {
		var formPreviewWell = formPreview.find(".well");

		formPreviewWell.find(".fieldValues > :input[name^=watermarkLocation_]").val($(this).val());
	}).change();

	$("#fieldSettings_file_convert_border").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var checked         = $(this).is(":checked");

		formPreviewWell.find(".fieldValues > :input[name^=border_]").val(checked);

		if (checked) {
			$(this).parent().next().show();
		}
		else {
			$(this).parent().next().hide();
		}
	}).change();

	$("#fieldSettings_file_border_height").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#borderHeight_"+id).val($(this).val());
	});

	$("#fieldSettings_file_border_width").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#borderWidth_"+id).val($(this).val());
	});

	$("#fieldSettings_file_border_color").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#borderColor_"+id).val($(this).val());
	});

	$("#fieldSettings_file_options_thumbnail").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var checked         = $(this).is(":checked");

		$("#thumbnail_"+id).val(checked);

		if (checked) {
			$("#fieldSettings_container_file_thumbnail").show();
		}
		else {
			$("#fieldSettings_container_file_thumbnail").hide();
		}
	});

	$("#fieldSettings_file_thumbnail_height").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#thumbnailHeight_"+id).val($(this).val());
	});

	$("#fieldSettings_file_thumbnail_width").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#thumbnailWidth_"+id).val($(this).val());
	});

	$("#fieldSettings_file_thumbnail_format").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#thumbnailFormat_"+id).val($(this).val());
	});

	$("#fieldSettings_file_options_mp3").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#mp3_"+id).val($(this).val());
	});

	$("#fieldSettings_fieldset").keyup(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];
		var val             = $(this).val();

		formPreviewWell.find(".fieldPreview legend").text(val);
		formPreviewWell.find('input[name^=fieldset_]').val(val);
	});
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

	$(item)
		.attr("id","formPreview_"+newID)
		.html('<div class="fieldPreview">'+newFieldPreview(newID,type)+'</div><div class="fieldValues">'+newFieldValues(newID,type)+'</div>');

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

    var defaultHiddenFormFields = ['position', 'type', 'label', 'value', 'placeholder', 'id', 'class', 'style',
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
			output += '<input type="hidden" id="choicesOptions_'+id+'" name="choicesOptions_'+id+'" value="'+((vals.choicesOptions !== undefined)?vals.choicesOptions:'First Choice%,%Second Choice')+'">';
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
        output += '<input type="hidden" id="'+field+'" name="'+field+'" data-bind="'+field+'" value="'+hiddenValues+'"/>';
    });
    return output;
}

function addChoice(val,def) {
	if (val === undefined) {
		return '<div class="row-fluid input-prepend input-append">'+
					'<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
					'<input name="fieldSettings_choices_text" type="text">'+
					'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
					'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
				'</div>';
	}
	else if (def === undefined) {
		return '<div class="row-fluid input-prepend input-append">'+
					'<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
					'<input name="fieldSettings_choices_text" type="text" value="'+val+'">'+
					'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
					'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
				'</div>';
	}

	return '<div class="row-fluid input-prepend input-append">'+
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

	return '<div class="row-fluid input-append">'+
				'<input name="fieldSettings_allowedExtension_text" type="text" value="'+val+'">'+
				'<button name="add" class="btn" type="button" title="Add an extension."><i class="icon-plus"></i></button>'+
				'<button name="remove" class="btn" type="button" title="Remove this extension."><i class="icon-remove"></i></button>'+
			'</div>';
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
