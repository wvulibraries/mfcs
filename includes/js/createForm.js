$(function() {
	$("[rel='tooltip']").tooltip();

	$("#fieldTab").on("click", "a", function() {
		$("#formPreview li").removeClass("well");
		showFieldSettings(); // blank the Field Settings pane
	});

	$("#formPreview").on("click", "li", function() {
		$(this).addClass("well").addClass("well-small").siblings().removeClass("well");
		$("#fieldTab a[href='#fieldSettings']").tab("show");
		showFieldSettings($(this).attr("id"));
	});

	$("#formPreview").sortable({
		revert: true,
		placeholder: "highlight",
		update: function(event, ui) {
			// Only perform this if it's a brand new field
			if ($(ui.item).hasClass("ui-draggable")) {
				addNewField(ui.item);
			}
		}
	});
	$("#fieldAdd li").draggable({
		connectToSortable: "#formPreview",
		helper: "clone",
		revert: "invalid",
	});

	fieldSettingsBindings();

});

function showFieldSettings(fullID) {
	if (fullID === undefined) {
		// Hide the form and show a warning about having nothing selected
		$("#noFieldSelected").show();
		$("#fieldSettings form").hide();
	}
	else {
		id       = fullID.split("_")[1];
		var type = $("#type_"+id).val();

		// Select the Field Settings tab
		$("#fieldTab a[href='#fieldSettings']").tab("show");

		// Hide the nothing selected error and show the form
		$("#noFieldSelected").hide();
		$("#fieldSettings form").show();

		// Hide all but the common fields
		$("#fieldSettings form").children().not(".noHide").hide();

		// Show optional fields
		switch(type) {
			case 'Single Line Text':
			case 'Paragraph Text':
				$("#fieldSettings_container_range").show();

				$("#fieldSettings_format option").hide();
				$("#fieldSettings_format option[value='characters']").show();
				$("#fieldSettings_format option[value='words']").show();
				break;

			case 'Multiple Choice':
			case 'Checkboxes':
			case 'Dropdown':
				break;

			case 'Number':
				$("#fieldSettings_container_range").show();

				$("#fieldSettings_format option").hide();
				$("#fieldSettings_format option[value='values']").show();
				$("#fieldSettings_format option[value='digits']").show();
				break;

			case 'Email':
			case 'Phone':
			case 'Date':
			case 'Time':
			case 'Website':
			default:
				break;
		}

		// Update field settings to use values from form display
		$("#fieldSettings_name").val($(":input", "#"+fullID).attr('name'));
		$("#fieldSettings_label").val($("label", "#"+fullID).text());
	}
}

function fieldSettingsBindings() {
	$("#fieldSettings_name").keyup(function() {
		$("#formPreview .well .controls :input").attr('name',$(this).val());

		// If no id, set id to be the same as name
		if (!$("#fieldSettings_id").val()) {
			$("#formPreview .well .control-group label").attr('for',$(this).val());
			$("#formPreview .well .controls :input").attr('id',$(this).val());
		}
	});

	$("#fieldSettings_label").keyup(function() {
		$("#formPreview .well .control-group label").text($(this).val());
	});

	$("#fieldSettings_id").keyup(function() {
		$("#formPreview .well .control-group label").attr('for',$(this).val());
		$("#formPreview .well .controls :input").attr('id',$(this).val());
	});

	$("#fieldSettings_class").keyup(function() {
		$("#formPreview .well .controls :input").attr('class',$(this).val());
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
	$(item).attr("id","formPreview_"+newID);

	// Add base html
	// $(item).html('<i class="icon-play"></i><div class="fieldPreview"></div>');
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

	output  = '<div class="control-group"><label class="control-label" for="">Untitled</label><div class="controls">';

	switch(type) {
		case 'Single Line Text':
			output += '<input type="text" name="untitled_'+id+'" id="untitled_'+id+'" class="" value="" placeholder="">';
			break;

		case 'Paragraph Text':
			output += '<textarea name="" id="" class=""></textarea>';
			break;

		case 'Multiple Choice':
		case 'Checkboxes':
		case 'Dropdown':
			break;

		case 'Number':
			output += '<input type="number" name="" id="" class="" value="" placeholder="">';
			break;

		case 'Email':
			output += '<input type="email" name="" id="" class="" value="" placeholder="">';
			break;

		case 'Phone':
			break;

		case 'Date':
			output += '<input type="date" name="" id="" class="" value="" placeholder="">';
			break;

		case 'Time':
		case 'Website':
		default:
			break;
	}

	output += '</div></div>';

	return output;
}

function newFieldValues(id,type) {
	var output;

	output  = '<input type="hidden" id="type_'+id+'" name="type_'+id+'" value="'+type+'" />';
	output += '<input type="hidden" id="position_'+id+'" name="position_'+id+'" value="" />';
	output += '<input type="hidden" id="ID_'+id+'" name="ID_'+id+'" value="untitled_'+id+'" />';
	output += '<input type="hidden" id="name_'+id+'" name="name_'+id+'" value="untitled_'+id+'" />';
	output += '<input type="hidden" id="label_'+id+'" name="label_'+id+'" value="Untitled" />';

	switch(type) {
		case 'Single Line Text':
		case 'Paragraph Text':
		case 'Number':
			output += '<input type="hidden" id="min_'+id+'" name="min_'+id+'" value="" />';
			output += '<input type="hidden" id="max_'+id+'" name="max_'+id+'" value="" />';
			output += '<input type="hidden" id="format_'+id+'" name="max_'+id+'" value="" />';
			break;

		case 'Multiple Choice':
		case 'Checkboxes':
		case 'Dropdown':
		case 'Email':
		case 'Phone':
		case 'Date':
		case 'Time':
		case 'Website':
		default:
			break;
	}

	return output;
}
