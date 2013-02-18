$(function() {
	// Instantiate the bootstrap tooltip plugin
	$("[rel='tooltip']").tooltip();

	// Blank all panes when changing tabs
	$("#fieldTab").on("click", "a", function() {
		$("#formPreview li").removeClass("well");
		showFieldSettings(); // blank the Field Settings pane
	});

	// Make the preview pane sortable -- sort order determines position
	sortable();

	// Make field types draggable, linked to preview pane
	$(".draggable li").draggable({
		connectToSortable: "ul.sortable",
		helper: "clone",
		revert: "invalid",
	});

	// Add new field on click as well as drag
	$("#fieldAdd li").click(function() {
		$(this).clone().appendTo($("#formPreview"));
		addNewField($("#formPreview li:last"));
	});

	// Re-order nesting on load
	// This loops through <li> and finds all the fieldsets, then loops through matching all <li> that have
	// the same fieldset name and moves them inside it
	$(".fieldValues :input[name^='type_'][value='Field Set']").each(function() {
		var fieldset = $(this).parents("li").prop("id");
		$(".fieldValues :input[name^='fieldset_'][value='"+$(this).siblings(":input[name^='fieldset_']").val()+"']").each(function() {
			if (fieldset != $(this).parents("li").prop("id")) {
				$(this).parents("li").detach().appendTo($("#"+fieldset+" ul"));
			}
		});
	});

	// Set all the black magic bindings
	fieldSettingsBindings();
	formSettingsBindings();

	// Form submit handler
	$("form[name=submitForm]").submit(function(e) {
		// Calculate position of all fields
		var pos = 0;
		$(".fieldValues :input[name^=position_]").each(function() {
			$(this).val(pos++);
		});

		// Create a multidimentional object to store field info
		var obj = {};
		$("#formSettings :input").each(function() {
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

			if ($(this).prop("type") == "checkbox") {
				obj[ field[1] ][ field[0] ] = $(this).prop("checked");
			}
			else {
				obj[ field[1] ][ field[0] ] = $(this).val();
			}
		});
		// Convert object to JSON and add it to a hidden form field
		$(":input[name=fields]", this).val(JSON.stringify(obj));
	});

	// Click through each field and then back to add field tab on page load to update form preview
	$("#formPreview li").click();
	$("#fieldTab li:last a").click();

});

function sortable() {
	$("ul.sortable").sortable({
		connectWith: "ul.sortable",
		revert: true,
		placeholder: "highlight",
		update: function(event, ui) {
			// Only perform this if it's a brand new field
			if ($(ui.item).hasClass("ui-draggable")) {
				addNewField(ui.item);
			}
			sortable();
		}
	});
}

