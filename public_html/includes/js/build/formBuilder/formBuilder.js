// Global Variable
// ===================================================================
var globalFieldID;
var choicesFields = {};
var idnoValues = {}; // global

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
	applyFormPreview();

	if($('#fieldVariablesLink').length !== 0){
		$('#fieldVariablesLink').click(function(){
			$('#defaultValueVariables').fadeIn().addClass('show');
    		$('.bgCloak').show();
    		$('html,body').addClass('modalBlockScroll');
		});
	}

	// Blank all panes when changing tabs
	fieldTab.on("click", "a", function() {
		$('li', formPreview).removeClass("well activeField");
		showFieldSettings(); // blank the Field Settings pane
	});

	$(document).keyup(function(e) {
         if (e.keyCode == 27) { // escape key maps to keycode `27`
          	$('#formPreview li').removeClass('activeField');
			$('.addFieldNav').click().tab('show');
        }
    });

    // Get panel to scroll with the user after 700px
    if($(window).width() >= 768 && ($('#leftPanel').length !== 0)){
		var stickyEl = $('#leftPanel');
		var elTop = stickyEl.offset().top - 150;
		stickyEl.wrapInner( "<div id='leftPanelFixed'><div id='widthContstrain'></div></div>");
		var mainHeight = $('.main').height();
		var windowHeight = $(window).height();

		if(mainHeight > windowHeight){
		    $(window).scroll(function() {
		        stickyEl.toggleClass('sticky', $(window).scrollTop() > elTop);
		    });
		} else {
			stickyEl.addClass('tooSmallSticky');
		}
	 }


	// Click and Draggable form fields.
	$(".draggable li", fieldAdd)
		.draggable({
			connectToSortable: "#formCreator ul.sortable",
			helper: "clone",
			revert: "invalid"})
		.click(function(event) {
			event.preventDefault();
			$(this).clone().appendTo(formPreview);
			addNewField($("li:last",formPreview));
			sortableForm();
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

		// // Make sure has a display title
		if($('#formSettings_objectDisplayTitle').val().length === 0){
			$('#formSettings_objectDisplayTitle').val($('#formSettings_formTitle').val()).change();
		}

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

	// make sure that form settings is showing first and when updating
	$('#fieldTab li:nth-child(3)').find('a').click();

	// Unassigned forms empty?
	if($('.unassignedForms li').length == 0){
		$('.unassignedForms').hide();
	} else {
		$('.unassignedForms').show();
	}

	// Navigation Tab Clicked
	$('.navigationCreator').click(function(){
		$('#groupingTab li:first-child').find('a').click().tab('show');
	});

	// Enable the submit button and hide thenoJavaScriptWarning
	$(':submit').removeAttr('disabled');
});

