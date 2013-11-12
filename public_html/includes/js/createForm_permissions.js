$(function() {
	$("form[name=submitPermissions]").submit(function() {
		entrySubmit();
	});
});

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

	return true;
}
