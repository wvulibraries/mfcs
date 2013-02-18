$(function() {
	// Make field types draggable, linked to preview pane
	$(".draggable li").draggable({
		connectToSortable: "ul.sortable",
		helper: "clone",
		revert: "invalid",
	}).disableSelection();

	// Make groupings draggable
	$("ul.sortable").sortable({
		connectWith: "ul.sortable",
		revert: true,
		placeholder: "highlight",
		update: function(event, ui) {
			// Only perform this if it's a brand new field
			if ($(ui.item).hasClass("ui-draggable")) {
				$(ui.item).removeClass("ui-draggable").append('<ul class="sortable"></ul>');
			}
			$("ul.sortable").sortable({
				connectWith: "ul.sortable",
				revert: true,
				placeholder: "highlight",
				update: function(event, ui) {
					// Only perform this if it's a brand new field
					if ($(ui.item).hasClass("ui-draggable")) {
						$(ui.item).removeClass("ui-draggable").append('<ul class="sortable"></ul>');
					}
				}
			});
		}
	});


})