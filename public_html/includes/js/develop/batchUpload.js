// Document Ready
// ===================================================================
$(function(){

    // Form Preview JS
    // ================================================================================

    var dropList =  "<datalist id='formDropList'>" +
    					"<option value='%%fileName%%'> File Name </option>" +
    					"<option value='%%mimeType%%'> Files MimeType (tiff, jpg ... etc) </option>" +
    					"<option value='%%filesize%%'> Filesize </option>" +
    				"</datalist>";

    $('#selectFormID select').change(function(){
    	var ID = $(this).val();

    	if(ID.length == 0){
    		$('#batchFormPreview').html('');
    	} else {
    		$.ajax({
    			dataType:'json',
    			url:'/api/1.0/get/form/spec/',
    			data:{id: ID},

    			success:function(){
    				console.log('success');
    			},

    			error: function (jqXHR, textStatus, errorThrown) {
    				console.log(textStatus + ': ' + errorThrown);
    			},

    			complete:function(data){
    				var jsonData = data.responseJSON;

    				// form specific
    				var formTitle       = jsonData.title;
    				var formDescription = jsonData.description;
    				var formFields      = jsonData.fields;
    				var uploadField;

    				// setup HTML
    				var html = "<div class='interactiveFormPreview'> <h3>" + formTitle + "</h3>";

    				if(formDescription != null){
    					html += "<blockquote>"+formDescription+"</blockquote>";
    				}

    				$.each(formFields, function(fieldName, fieldValues){

    					// if the field in the form is a file feild get the data
                        // else create inputs
    					if(fieldValues.type === "file"){
    						uploadField = { fieldName: fieldValues.name, fileTypes: fieldValues.allowedExtensions}
                            fineUploaderBatch(uploadField);
    					}
                        else {
        					html += "<label for='form_"+fieldName+"'>"+fieldValues.label+"</label>";

        					// Build Input Element from Data
        					inputElm = "<input list='formDropList' name='form_"+fieldName+"' id='form_"+fieldName+"'placeholder='"+fieldValues.placeholder+"'";

        					if(fieldValues.readonly == "true" || fieldValues.disabled == "true"){
        						inputElm += 'readonly="readonly" disabled="disabled" class="disabled"';
        					}

        					inputElm += "value='"+fieldValues.value+"'/>";
        					html += inputElm;
                        }
    				});

    				html += dropList;

    				html += "</div>";

    				$('#batchFormPreview').html(html);
    			}
    		});
    	}
    });

    // Regular Expression JS
    // ================================================================================

    $('#regExBool').change(function(){
    	if ($(this).is(':checked')) {
    		$('.toggleRegEx').removeClass('hide');
    	} else {
    		$('.toggleRegEx').addClass('hide');
    	}
    });

    $('.previewRegEx').click(function(){
    	var filename = $('#exampleFileName').val();
    	var regEx    = $('#regEx').val();

    	$('.regExPreview').html('');

    	$.ajax({
    		dataType: 'json',
    		url:'/batchUpload/regEx',
    		data: { filename: filename, regex: regEx },
    		success:function(){
    			console.log('success');
    		},

    		error: function (jqXHR, textStatus, errorThrown) {
    			console.log(textStatus + ': ' + errorThrown);
    		},

    		complete:function(data){
    			var jsonData = data.responseJSON;

    			var html = "<h2> Regular Expression Preview </h2>";

    			html += "<p> The following is a preview of the regular expressions that will be used in parsing your file name.  Use the variables in the form preview to setup the data automatically. </p>";

    			html += "<p><strong> Example Filename : </strong>" + filename + "</p>";
    			html += "<p><strong> RegEx Given : </strong>" + regEx + "</p>";

    			html += "<em><srong> Variable </strong> = Value</em>";

    			options = "";
    			for(var i = 0; i < jsonData.length; i++){
    			   html += "<p>";
    			   html += "<strong>{" + i + "}</strong> = " + jsonData[i].toString();
    			   html += "</p>";

    			   options += "<option value='{"+i+"}'>"+jsonData[i].toString()+"</option>";
    			}

    			if($('#formDropList').length){
    				// default options
    				options +=  "<option value='%%fileName%%'> File Name </option>" +
    							"<option value='%%mimeType%%'> Files MimeType (tiff, jpg ... etc) </option>" +
    							"<option value='%%filesize%%'> Filesize </option>";

    				$('#formDropList').html(options);
    			} else {
    				dropList =  "<datalist id='formDropList'>" +
    					options +
    					"<option value='%%fileName%%'> File Name </option>" +
    					"<option value='%%mimeType%%'> Files MimeType (tiff, jpg ... etc) </option>" +
    					"<option value='%%filesize%%'> Filesize </option>" +
    				"</datalist>";
    			}

    			$('.regExPreview').html(html);
    		}
       });
    });

}); // end doc ready


function fineUploaderBatch(uploadData){
    // Make any file uploader div's live
    $('div.fineUploader').each(function(i,n){
        var $div               = $(n);
        var $form              = $div.closest('form');

        var uploadID           = md5(uploadData.fieldName + mt_rand());
        var allowMultipleFiles = true;
        var allowedExtentions  = uploadData.fileTypes;

        $(this).attr({
            'data-allowed_extensions': allowedExtentions.join(),
            'data-upload_id': uploadID,
        });

        $('#batchUploadFiles').append('<input type="hidden" name="'+uploadData.fieldName+'" value="'+ uploadID+'"/>');

        $div.fineUploader({
            request: {
                endpoint: siteRoot+"includes/uploader.php",
                params: {
                    engineCSRFCheck: csrfToken,
                    uploadID: uploadID,
                    multiple: allowMultipleFiles
                }
            },
            failedUploadTextDisplay: {
            mode: "custom",
                maxChars: 40,
                responseProperty: "error",
                enableTooltip: true
            },
            multiple: allowMultipleFiles,
            validation: {
                allowedExtensions: allowedExtentions,
            },
            text: {
                uploadButton: '<div class="uploadText"> <i class="fa fa-upload fa-4x"></i> <br> Drag or Click Here <br> To Upload Files </div>',
                dropButton: 'HELP'
            },
            showMessage: function(message) {
                $div.find(".qq-upload-list").append('<li class="alert alert-danger">'+message+'</li >');
            },
            classes: {
                success: "alert alert-success",
                fail: "alert alert-danger"
            }
        }).on('submit',function(){
                var uploads_working = $form.data('uploads_working');
                var i = typeof(uploads_working) == 'undefined' ? 0 : parseInt(uploads_working);
                $form
                    .data('uploads_working', ++i)
                    .find(':submit').attr('disabled','disabled');
        }).on('complete cancel',function(){
                var i = parseInt($form.data('uploads_working'));
                i--;
                $form.data('uploads_working', i);
                if(i == 0) $form.find(':submit').removeAttr('disabled','disabled');
        });
    });

    $('.fineUploader').parent().addClass('uploadFiles');
    $('.qq-upload-drop-area').html('<div class="uploadText"> <i class="fa fa-dropbox fa-4x"></i> <br><br> Drop Files Here </div>');
}