function showFieldSettings(fullID) {
	if (fullID === undefined) {
		// Hide the form and show a warning about having nothing selected
		$("#noFieldSelected").show();
		$("#fieldSettings_fieldset_form").hide();
		$("#fieldSettings_form").hide();
	}
	else {
		id       = fullID.split("_")[1];
		var type = $("#type_"+id).val();

		// Select the Field Settings tab
		$("#fieldTab a[href='#fieldSettings']").tab("show");

		// Hide the nothing selected error and show the form
		$("#noFieldSelected").hide();
		if (type == "fieldset") {
			$("#fieldSettings_fieldset_form").show();
			$("#fieldSettings_form").hide();
		}
		else {
			$("#fieldSettings_fieldset_form").hide();
			$("#fieldSettings_form").show();

			// Hide all but the common fields
			$("#fieldSettings_form").children().not(".noHide").hide();

			// Show optional fields
			switch(type) {
				case 'text':
				case 'textarea':
					$("#fieldSettings_container_range").show();

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format")
						.append('<option value="characters">Characters</option')
						.append('<option value="words">Words</option')
					break;

				case 'radio':
				case 'checkbox':
				case 'select':
					$("#fieldSettings_container_choices").show();
					break;

				case 'number':
					$("#fieldSettings_container_range").show();

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format")
						.append('<option value="value">Value</option')
						.append('<option value="digits">Digits</option')
					break;

				case 'email':
				case 'phone':
				case 'date':
				case 'datetime':
				case 'website':
				default:
					break;
			}
		}

		// Update field settings to use values from form display
		$("#fieldSettings_name").val($("#name_"+id).val()).keyup();
		$("#fieldSettings_label").val($("#label_"+id).val()).keyup();
		$("#fieldSettings_value").val($("#value_"+id).val()).keyup();
		$("#fieldSettings_placeholder").val($("#placeholder_"+id).val()).keyup();
		$("#fieldSettings_cssID").val($("#cssID_"+id).val()).keyup();
		$("#fieldSettings_cssClass").val($("#cssClass_"+id).val()).keyup();
		$("#fieldSettings_styles").val($("#styles_"+id).val()).keyup();
		$("#fieldSettings_choices_type").val($("#choicesType_"+id).val()).change();

		var opts = $("#choicesOptions_"+id).val().split(",");
		$("#fieldSettings_choices_manual").html('');
		for (var i = 0; i < opts.length; i++) {
			$("#fieldSettings_choices_manual").append(addChoice(opts[i],$("#choicesDefault_"+id).val()));
		}
		$("#fieldSettings_choices_manual :input[name=fieldSettings_choices_text]").keyup();
		$("#fieldSettings_choices_formSelect").val($("#choicesForm_"+id).val()).change();
		$("#fieldSettings_choices_fieldSelect").val($("#choicesField_"+id).val()).change();

		$("#fieldSettings_options_required").prop("checked",($("#required_"+id).val()==='true'));
		$("#fieldSettings_options_duplicates").prop("checked",($("#duplicates_"+id).val()==='true'));
		$("#fieldSettings_options_readonly").prop("checked",($("#readonly_"+id).val()==='true')).change();
		$("#fieldSettings_options_disable").prop("checked",($("#disable_"+id).val()==='true')).change();
		$("#fieldSettings_options_publicRelease").prop("checked",($("#publicRelease_"+id).val()==='true')).change();
		$("#fieldSettings_options_sortable").prop("checked",($("#sortable_"+id).val()==='true'));
		$("#fieldSettings_options_searchable").prop("checked",($("#searchable_"+id).val()==='true'));
		$("#fieldSettings_validation").val($("#validation_"+id).val()).change();
		$("#fieldSettings_validationRegex").val($("#validationRegex_"+id).val());
		$("#fieldSettings_range_min").val($("#rangeMin_"+id).val()).change();
		$("#fieldSettings_range_max").val($("#rangeMax_"+id).val()).change();
		$("#fieldSettings_range_format").val($("#rangeFormat_"+id).val()).change();

		$("#fieldSettings_fieldset").val($("#fieldset_"+id).val()).keyup();
	}
}

