function previewFile(linkObj,url){
	var $link     = $(linkObj);
	var $modal    = $('#filePreviewModal');
	var linkLabel = $link.text();
	var filename  = $link.closest('.btn-group').siblings('.filename').text();

	// Create a ucfirst() version of the linkLabel
	var typeLabel = linkLabel.charAt(0).toUpperCase() + linkLabel.substr(1).toLowerCase();

	$modal.modal('hide');
	$modal.find('h3').html( filename+' - '+typeLabel );
	$modal.find('iframe.filePreview')[0].src = url;
	$modal.find('a.previewDownloadLink')[0].href = url+'&download=1';
	$modal.modal('show');
}

