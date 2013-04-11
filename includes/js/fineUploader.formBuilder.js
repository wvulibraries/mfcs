$("#fineUploader_{local var="fieldName"}")
.fineUploader({
	request: {
		endpoint: "{local var="siteRoot"}includes/uploader.php",
		params: {
			engineCSRFCheck: "{engine name="csrf" insert="false"}",
			uploadID: $("#{local var="fieldName"}").val(),
		}
	},
	failedUploadTextDisplay: {
		mode: "custom",
		maxChars: 40,
		responseProperty: "error",
		enableTooltip: true
	},
	multiple: {local var="multipleFiles"},
	validation: {
		allowedExtensions: ["{local var="allowedExtensions"}"],
	},
	text: {
		uploadButton: '<i class="icon-plus icon-white"></i> Select Files'
	},
	showMessage: function(message) {
		$("#fineUploader_{local var="fieldName"} .qq-upload-list").append('<li class="alert alert-error">' + message + '</li >');
	},
	classes: {
		success: "alert alert-success",
		fail: "alert alert-error"
	},
})
.on("complete", function(event,id,fileName,responseJSON) {
});