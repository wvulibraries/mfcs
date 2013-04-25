<div id="progressModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="progressBarModalBox" aria-hidden="true">
	<div class="modal-header">
		<h3 id="metadataModalHeader">Loading ...</h3>
	</div>
	<div class="modal-body">
		<div class="progress progress-striped active">
			<div id="createFormProgressBar" class="bar" style="width: 75%;"></div>
		</div>
	</div>
	<div class="modal-footer">
	</div>
</div>

<script type="text/javascript">
var url = window.location.pathname.split("/");
if (url[url.length-1] == "index.php") {
	$('#progressModal').modal('show');
}

</script>

<script type="text/javascript" src='{local var="siteRoot"}includes/js/createForm_nav.js'></script>
<script type="text/javascript" src='{local var="siteRoot"}includes/js/createForm_form.js'></script>
<script type="text/javascript" src='{local var="siteRoot"}includes/js/createForm_permissions.js'></script>