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
