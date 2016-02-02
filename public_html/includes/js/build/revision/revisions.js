function scrollSync(iFrameObj){
	$($(iFrameObj).contents()).scroll(function(){
		if($('#revisionSelector').val()){
			var thisObj     = $(this);
			var url         = thisObj[0].URL;
			var fieldName   = url.match(/field=\w+/i)[0].split('=')[1];
			var iFrameClass = url.match(/#\w+/i)[0].substr(1);
			var targetClass = iFrameClass=='rightFileViewer' ? 'leftFileViewer' : 'rightFileViewer';
			var scrollTop   = thisObj.scrollTop();
			var scrollLeft  = thisObj.scrollLeft();
			$('.'+targetClass+'[data-field_name="'+fieldName+'"]').contents().scrollTop(scrollTop).scrollLeft(scrollLeft);
		}
	});
	// onLoad, trigger sync from the left fileViewer
	$('.leftFileViewer').contents().scroll();
}

$(function(){
	var objectID = $('#revisionsScript').data("objectid");
	var currentObjects = $('#current .revisionBody').find('div.objectField');

	$('#revisionSelector').change(function(){
		var url = '?objectID='+objectID+'&revisionID='+$(this).val()+'#grabVersion';
		$('#revisionViewer').load(url, function(){
		  console.log( "Load was performed." );
		  console.log(url);

		  	var newObjects = $('#revisions .revisionBody').find('div.objectField');
			for(var i = 0; i < currentObjects.length; i++){
				var elm1 = currentObjects[i];
				var elm2 = newObjects[i];

				if(!elm1.isEqualNode(elm2)){
					$(elm1).addClass('diffCurrent');
					$(elm2).addClass('diffRevision');
				}
			}
		});
	});

	$('#revertBtn').click(function(){
		if(confirm('Are you sure you want to revert back to this version?')){
			$('#revisionID').val( $('#revisionSelector').val() );
			$('#revisionForm').submit();
			$('#revisions :input').attr('disabled','disabled');
		}else{
			alert('Revert canceled');
		}
	});
	$('#objectComparator').on('click','.toggleFileList',function(){
		$link = $(this);
		$ul   = $link.next('ul');
		if($ul.is(':visible')){
			$ul.slideUp('fast');
			$link.html('click to show list');
		}else{
			$ul.slideDown('fast');
			$link.html('click to hide list');
		}
	});
});
