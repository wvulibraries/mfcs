/*
 * Form Creator Functions
 */

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

function showFieldSettings(fullID) {
	// Create jQuery shortcuts (code optimization)
	var fieldSettings_form          = $("#fieldSettings_form");
	var fieldSettings_fieldset_form = $("#fieldSettings_fieldset_form");

	if (fullID === undefined) {
		// Hide the form and show a warning about having nothing selected
		$("#noFieldSelected").show();
		fieldSettings_fieldset_form.hide();
		fieldSettings_form.hide();
	}
	else {

		var id       = fullID.split("_")[1];
		var type     = $("#type_"+id).val();
		var fieldset = $("#fieldset_"+id);

		// Select the Field Settings tab
		$("#fieldTab a[href='#fieldSettings']").tab("show");

		// Hide the nothing selected error and show the form
		$("#noFieldSelected").hide();
		if (type == "fieldset") {
			fieldSettings_fieldset_form.show();
			fieldSettings_form.hide();

			$("#fieldSettings_fieldset").val(fieldset.val()).keyup();
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
				case 'textarea':
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();

					$("#fieldSettings_container_range").show();
					$("#fieldSettings_range_step").parent().hide();
					$("#fieldSettings_range_min").parent().addClass("span4").removeClass("span3");
					$("#fieldSettings_range_max").parent().addClass("span4").removeClass("span3");

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="characters">Characters</option><option value="words">Words</option>')
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
					$("#fieldSettings_range_format").append('<option value="value">Value</option><option value="digits">Digits</option>')
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

				case 'email':
				case 'phone':
				case 'date':
				case 'time':
				case 'website':
				default:
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();
					break;
			}

			// Update field settings to use values from form display
			fieldSettings_name.val($("#name_"+id).val()).keyup();
			$("#fieldSettings_label").val($("#label_"+id).val()).keyup();
			$("#fieldSettings_value").val($("#value_"+id).val()).keyup();
			$("#fieldSettings_placeholder").val($("#placeholder_"+id).val()).keyup();
			$("#fieldSettings_id").val($("#id_"+id).val()).keyup();
			$("#fieldSettings_class").val($("#class_"+id).val()).keyup();
			$("#fieldSettings_style").val($("#style_"+id).val()).keyup();

			var fieldHelp = $("#help_"+id).val();
			if(fieldHelp != ''){
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
			if (choicesOptions_val != undefined) {
				var opts                         = choicesOptions_val.split("%,%");
				var fieldSettings_choices_manual = $("#fieldSettings_choices_manual");
				var tmp                          = '';

				// Update left panel
				for (var i = 0; i < opts.length; i++) {
					tmp += addChoice(opts[i],$("#choicesDefault_"+id).val());
				}
				fieldSettings_choices_manual.html(tmp).find("input[name=fieldSettings_choices_text]").keyup();
			}

			$("#fieldSettings_choices_formSelect").val($("#choicesForm_"+id).val()).change();
			$("#fieldSettings_choices_fieldSelect").val($("#choicesField_"+id).val()).change();
			$("#fieldSettings_choices_fieldDefault").val($("#choicesFieldDefault_"+id).val()).change();

			$("#fieldSettings_choices_type").val($("#choicesType_"+id).val()).change(); // Must be after options stuff
			$("#fieldSettings_choices_null").prop("checked",($("#choicesNull_"+id).val()==='true')).change();

			fieldSettings_options_required.prop("checked",($("#required_"+id).val()==='true'));
			fieldSettings_options_duplicates.prop("checked",($("#duplicates_"+id).val()==='true'));
			fieldSettings_options_readonly.prop("checked",($("#readonly_"+id).val()==='true')).change();
			fieldSettings_options_disabled.prop("checked",($("#disabled_"+id).val()==='true')).change();
			$("#fieldSettings_options_disabled_insert").prop("checked",($("#disabledInsert_"+id).val()==='true')).change();
			$("#fieldSettings_options_disabled_update").prop("checked",($("#disabledUpdate_"+id).val()==='true')).change();
			$("#fieldSettings_options_publicRelease").prop("checked",($("#publicRelease_"+id).val()==='true')).change();
			$("#fieldSettings_options_sortable").prop("checked",($("#sortable_"+id).val()==='true'));
			$("#fieldSettings_options_searchable").prop("checked",($("#searchable_"+id).val()==='true'));
			fieldSettings_options_displayTable.prop("checked",($("#displayTable_"+id).val()==='true'));
			$("#fieldSettings_options_hidden").prop("checked",($("#hidden_"+id).val()==='true'));
			$("#fieldSettings_validation").val($("#validation_"+id).val()).change();
			$("#fieldSettings_validationRegex").val($("#validationRegex_"+id).val());
			$("#fieldSettings_range_min").val($("#min_"+id).val()).change();
			$("#fieldSettings_range_max").val($("#max_"+id).val()).change();
			$("#fieldSettings_range_step").val($("#step_"+id).val()).change();
			$("#fieldSettings_range_format").val($("#format_"+id).val()).change();
			$("#fieldSettings_idno_managedBy").val($("#managedBy_"+id).val()).change();
			$("#fieldSettings_idno_format").val($("#idnoFormat_"+id).val());
			$("#fieldSettings_idno_startIncrement").val($("#startIncrement_"+id).val());
			$("#fieldSettings_idno_confirm").prop("checked",($("#idnoConfirm_"+id).val()==='true'));

			var allowedExtensions_val = $("#allowedExtensions_"+id).val();
			if (allowedExtensions_val != undefined) {
				var opts                                 = allowedExtensions_val.split("%,%");
				var fieldSettings_file_allowedExtensions = $("#fieldSettings_file_allowedExtensions");
				var tmp                                  = '';

				fieldSettings_file_allowedExtensions.html('');
				for (var i = 0; i < opts.length; i++) {
					tmp += addAllowedExtension(opts[i]);
				}
				fieldSettings_file_allowedExtensions.append(tmp);
				fieldSettings_file_allowedExtensions.find(":input[name=fieldSettings_allowedExtension_text]:first").keyup();
			}

			$("#fieldSettings_file_options_bgProcessing").prop("checked",($("#bgProcessing_"+id).val()==='true')).change();

			var $fieldSettings_file_options_multipleFiles = $("#fieldSettings_file_options_multipleFiles");
			$fieldSettings_file_options_multipleFiles.prop("checked",($("#multipleFiles_"+id).val()==='true'));
			if($("#combine_"+id).val()==='true'){
				$("#fieldSettings_file_options_combine").prop("checked",true);
				$fieldSettings_file_options_multipleFiles.attr('disabled','disabled');
			}else{
				$("#fieldSettings_file_options_combine").prop("checked",false);
				$fieldSettings_file_options_multipleFiles.removeAttr('disabled');
			}
			$("#fieldSettings_file_options_ocr").prop("checked",($("#ocr_"+id).val()==='true'));
			$("#fieldSettings_file_options_convert").prop("checked",($("#convert_"+id).val()==='true')).change();
			$("#fieldSettings_file_convert_height").val($("#convertHeight_"+id).val());
			$("#fieldSettings_file_convert_width").val($("#convertWidth_"+id).val());
			$("#fieldSettings_file_convert_resolution").val($("#convertResolution_"+id).val());
			$("#fieldSettings_file_convert_format").val($("#convertFormat_"+id).val());
			$("#fieldSettings_file_convert_watermark").prop("checked",($("#watermark_"+id).val()==='true')).change();
			$("#fieldSettings_file_watermark_image").val($("#watermarkImage_"+id).val());
			$("#fieldSettings_file_watermark_location").val($("#watermarkLocation_"+id).val());
			$("#fieldSettings_file_convert_border").prop("checked",($("#border_"+id).val()==='true')).change();
			$("#fieldSettings_file_border_height").val($("#borderHeight_"+id).val());
			$("#fieldSettings_file_border_width").val($("#borderWidth_"+id).val());
			$("#fieldSettings_file_border_color").val($("#borderColor_"+id).val());
			$("#fieldSettings_file_options_thumbnail").prop("checked",($("#thumbnail_"+id).val()==='true')).change();
			$("#fieldSettings_file_thumbnail_height").val($("#thumbnailHeight_"+id).val());
			$("#fieldSettings_file_thumbnail_width").val($("#thumbnailWidth_"+id).val());
			$("#fieldSettings_file_thumbnail_format").val($("#thumbnailFormat_"+id).val());
			$("#fieldSettings_file_options_mp3").prop("checked",($("#mp3_"+id).val()==='true')).change();

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

	}
}

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
		$helpPreviewModal.hide()
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

			switch ($("#type_"+id).val()) {
				case 'select':
					if ($(this).hasClass("active")) {
						formPreviewWell.find(".control-group > .controls > :input").val('');
						$("#choicesDefault_"+id).val('');
					}
					else {
						var val = $(this).siblings(":input").val();

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
						var val  = $(this).siblings(":input").val();
						var text = $(this).text();

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
					var text = $(this).text();
					var val  = $(this).siblings(":input").val();
					var vals = [];

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
						var val = $(this).siblings(":input").val();

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
			if ($(this).parent().siblings().length == 0) {
				$(this).siblings("button[name=add]").click();
			}
			$(this).parent().remove();

			var formPreviewWell = formPreview.find(".well");
			var id              = formPreviewWell.prop("id").split("_")[1];
			var val             = $(this).val();
			var vals            = [];

			// Change value in hidden field
			$("#fieldSettings_choices_manual").find("input[name=fieldSettings_choices_text]").each(function() {
				vals.push($(this).val());
			});
			$("#choicesOptions_"+id).val(vals.join("%,%"));

			switch ($("#type_"+id).val()) {
				case 'select':
					var input = formPreviewWell.find(".control-group > .controls > :input");
					var tmp   = '';

					// Set options in preview pane
					for (var i = 0; i < vals.length; i++) {
						tmp += '<option value="'+vals[i]+'">'+vals[i]+'</option>';
					}
					input.html(tmp);
					break;

				case 'radio':
					var controls = formPreviewWell.find(".controls");
					var tmp      = '';

					controls.html('');
					for (var i = 0; i < vals.length; i++) {
						tmp += '<label class="radio"><input type="radio" name="'+$("#name_"+id).val()+'">'+vals[i]+'</label>';
					}
					controls.append(tmp);
					break;

				case 'checkbox':
					var controls = formPreviewWell.find(".controls");
					var tmp      = '';

					controls.html('');
					for (var i = 0; i < vals.length; i++) {
						tmp += '<label class="checkbox"><input type="checkbox" name="'+$("#name_"+id).val()+'">'+vals[i]+'</label>';
					}
					controls.append(tmp);
					break;

				case 'multiselect':
					var lastInput = formPreviewWell.find(".control-group > .controls > :input:last");
					var tmp       = '';

					lastInput.html('');
					for (var i = 0; i < vals.length; i++) {
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

			// Change value in hidden field
			$("#fieldSettings_choices_manual").find("input[name=fieldSettings_choices_text]").each(function() {
				vals.push($(this).val());
			});
			$("#choicesOptions_"+id).val(vals.join("%,%"));

			if ($("#fieldSettings_choices_type").val() == 'manual') {
				switch ($("#type_"+id).val()) {
					case 'select':
						var input = formPreviewWell.find(".control-group > .controls > :input");
						var tmp   = '';

						// Set options in preview pane
						if($('#fieldSettings_choices_null').prop('checked')){
							tmp += '<option value="">Make a selection</option>';
						}
						for (var i = 0; i < vals.length; i++) {
							tmp += '<option value="'+vals[i]+'">'+vals[i]+'</option>';
						}
						input.html(tmp);
						break;

					case 'radio':
						var controls = formPreviewWell.find(".controls");
						var tmp      = '<div class="checkboxList">';

						for (var i = 0; i < vals.length; i++) {
							tmp += '<label class="radio"><input type="radio" name="'+$("#name_"+id).val()+'">'+vals[i]+'</label>';
						}
						controls.html(tmp+'</div>');
						break;

					case 'checkbox':
						var controls = formPreviewWell.find(".controls");
						var tmp      = '<div class="checkboxList">';

						for (var i = 0; i < vals.length; i++) {
							tmp += '<label class="checkbox"><input type="checkbox" name="'+$("#name_"+id).val()+'">'+vals[i]+'</label>';
						}
						controls.html(tmp+'</div>');
						break;

					case 'multiselect':
						var lastInput = formPreviewWell.find(".control-group > .controls > :input:last");
						var tmp       = '';

						lastInput.html('');
						for (var i = 0; i < vals.length; i++) {
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

			if (choicesFields[val] == undefined) {
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

		if ($('#submitForm').find('input[name=id]').val() != '') {
			$("#fieldSettings_container_idno_confirm").removeClass('hidden');
		}
	});

	$("#fieldSettings_idno_startIncrement").change(function() {
		var formPreviewWell = formPreview.find(".well");
		var id              = formPreviewWell.prop("id").split("_")[1];

		$("#startIncrement_"+id).val($(this).val());

		if ($('#submitForm').find('input[name=id]').val() != '') {
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
			if ($(this).parent().siblings().length == 0) {
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

			if(val != '') $('#allowedExtensionsAlert').hide();
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
			if (idnoType.length == 0) {
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
			fieldAdd.find("li:contains('ID Number')").show();
			fieldAdd.find("li:contains('Paragraph Text')").show();
			fieldAdd.find("li:contains('Radio')").show();
			fieldAdd.find("li:contains('Checkboxes')").show();
			fieldAdd.find("li:contains('Dropdown')").show();
			fieldAdd.find("li:contains('Multi-Select')").show();
			fieldAdd.find("li:contains('File Upload')").show();
			fieldAdd.find("li:contains('WYSIWYG')").show();
			fieldAdd.find("li:contains('Field Set')").parent().show().prev().show();

			if (idnoType.length == 0) {
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
		if ($("#formPreview").find("input[name^=type_][value=idno]").length == 0) {
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
		output += ' <span class="icon-question-sign helpPreview" style="display: none; cursor: pointer;"></span>';
		output += ' <span class="icon-question-sign helpPreviewModal" style="display: none; cursor: pointer;" title="Click to view help" onclick="$(\'#fieldHelpModal\').modal(\'show\')"></span>';
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
		case 'time':
			type = vals['type'] = 'time';
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

		case 'File Upload':
		case 'file':
			type = vals['type'] = 'file';
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
	output += '<input type="hidden" id="help_'+id+'" name="help_'+id+'" value="'+((vals['help']!=undefined)?vals['help']:'')+'">';
	output += '<input type="hidden" id="required_'+id+'" name="required_'+id+'" value="'+((vals['required']!=undefined)?vals['required']:'false')+'">';
	output += '<input type="hidden" id="duplicates_'+id+'" name="duplicates_'+id+'" value="'+((vals['duplicates']!=undefined)?vals['duplicates']:'false')+'">';
	output += '<input type="hidden" id="readonly_'+id+'" name="readonly_'+id+'" value="'+((vals['readonly']!=undefined)?vals['readonly']:'false')+'">';
	output += '<input type="hidden" id="disabled_'+id+'" name="disabled_'+id+'" value="'+((vals['disabled']!=undefined)?vals['disabled']:'false')+'">';
	output += '<input type="hidden" id="disabledInsert_'+id+'" name="disabledInsert_'+id+'" value="'+((vals['disabledInsert']!=undefined)?vals['disabledInsert']:'false')+'">';
	output += '<input type="hidden" id="disabledUpdate_'+id+'" name="disabledUpdate_'+id+'" value="'+((vals['disabledUpdate']!=undefined)?vals['disabledUpdate']:'false')+'">';
	output += '<input type="hidden" id="publicRelease_'+id+'" name="publicRelease_'+id+'" value="'+((vals['publicRelease']!=undefined)?vals['publicRelease']:'true')+'">';
	output += '<input type="hidden" id="sortable_'+id+'" name="sortable_'+id+'" value="'+((vals['sortable']!=undefined)?vals['sortable']:'')+'">';
	output += '<input type="hidden" id="searchable_'+id+'" name="searchable_'+id+'" value="'+((vals['searchable']!=undefined)?vals['searchable']:'')+'">';
	output += '<input type="hidden" id="displayTable_'+id+'" name="displayTable_'+id+'" value="'+((vals['displayTable']!=undefined)?vals['displayTable']:'')+'">';
	output += '<input type="hidden" id="hidden_'+id+'" name="hidden_'+id+'" value="'+((vals['hidden']!=undefined)?vals['hidden']:'')+'">';
	output += '<input type="hidden" id="validation_'+id+'" name="validation_'+id+'" value="'+((vals['validation']!=undefined)?vals['validation']:'')+'">';
	output += '<input type="hidden" id="validationRegex_'+id+'" name="validationRegex_'+id+'" value="'+((vals['validationRegex']!=undefined)?vals['validationRegex']:'')+'">';
	output += '<input type="hidden" id="access_'+id+'" name="access_'+id+'" value="'+((vals['access']!=undefined)?vals['access']:'')+'">';
	output += '<input type="hidden" id="fieldset_'+id+'" name="fieldset_'+id+'" value="'+((vals['fieldset']!=undefined)?vals['fieldset']:'')+'">';

	switch(type) {
		case 'idno':
			output += '<input type="hidden" id="managedBy_'+id+'" name="managedBy_'+id+'" value="'+((vals['managedBy']!=undefined)?vals['managedBy']:'')+'">';
			output += '<input type="hidden" id="idnoFormat_'+id+'" name="idnoFormat_'+id+'" value="'+((vals['idnoFormat']!=undefined)?vals['idnoFormat']:'')+'">';
			output += '<input type="hidden" id="startIncrement_'+id+'" name="startIncrement_'+id+'" value="'+((vals['startIncrement']!=undefined)?vals['startIncrement']:'1')+'">';
			output += '<input type="hidden" id="idnoConfirm_'+id+'" name="idnoConfirm_'+id+'" value="false">';
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
			output += '<input type="hidden" id="choicesNull_'+id+'" name="choicesNull_'+id+'" value="'+((vals['choicesNull']!=undefined)?vals['choicesNull']:'')+'">';
			output += '<input type="hidden" id="choicesDefault_'+id+'" name="choicesDefault_'+id+'" value="'+((vals['choicesDefault']!=undefined)?vals['choicesDefault']:'')+'">';
			output += '<input type="hidden" id="choicesOptions_'+id+'" name="choicesOptions_'+id+'" value="'+((vals['choicesOptions']!=undefined)?vals['choicesOptions']:'First Choice%,%Second Choice')+'">';
			output += '<input type="hidden" id="choicesForm_'+id+'" name="choicesForm_'+id+'" value="'+((vals['choicesForm']!=undefined)?vals['choicesForm']:'')+'">';
			output += '<input type="hidden" id="choicesField_'+id+'" name="choicesField_'+id+'" value="'+((vals['choicesField']!=undefined)?vals['choicesField']:'')+'">';
			output += '<input type="hidden" id="choicesFieldDefault_'+id+'" name="choicesFieldDefault_'+id+'" value="'+((vals['choicesFieldDefault']!=undefined)?vals['choicesFieldDefault']:'')+'">';
			break;

		case 'file':
			output += '<input type="hidden" id="allowedExtensions_'+id+'" name="allowedExtensions_'+id+'" value="'+((vals['allowedExtensions']!=undefined)?vals['allowedExtensions']:'tif%,%tiff')+'">';
			output += '<input type="hidden" id="bgProcessing_'+id+'" name="bgProcessing_'+id+'" value="'+((vals['bgProcessing']!=undefined)?vals['bgProcessing']:'')+'">';
			output += '<input type="hidden" id="multipleFiles_'+id+'" name="multipleFiles_'+id+'" value="'+((vals['multipleFiles']!=undefined)?vals['multipleFiles']:'')+'">';
			output += '<input type="hidden" id="combine_'+id+'" name="combine_'+id+'" value="'+((vals['combine']!=undefined)?vals['combine']:'')+'">';
			output += '<input type="hidden" id="ocr_'+id+'" name="ocr_'+id+'" value="'+((vals['ocr']!=undefined)?vals['ocr']:'')+'">';
			output += '<input type="hidden" id="convert_'+id+'" name="convert_'+id+'" value="'+((vals['convert']!=undefined)?vals['convert']:'')+'">';
			output += '<input type="hidden" id="convertHeight_'+id+'" name="convertHeight_'+id+'" value="'+((vals['convertHeight']!=undefined)?vals['convertHeight']:'')+'">';
			output += '<input type="hidden" id="convertWidth_'+id+'" name="convertWidth_'+id+'" value="'+((vals['convertWidth']!=undefined)?vals['convertWidth']:'')+'">';
			output += '<input type="hidden" id="convertResolution_'+id+'" name="convertResolution_'+id+'" value="'+((vals['convertResolution']!=undefined)?vals['convertResolution']:'192')+'">';
			output += '<input type="hidden" id="convertFormat_'+id+'" name="convertFormat_'+id+'" value="'+((vals['convertFormat']!=undefined)?vals['convertFormat']:'JPG')+'">';
			output += '<input type="hidden" id="watermark_'+id+'" name="watermark_'+id+'" value="'+((vals['watermark']!=undefined)?vals['watermark']:'')+'">';
			output += '<input type="hidden" id="watermarkImage_'+id+'" name="watermarkImage_'+id+'" value="'+((vals['watermarkImage']!=undefined)?vals['watermarkImage']:'')+'">';
			output += '<input type="hidden" id="watermarkLocation_'+id+'" name="watermarkLocation_'+id+'" value="'+((vals['watermarkLocation']!=undefined)?vals['watermarkLocation']:'')+'">';
			output += '<input type="hidden" id="border_'+id+'" name="border_'+id+'" value="'+((vals['border']!=undefined)?vals['border']:'')+'">';
			output += '<input type="hidden" id="borderHeight_'+id+'" name="borderHeight_'+id+'" value="'+((vals['borderHeight']!=undefined)?vals['borderHeight']:'')+'">';
			output += '<input type="hidden" id="borderWidth_'+id+'" name="borderWidth_'+id+'" value="'+((vals['borderWidth']!=undefined)?vals['borderWidth']:'')+'">';
			output += '<input type="hidden" id="borderColor_'+id+'" name="borderColor_'+id+'" value="'+((vals['borderColor']!=undefined)?vals['borderColor']:'')+'">';
			output += '<input type="hidden" id="thumbnail_'+id+'" name="thumbnail_'+id+'" value="'+((vals['thumbnail']!=undefined)?vals['thumbnail']:'')+'">';
			output += '<input type="hidden" id="thumbnailHeight_'+id+'" name="thumbnailHeight_'+id+'" value="'+((vals['thumbnailHeight']!=undefined)?vals['thumbnailHeight']:'150')+'">';
			output += '<input type="hidden" id="thumbnailWidth_'+id+'" name="thumbnailWidth_'+id+'" value="'+((vals['thumbnailWidth']!=undefined)?vals['thumbnailWidth']:'150')+'">';
			output += '<input type="hidden" id="thumbnailFormat_'+id+'" name="thumbnailFormat_'+id+'" value="'+((vals['thumbnailFormat']!=undefined)?vals['thumbnailFormat']:'JPG')+'">';
			output += '<input type="hidden" id="mp3_'+id+'" name="mp3_'+id+'" value="'+((vals['mp3']!=undefined)?vals['mp3']:'')+'">';
			break;

		default:
			break;
	}

	return output;
}

function addChoice(val,def) {
	if (val == undefined) {
		return '<div class="row-fluid input-prepend input-append">'+
					'<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
					'<input name="fieldSettings_choices_text" type="text">'+
					'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
					'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
				'</div>';
	}
	else if (def == undefined) {
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

	if (val == undefined) {
		val = '';
	}

	return '<div class="row-fluid input-append">'+
				'<input name="fieldSettings_allowedExtension_text" type="text" value="'+val+'">'+
				'<button name="add" class="btn" type="button" title="Add an extension."><i class="icon-plus"></i></button>'+
				'<button name="remove" class="btn" type="button" title="Remove this extension."><i class="icon-remove"></i></button>'+
			'</div>';
}

/*
 * Navigation Creator Functions
 */

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
		vals['type']   = type = $(item).data("type");
		vals['label']  = $("a", item).text();
	}

	if ($(item).data("formid")) {
		vals['formID'] = $(item).data("formid");
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

	if (vals == undefined) {
		vals = {};
	}

	switch(type) {
		case 'New Grouping':
		case 'grouping':
			type = vals['type'] = 'grouping';
			break;

		case 'Log Out':
		case 'logout':
			type = vals['type'] = 'logout';
			break;

		case 'Export Link':
		case 'export':
			type = vals['type'] = 'export';
			break;

		case 'Link':
		case 'link':
			type = vals['type'] = 'link';
			break;

		default:
			break;
	}

	output  = '<input type="hidden" id="nav_position_'+id+'" name="nav_position_'+id+'" value="'+((vals['position']!=undefined)?vals['position']:'')+'">';
	output += '<input type="hidden" id="nav_type_'+id+'" name="nav_type_'+id+'" value="'+((vals['type']!=undefined)?vals['type']:type)+'">';
	output += '<input type="hidden" id="nav_label_'+id+'" name="nav_label_'+id+'" value="'+((vals['label']!=undefined)?vals['label']:'Untitled')+'">';
	output += '<input type="hidden" id="nav_url_'+id+'" name="nav_url_'+id+'" value="'+((vals['url']!=undefined)?vals['url']:'')+'">';
	output += '<input type="hidden" id="nav_grouping_'+id+'" name="nav_grouping_'+id+'" value="'+((vals['grouping']!=undefined)?vals['grouping']:'')+'">';

	switch(type) {
		case 'objectForm':
		case 'metadataForm':
			output += '<input type="hidden" id="nav_formID_'+id+'" name="nav_formID_'+id+'" value="'+((vals['formID']!=undefined)?vals['formID']:'')+'">';
			break;

		default:
			break;
	}

	return output;
}
