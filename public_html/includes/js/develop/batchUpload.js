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
    			url:'http://localhost:8080/api/1.0/get/form/spec/',
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




// ===================================================================
// ===================================================================
// Helper Function to Match PHP Functions used in Upload
// ===================================================================
// ===================================================================

    function mt_rand(min, max) {
        // original by: Onno Marsman
        // improved by: Brett Zamir (http://brett-zamir.me)
        var argc = arguments.length;
        if (argc === 0) {
        min = 0;
        max = 2147483647;
        } else if (argc === 1) {
        throw new Error('Warning: mt_rand() expects exactly 2 parameters, 1 given');
        } else {
        min = parseInt(min, 10);
        max = parseInt(max, 10);
        }
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    function md5(str) {
        // original by: Webtoolkit.info (http://www.webtoolkit.info/)
        // improved by: Michael White (http://getsprink.com)
        // improved by: Jack
        // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        //    input by: Brett Zamir (http://brett-zamir.me)
        // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

        var xl;

        var rotateLeft = function(lValue, iShiftBits) {
            return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
        };

        var addUnsigned = function(lX, lY) {
            var lX4, lY4, lX8, lY8, lResult;
            lX8 = (lX & 0x80000000);
            lY8 = (lY & 0x80000000);
            lX4 = (lX & 0x40000000);
            lY4 = (lY & 0x40000000);
            lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
            if (lX4 & lY4) {
              return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
            }
            if (lX4 | lY4) {
              if (lResult & 0x40000000) {
                return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
              } else {
                return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
              }
            } else {
              return (lResult ^ lX8 ^ lY8);
            }
        };

        var _F = function(x, y, z) {
            return (x & y) | ((~x) & z);
        };
        var _G = function(x, y, z) {
            return (x & z) | (y & (~z));
        };
        var _H = function(x, y, z) {
            return (x ^ y ^ z);
        };
        var _I = function(x, y, z) {
            return (y ^ (x | (~z)));
        };

        var _FF = function(a, b, c, d, x, s, ac) {
            a = addUnsigned(a, addUnsigned(addUnsigned(_F(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };

        var _GG = function(a, b, c, d, x, s, ac) {
            a = addUnsigned(a, addUnsigned(addUnsigned(_G(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };

        var _HH = function(a, b, c, d, x, s, ac) {
            a = addUnsigned(a, addUnsigned(addUnsigned(_H(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };

        var _II = function(a, b, c, d, x, s, ac) {
            a = addUnsigned(a, addUnsigned(addUnsigned(_I(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };

        var convertToWordArray = function(str) {
            var lWordCount;
            var lMessageLength = str.length;
            var lNumberOfWords_temp1 = lMessageLength + 8;
            var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
            var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
            var lWordArray = new Array(lNumberOfWords - 1);
            var lBytePosition = 0;
            var lByteCount = 0;
            while (lByteCount < lMessageLength) {
              lWordCount = (lByteCount - (lByteCount % 4)) / 4;
              lBytePosition = (lByteCount % 4) * 8;
              lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount) << lBytePosition));
              lByteCount++;
            }
            lWordCount = (lByteCount - (lByteCount % 4)) / 4;
            lBytePosition = (lByteCount % 4) * 8;
            lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
            lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
            lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;

            return lWordArray;
        };

        var wordToHex = function(lValue) {
            var wordToHexValue = '',
              wordToHexValue_temp = '',
              lByte, lCount;
            for (lCount = 0; lCount <= 3; lCount++) {
              lByte = (lValue >>> (lCount * 8)) & 255;
              wordToHexValue_temp = '0' + lByte.toString(16);
              wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length - 2, 2);
            }
            return wordToHexValue;
        };

        var x = [],
        k, AA, BB, CC, DD, a, b, c, d, S11 = 7,
        S12 = 12,
        S13 = 17,
        S14 = 22,
        S21 = 5,
        S22 = 9,
        S23 = 14,
        S24 = 20,
        S31 = 4,
        S32 = 11,
        S33 = 16,
        S34 = 23,
        S41 = 6,
        S42 = 10,
        S43 = 15,
        S44 = 21;

        str = this.utf8_encode(str);
        x = convertToWordArray(str);
        a = 0x67452301;
        b = 0xEFCDAB89;
        c = 0x98BADCFE;
        d = 0x10325476;

        xl = x.length;
        for (k = 0; k < xl; k += 16) {
            AA = a;
            BB = b;
            CC = c;
            DD = d;
            a = _FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
            d = _FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
            c = _FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
            b = _FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
            a = _FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
            d = _FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
            c = _FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
            b = _FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
            a = _FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
            d = _FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
            c = _FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
            b = _FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
            a = _FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
            d = _FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
            c = _FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
            b = _FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
            a = _GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
            d = _GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
            c = _GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
            b = _GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
            a = _GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
            d = _GG(d, a, b, c, x[k + 10], S22, 0x2441453);
            c = _GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
            b = _GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
            a = _GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
            d = _GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
            c = _GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
            b = _GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
            a = _GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
            d = _GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
            c = _GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
            b = _GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
            a = _HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
            d = _HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
            c = _HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
            b = _HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
            a = _HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
            d = _HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
            c = _HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
            b = _HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
            a = _HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
            d = _HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
            c = _HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
            b = _HH(b, c, d, a, x[k + 6], S34, 0x4881D05);
            a = _HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
            d = _HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
            c = _HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
            b = _HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
            a = _II(a, b, c, d, x[k + 0], S41, 0xF4292244);
            d = _II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
            c = _II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
            b = _II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
            a = _II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
            d = _II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
            c = _II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
            b = _II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
            a = _II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
            d = _II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
            c = _II(c, d, a, b, x[k + 6], S43, 0xA3014314);
            b = _II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
            a = _II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
            d = _II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
            c = _II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
            b = _II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
            a = addUnsigned(a, AA);
            b = addUnsigned(b, BB);
            c = addUnsigned(c, CC);
            d = addUnsigned(d, DD);
        }

        var temp = wordToHex(a) + wordToHex(b) + wordToHex(c) + wordToHex(d);
        return temp.toLowerCase();
    }

    function utf8_encode(argString) {
        // original by: Webtoolkit.info (http://www.webtoolkit.info/)
        // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: sowberry
        // improved by: Jack
        // improved by: Yves Sucaet
        // improved by: kirilloid
        // bugfixed by: Onno Marsman
        // bugfixed by: Onno Marsman
        // bugfixed by: Ulrich
        // bugfixed by: Rafal Kukawski
        // bugfixed by: kirilloid
        //   example 1: utf8_encode('Kevin van Zonneveld');
        //   returns 1: 'Kevin van Zonneveld'

        if (argString === null || typeof argString === 'undefined') {
        return '';
        }

        var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
        var utftext = '',
        start, end, stringl = 0;

        start = end = 0;
        stringl = string.length;
        for (var n = 0; n < stringl; n++) {
        var c1 = string.charCodeAt(n);
        var enc = null;

        if (c1 < 128) {
          end++;
        } else if (c1 > 127 && c1 < 2048) {
          enc = String.fromCharCode(
            (c1 >> 6) | 192, (c1 & 63) | 128
          );
        } else if ((c1 & 0xF800) != 0xD800) {
          enc = String.fromCharCode(
            (c1 >> 12) | 224, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
          );
        } else { // surrogate pairs
          if ((c1 & 0xFC00) != 0xD800) {
            throw new RangeError('Unmatched trail surrogate at ' + n);
          }
          var c2 = string.charCodeAt(++n);
          if ((c2 & 0xFC00) != 0xDC00) {
            throw new RangeError('Unmatched lead surrogate at ' + (n - 1));
          }
          c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
          enc = String.fromCharCode(
            (c1 >> 18) | 240, ((c1 >> 12) & 63) | 128, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
          );
        }
        if (enc !== null) {
          if (end > start) {
            utftext += string.slice(start, end);
          }
          utftext += enc;
          start = end = n + 1;
        }
        }

        if (end > start) {
        utftext += string.slice(start, stringl);
        }

        return utftext;
      }
