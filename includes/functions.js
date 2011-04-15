jQuery.extend(jQuery.expr[':'], {
    focus: function(element) { 
        return element == document.activeElement; 
    }
});

var ID = 0;

function init() {
	
	// Get number of elements to find highest ID
	$(":input").each(
		function() {
			var name = $(this).attr('name').split("_");
			var id = name[0];
			if (id.toString().search(/^[0-9]+$/) == 0) {
				if (ID < id) {
					ID = id;
				}
			}
		}
	)

	// Make items sortable
	$('#mainList').sortable({
		handle: '.dragHandle',
		connectWith: '#draggableFormElements',
		helper: 'clone',
		placeholder: 'ui-state-highlight',
		update:
			function(event, ui) {
				// convert "Text" to "[Label]: <input type=..." etc.
				var orig = $(ui.item).html();
				$(ui.item).html(genLabelAndField($(ui.item),orig));
				if (orig == "WYSIWYG") {
					wysiwygInit($(':input:not(input[type=hidden])', ui.item).attr('id'));
				}
			}
	}).enableSelection();

	// Make form elements draggable
	$('#draggableFormElements li').draggable({
		connectToSortable: '#mainList',
		helper: 'clone'
	}).disableSelection();
	
	toggleSubmitButton();

	// Add regex as a validation type
	$.validator.addMethod(
		"regex",
		function(value, element, regexp) {
			var check = false;
			var re = new RegExp(regexp);
			return this.optional(element) || re.test(value);
		},
		" Invalid. "
	);

	// initiate form validation
	$('#createForm').validate({
		// debug: true
	});

	// Add delete, edit, handle to hover action for items that are already in the list at startup
	$('#mainList li').each(
		function() {
			dynamicEffects($(this));
			validateRules($(this));
			setWeight('.label',$(this));
		}
	);

	$('.engineWYSIWYG').each(function() {
		wysiwygInit($(this).attr('id'));
	})

	$('#draggableFormElements li').bind({
		mouseenter:
			function() {
				$(this).css('background-color','yellow').css('border','1px #000 dashed');
			},
		mouseleave:
			function() {
				$(this).css('background-color','#FFF').css('border','1px #FFF dashed');
			}
	})

}


function toggleAddlInfoForm(item) {
	var e = $('.addlInfoForm', item);
	
	if (e.is(":hidden")) {
		e.show(); // input type=number breaks when slow
		$('.addlInfoForm').expose();
		toggleSubmitButton();
	}
	else {
		// hide only when valid
		if ($(':input', e).valid()) {
			e.slideUp('slow', toggleSubmitButton);
		}
	}
}


function toggleSubmitButton() {
	var buttonState = "show";

	// hide when there are no elements
	if ($.trim($("#mainList").text()) == "") {
		buttonState = "hide";
	}

	// hide when an element is being configured has not yet been configured
	$('.addlInfoForm').each(
		function () {
			if ($(this).is(':visible') || $(this).is(':empty')) {
				buttonState = "hide";
			}
		}
	)

	// if identifier is required, validate
	if ($(':input[name=requireIdentifier]').val() == 1) {
		// if no identifier field
		if ($(':input[name*=identifier]').length == 0) {
			buttonState = "hide";

			// create error message if it doesn't exist
			if ($('#identifierError').length == 0) {
				$('<span id="identifierError">Record forms require an Identifier field.</span>').insertAfter(':input[name=createFormSubmit][type=submit]').css('color','#F00');
			}

			$('#identifierError').show();
		}
		else {
			$('#identifierError').hide();
		}
	}

	if (buttonState == "show") {
		$(':input[type=submit]').removeAttr('disabled');
	}
	else {
		$(':input[type=submit]').attr('disabled',true);
	}
}

function genLabelAndField(item,type) {
	if(! $('.item', item).length) {
		var orig = item.html();
		
		$.ajax({
			type: "GET", // cannot insert csrf, must be get
			url: "../includes/createFormContent.php",
			data: {
				type: type,
				form: $(':input[name=form]').val(),
				id: (++ID)
			},
			success: function(result) {
				item.html(result);
				if (orig == "WYSIWYG") {
					wysiwygInit($(':input:not(input[type=hidden])', item).attr('id'));
				}

				dynamicEffects(item);
				validateRules(item);
				toggleSubmitButton();
			}
		})
	}
}

function setEqWidth(elem) {
	widest = 0;
	
	elem.width('');
	
	elem.each(function() {
		thisWidth = $(this).width();
		if (thisWidth > widest) {
			widest = thisWidth;
		}
	});

	elem.width(widest);
}

function maxAutoinc(item) {
	var val = $(':input[name*=format]', item).val();
	if (val == undefined) {
		return;
	}

	var length = val.split(/#/g).length-1;
	var str = '';

	for (var i = 0; i < length; i++) {
		str += '9';
	}

	return str;
}

function managedByOptions(item,value) {
	if (value == 'user') {
		$(':input[name*=validation]', item).removeAttr('disabled').closest('tr').show();
		
		$(':input[name*=reuseids]', item).attr('disabled', true).closest('tr').hide();
		$(':input[name*=format]', item).attr('disabled', true).closest('tr').hide();
		$(':input[name*=autoinc]', item).attr('disabled', true).closest('tr').hide();
		
		$(':input[name*=readonly]', item).val('0');
		$('.item :input:not(input[type=hidden])', item).removeAttr('readonly');
	}
	else if (value == 'system') {
		$(':input[name*=validation]', item).attr('disabled', true).closest('tr').hide();
		
		$(':input[name*=reuseids]', item).removeAttr('disabled').closest('tr').show();
		$(':input[name*=format]', item).removeAttr('disabled').closest('tr').show();
		$(':input[name*=autoinc]', item).removeAttr('disabled').closest('tr').show();
		
		$(':input[name*=readonly]', item).val('1');
		$('.item :input:not(input[type=hidden])', item).attr('readonly', true);
	}
}

function toggleSortable(item,value) {
	if (value == '1') {
		$(':input[name*=sortable]', item).removeAttr('disabled').closest('tr').show();
	}
	else if (value == '0') {
		$(':input[name*=sortable]', item).attr('disabled', true).closest('tr').hide();
	}
}

function changeSelectOptions(item) {
	var inputElm = $('.item :input:not(input[type=hidden])', item);
	var value = $(':input[name*=optionValues]', item).val();
	if (value == undefined) {
		return;
	}

	var vals = value.split("_");

	$.ajax({
		type: "GET", // cannot insert csrf, must be get
		url: "../includes/changeSelectOptions.php",
		data: {
			formName: vals[0],
			value: vals[1]
		},
		success: function(result) {
			// alert(result);
			inputElm.html(result);
			$(":input[name*='multiselect_fieldName']", item).val("ms_"+vals[0]);
		}
	})
}