function fieldSettingsBindings() {
	// Select a field to change settings
	$("#formPreview").on("click", "li", function(event) {
		event.stopPropagation();
		$("#formPreview .well").removeClass("well");
		$(this).addClass("well well-small");
		$("#fieldTab a[href='#fieldSettings']").tab("show");
		showFieldSettings($(this).attr("id"));
	});

	$("#fieldSettings_name").keyup(function() {
		$("#formPreview .well .controls :input").prop('name',$(this).val());
		$("#formPreview .well :input[name^=name_]").val($(this).val());
	});

	$("#fieldSettings_label").keyup(function() {
		$("#formPreview .well .control-group > label").text($(this).val());
		$("#formPreview .well :input[name^=label_]").val($(this).val());
	});

	$("#fieldSettings_value").keyup(function() {
		$("#formPreview .well .controls :input").val($(this).val());
		$("#formPreview .well :input[name^=value_]").val($(this).val());
	});

	$("#fieldSettings_placeholder").keyup(function() {
		$("#formPreview .well .controls :input").prop('placeholder',$(this).val());
		$("#formPreview .well :input[name^=placeholder_]").val($(this).val());
	});

	$("#fieldSettings_cssID").keyup(function() {
		$("#formPreview .well .control-group > label").prop('for',$(this).val());
		$("#formPreview .well .controls :input").prop('id',$(this).val());
		$("#formPreview .well :input[name^=cssID_]").val($(this).val());
	});

	$("#fieldSettings_cssClass").keyup(function() {
		$("#formPreview .well .controls :input").prop('class',$(this).val());
		$("#formPreview .well :input[name^=cssClass_]").val($(this).val());
	});

	$("#fieldSettings_styles").keyup(function() {
		$("#formPreview .well .controls :input").attr('style',$(this).val());
		$("#formPreview .well :input[name^=styles_]").val($(this).val());
	});

	$("#fieldSettings_choices_type").change(function() {
		$("#formPreview .well :input[name^=choicesType_]").val($(this).val());
		if ($(this).val() == 'manual') {
			$("#fieldSettings_choices_manual").show();
			$("#fieldSettings_choices_form").hide();
		}
		else if ($(this).val() == 'form') {
			$("#fieldSettings_choices_manual").hide();
			$("#fieldSettings_choices_form").show();
		}
	});

	$("#fieldSettings_choices_manual")
		.on("click","button[name=default]",function() {
			switch ($("#formPreview .well :input[name^=type_]").val()) {
				case 'select':
					if ($(this).hasClass("active")) {
						$("#formPreview .well .controls :input").val('');
						$("#formPreview .well :input[name^=choicesDefault_]").val('');
					}
					else {
						$("#formPreview .well .controls :input").val($(this).siblings(":input").val());
						$("#formPreview .well :input[name^=choicesDefault_]").val($(this).siblings(":input").val());
					}
					$("#fieldSettings_choices_manual button[name=default]").not(this).removeClass("active");
					break;

				case 'radio':
					if ($(this).hasClass("active")) {
						$("#formPreview .well .controls :input").removeAttr('checked');
						$("#formPreview .well :input[name^=choicesDefault_]").val('');
					}
					else {
						var val = $(this).siblings(":input").val();
						$("#formPreview .well .controls label").each(function() {
							if ($(this).text() == val) {
								$(":input",this).prop('checked',true);
							}
						});
						$("#formPreview .well :input[name^=choicesDefault_]").val($(this).siblings(":input").val());
					}
					$("#fieldSettings_choices_manual button[name=default]").not(this).removeClass("active");
					break;

				case 'checkbox':
					var val = $(this).siblings(":input").val();
					if ($(this).hasClass("active")) {
						$("#formPreview .well .controls label").each(function() {
							if ($(this).text() == val) {
								$(":input",this).removeAttr('checked');
							}
						});
					}
					else {
						$("#formPreview .well .controls label").each(function() {
							if ($(this).text() == val) {
								$(":input",this).prop('checked',true);
							}
						});
					}

					var vals = [];
					$("#formPreview .well .controls :input:checked").each(function() {
						vals.push($(this).parent().text());
					});

					$("#formPreview .well :input[name^=choicesDefault_]").val('').val(vals.join());
					break;

			}
		})
		.on("click","button[name=add]",function() {
			$(this).parent().after(addChoice());
		})
		.on("click","button[name=remove]",function() {
			if ($(this).parent().siblings().length == 0) {
				$(this).siblings("button[name=add]").click();
			}
			$(this).parent().remove();

			var vals = [];
			$("#fieldSettings_choices_manual input[name=fieldSettings_choices_text]").each(function() {
				vals.push($(this).val());
			});
			$("#formPreview .well :input[name^=choicesOptions_]").val(vals.join());

			switch ($("#formPreview .well :input[name^=type_]").val()) {
				case 'select':
					$("#formPreview .well .controls :input").html('');
					for (var i = 0; i < vals.length; i++) {
						$("#formPreview .well .controls :input").append($("<option>").prop("value",vals[i]).text(vals[i]));
					}
					break;

				case 'radio':
					$("#formPreview .well .controls").html('');
					for (var i = 0; i < vals.length; i++) {
						$("#formPreview .well .controls").append($("<label>").addClass("radio").append($("<input>").prop("type","radio").prop("name",$("#formPreview .well :input[name^=name_]").val())).append(vals[i]));
					}
					break;

				case 'checkbox':
					$("#formPreview .well .controls").html('');
					for (var i = 0; i < vals.length; i++) {
						$("#formPreview .well .controls").append($("<label>").addClass("checkbox").append($("<input>").prop("type","checkbox").prop("name",$("#formPreview .well :input[name^=name_]").val())).append(vals[i]));
					}
					break;
			}
		})
		.on("keyup",":input[name=fieldSettings_choices_text]",function() {
			var vals = [];
			$("#fieldSettings_choices_manual input[name=fieldSettings_choices_text]").each(function() {
				vals.push($(this).val());
			});
			$("#formPreview .well :input[name^=choicesOptions_]").val(vals.join());

			switch ($("#formPreview .well :input[name^=type_]").val()) {
				case 'select':
					$("#formPreview .well .controls :input").html('');
					for (var i = 0; i < vals.length; i++) {
						$("#formPreview .well .controls :input").append($("<option>").prop("value",vals[i]).text(vals[i]));
					}
					break;

				case 'radio':
					$("#formPreview .well .controls").html('');
					for (var i = 0; i < vals.length; i++) {
						$("#formPreview .well .controls").append($("<label>").addClass("radio").append($("<input>").prop("type","radio").prop("name",$("#formPreview .well :input[name^=name_]").val())).append(vals[i]));
					}
					break;

				case 'checkbox':
					$("#formPreview .well .controls").html('');
					for (var i = 0; i < vals.length; i++) {
						$("#formPreview .well .controls").append($("<label>").addClass("checkbox").append($("<input>").prop("type","checkbox").prop("name",$("#formPreview .well :input[name^=name_]").val())).append(vals[i]));
					}
					break;
			}
		});

	$("#fieldSettings_choices_form")
		.on("change","#fieldSettings_choices_formSelect",function() {
			$("#formPreview .well :input[name^=choicesForm_]").val($(this).val());
			$.ajax("../includes/getFormFields.php?id="+$(this).val())
				.done(function(data) {
					$("#fieldSettings_choices_fieldSelect").html('');

					var obj = JSON.parse(data);
					for(var i in obj) {
						var field = obj[i];
						$("#fieldSettings_choices_fieldSelect").append('<option value="'+field.name+'">'+field.label+'</option>');
					}
				});
		})
		.on("change","#fieldSettings_choices_fieldSelect",function() {
			$("#formPreview .well :input[name^=choicesField_]").val($(this).val());
		});

	$("#fieldSettings_options_required").change(function() {
		$("#formPreview .well .controls :input").prop('required',$(this).is(":checked"));
		$("#formPreview .well :input[name^=required_]").val($(this).is(":checked"));
	});

	$("#fieldSettings_options_duplicates").change(function() {
		$("#formPreview .well :input[name^=duplicates_]").val($(this).is(":checked"));
	});

	$("#fieldSettings_options_readonly").change(function() {
		$("#formPreview .well .controls :input").prop('readonly',$(this).is(":checked"));
		$("#formPreview .well :input[name^=readonly_]").val($(this).is(":checked"));
	});

	$("#fieldSettings_options_disable").change(function() {
		$("#formPreview .well .controls :input").prop('disabled',$(this).is(":checked"));
		$("#formPreview .well :input[name^=disable_]").val($(this).is(":checked"));
	});

	$("#fieldSettings_options_publicRelease").change(function() {
		$("#formPreview .well :input[name^=publicRelease_]").val($(this).is(":checked"));
	});

	$("#fieldSettings_options_sortable").change(function() {
		$("#formPreview .well :input[name^=sortable_]").val($(this).is(":checked"));
	});

	$("#fieldSettings_options_searchable").change(function() {
		$("#formPreview .well :input[name^=searchable_]").val($(this).is(":checked"));
	});

	$("#fieldSettings_validation").change(function() {
		$("#formPreview .well :input[name^=validation_]").val($(this).val());
		if ($(this).val() == 'regexp') {
			$("#fieldSettings_validationRegex").show().focus();
		}
		else {
			$("#fieldSettings_validationRegex").hide().val('').keyup();
		}
	});

	$("#fieldSettings_validationRegex").keyup(function() {
		$("#formPreview .well :input[name^=validationRegex_]").val($(this).val());
	});

	$("#fieldSettings_range_min").change(function() {
		$("#formPreview .well :input[name^=range_rangeMin_]").val($(this).val());
		if ($("#fieldSettings_range_min").val() > $("#fieldSettings_range_max").val()) {
			$("#fieldSettings_range_max").val($("#fieldSettings_range_min").val()).change();
		}
	});

	$("#fieldSettings_range_max").change(function() {
		$("#formPreview .well :input[name^=rangeMax_]").val($(this).val());
		if ($("#fieldSettings_range_min").val() > $("#fieldSettings_range_max").val()) {
			$("#fieldSettings_range_min").val($("#fieldSettings_range_max").val()).change();
		}
	});

	$("#fieldSettings_range_format").change(function() {
		$("#formPreview .well :input[name^=rangeFormat_]").val($(this).val());
	});

	$("#fieldSettings_fieldset").keyup(function() {
		$("#formPreview .well .fieldPreview legend").text($(this).val());
		$("#formPreview .well :input[name^=fieldset_]").val($(this).val());
	});
}

