function previewFile(linkObj,url){
	var $link     = $(linkObj);
	var $modal    = $('#filePreviewModal');
	var linkLabel = $link.text();
	var filename  = $link.closest('.btn-group').siblings('.filename').text();
	var iFrame    = $modal.find('iframe.filePreview')[0];

	// Create a ucfirst() version of the linkLabel
	var typeLabel = linkLabel.charAt(0).toUpperCase() + linkLabel.substr(1).toLowerCase();

	$modal.modal('hide');
	iFrame.src = 'about:blank';
	$modal.find('h3').html( filename+' - '+typeLabel );
	iFrame.src = url;
	$modal.find('a.previewDownloadLink')[0].href = url+'&download=1';
	$modal.modal('show');
}

