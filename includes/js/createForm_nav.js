$(function() {
	// Instantiate the bootstrap tooltip plugin
	$("[rel='tooltip']").tooltip();

	// Blank all panes when changing tabs
	$("#groupingTab").on("click", "a", function() {
		$("#GroupingsPreview li").removeClass("well");
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
			$(this).clone().appendTo($("#GroupingsPreview"));
			addNew($("#GroupingsPreview li:last"));
			sortableNav();
		}
	});


	// Delete icon binding
	$("#GroupingsPreview").on("click", ".groupingPreview i.icon-remove", function() {
		if (confirm("Are you sure you want to remove this grouping?")) {
			var thisLI = $(this).parent().parent();

			// If I'm a grouping, move any groupings that are within me
			if ($(this).parent().next().children(":input[name^=nav_type_]").val() == 'grouping') {
				thisLI.after($(this).next().find("li"));
			}
			// Delete this li
			thisLI.remove();
		}
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
	$("#GroupingsPreview li").click();
	$("#groupingTab li:first a").click();

	// Disable links in preview
	$("#GroupingsPreview").on("click", "a", function(event) {
		event.preventDefault();
	});

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
		$("#GroupingsPreview :input").prop("disabled", true);
	});
});

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
	// Select an option to change settings
	$("#GroupingsPreview").on("click", "li", function(event) {
		event.stopPropagation();
		if (!$(this).hasClass("well")) {
			$("#GroupingsPreview .well").removeClass("well");
			$(this).addClass("well well-small");
			showSettings($(this).attr("id"));
		}
	});

	$("#groupingsSettings_grouping").keyup(function() {
		var before   = $("#GroupingsPreview .well .groupingPreview i");
		var after    = $("#GroupingsPreview .well .groupingPreview ul");
		var contents = $("#GroupingsPreview .well .groupingPreview").contents();

		// remove old label
		contents.slice(contents.index(before)+1, contents.index(after)).remove();

		// add new label
		after.before($(this).val());

		$("#GroupingsPreview .well :input[name^=nav_grouping_]").val($(this).val());
		$("#GroupingsPreview .well > .groupingValues :input[name^=nav_label_]").val($(this).val());
	});

	$("#groupingsSettings_label").keyup(function() {
		$("#GroupingsPreview .well a").text($(this).val());
		$("#GroupingsPreview .well :input[name^=nav_label_]").val($(this).val());
	});

	$("#groupingsSettings_url").keyup(function() {
		$("#GroupingsPreview .well a").prop("href",$(this).val());
		$("#GroupingsPreview .well :input[name^=nav_url_]").val($(this).val());
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
		id       = fullID.split("_")[1];
		var type = $("#nav_type_"+id).val();

		// Show the form
		if (type == "grouping") {
			$("#groupingsSettings_container_grouping").show();
			$("#groupingsSettings_grouping").val($("#nav_grouping_"+id).val()).keyup();
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

			if ($("#type_"+id).val() != 'grouping') {
				$("#nav_grouping_"+id).val($("#nav_grouping_"+id).parents("li").parents("li").find(":input[name^=nav_grouping_]").val());
			}
			else {
				$("#groupingsSettings_grouping").val($("#nav_grouping_"+id).val());
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
	$(item).attr("id","GroupingsPreview_"+newID);

	// Add base html
	$(item).html('<div class="groupingPreview"></div>');

	// Add field specific html to .fieldPreview
	$(".groupingPreview", item).html(newGroupingPreview(type));

	// Container for hidden fields
	$(item).append('<div class="groupingValues"></div>');
	$(".groupingValues", item).html(newGroupingValues(newID,type,vals));

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
