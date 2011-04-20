jQuery.validator.addMethod("unique", function(value, element, param) {
	var i = 0;
	$(param).each(function() {
		if (value == $(this).val()) {
			i++;
		}
	})
	return i <= 1;
}, "Duplicate exists.");

function validateRules(item) {

	var numeric              = "^[0-9]+$";
	var alphaNoSpaces        = "^[A-Za-z]+$";
	var alphaNumeric         = "^[A-Za-z0-9\_\ ]+$";
	var alphaNumericNoSpaces = "^[A-Za-z0-9\_]+$";
	var autoIDformat         = "^[^#]*?(#+){1}[^#]*?$";

	if ($(":input[name*='fieldName']", item).length) {
		$(":input[name*='fieldName']", item).rules("add", { required: true, regex: alphaNumericNoSpaces, unique: $(':input[name*="fieldName"]') });
		$(":input[name*='fieldName']", item).closest("tr").find("td:first-child").css('font-weight','bold');
	}
	if ($(":input[name*='fieldLabel']", item).length) {
		$(":input[name*='fieldLabel']", item).rules("add", { required: true });
		$(":input[name*='fieldLabel']", item).closest("tr").find("td:first-child").css('font-weight','bold');
	}

	if ($(":input[name*='size']", item).length) {
		$(":input[name*='size']", item).rules("add", { required: true, regex: numeric });
		$(":input[name*='size']", item).closest("tr").find("td:first-child").css('font-weight','bold');
	}
	if ($(":input[name*='width']", item).length) {
		$(":input[name*='width']", item).rules("add", { required: true, regex: numeric });
		$(":input[name*='width']", item).closest("tr").find("td:first-child").css('font-weight','bold');
	}
	if ($(":input[name*='height']", item).length) {
		$(":input[name*='height']", item).rules("add", { required: true, regex: numeric });
		$(":input[name*='height']", item).closest("tr").find("td:first-child").css('font-weight','bold');
	}

	if ($(":input[name*='format']", item).length) {
		$(":input[name*='format']", item).rules("add", { required: true, regex: autoIDformat });
		$(":input[name*='format']", item).closest("tr").find("td:first-child").css('font-weight','bold');
	}

	if ($(":input[name*='optionValues']", item).length) {
		$(":input[name*='optionValues']", item).rules("add", { required: true });
		$(":input[name*='optionValues']", item).closest("tr").find("td:first-child").css('font-weight','bold');
	}

	if ($(":input[name*='linkURL']", item).length) {
		$(":input[name*='linkURL']", item).rules("add", { required: true });
		$(":input[name*='linkURL']", item).closest("tr").find("td:first-child").css('font-weight','bold');
	}
	if ($(":input[name*='linkLabel']", item).length) {
		$(":input[name*='linkLabel']", item).rules("add", { required: true });
		$(":input[name*='linkLabel']", item).closest("tr").find("td:first-child").css('font-weight','bold');
	}

}