function formSettingsBindings() {
	$("#formTitle").on("click", function() {
		$("#fieldTab a[href='#formSettings']").click();
		$("#formSettings_formTitle").focus();
	});
	$("#formDescription").on("click", function() {
		$("#fieldTab a[href='#formSettings']").click();
		$("#formSettings_formDescription").focus();
	});

	$("#formSettings_formTitle").keyup(function() {
		$("#formTitle").html($(this).val());
	}).keyup();
	$("#formSettings_formDescription").keyup(function() {
		$("#formDescription").html($(this).val());
	}).keyup();
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
	$(item).attr("id","formPreview_"+newID);

	// Add base html
	$(item).html('<div class="fieldPreview"></div>');

	// Add field specific html to .fieldPreview
	$(".fieldPreview", item).html(newFieldPreview(newID,type));

	// Container for hidden fields
	$(item).append('<div class="fieldValues"></div>');
	$(".fieldValues", item).html(newFieldValues(newID,type));

	// Display settings for new field
	$("#formPreview_"+newID).click();
}

function newFieldPreview(id,type) {
	var output;

	if (type == 'fieldet') {
		output = '<fieldset><legend></legend><ul class="unstyled sortable"></ul></fieldset>';
	}
	else {
		output = '<div class="control-group"><label class="control-label">Untitled</label><div class="controls">';

		switch(type) {
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
			case 'datetime':
				output += '<input type="datetime">';
				break;

			case 'Website':
			case 'url':
				output += '<input type="url">';
				break;

			default:
				break;
		}

		output += '</div></div>';
	}

	return output;
}