// Remove Item Function
// ===================================================================
function removeFormPreviewItem(icon){
	if (confirm("Are you sure you want to remove this field?")) {
		var thisLI = icon.parent().parent();

		if (icon.parent().next().children(":input[name^=type_]").val() == 'fieldset') {
			thisLI.after(icon.next().find("li"));
		}

		var activeNavTab = $('.addFieldNav').parent();

		thisLI.removeClass('well activeField').remove();

		if(!activeNavTab.hasClass('active')){
			$('.addFieldNav').click().tab('show');
		 	$('#formPreview li:first-child').trigger('click');
		}

		if ($("#formSettings_formMetadata").not(":checked")) {
			if ($(":input[name^=type_][value=idno]",formPreview).length === 0) {
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
}


// Helper Functions
// ===================================================================
function applyFormPreview(){
	var formPreview;

	if(typeof globalFieldID === 'undefined'){
		formPreview = $('#formPreview').children().find('.fieldPreview');
	}
	else {
		formPreview = $('#formPreview_'+globalFieldID).find('.fieldPreview');
	}

	formPreview.each(function(){
		var label          = $(this).find('.fieldLabels');
		var controls       = $(this).find('.controls').children();
		var settings       = $(this).next();
		var previewID      = $(this).parent().data('id');

		var placeholder    = $('#placeholder_'+previewID).val();
		var type           = $('#type_'+previewID).val();
		var name           = $('#name_'+previewID).val();
		var style          = $('#style_'+previewID).val();
		var labelValue     = $('#label_'+previewID).val();
		var id             = $('#id_'+previewID).val();
		var someClass      = $('#class_'+previewID).val();
		var value          = $('#value_'+previewID).val();

		var disabled       = $('#disabled_'+previewID).val();
		var readonly       = $('#readonly_'+previewID).val();
		var hidden         = $('#hidden_'+previewID).val();

		var choices        = $('#choices_'+previewID).val();
		var choicesType    = $('#choicesType_'+previewID).val();

		controls.attr({
			'placeholder' : placeholder,
			'name'        : name,
			'id'		  : id,
			'class'		  : someClass
		});

		controls.val(value);

		if(disabled === "true" || readonly === "true"){
			controls.prop('readonly', true).addClass('disabled');
			controls.prop('disabled', true).addClass('disabled');
		} else {
			controls.prop('readonly', false).removeClass('disabled');
			controls.prop('disabled', false).removeClass('disabled');
		}

		if(value.length){
			controls.val(value);
		}

		label.html(labelValue);
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
// It also selects what field options show when a field is clicked
// This form is the main caller of bindings and form previews

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
				fieldSettings_options_readonly.prop({
					checked:  false,
					disabled: false,
				}).change();
				fieldSettings_options_disabled.prop({
					checked:  false,
					disabled: true,
				}).change();
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
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();
					$("#fieldSettings_container_externalUpdate").parent().hide();
					$("#fieldSettings_container_range").parent().show();
					$("#fieldSettings_range_step").parent().hide();
					$('#fieldSettings_range_format').parent().removeClass('span4').addClass('span6');
					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="characters">Characters</option><option value="words">Words</option>');
					break;

				case 'textarea':
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();
					$("#fieldSettings_container_range").parent().show();
					$("#fieldSettings_range_step").parent().hide();
					$('#fieldSettings_range_format').parent().removeClass('span4').addClass('span6');
					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="characters">Characters</option><option value="words">Words</option>');
					break;

				case 'radio':
				case 'checkbox':
					$("#fieldSettings_container_choices").parent().show();
					$("#fieldSettings_container_value").hide();
					$("#fieldSettings_container_placeholder").hide();
					$('#fieldSettings_choices_null').parent().css("visibility", "hidden");
					break;

				case 'select':
				case 'multiselect':
					$("#fieldSettings_container_choices").parent().show();
					$("#fieldSettings_container_value").hide();
					$("#fieldSettings_container_placeholder").hide();
					$('#fieldSettings_choices_null').parent().css("visibility", "visible");
					break;

				case 'number':
					$("#fieldSettings_container_value").hide();
					$("#fieldSettings_container_placeholder").show();
					$("#fieldSettings_container_range").parent().show();
					$("#fieldSettings_range_step").parent().show();
					$('#fieldSettings_range_format').parent().removeClass('span6').addClass('span4');

					$("#fieldSettings_range_format option").remove();
					$("#fieldSettings_range_format").append('<option value="value">Value</option><option value="digits">Digits</option>');
					break;

				case 'wysiwyg':
					$("#fieldSettings_container_placeholder").hide();
					$("#fieldSettings_container_value").show();
					break;

				case 'file':
					$("#fieldSettings_container_file_allowedExtensions").parent().show();
					$("#fieldSettings_container_file_options").parent().show();
					$("#fieldSettings_container_value").hide();
					$("#fieldSettings_container_placeholder").hide();
					break;

				default:
					$("#fieldSettings_container_value").show();
					$("#fieldSettings_container_placeholder").show();
					break;
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

			var metaDataStandards = $('#metadataStandard_'+id).val();
			if(metaDataStandards !== undefined){
				var displayMDStandards = $("#metadataStandard_options");
				opts = metaDataStandards.split("%,%");
				tmp  = '';
				displayMDStandards.html('');

				for (i = 0; i < opts.length; i++) {
					tmp += addMetadataStandard(opts[i]);
				}
				displayMDStandards.append(tmp);

				// need to be used to get the values into the select menus
				$('.mdStandardSelect').each(function(){
					var selectValue = $(this).data('selectvalue');
					$(this).val(selectValue);
				});
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

			// Ajax Stuff that still needed
			$("#fieldSettings_externalUpdate_formSelect").change(function() {
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

			$('.uxOptions').find('input').change(function(){
				var dataObj = $(this).data('bindmodel');
				var element  = $("."+dataObj);
				if($(this).is(':checked') || $(this).value){
					element.show();
				} else {
					element.hide();
				}

				if(dataObj == 'convert' && (!$(this).is(':checked') || !$(this).value)){
					$('#fieldSettings_file_convert_watermark').prop('checked', false).change();
					$('#fieldSettings_file_convert_border').prop('checked', false).change();
				}
			});

			// bind functionality of form
			enableChoiceFunctionality();
		}
	}
}

function fieldSettingsBindings(){
	var formPreview   = $("#formPreview");
	formPreview.children('li').removeClass('activeField');

    // Setup Form Bindings
    $('#fieldSettings_form').find("[data-bindmodel]").bind('change keyup', bindToHiddenForm);

	// Select a field to change settings
	formPreview.on("click", "li", function(event) {
		event.stopPropagation();
		if($(event.target).is('.icon-remove')){
			removeFormPreviewItem($(event.target));
		} else {
			var id = $(this).data('id');
			globalFieldID = id;

			formPreview.find('[data-bind]').bind('change', setOriginalValues);

			if(!$(this).hasClass('activeField')){
				formPreview.find('.activeField').removeClass('activeField');
				$(this).addClass('activeField');
				$("#fieldTab a[href='#fieldSettings']").tab("show");
				showFieldSettings(id);
				setInitialBind();
				applyFormPreview();
			}
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

    var parentObj  = $("#formPreview").find("[data-id='"+ id +"']");
    var formPreview = parentObj.find('.fieldPreview');

	if(bindObj == "convert" || bindObj == "convertAudio" || bindObj == "convertVideo" || bindObj =="videothumbnail" || bindObj == "thumbnail" || bindObj == "watermark" || bindObj == "border"){
		fileInterface(bindObj, value);
	}

    if(bindObj == 'name'){
    	value = $.trim(value);
    	evaluateSpace(value, bindToInput);
    }

    if(bindObj == 'idnoFormat'){
    	idnoValues.idnoFormat = value;
    }

    if(bindObj == 'startIncrement'){
    	idnoValues.startIncrement = value;
    }

    // Object Specific Value Change
	if( bindObj == 'help'){
		var helpType = value.split("|")[0];
		var help     = value.split("|")[1];
		var value    = (help === undefined ? "" : help);
		$(this).val(helpType + "|" + help);

		$("#fieldSettings_help_textarea").val(value.unEscapeHtml()).hide();
		$("#fieldSettings_help_url").hide().val(value);
		$('#fieldSettings_help_type').val(helpType).change();
	}

    // Modifications for inputs and selects need to be done here same with checks
   if(bindToInput.is("input[type=checkbox]")) {
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
    else if(bindToInput.is("input[type=number]")){
    	bindToInput.val(parseInt(value));
    }
    else{
    	bindToInput.val(value);
    }

    // choices
    if(bindToInput.is($("#fieldSettings_choices_type"))){
		if(value == 'manual'){
			$('#fieldSettings_container_choices').find('.manual_choices').show();
			$('#fieldSettings_container_choices').find('.form_choices').hide();
		} else {
			$('#fieldSettings_container_choices').find('.manual_choices').hide();
			$('#fieldSettings_container_choices').find('.form_choices').show();
		}
	}

	// system
	if(bindObj == 'managedBy'){
		if(value == 'system'){
			$("#fieldSettings_idno_managedBy").next().show();
			$("#fieldSettings_options_readonly").prop({checked:true, disabled:true}).change();
		} else {
			$("#fieldSettings_idno_managedBy").next().hide();
			$("#fieldSettings_options_readonly").prop({checked:false, disabled:true}).change();
		}
	}

	if(bindObj == 'hidden'){
		if(value == true){
			formPreview.css('opacity', '.5');
		}
		else{
			formPreview.css('opacity', '1');
		}
	}

	if(bindObj == 'validation'){
		if(value == 'regexp'){
			$('#fieldSettings_validationRegex').show();
		} else {
			$('#fieldSettings_validationRegex').hide();
		}
	}

	if(bindObj == 'choicesForm'){
		if(value == "null" || value == undefined){
			// remove options add errors
			$('select[data-bindmodel="choicesField"]').addClass('has-error').html('');
			$('#fieldSettings_choices_formSelect').addClass('has-error');
		} else{
			$('select[data-bindmodel="choicesField"]').removeClass('has-error');
			$('#fieldSettings_choices_formSelect').removeClass('has-error');
		}
	}

	if(bindObj == 'choicesDefault'){
		$('input[value="'+value+'"]').prev().addClass('active');
	}

	if(bindObj == 'choicesField'){
		$('#fieldSettings_choices_formSelect').change();
		$('#fieldSettings_choices_fieldSelect').val(value).change();
	}

}

function fileInterface(dataObj, value){
	var element  = $("."+dataObj);
	if(value === "true"){
		element.show();
	} else {
		element.hide();
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
		var formPreview = parentObj.find('.fieldPreview');
		hiddenForm.find("[data-bind='"+ inputObj +"']").val(value);

		if(inputObj == 'name'){
			evaluateSpace(value, $(this));
			titleField();
		}

		if(inputObj == 'hidden'){
			if(value == true){
				formPreview.css('opacity', '.5');
			}
			else{
				formPreview.css('opacity', '1');
			}
		}

		if(inputObj == 'choicesType'){
			if(value == 'manual'){
				$('#fieldSettings_container_choices').find('.manual_choices').show();
				$('#fieldSettings_container_choices').find('.form_choices').hide();
				$('#choicesForm_'+id).val('');
			} else {
				$('#fieldSettings_container_choices').find('.manual_choices').hide();
				$('#fieldSettings_container_choices').find('.form_choices').show();

				var  type = hiddenForm.find($('input[name^="type"]')).val();

				if(type == 'multiselect'){
					formPreview.find('.controls').find($('select')).html();

				}
				else if(type == 'checkbox' || type == 'radio') {
					formPreview.find('.controls').find($('label')).remove();
				}
				else if(type == 'select'){
					formPreview.find($('.controls')).find($('select')).html(output);
				}
			}
		}

		if(inputObj == 'managedBy'){
			if(value == 'system'){
				$("#fieldSettings_idno_managedBy").next().show();
				$("#fieldSettings_options_readonly").prop({checked:true, disabled:true});
			} else {
				$("#fieldSettings_idno_managedBy").next().hide();
				$("#fieldSettings_options_readonly").prop({checked:false, disabled:true});
			}
		}

		if(inputObj == 'idnoFormat' || inputObj == 'startIncrement' || inputObj == 'idnoConfirm'){
			$('#fieldSettings_container_idno_confirm').removeClass('hidden').show();

			var idnoConfirm = $('#fieldSettings_idno_confirm').is(':checked');
			if( idnoConfirm === false && idnoValues.idnoFormat){
				// $('#fieldSettings_idno_format').val(idnoValues.idnoFormat);
				// $('#fieldSettings_idno_startIncrement').val(idnoValues.startIncrement);
				$('#submitForm input[type="submit"]').prop('disabled', true).addClass('disabled');
			} else {
				$('#submitForm input[type="submit"]').prop('disabled', false).removeClass('disabled');
			}
		}

		if(inputObj == 'validation'){
			var validationValue = $('#fieldSettings_validation').val();
			if(validationValue === 'regexp'){
				$('#fieldSettings_validationRegex').show();
			} else {
				$('#fieldSettings_validationRegex').hide().val('');
			}
		}

		if(inputObj == 'helpText' || inputObj == 'helpURL'){
			$('#fieldSettings_help_type').change(); // pass the buck to help type
		}

		if(inputObj == 'helpType'){
			switch($(this).val()){
				case 'text':
				case 'html':
					$("#fieldSettings_help_textarea").show().removeClass('hidden');
					$("#fieldSettings_help_url").hide().removeClass('hidden');
					break;
				case 'web':
					$("#fieldSettings_help_url").show().removeClass('hidden');
					$("#fieldSettings_help_textarea").hide().removeClass('hidden');
					break;
				case 'none':
				case 'default':
					$("#fieldSettings_help_textarea").hide().removeClass('hidden');
					$("#fieldSettings_help_url").hide().removeClass('hidden');
					break;
			}
			formatHelpForHiddenField(hiddenForm);
		}

		applyFormPreview();
	}
}

function formatHelpForHiddenField(hiddenForm){
	var value;
	var type = $('#fieldSettings_help_type').val();
	var id = globalFieldID;

	if(type == 'text' || type == 'html'){
		value = $('#fieldSettings_help_textarea').val().sanitizeInput().unEscapeHtml();
	}
	else {
		value = $('#fieldSettings_help_url').val();
		if(validateURL(value)){
			$('#fieldSettings_help_url').removeClass('has-error');
		} else {
			$('#fieldSettings_help_url').addClass('has-error');
		}
	}

	var newValues = type + "|" + value.sanitizeInput().escapeHtml().removeQuotes();

	$('#fieldSettings_help_text').val(newValues);
	hiddenForm.find("[data-bind='help']").val(newValues);

	$('.helpPreview').popover('destroy');
 	$('.helpPreview').hide();

	if(type === 'html' || type === 'text'){
		$('#formPreview_'+id).find('.helpPreview').show();
		$('.helpPreview').popover({
			'title'   : 'Help',
			'content' : '<div>' + value.unEscapeHtml().removeQuotes() + '</div>',
			'trigger' : 'click',
			'html' : true
		});
	} else if(type === 'web'){
		$('#formPreview_'+id).find('.helpPreview').show();
		$('.helpPreview').popover({
			'title'   : 'Help Url',
			'content' : '<div><a href="'+value+'">' + value + '</a></div>',
			'trigger' : 'click',
			'html' : true
		});
	} else {
		//$('.helpPreview').hide();
	}

}

function evaluateSpace(value, input){
	if(checkForSpaces(value)){
		input.addClass('has-error testy');
		$('.noSpacesAlert').show();
		$('input[type=submit]').addClass('disabled').attr('disabled', true);
	}
	else {
		input.removeClass('has-error');
		$('input[type=submit]').removeClass('disabled').removeAttr('disabled');
		$('.noSpacesAlert').hide();
	}
}

function getFormFields(){
	var options = '';
	$.ajax({
		url: "../includes/getFormFields.php",
		async: false
	}).always(function(data) {
		var obj = JSON.parse(data);
		$.each(obj, function(I, field) {
			$.each(field, function(i, f) {
				options += '<option value="'+f.name+'">'+f.label+'</option>';
			});
		});
	});
	return options;
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

		if($(this).val().length !== 0){
			$('#submitForm input[type="submit"]').prop('disabled', false).removeClass('disabled');
		} else {
			$('#submitForm input[type="submit"]').prop('disabled', true).addClass('disabled');
		}
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

		if ($(this).is(":checked")){
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
			var fieldAdd                         = $('#fieldAdd');
			var formPreviewWell                  = $("#formPreview .well");
			var fieldSettings_label              = $("#fieldSettings_label");
			var fieldSettings_options_sortable   = $("#fieldSettings_options_sortable");
			var fieldSettings_options_searchable = $("#fieldSettings_options_searchable");

			// Add a title Field
			fieldAdd.find("li:contains('Single Line Text')").click();
			$("#fieldSettings_name").val('title').keyup();
			fieldSettings_label.val('Title').keyup();

			// Select Metadata form
			$('.nav-tabs li:nth-child(3) a').click();
			$("#formSettings_formMetadata").prop("checked", true).change();
			$("#formTypeSelector").modal("hide").hide().removeClass('in');

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
			$("#fieldTab li:last a").click();
			$('#formPreview li').removeClass('activeField');

			// Deselect object form
			$("#formSettings_formMetadata").removeAttr("checked").change();

			// Hide modal
			$("#formTypeSelector").modal("hide").hide().removeClass('in');

			// trigger keyup for title change
			$("#formSettings_formTitle");
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

var numIDNOs = 0; // global only associated with this function keeps track of total
function newFieldPreview(id,type,vals) {
	var output = "";
	// sets default values for new fields if they are currently undefined
	if (vals === undefined) {
		vals = {};
		vals.validation    = determineValidation(type);
		vals.name          = 'Untitled'+'_'+id;
		vals.label         = 'Untitiled';
		vals.help          = 'none|';
		vals.choicesType   = 'manual';
		vals.choicesForm   = 'null';
		vals.choicesOptions = 'First Choice%,%Second Choice';
		vals.publicRelease = true;
		vals.managedBy     = 'system';
		vals.idnoFormat    = 'st_###';
	}

	if((type == 'idno' || type == 'ID Number') && numIDNOs === 0){

	} else {
		output += '<i class="icon-remove"></i>';
	}

	output += '<span class="fa fa-question-circle helpPreview"></span>';

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
				numIDNOs++;
				break;

			case 'Paragraph Text':
			case 'textarea':
				output += '<textarea></textarea>';
				break;

			case 'Radio':
			case 'radio':
				if(vals.choicesOptions){
					var choices = vals.choicesOptions;
					var defaultChoice = vals.choicesDefault;
					choices = choices.split('%,%');

					$.each(choices, function( index, value ) {
						if(value == defaultChoice){
							output += '<label> <input type="radio" checked> '+value+'</label>';
						} else {
							output += '<label> <input type="radio"> '+value+'</label>';
						}
					});

				} else{
				output += '<label class="radio"><input type="radio">First Choice</label><label class="radio"><input type="radio">Second Choice</label>';
				}
				break;

			case 'Checkboxes':
			case 'checkbox':
				if(vals.choicesOptions){
					var choices = vals.choicesOptions;
					var defaultChoice = vals.choicesDefault;
					choices = choices.split('%,%');

					$.each(choices, function( index, value ) {
						if(value == defaultChoice){
							output += '<label> <input type="checkbox" checked> '+value+'</label>';
						} else {
							output += '<label> <input type="checkbox"> '+value+'</label>';
						}
					});

				} else{
				output += '<label class="checkbox"><input type="checkbox">First Choice</label><label class="checkbox"><input type="checkbox">Second Choice</label>';
				}
				break;

			case 'Dropdown':
			case 'select':
				if(vals.choicesType == 'manual'){
					var choices = vals.choicesOptions;
					var defaultChoice = vals.choicesDefault;
					choices = choices.split('%,%');

					output += '<select>';
						$.each(choices, function( index, value ) {
							if(value == defaultChoice){
								output += '<option value="'+value+'" selected>'+value+'</option>';
							} else {
								output += '<option value="'+value+'">'+value+'</option>';
							}
						});
					output += '</select>';
				} else {
					output += '<select></select>';
				}
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
				if(vals.choicesType == 'manual'){
					var choices = vals.choicesOptions;
					var defaultChoice = vals.choicesDefault;
					choices = choices.split('%,%');

					output += '<select multiple></select><br><select class="selectPreview">';
						$.each(choices, function( index, value ) {
							if(value == defaultChoice){
								output += '<option value="'+value+'" selected>'+value+'</option>';
							} else {
								output += '<option value="'+value+'">'+value+'</option>';
							}
						});
					output += '</select>';
				} else {
					output += '<select multiple></select><br><select class="selectPreview"></select>';
				}
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
		output += '</div></div>';
	}
	return output;
}

function newFieldValues(id,type,vals) {
	var output = "";

	// sets default values for new fields if they are currently undefined
	if (vals === undefined) {
		vals = {};
		vals.validation    = determineValidation(type);
		vals.name          = 'Untitled'+'_'+id;
		vals.label         = 'Untitiled';
		vals.help          = 'none|';
		vals.choicesType   = 'manual';
		vals.choicesForm   = 'null';
		vals.publicRelease = true;
		vals.managedBy     = 'system';
		vals.idnoFormat    = 'st_###';
	}

    vals.type = determineType(type);
    type = vals.type;

    var defaultHiddenFormFields = ['name','position', 'type', 'label', 'value', 'placeholder', 'id', 'class', 'style', 'helpType', 'required', 'duplicates', 'readonly', 'disabled', 'disabledInsert', 'disabledUpdate', 'publicRelease', 'sortable', 'searchable', 'displayTable', 'hidden', 'validation', 'validationRegex', 'access', 'fieldset', 'metadataStandard' ];

    output += createHiddenFields(defaultHiddenFormFields, id, vals);

    output += '<input type="hidden" id="help_'+id+'"   name="help_'+id+'"       data-bind="help"      value="'+((vals.help !== undefined)?vals.help.removeQuotes().escapeHtml():'none|')+'">';

    // handle additional form information based on field added
	switch(type) {
		case 'idno':
            var idnoHiddenFields = ['managedBy', 'idnoFormat'];
            output += createHiddenFields(idnoHiddenFields, id, vals);
			output += '<input type="hidden" id="startIncrement_'+id+'" name="startIncrement_'+id+'"   data-bind="startIncrement"    value="'+((vals.startIncrement !== undefined)?vals.startIncrement:'1')+'">';
			output += '<input type="hidden" id="idnoConfirm_'+id+'"    name="idnoConfirm_'+id+'"      data-bind"idnoConfirm"        value="false">';  // why is this hard coded
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
                'multipleFiles', 'combine', 'ocr', 'convert', 'convertHeight', 'convertWidth', 'watermark', 'watermarkImage',
                'watermarkLocation', 'border', 'borderHeight', 'borderWidth', 'borderColor', 'thumbnail', 'convertAudio', 'bitRate', 'audioFormat', 'convertVideo',
                'videoHeight', 'videoWidth', 'videobitRate', 'aspectRatio', 'videoFormat', 'videothumbnail', 'videoThumbFrames', 'videoThumbHeight',
                'videoThumbWidth', 'videoFormatThumb'];

     		output += createHiddenFields(fileHiddenFields, id, vals);

             // default values
            output += '<input type="hidden" id="bgProcessing_'+id+'" name="bgProcessing_'+id+'"     data-bind="bgProcessing"    value="true">';

            output += '<input type="hidden" id="allowedExtensions_'+id+'" name="allowedExtensions_'+id+'"     data-bind="allowedExtensions"    value="'+((vals.allowedExtensions !== undefined)?vals.allowedExtensions:'tif%,%tiff%,%jpg')+'">';
			output += '<input type="hidden" id="convertResolution_'+id+'" name="convertResolution_'+id+'"     data-bind="convertResolution"    value="'+((vals.convertResolution !== undefined)?vals.convertResolution:'192')+'">';
			output += '<input type="hidden" id="convertFormat_'+id+'"     name="convertFormat_'+id+'"         data-bind="convertFormat"        value="'+((vals.convertFormat !== undefined)?vals.convertFormat:'JPG')+'">';
			output += '<input type="hidden" id="thumbnailHeight_'+id+'"   name="thumbnailHeight_'+id+'"       data-bind="thumbnailHeight"      value="'+((vals.thumbnailHeight !== undefined)?vals.thumbnailHeight:'150')+'">';
			output += '<input type="hidden" id="thumbnailWidth_'+id+'"    name="thumbnailWidth_'+id+'"        data-bind="thumbnailWidth"       value="'+((vals.thumbnailWidth !== undefined)?vals.thumbnailWidth:'150')+'">';
			output += '<input type="hidden" id="thumbnailFormat_'+id+'"   name="thumbnailFormat_'+id+'"       data-bind="thumbnailFormat"      value="'+((vals.thumbnailFormat !== undefined)?vals.thumbnailFormat:'JPG')+'">';
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
	if (typeof val === 'undefined') {
		return '<div class="input-prepend input-append choicesItem" data-itemtype="choice">'+
					'<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
					'<input name="fieldSettings_choices_text" type="text">'+
					'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
					'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
				'</div>';
	}
	else if (typeof def === 'undefined') {
		return '<div class="input-prepend input-append choicesItem" data-itemtype="choice">'+
					'<button name="default" class="btn" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
					'<input name="fieldSettings_choices_text" type="text" value="'+val+'">'+
					'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
					'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
				'</div>';
	}
	return '<div class="input-prepend input-append choicesItem" data-itemtype="choice">'+
				'<button name="default" class="btn'+(val==def?" active":"")+'" type="button" data-toggle="buttons-radio" title="Set this choice as the default."><i class="icon-ok"></i></button>'+
				'<input name="fieldSettings_choices_text" type="text" value="'+val+'">'+
				'<button name="add" class="btn" type="button" title="Add a choice."><i class="icon-plus"></i></button>'+
				'<button name="remove" class="btn" type="button" title="Remove this choice."><i class="icon-remove"></i></button>'+
			'</div>';
}

function addAllowedExtension(val) {
	if (typeof val === 'undefined') {
		val = '';
	}
	return '<div class="row-fluid input-append extensionItem" data-itemtype="extension">'+
				'<input name="fieldSettings_allowedExtension_text" type="text" value="'+val+'">'+
				'<button name="add" class="btn" type="button" title="Add an extension."><i class="icon-plus"></i></button>'+
				'<button name="remove" class="btn" type="button" title="Remove this extension."><i class="icon-remove"></i></button>'+
			'</div>';
}

function addMetadataStandard(val){
	if (typeof val === 'undefined') {
		val = '';
	}

	var options    = metadataSchema;
	var identifier = val.split(' : ')[1];
	var standard   = val.split(' : ')[0];

	if(typeof identifier === 'undefined'){
		identifier = '';
	}

	if(typeof standard === 'undefined'){
		 standard = '';
	}

	var metaDataOptions = '<select class="input-block-level form-control mdStandardSelect" id="fieldSettings_standardType" name="fieldSettings_standardType" data-selectvalue="' + standard +'"><option value=""> None </option>' + options + '</select>';


	return '<div class="row-fluid input-append metadata-item" data-itemtype="metadataStandard">' + metaDataOptions +
				'<input name="fieldSettings_metadataIdentifer" type="text" value="'+identifier+'">'+
				'<button name="add" class="btn" type="button" title="Add Metadata Standard"><i class="icon-plus"></i></button>'+
				'<button name="remove" class="btn" type="button" title="Remove Metadata Standard"><i class="icon-remove"></i></button>'+
			'</div>';
}

function enableChoiceFunctionality(){
	//bind choices
	$('.choicesItem').find($('input[type=text], select')).bind('change keyup', modifyChoiceBinding).change();
	$('.extensionItem').find($('input[type=text], select')).bind('change keyup', extensionBinding).change();
	$('.metadata-item').find($('input[type=text], select')).bind('change keyup', metadataBinding).change();

	$('#fieldSettings_choices_null').change(function(){
		$('.input-append').find('input[type=text]').change();
	});

	$('.input-append').find('button').click(function(){
		var state = $(this).attr('name');
		var type  = $(this).parent().data('itemtype');

		if(state == "add"){
			if(type == "choice"){
				$(this).parent().after(addChoice());
			}
			else if(type == "extension") {
				$(this).parent().after(addAllowedExtension());
			}
			else {
				$(this).parent().after(addMetadataStandard());
			}

			// EVENT LISTENER Recouple
			$('.input-append').find('button').unbind('click');
			enableChoiceFunctionality();
		}
		else if(state == "remove"){
			$(this).parent().remove();
			$('.input-append').find($('input[type=text]')).change();
		}
		else if(state == "default"){
			// get value
			var value = $(this).next().val();
			var id = globalFieldID;
			// change hidden form
			$('#choicesDefault_' + id).val(value);
			// remove active classes and use this active class
			$('button[name="default"]').removeClass('active');

			if($(this).hasClass('focus')){
				$(this).addClass('selected');
			}
		}
	});

	$("#fieldSettings_choices_formSelect").change(function(){
		var val             = $(this).val();

		if(val == "null"){
			// remove options add errors
			$('select[data-bindmodel="choicesField"]').addClass('has-error').html('');
			$('#fieldSettings_choices_formSelect').addClass('has-error');
		} else{
			$('select[data-bindmodel="choicesField"]').removeClass('has-error');
			$('#fieldSettings_choices_formSelect').removeClass('has-error');
		}

		if (choicesFields[val] === undefined) {
			var options;
			choicesFields[null] = options;

			$.ajax({
				url: "../includes/getFormFields.php",
				async: false
			}).always(function(data) {
				var obj = JSON.parse(data);

				$.each(obj, function(I, field) {
					options = "<option value> Select A Field</option>";
					$.each(field, function(i, f) {
						options += '<option value="'+f.name+'">'+f.label+'</option>';
					});
					choicesFields[I] = options;
				});
			});
		}

		$("#fieldSettings_choices_fieldSelect").html(choicesFields[val]);
	});
}

function modifyChoiceBinding(){
	var valueObject = [];
	var dataType = $(this).parent().data('itemtype');

	$(this).parent().parent().find($('input[type=text]')).each(function(index){
		value = $(this).val();
		valueObject[index] = value;
	});

	// hidden form binding
	var choices = valueObject.join('%,%');
	$('.choicesOptions').val(choices).change();

	// preivew binding
	if($('#fieldSettings_choices_null').is(':checked')){
		valueObject.unshift('Make A Selection');
	}

	// use global id  to make form changes
	var targetFormPreview = $('#formPreview_'+globalFieldID);
	var targetType        = targetFormPreview.find($('#type_'+globalFieldID)).val();
	var output            = "";

	for(var iterateChoice = 0; iterateChoice < valueObject.length; iterateChoice++){
		if(targetType == 'multiselect' || targetType == 'select'){
			output += "<option value='"+valueObject[iterateChoice]+"'>"+valueObject[iterateChoice]+"</option>";
		}
		else if(targetType == 'checkbox') {
			output += "<label for='checkbox'><input type='checkbox'/>"+valueObject[iterateChoice]+"</label>";
		}
		else if(targetType == 'radio') {
			output += "<label for='checkbox'><input type='radio'/>"+valueObject[iterateChoice]+"</label>";
		} else {
			// do nothing
		}
	}

	if(targetType == 'multiselect'){
		var target = targetFormPreview.find($('.controls')).find($('select'));
		target.html(output);
	}
	else if(targetType == 'checkbox' || targetType == 'radio') {
		var target = targetFormPreview.find($('.controls'));
		target.find($('label')).remove();
		target.append(output);
	}
	else if(targetType == 'select'){
		var target = targetFormPreview.find($('.controls')).find($('select'));
		target.html(output);
	}

	if($(this).prev().hasClass('btn active')){
		$('#choicesDefault_'+globalFieldID).val($(this).val());
	}
}

function extensionBinding(){
	var valueObject = [];

	$(this).parent().parent().find($('input[type=text]')).each(function(index){
		value = $(this).val();
		valueObject[index] = value;
	});

	// hidden form binding
	var choices = valueObject.join('%,%');
	$('.allowedExtensions').val(choices).change();

	var numOfExtensions = $('#fieldSettings_file_allowedExtensions').children().length;

	if(numOfExtensions <= 1){
		$('#allowedExtensionsAlert').show();
	} else {
		$('#allowedExtensionsAlert').hide();
	}

	if(numOfExtensions < 1){
		$(this).parent().after(addAllowedExtension());
	}
}

function metadataBinding(){
	var valueObject = [];
	var dataType = $(this).parent().data('itemtype');

	$(this).parent().parent().find($('input[type=text]')).each(function(index){
		var select = $(this).prev('select').val();
		value = select + " : " + $(this).val();
		valueObject[index] = value;
	});

	var choices = valueObject.join('%,%');
	$('.metadataStandard').val(choices).change();
}

// Title Field Form Settings
function titleField(){
	var titleField   = $('#formSettings_objectTitleField');
	var titleOptions = "";

	$('#formPreview').find($(':input[type=text]')).each(function(){
		var name = $(this).parent().parent().parent().next('div').find($("input[name^='label']" )).val();
		var optionValue = $(this).parent().parent().parent().next('div').find($("input[name^='name']" )).val();
		if(optionValue === "idno"){
			//do nothing
		} else {
			titleOptions += "<option value='"+optionValue+"'>"+optionValue+"</option>";
		}
	});

	titleField.html(titleOptions);
}

// REGEX VALIDATION FUNCTIONS
// ====================================================================
function validateURL(value) {
    var urlregex = /^((https?|ftp):\/\/)?([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/;
    return urlregex.test(value);
}

// Prototypical Helper Functions
// ====================================================================
String.prototype.sanitizeInput = function(){
    var regex = /<\/?(script|embed|object|frameset|frame|iframe|meta|link|style).*?>/gmi;
  return this.replace(regex, "");
};

String.prototype.escapeHtml = function(){
    var string = this.replace(/&/g, '&amp;')
    .replace(/>/g, '&gt;')
    .replace(/</g, '&lt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;')
    .replace(/\//g, '&#x2F;');

   return string;
};

String.prototype.unEscapeHtml = function(){
   var string  = this.replace(/&amp;/g, '&')
                 .replace(/&gt;/g, '>')
                 .replace(/&lt;/g, '<')
                 .replace(/&quot;/g, '"')
                 .replace(/&#39;/g, "'")
                 .replace(/&#x2F;/g, '/');
    return string;
};

String.prototype.removeQuotes = function(){
	var string = this.replace(/"/g, '&quot;').replace(/'/g, '&apos;');
	return string;
};

String.prototype.removeHTML = function(){
	var string = this.replace(/&/g, '&amp;')
                 .replace(/>/g, '')
                 .replace(/</g, '')
                 .replace(/"/g, '&quot;')
                 .replace(/'/g, '&apos;')
                 .replace(/\//g, '');
    return string;
}

// Test Functions
// ====================================================================
function checkForSpaces(string) {
  return /\s/g.test(string);
}

function isEmpty(str) {
    return (!str || 0 === str.length);
}

// Document Ready
// =================================================================
//
$(function() {
	$("form[name=submitPermissions]").submit(function() {
		entrySubmit();
	});
});

// Helper Functions
// =================================================================

function addItemToID(id, item) {
    var theSelect = document.getElementById(id);

    if (item.value == "null") {
        return;
    }

    for (i = theSelect.length - 1; i >= 0; i--) {
        if (theSelect.options[i].value == item.value) {
            return;
        }
    }

    theSelect.options[theSelect.length] = new Option(item.text, item.value);
}

function removeItemFromID(id, item) {
	var selIndex = item.selectedIndex;
	if (selIndex != -1) {
		for (i = item.length - 1; i >= 0; i--) {
			if (item.options[i].selected) {
				item.options[i] = null;
			}
		}
		if (item.length > 0) {
			item.selectedIndex = selIndex == 0 ? 0 : selIndex - 1;
		}
	}
}

function selectAllOnSubmit(id) {
	var item = document.getElementById(id);
    if(item != null){
        for (i = item.length - 1; i >= 0; i--) {
            item.options[i].selected = true;
        }
    }
}

function entrySubmit() {
	selectAllOnSubmit("selectedEntryUsers");
	selectAllOnSubmit("selectedViewUsers");
	selectAllOnSubmit("selectedUsersAdmins");
	selectAllOnSubmit("selectedContactUsers");

	return true;
}

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
    });

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

    $("form[name=submitNavigation]").submit(function(event) {
        //event.preventDefault();

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

// Create For Nav Sumbissions
// ===================================================================
function settingsBindings() {
    var groupingsPreview = $("#GroupingsPreview");

    // Select an option to change settings
    $("#GroupingsPreview").on("click", "li", function(event) {
        event.stopPropagation();
        if (!$(this).hasClass("activeField well")) {
            groupingsPreview.find(".activeField").removeClass("activeField well");
            $(this).addClass("activeField well");
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
        .attr({"id":"GroupingsPreview_"+newID})
        .html('<div class="groupingPreview">'+newGroupingPreview(type, vals)+'</div><div class="groupingValues">'+newGroupingValues(newID,type,vals)+'</div>');

    // Display settings for new field
    $("#GroupingsPreview_"+newID).click();
}

function newGroupingPreview(type, vals) {
    var output;

    output = '<i class="icon-remove"></i>';

    if (type == 'New Grouping' || type == 'grouping') {
        output += '<ul class="unstyled sortable"></ul>';
    }
    else {
        if(vals === undefined || vals === null){
            output += '<a href="#">[Link]</a>';
        } else {
            output += '<a href="'+((vals.url !== undefined)?vals.url:'')+'">'+((vals.label !== undefined)?vals.label:'Untitled')+'</a>';
        }
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

    output  = '<input type="hidden" id="nav_position_'+id+'" name="nav_position_'+id+'" value="'+((vals.position !== undefined)?vals.position:'')+'">';
    output += '<input type="hidden" id="nav_type_'+id+'" name="nav_type_'+id+'" value="'+((vals.type !== undefined)?vals.type:type)+'">';
    output += '<input type="hidden" id="nav_label_'+id+'" name="nav_label_'+id+'" value="'+((vals.label !== undefined)?vals.label:'Untitled')+'">';
    output += '<input type="hidden" id="nav_url_'+id+'" name="nav_url_'+id+'" value="'+((vals.url !== undefined)?vals.url:'')+'">';
    output += '<input type="hidden" id="nav_grouping_'+id+'" name="nav_grouping_'+id+'" value="'+((vals.grouping !== undefined)?vals.grouping:'')+'">';

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