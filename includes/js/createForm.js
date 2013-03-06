$(function() {
	// Instantiate the bootstrap tooltip plugin
	$("[rel='tooltip']").tooltip();

	// Blank all panes when changing tabs
	$("#fieldTab").on("click", "a", function() {
		$("#formPreview li").removeClass("well");
		showFieldSettings(); // blank the Field Settings pane
	});

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

	// Delete icon binding
	$("#formPreview").on("click", ".fieldPreview i.icon-remove", function() {
		if (confirm("Are you sure you want to remove this field?")) {
			var thisLI = $(this).parent().parent();

			// If I'm a fieldset, move any fields that are within me
			if ($(this).parent().next().children(":input[name^=type_]").val() == 'fieldset') {
				thisLI.after($(this).next().find("li"));
			}
			// Delete this li
			thisLI.remove();
		}
	});

	// Re-order nesting on load
	// This loops through <li> and finds all the fieldsets, then loops through matching all <li> that have
	// the same fieldset name and moves them inside it
	$(".fieldValues :input[name^='type_'][value='fieldset']").each(function() {
		var fieldset = $(this).parents("li").prop("id");
		$(".fieldValues :input[name^='fieldset_'][value='"+$(this).siblings(":input[name^='fieldset_']").val()+"']").each(function() {
			if (fieldset != $(this).parents("li").prop("id")) {
				$(this).parents("li").detach().appendTo($("#"+fieldset+" ul"));
			}
		});
	});

	// Make the preview pane sortable -- sort order determines position
	sortable();

	// Set all the black magic bindings
	fieldSettingsBindings();
	formSettingsBindings();

	// Form submit handler
	$("form[name=submitForm]").submit(function(e) {
		// e.preventDefault();

		// Calculate position of all fields
		$(".fieldValues :input[name^=position_]").each(function(index) {
			$(this).val(index);
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

			if ($(this).is('[id^="choicesOptions_"]')) {
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
	$("#formPreview li").click();
	$("#fieldTab li:last a").click();

	// Make the left panel fixed if the viewport is big enough to hold the content
	$(window).scroll(function() {
		var left = $('#leftPanel');
		var height = $('#leftPanel .tab-content').outerHeight() + $('#fieldTab').outerHeight() + 170;

		// Is the window big enough?
		if ($(window).height() > height) {
			// Yes - should we fix it?
			if (!left.hasClass("fix") && $(window).scrollTop() - left.offset().top + 170 > 0) {
				left.addClass('fix');
				left.css("width",left.parent().width());
			}
		}
		else {
			// No - make sure it's not currently fixed
			left.removeClass('fix');
		}
	}).scroll();

	$(window).resize(function() {
		if ($("#leftPanel").hasClass("fix")) {
			$("#leftPanel").css("width",$("#leftPanel").parent().width());
		}
		else {
			$("#leftPanel").css("width",auto);
		}
	});
});

function sortable() {
	$("ul.sortable").sortable({
		connectWith: "ul.sortable",
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

			$("#fieldSettings_fieldset").val($("#fieldset_"+id).val()).keyup();
		}
		else {
			$("#fieldSettings_fieldset_form").hide();
			$("#fieldSettings_form").show();

			// Hide all but the common fields
			$("#fieldSettings_form").children().not(".noHide").hide();

			if (type == 'idno') {
				$("#fieldSettings_name").prop("readonly",true).val("idno").keyup();
				$("#fieldSettings_options_required").prop({
					checked:  true,
					disabled: true,
				}).change();
				$("#fieldSettings_options_duplicates").prop({
					checked:  true,
					disabled: true,
				}).change();
				$("#fieldSettings_options_readonly").prop("disabled",true);
				$("#fieldSettings_options_disabled").removeAttr("checked").change().prop("disabled",true);
			}
			else {
				$("#fieldSettings_name").removeAttr("readonly");
				$("#fieldSettings_options_required").removeAttr("disabled");
				$("#fieldSettings_options_duplicates").removeAttr("disabled");
				$("#fieldSettings_options_readonly").removeAttr("disabled");
				$("#fieldSettings_options_disabled").removeAttr("disabled");
			}

			// Show optional fields
			switch(type) {
				case 'idno':
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();
					$("#fieldSettings_container_idno").show();
					break;

				case 'text':
				case 'textarea':
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();

					$("#fieldSettings_container_range").show();
					$("#fieldSettings_range_step").parent().hide();
					$("#fieldSettings_range_min").parent().addClass("span4").removeClass("span3");
					$("#fieldSettings_range_max").parent().addClass("span4").removeClass("span3");

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format")
						.append('<option value="characters">Characters</option')
						.append('<option value="words">Words</option')
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
					$("#fieldSettings_range_min").parent().addClass("span3").removeClass("span4");
					$("#fieldSettings_range_max").parent().addClass("span3").removeClass("span4");

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format")
						.append('<option value="value">Value</option')
						.append('<option value="digits">Digits</option')
					break;

				case 'wysiwyg':
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").hide();
					break;

				case 'email':
				case 'phone':
				case 'date':
				case 'datetime':
				case 'website':
				default:
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();
					break;
			}

			// Update field settings to use values from form display
			$("#fieldSettings_name").val($("#name_"+id).val()).keyup();
			$("#fieldSettings_label").val($("#label_"+id).val()).keyup();
			$("#fieldSettings_value").val($("#value_"+id).val()).keyup();
			$("#fieldSettings_placeholder").val($("#placeholder_"+id).val()).keyup();
			$("#fieldSettings_id").val($("#id_"+id).val()).keyup();
			$("#fieldSettings_class").val($("#class_"+id).val()).keyup();
			$("#fieldSettings_style").val($("#style_"+id).val()).keyup();

			$("#fieldSettings_choices_type").val($("#choicesType_"+id).val()).change();

			if ($("#choicesOptions_"+id).val() != undefined) {
				var opts = $("#choicesOptions_"+id).val().split("%,%");
				$("#fieldSettings_choices_manual").html('');
				for (var i = 0; i < opts.length; i++) {
					$("#fieldSettings_choices_manual").append(addChoice(opts[i],$("#choicesDefault_"+id).val()));
				}
				$("#fieldSettings_choices_manual :input[name=fieldSettings_choices_text]").keyup();
			}
			else {
				$("#fieldSettings_choices_formSelect").val($("#choicesForm_"+id).val()).change();
				$("#fieldSettings_choices_fieldSelect").val($("#choicesField_"+id).val()).change();
			}

			$("#fieldSettings_options_required").prop("checked",($("#required_"+id).val()==='true'));
			$("#fieldSettings_options_duplicates").prop("checked",($("#duplicates_"+id).val()==='true'));
			$("#fieldSettings_options_readonly").prop("checked",($("#readonly_"+id).val()==='true')).change();
			$("#fieldSettings_options_disabled").prop("checked",($("#disabled_"+id).val()==='true')).change();
			$("#fieldSettings_options_publicRelease").prop("checked",($("#publicRelease_"+id).val()==='true')).change();
			$("#fieldSettings_options_sortable").prop("checked",($("#sortable_"+id).val()==='true'));
			$("#fieldSettings_options_searchable").prop("checked",($("#searchable_"+id).val()==='true'));
			$("#fieldSettings_options_displayTable").prop("checked",($("#displayTable_"+id).val()==='true'));
			$("#fieldSettings_validation").val($("#validation_"+id).val()).change();
			$("#fieldSettings_validationRegex").val($("#validationRegex_"+id).val());
			$("#fieldSettings_range_min").val($("#min_"+id).val()).change();
			$("#fieldSettings_range_max").val($("#max_"+id).val()).change();
			$("#fieldSettings_range_step").val($("#step_"+id).val()).change();
			$("#fieldSettings_range_format").val($("#format_"+id).val()).change();
			$("#fieldSettings_idno_managedBy").val($("#managedBy_"+id).val()).change();
			$("#fieldSettings_idno_format").val($("#idnoFormat_"+id).val());
			$("#fieldSettings_idno_startIncrement").val($("#startIncrement_"+id).val());

			if ($("#type_"+id).val() != 'fieldset') {
				$("#fieldset_"+id).val($("#fieldset_"+id).parents("li").parents("li").find(":input[name^=fieldset_]").val());
			}
			else {
				$("#fieldSettings_fieldset").val($("#fieldset_"+id).val());
			}
		}

	}
}

function fieldSettingsBindings() {
	var choicesFields = {};

	// Select a field to change settings
	$("#formPreview").on("click", "li", function(event) {
		event.stopPropagation();
		if (!$(this).hasClass("well")) {
			$("#formPreview .well").removeClass("well");
			$(this).addClass("well well-small");
			$("#fieldTab a[href='#fieldSettings']").tab("show");
			showFieldSettings($(this).attr("id"));
		}
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

	$("#fieldSettings_id").keyup(function() {
		$("#formPreview .well .control-group > label").prop('for',$(this).val());
		$("#formPreview .well .controls :input").prop('id',$(this).val());
		$("#formPreview .well :input[name^=id_]").val($(this).val());
	});

	$("#fieldSettings_class").keyup(function() {
		$("#formPreview .well .controls :input").prop('class',$(this).val());
		$("#formPreview .well :input[name^=class_]").val($(this).val());
	});

	$("#fieldSettings_style").keyup(function() {
		$("#formPreview .well .controls :input").attr('style',$(this).val());
		$("#formPreview .well :input[name^=style_]").val($(this).val());
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
			$("#fieldSettings_choices_formSelect").change();
			$("#fieldSettings_choices_fieldSelect").change();
		}
	}).change();

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

					$("#formPreview .well :input[name^=choicesDefault_]").val('').val(vals.join("%,%"));
					break;

				case 'multiselect':
					if ($(this).hasClass("active")) {
						$("#formPreview .well .controls :input:last").val('');
						$("#formPreview .well :input[name^=choicesDefault_]").val('');
					}
					else {
						$("#formPreview .well .controls :input:last").val($(this).siblings(":input").val());
						$("#formPreview .well :input[name^=choicesDefault_]").val($(this).siblings(":input").val());
					}
					$("#fieldSettings_choices_manual button[name=default]").not(this).removeClass("active");
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
			$("#formPreview .well :input[name^=choicesOptions_]").val(vals.join("%,%"));

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

				case 'multiselect':
					$("#formPreview .well .controls :input:last").html('');
					for (var i = 0; i < vals.length; i++) {
						$("#formPreview .well .controls :input:last").append($("<option>").prop("value",vals[i]).text(vals[i]));
					}
					break;
			}
		})
		.on("keyup",":input[name=fieldSettings_choices_text]",function() {
			var vals = [];
			$("#fieldSettings_choices_manual input[name=fieldSettings_choices_text]").each(function() {
				vals.push($(this).val());
			});
			$("#formPreview .well :input[name^=choicesOptions_]").val(vals.join("%,%"));

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

				case 'multiselect':
					$("#formPreview .well .controls :input:last").html('');
					for (var i = 0; i < vals.length; i++) {
						$("#formPreview .well .controls :input:last").append($("<option>").prop("value",vals[i]).text(vals[i]));
					}
					break;
			}
		});

	$("#fieldSettings_choices_form")
		.on("change","#fieldSettings_choices_formSelect",function() {
			var val = $(this).val();

			if (choicesFields[val] == undefined) {
				$.ajax({
					url: "../includes/getFormFields.php?id="+val,
					async: false,
				}).done(function(data) {
					var obj = JSON.parse(data);
					var options;
					for(var i in obj) {
						var field = obj[i];
						options += '<option value="'+field.name+'">'+field.label+'</option>';
					}
					choicesFields[val] = options;
				});
			}

			$("#formPreview .well :input[name^=choicesForm_]").val(val);
			$("#fieldSettings_choices_fieldSelect").html(choicesFields[val]);
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

	$("#fieldSettings_options_disabled").change(function() {
		$("#formPreview .well .controls :input").prop('disabled',$(this).is(":checked"));
		$("#formPreview .well :input[name^=disabled_]").val($(this).is(":checked"));
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

	$("#fieldSettings_options_displayTable").change(function() {
		$("#formPreview .well :input[name^=displayTable_]").val($(this).is(":checked"));
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
		$("#formPreview .well :input[name^=min_]").val($(this).val());
		if ($("#fieldSettings_range_min").val() > $("#fieldSettings_range_max").val()) {
			$("#fieldSettings_range_max").val($("#fieldSettings_range_min").val()).change();
		}
	});

	$("#fieldSettings_range_max").change(function() {
		$("#formPreview .well :input[name^=max_]").val($(this).val());
		if ($("#fieldSettings_range_min").val() > $("#fieldSettings_range_max").val()) {
			$("#fieldSettings_range_min").val($("#fieldSettings_range_max").val()).change();
		}
	});

	$("#fieldSettings_range_step").change(function() {
		$("#formPreview .well :input[name^=step_]").val($(this).val());
	});

	$("#fieldSettings_range_format").change(function() {
		$("#formPreview .well :input[name^=format_]").val($(this).val());
	});

	$("#fieldSettings_idno_managedBy").change(function() {
		$("#formPreview .well :input[name^=managedBy_]").val($(this).val());
		if ($("#formPreview .well :input[name^=type_]").val() == 'idno') {
			if ($(this).val() == "system") {
				$("#fieldSettings_options_readonly").prop("checked",true).change();
				$("#fieldSettings_container_idno_format").show();
				$("#fieldSettings_container_idno_startIncrement").show();
			}
			else if ($(this).val() == "user") {
				$("#fieldSettings_options_readonly").removeAttr("checked").change();
				$("#fieldSettings_container_idno_format").hide();
				$("#fieldSettings_container_idno_startIncrement").hide();
			}
		}
	});

	$("#fieldSettings_idno_format").keyup(function() {
		$("#formPreview .well :input[name^=idnoFormat_]").val($(this).val());
	});

	$("#fieldSettings_idno_startIncrement").change(function() {
		$("#formPreview .well :input[name^=startIncrement_]").val($(this).val());
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
		if ($(this).is(":checked")) {
			if (confirm("Enabling this will remove any existing ID Number fields. Do you want to continue?")) {
				$("#fieldAdd li:contains('ID Number')").hide();
				$("#formPreview :input[name^=type_][value=idno]").parent().parent().remove();
			}
			else {
				$(this).removeAttr('checked');
			}
		}
		else {
			$("#fieldAdd li:contains('ID Number')").show();
		}
	}).change();
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

	output = '<i class="icon-remove"></i>';

	if (type == 'Field Set' || type == 'fieldset') {
		output += '<fieldset><legend></legend><ul class="unstyled sortable"></ul></fieldset>';
	}
	else {
		output += '<div class="control-group"><label class="control-label">Untitled</label><div class="controls">';

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
			case 'datetime':
				output += '<input type="datetime">';
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
				output += '<img src="../includes/img/wysiwyg.png" />';
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
			case 'number':
				vals['validation'] = "integer";
				break;
			case 'Email':
			case 'email':
				vals['validation'] = "emailAddr";
				break;
			case 'Phone':
			case 'tel':
				vals['validation'] = "phoneNumber";
				break;
			case 'Date':
			case 'date':
				vals['validation'] = "date";
				break;
			case 'Website':
			case 'url':
				vals['validation'] = "url";
				break;
		}
	}

	switch(type) {
		case 'ID Number':
		case 'idno':
			type = vals['type'] = 'idno';
			break;

		case 'Single Line Text':
		case 'text':
			type = vals['type'] = 'text';
			break;

		case 'Paragraph Text':
		case 'textarea':
			type = vals['type'] = 'textarea';
			break;

		case 'Radio':
		case 'radio':
			type = vals['type'] = 'radio';
			break;

		case 'Checkboxes':
		case 'checkbox':
			type = vals['type'] = 'checkbox';
			break;

		case 'Dropdown':
		case 'select':
			type = vals['type'] = 'select';
			break;

		case 'Number':
		case 'number':
			type = vals['type'] = 'number';
			break;

		case 'Email':
		case 'email':
			type = vals['type'] = 'email';
			break;

		case 'Phone':
		case 'tel':
			type = vals['type'] = 'tel';
			break;

		case 'Date':
		case 'date':
			type = vals['type'] = 'date';
			break;

		case 'Time':
		case 'datetime':
			type = vals['type'] = 'datetime';
			break;

		case 'Website':
		case 'url':
			type = vals['type'] = 'url';
			break;

		case 'Multi-Select':
		case 'multiselect':
			type = vals['type'] = 'multiselect';
			break;

		case 'WYSIWYG':
		case 'wysiwyg':
			type = vals['type'] = 'wysiwyg';
			break;

		case 'Field Set':
		case 'fieldset':
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
	output += '<input type="hidden" id="id_'+id+'" name="id_'+id+'" value="'+((vals['id']!=undefined)?vals['id']:'')+'">';
	output += '<input type="hidden" id="class_'+id+'" name="class_'+id+'" value="'+((vals['class']!=undefined)?vals['class']:'')+'">';
	output += '<input type="hidden" id="style_'+id+'" name="style_'+id+'" value="'+((vals['style']!=undefined)?vals['style']:'')+'">';
	output += '<input type="hidden" id="required_'+id+'" name="required_'+id+'" value="'+((vals['required']!=undefined)?vals['required']:'false')+'">';
	output += '<input type="hidden" id="duplicates_'+id+'" name="duplicates_'+id+'" value="'+((vals['duplicates']!=undefined)?vals['duplicates']:'false')+'">';
	output += '<input type="hidden" id="readonly_'+id+'" name="readonly_'+id+'" value="'+((vals['readonly']!=undefined)?vals['readonly']:'false')+'">';
	output += '<input type="hidden" id="disabled_'+id+'" name="disabled_'+id+'" value="'+((vals['disabled']!=undefined)?vals['disabled']:'false')+'">';
	output += '<input type="hidden" id="publicRelease_'+id+'" name="publicRelease_'+id+'" value="'+((vals['publicRelease']!=undefined)?vals['publicRelease']:'true')+'">';
	output += '<input type="hidden" id="sortable_'+id+'" name="sortable_'+id+'" value="'+((vals['sortable']!=undefined)?vals['sortable']:'')+'">';
	output += '<input type="hidden" id="searchable_'+id+'" name="searchable_'+id+'" value="'+((vals['searchable']!=undefined)?vals['searchable']:'')+'">';
	output += '<input type="hidden" id="displayTable_'+id+'" name="displayTable_'+id+'" value="'+((vals['displayTable']!=undefined)?vals['displayTable']:'')+'">';
	output += '<input type="hidden" id="validation_'+id+'" name="validation_'+id+'" value="'+((vals['validation']!=undefined)?vals['validation']:'')+'">';
	output += '<input type="hidden" id="validationRegex_'+id+'" name="validationRegex_'+id+'" value="'+((vals['validationRegex']!=undefined)?vals['validationRegex']:'')+'">';
	output += '<input type="hidden" id="access_'+id+'" name="access_'+id+'" value="'+((vals['access']!=undefined)?vals['access']:'')+'">';
	output += '<input type="hidden" id="fieldset_'+id+'" name="fieldset_'+id+'" value="'+((vals['fieldset']!=undefined)?vals['fieldset']:'')+'">';

	switch(type) {
		case 'idno':
			output += '<input type="hidden" id="managedBy_'+id+'" name="managedBy_'+id+'" value="'+((vals['managedBy']!=undefined)?vals['managedBy']:'')+'">';
			output += '<input type="hidden" id="idnoFormat_'+id+'" name="idnoFormat_'+id+'" value="'+((vals['idnoFormat']!=undefined)?vals['idnoFormat']:'')+'">';
			output += '<input type="hidden" id="startIncrement_'+id+'" name="startIncrement_'+id+'" value="'+((vals['startIncrement']!=undefined)?vals['startIncrement']:'1')+'">';
			break;

		case 'text':
		case 'textarea':
		case 'number':
			output += '<input type="hidden" id="min_'+id+'" name="min_'+id+'" value="'+((vals['min']!=undefined)?vals['min']:'')+'">';
			output += '<input type="hidden" id="max_'+id+'" name="max_'+id+'" value="'+((vals['max']!=undefined)?vals['max']:'')+'">';
			output += '<input type="hidden" id="step_'+id+'" name="step_'+id+'" value="'+((vals['step']!=undefined)?vals['step']:'')+'">';
			output += '<input type="hidden" id="format_'+id+'" name="format_'+id+'" value="'+((vals['format']!=undefined)?vals['format']:'')+'">';
			break;

		case 'radio':
		case 'checkbox':
		case 'select':
		case 'multiselect':
			output += '<input type="hidden" id="choicesType_'+id+'" name="choicesType_'+id+'" value="'+((vals['choicesType']!=undefined)?vals['choicesType']:'')+'">';
			output += '<input type="hidden" id="choicesDefault_'+id+'" name="choicesDefault_'+id+'" value="'+((vals['choicesDefault']!=undefined)?vals['choicesDefault']:'')+'">';
			output += '<input type="hidden" id="choicesOptions_'+id+'" name="choicesOptions_'+id+'" value="'+((vals['choicesOptions']!=undefined)?vals['choicesOptions']:'First Choice%,%Second Choice')+'">';
			output += '<input type="hidden" id="choicesForm_'+id+'" name="choicesForm_'+id+'" value="'+((vals['choicesForm']!=undefined)?vals['choicesForm']:'')+'">';
			output += '<input type="hidden" id="choicesField_'+id+'" name="choicesField_'+id+'" value="'+((vals['choicesField']!=undefined)?vals['choicesField']:'')+'">';
			break;

		default:
			break;
	}

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