function newFieldValues(id,type,vals) {
	var output;

	if (vals == undefined) {
		vals = {};

		switch (type) {
			case 'Number':
				vals['validation'] = "integer";
				break;
			case 'Email':
				vals['validation'] = "emailAddr";
				break;
			case 'Phone':
				vals['validation'] = "phoneNumber";
				break;
			case 'Date':
				vals['validation'] = "date";
				break;
			case 'Website':
				vals['validation'] = "url";
				break;
		}
	}

	switch(type) {
		case 'Single Line Text':
			type = vals['type'] = 'text';
			break;

		case 'Paragraph Text':
			type = vals['type'] = 'textarea';
			break;

		case 'Radio':
			type = vals['type'] = 'radio';
			break;

		case 'Checkboxes':
			type = vals['type'] = 'checkbox';
			break;

		case 'Dropdown':
			type = vals['type'] = 'select';
			break;

		case 'Number':
			type = vals['type'] = 'number';
			break;

		case 'Email':
			type = vals['type'] = 'email';
			break;

		case 'Phone':
			type = vals['type'] = 'tel';
			break;

		case 'Date':
			type = vals['type'] = 'date';
			break;

		case 'Time':
			type = vals['type'] = 'datetime';
			break;

		case 'Website':
			type = vals['type'] = 'url';
			break;

		case 'Field Set':
			type = vals['type'] = 'fieldset';
			break;

		default:
			break;
	}

	output  = '<input type="hidden" id="position_'+id+'" name="position_'+id+'" value="'+((vals['position']!=undefined)?vals['position']:'')+'">';
	output += '<input type="hidden" id="type_'+id+'" name="type_'+id+'" value="'+((vals['type']!=undefined)?vals['type']:type)+'">';
	output += '<input type="hidden" id="name_'+id+'" name="name_'+id+'" value="'+((vals['name']!=undefined)?vals['name']:'untitled'+(id+1))+'">';
	output += '<input type="hidden" id="label_'+id+'" name="label_'+id+'" value="'+((vals['label']!=undefined)?vals['label']:'Untitled')+'">';
	output += '<input type="hidden" id="value_'+id+'" name="value_'+id+'" value="'+((vals['value']!=undefined)?vals['value']:'')+'">';
	output += '<input type="hidden" id="placeholder_'+id+'" name="placeholder_'+id+'" value="'+((vals['placeholder']!=undefined)?vals['placeholder']:'')+'">';
	output += '<input type="hidden" id="cssID_'+id+'" name="cssID_'+id+'" value="'+((vals['cssID']!=undefined)?vals['cssID']:'')+'">';
	output += '<input type="hidden" id="fieldset_'+id+'" name="fieldset_'+id+'" value="'+((vals['fieldset']!=undefined)?vals['fieldset']:'')+'">';
	output += '<input type="hidden" id="cssClass_'+id+'" name="cssClass_'+id+'" value="'+((vals['cssClass']!=undefined)?vals['cssClass']:'')+'">';
	output += '<input type="hidden" id="localCSS_'+id+'" name="localCSS_'+id+'" value="'+((vals['localCSS']!=undefined)?vals['localCSS']:'')+'">';
	output += '<input type="hidden" id="choicesType_'+id+'" name="choicesType_'+id+'" value="'+((vals['choicesType']!=undefined)?vals['choicesType']:'')+'">';
	output += '<input type="hidden" id="choicesDefault_'+id+'" name="choicesDefault_'+id+'" value="'+((vals['choicesDefault']!=undefined)?vals['choicesDefault']:'')+'">';
	output += '<input type="hidden" id="choicesOptions_'+id+'" name="choicesOptions_'+id+'" value="'+((vals['choicesOptions']!=undefined)?vals['choicesOptions']:'First Choice,Second Choice')+'">';
	output += '<input type="hidden" id="choicesForm_'+id+'" name="choicesForm_'+id+'" value="'+((vals['choicesForm']!=undefined)?vals['choicesForm']:'')+'">';
	output += '<input type="hidden" id="choicesField_'+id+'" name="choicesField_'+id+'" value="'+((vals['choicesField']!=undefined)?vals['choicesField']:'')+'">';
	output += '<input type="hidden" id="required_'+id+'" name="required_'+id+'" value="'+((vals['required']!=undefined)?vals['required']:'false')+'">';
	output += '<input type="hidden" id="duplicates_'+id+'" name="duplicates_'+id+'" value="'+((vals['duplicates']!=undefined)?vals['duplicates']:'false')+'">';
	output += '<input type="hidden" id="readonly_'+id+'" name="readonly_'+id+'" value="'+((vals['readonly']!=undefined)?vals['readonly']:'false')+'">';
	output += '<input type="hidden" id="disable_'+id+'" name="disable_'+id+'" value="'+((vals['disable']!=undefined)?vals['disable']:'false')+'">';
	output += '<input type="hidden" id="publicRelease_'+id+'" name="publicRelease_'+id+'" value="'+((vals['publicRelease']!=undefined)?vals['publicRelease']:'true')+'">';
	output += '<input type="hidden" id="sortable_'+id+'" name="sortable_'+id+'" value="'+((vals['sortable']!=undefined)?vals['sortable']:'')+'">';
	output += '<input type="hidden" id="searchable_'+id+'" name="searchable_'+id+'" value="'+((vals['searchable']!=undefined)?vals['searchable']:'')+'">';
	output += '<input type="hidden" id="validation_'+id+'" name="validation_'+id+'" value="'+((vals['validation']!=undefined)?vals['validation']:'')+'">';
	output += '<input type="hidden" id="validationRegex_'+id+'" name="validationRegex_'+id+'" value="'+((vals['validationRegex']!=undefined)?vals['validationRegex']:'')+'">';
	output += '<input type="hidden" id="access_'+id+'" name="access_'+id+'" value="'+((vals['access']!=undefined)?vals['access']:'')+'">';
	output += '<input type="hidden" id="rangeMin_'+id+'" name="rangeMin_'+id+'" value="'+((vals['rangeMin']!=undefined)?vals['rangeMin']:'')+'">';
	output += '<input type="hidden" id="rangeMax_'+id+'" name="rangeMax_'+id+'" value="'+((vals['rangeMax']!=undefined)?vals['rangeMax']:'')+'">';
	output += '<input type="hidden" id="rangeFormat_'+id+'" name="rangeFormat_'+id+'" value="'+((vals['rangeFormat']!=undefined)?vals['rangeFormat']:'')+'">';

	return output;
}

function addChoice(val,def) {
	if (val == undefined) {
		return '<div class="input-prepend input-append">'+
					'<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
					'<input name="fieldSettings_choices_text" class="input-block-level" type="text">'+
					'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
					'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
				'</div>';
	}
	else if (def == undefined) {
		return '<div class="input-prepend input-append">'+
					'<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
					'<input name="fieldSettings_choices_text" class="input-block-level" type="text" value="'+val+'">'+
					'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
					'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
				'</div>';
	}

	return '<div class="input-prepend input-append">'+
				'<button name="default" class="btn'+(val==def?" active":"")+'" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
				'<input name="fieldSettings_choices_text" class="input-block-level" type="text" value="'+val+'">'+
				'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
				'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
			'</div>';
}