function dynamicEffects(item) {

	hoverOptions(item);
	
	var inputElm = $('.item :input:not(input[type=hidden])', item);

	// launch date picker
	if (inputElm.hasClass('date_input')) {
		inputElm.date_input()
	}

	// change label text
	var label = $(":input[name*='fieldLabel']", item).val();
	if (label == undefined || label.length == 0) {
		label = "[Label]";
	}
	$('.label', item).text(label).html();
	setEqWidth($('.labelContainer'));
	$(":input[name*='fieldLabel']", item).keyup(
		function() {
			var label = $(this).val();
			if (label.length == 0) {
				label = "[Label]";
			}
			$('.label', item).text(label).html();
			setEqWidth($('.labelContainer')); // dynamically set label width for all elements
		}
	)

	// change link text
	if ($(":input[name*='linkLabel'], :input[name*='linkURL']", item).length > 1) {
		$('.labelContainer', item).html('<a href="'+$(":input[name*='linkURL']", item).val()+'">'+$(":input[name*='linkLabel']", item).val()+'</a>');
	}
	$(":input[name*='linkLabel'], :input[name*='linkURL']", item).keyup(
		function() {
			$('.labelContainer', item).html('<a href="'+$(":input[name*='linkURL']", item).val()+'">'+$(":input[name*='linkLabel']", item).val()+'</a>');
		}
	)
		
	// change placeholder text
	$(":input[name*='placeHolder']", item).keyup(
		function() {
			inputElm.attr('placeholder', $(this).val());
		}
	)
	
	// change size of field
	$(":input[name*='size']:not(input[name*='mssize'])", item).change(
		function() {
			inputElm.attr('size', $(this).val())
		}
	)

	// change size of multiselect field
	$(".item :input[name$='_ms']", item).attr('size', $(":input[name*='mssize']", item).val());
	$(":input[name*='mssize']", item).change(
		function() {
			$(".item :input[name$='_ms']", item).attr('size', $(this).val());
		}
	);

	// change width
	$(":input[name*='width']", item).change(
		function() {
			inputElm.attr('cols', $(this).val());
		}
	);

	// change height
	$(":input[name*='height']", item).change(
		function() {
			inputElm.attr('rows', $(this).val());
		}
	);

	// change required
	setWeight('.label',item);
	setEqWidth($('.labelContainer'));
	$(":input[name*='required']", item).change(
		function() {
			setWeight('.label',item);
			setEqWidth($('.labelContainer'));
		}
	);

	// change readonly
	if ($(":input[name*='readonly']", item).val() == 1) {
		inputElm.attr('readonly', true);
	}
	else {
		inputElm.removeAttr('readonly')
	}
	$(":input[name*='readonly']", item).change(
		function() {
			if ($(this).val() == 1) {
				inputElm.attr('readonly', true);
			}
			else {
				inputElm.removeAttr('readonly');
			}
		}
	)

	// change disabled
	if ($(":input[name*='disable']", item).val() == 1) {
		inputElm.attr('disabled', true);
	}
	else {
		inputElm.removeAttr('disabled');
	}
	$(":input[name*='disable']", item).change(
		function() {
			if ($(this).val() == 1) {
				inputElm.attr('disabled', true);
			}
			else {
				inputElm.removeAttr('disabled');
			}
		}
	);

	// change options in select based on optionValues
	changeSelectOptions(item);
	$(":input[name*='optionValues']", item).change(
		function() {
			changeSelectOptions(item);
		}
	)

	// toggle other field
	$(':input[name$=validation]', item).change(
		function () {
			var i = $(this).attr('name')+'Other';

			if ($(this).val() == 'other') {
				$(this).after('<div id="'+i+'"><input type="text" name="'+i+'" placeholder="PHP regular expression." /></div>');
				$('input[name='+i+']').rules("add", { required: true, regex: "^/(.?)+/$" });
			}
			else if ($('input[name='+i+']').length) {
				$('input[name='+i+']').rules("remove", "required regex");
				$('input[name='+i+']').valid();
				$('#'+i).remove();
			}
		}
	);

	// close on Done button
	$('input[name="closeAddlInfoForm"]', item).unbind('click').click(
		function() {
			item = $(this).closest('li');
			toggleAddlInfoForm(item);
			$.mask.close();
		}
	);

	// close on X icon
	$('.ui-icon-circle-close').unbind('click').click(
		function() {
			item = $(this).closest('li');
			toggleAddlInfoForm(item);
			$.mask.close();
		}
	);

	// close on Esc
	$(document).unbind('keyup').keyup(function(e) { 
		if (e.keyCode == 27) { // esc
			item = $(':focus').closest('.addlInfoForm');

			if (item.length) {
				// hide only when valid
				if ($(':input', item).valid()) {
					item.hide('slow', toggleSubmitButton);
				}
			}
		}
	});

	// set autoinc limits
	$(':input[name*=autoinc]', item).attr("max", maxAutoinc(item));
	$(':input[name*=format]', item).change(
		function() {
			$(':input[name*=autoinc]', item).attr("max", maxAutoinc(item));
		}
	);

	// change options associated with managedBy
	managedByOptions(item,$(":input[name*='managedBy']", item).val());
	$(":input[name*='managedBy']", item).change(
		function() {
			managedByOptions(item,$(this).val());
		}
	);

	// show/hide sortable based on searchable
	toggleSortable(item,$(":input[name*='searchable']", item).val());
	$(":input[name*='searchable']", item).change(
		function() {
			toggleSortable(item,$(this).val());
		}
	);
}

function hoverOptions(item) {
	
	item.bind({
		// Show options on hover
		mouseenter:
			function() {
				$('.itemOptions', item).show();
			},

		// Hide options when mouse leaves area
		mouseleave:
			function() {
				$('.itemOptions', item).hide();
			}
	})

	// delete field
	$('.trashIcon', item).unbind('click').click(
		function() {
			if (confirm("You have selected to delete this field. Continue?")) {
				item.remove();
				toggleSubmitButton();
			}
		}
	)

	// Toggle field configuration
	$('.editLink', item).unbind('click').click(
		function() {
			toggleAddlInfoForm(item);
		}
	)

}

function setWeight(selector,item) {
	if ($(":input[name*='required']", item).val() == 0) {
		$(selector, item).css('font-weight', 'normal');
	}
	else {
		$(selector, item).css('font-weight', 'bold');
	}
}
