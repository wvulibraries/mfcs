<?php
include("header.php");

recurseInsert("acl.php","php");

$engine->eTemplate("include","header");

if (isset($engine->cleanPost['MYSQL']['submitForm'])) {
	print "<pre>";
	print_r($engine->cleanPost['MYSQL']);
	print "</pre>";
}

?>

<script type="text/javascript" src="{local var="siteRoot"}includes/js/createForm.js"></script>

<section>
	<header class="page-header">
		<h1>Form Creator</h1>
	</header>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span5">
				<ul class="nav nav-tabs" id="fieldTab">
					<li class="active"><a href="#fieldAdd" data-toggle="tab">Add a Field</a></li>
					<li><a href="#fieldSettings" data-toggle="tab">Field Settings</a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active" id="fieldAdd">
						<div class="span6">
							<ul class="unstyled">
								<li><a href="#" class="btn btn-block">Single Line Text</a></li>
								<li><a href="#" class="btn btn-block">Paragraph Text</a></li>
								<li><a href="#" class="btn btn-block">Multiple Choice</a></li>
								<li><a href="#" class="btn btn-block">Checkboxes</a></li>
								<li><a href="#" class="btn btn-block">Dropdown</a></li>
								<li><a href="#" class="btn btn-block">Number</a></li>
							</ul>
						</div>
						<div class="span6">
							<ul class="unstyled">
								<li><a href="#" class="btn btn-block">Email</a></li>
								<li><a href="#" class="btn btn-block">Phone</a></li>
								<li><a href="#" class="btn btn-block">Date</a></li>
								<li><a href="#" class="btn btn-block">Time</a></li>
								<li><a href="#" class="btn btn-block">Website</a></li>
							</ul>
						</div>
					</div>

					<div class="tab-pane" id="fieldSettings">
						<div class="alert alert-block" id="noFieldSelected">
							<h4>No Field Selected</h4>
							To change a field, click on it in the form preview to the right.
						</div>

						<form class="form form-horizontal">
							<div class="row-fluid noHide">
								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_name">
										<label for="fieldSettings_name">
											Field Name
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The field name is a unique value that is used to identify a field."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_name" name="fieldSettings_name" />
										<span class="help-block hidden"></span>
									</div>

									<div class="control-group well well-small" id="fieldSettings_container_ID">
										<label for="fieldSettings_ID">
											HTML ID
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The ID is a unique value that can be used to identify a field."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_ID" name="fieldSettings_ID" />
										<span class="help-block hidden"></span>
									</div>
								</span>

								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_label">
										<label for="fieldSettings_label">
											Field Label
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The field label tells your users what to enter in this field."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_label" name="fieldSettings_label" />
										<span class="help-block hidden"></span>
									</div>

									<div class="control-group well well-small" id="fieldSettings_container_class">
										<label for="fieldSettings_class">
											HTML Classes
											<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Classes can be entered to give the field a different look and feel."></i>
										</label>
										<input type="text" class="input-block-level" id="fieldSettings_class" name="fieldSettings_class" />
										<span class="help-block hidden"></span>
									</div>
								</span>
							</div>

							<div class="row-fluid noHide">
								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_options">
										<label for="fieldSettings_options">
											Options
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_required" name="fieldSettings_options_required"> Required
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_duplicates" name="fieldSettings_options_duplicates"> No Duplicates
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_readonly" name="fieldSettings_options_readonly"> Read Only
										</label>
										<label class="checkbox">
											<input type="checkbox" id="fieldSettings_options_disable" name="fieldSettings_options_disable"> Disabled
										</label>
									</div>
								</span>
								<span class="span6">
									<div class="control-group well well-small" id="fieldSettings_container_access">
										<label for="fieldSettings_access">
											Allow Access
										</label>
										<select class="input-block-level" id="fieldSettings_access" name="fieldSettings_access" multiple>
										</select>
									</div>
								</span>
							</div>

							<div class="control-group well well-small" id="fieldSettings_container_range">
								<label for="fieldSettings_min">
									Range
									<i class="icon-question-sign" rel="tooltip" data-placement="right" data-title=""></i>
								</label>

								<div class="row-fluid">
									<span class="span4">
										<label for="fieldSettings_min">
											Min
										</label>
										<input type="number" class="input-block-level" id="fieldSettings_min" name="fieldSettings_min" min="0" />
									</span>
									<span class="span4">
										<label for="fieldSettings_max">
											Max
										</label>
										<input type="number" class="input-block-level" id="fieldSettings_max" name="fieldSettings_max" min="0" />
									</span>
									<span class="span4">
										<label for="fieldSettings_format">
											Format
										</label>
										<select class="input-block-level" id="fieldSettings_format" name="fieldSettings_format"></select>
									</span>
								</div>
							</div>


						</form>
					</div>
				</div>
			</div>

			<div class="span7">
				<form class="form-horizontal" name="formPreview" method="post">
					<ul class="unstyled" id="formPreview"></ul>

					<input type="submit" name="submitForm" value="Add/Update Form" >
					{engine name="csrf"}
				</form>
			</div>

		</div>
	</div>
</section>

<script type="text/javascript">
	$("form[name=formPreview]").submit(function() {
		$(".fieldPreview :input").prop('disabled', true);

		var pos = 0;
		$(":input[name^=position_]", this).each(function() {
			$(this).val(pos++);
		});

		// console.log($("form[name=formPreview]").serialize());
		// return false;
	});
</script>

<?php
$engine->eTemplate("include","footer");
?>
