<?php
require_once "../../header.php";

$object_forms   = forms::getForms(TRUE);
$metadata_forms = forms::getForms(FALSE);

$form_block = "";
foreach ($object_forms as $form) {
	$form_block .= build_permissions_html($form);
}
localvars::add("object_form_permissions",$form_block);

$form_block = "";
foreach ($metadata_forms as $form) {
	$form_block .= build_permissions_html($form);
}
localvars::add("metadata_form_permissions",$form_block);

$active_users   = "<ul>";
$inactive_users = "<ul>";
$admin_users    = "<ul>";
$student_users  = "<ul>";
foreach (users::getUsers() as $user) {

	if (!mfcsPerms::isActive($user['username'])) {
		$inactive_users .= format_user_display($user);
	}
	else {
		$active_users   .= format_user_display($user);
	}
	if (strtolower($user['status']) == "admin") {
		$admin_users    .= format_user_display($user);
	}
	if (strtolower($user['isStudent']) == "1") {
		$student_users  .= format_user_display($user);
	}
}
$active_users   .= "</ul>";
$inactive_users .= "</ul>";
$admin_users    .= "</ul>";
$student_users  .= "</ul>";

localvars::add("active_users",   $active_users);
localvars::add("inactive_users", $inactive_users);
localvars::add("admin_users",    $admin_users);
localvars::add("student_users",  $student_users);

function format_user_display($user) {

	return sprintf("<li>%s%s, %s -- %s%s</li>",
		(!mfcsPerms::isActive($user['username']))?'<span class="inactive_user">':"",
		$user['lastname'],
		$user['firstname'],
		$user['username'],
		(!mfcsPerms::isActive($user['username']))?'</span>':""
		);
}

function build_permissions_html($form) {
	$permissions = mfcsPerms::permissions_for_form($form['ID']);

	$form_block  = sprintf('<div class="form_permission_block" id="formID-%s">',$form['ID']);
	$form_block .= sprintf('<h4>%s</h4>',forms::title($form['ID']));

	foreach ($permissions as $type=>$usernames) {
		$form_block .= sprintf('<h5>%s</h5>',mfcsPerms::type_is($type));
		$form_block .= '<ul>';

		foreach ($usernames as $username) {
			if (is_empty($username)) continue;

			$user          = users::get($username);
			$form_block   .= format_user_display($user);
		}

		$form_block .= '</ul>';
	}

	$form_block .= '</div>';

	return $form_block;
}

log::insert("Dashboard: Permissions Audit");

$engine->eTemplate("include","header");

?>

<section>
	<header class="page-header">
		<h1>Permissions Information</h1>
	</header>

	<ul class="breadcrumbs">
		<li><a href="{local var="siteRoot"}">Home</a></li>
		<li><a href="{local var="siteRoot"}dashboard">Dashboard</a></li>
		<li class="pull-right noDivider"><a href="https://github.com/wvulibraries/mfcs/wiki/Permissions"> <i class="fa fa-book"></i> Documentation</a></li>
	</ul>

<h2 style="font-size: 150%; border-bottom: 1px solid #d4d4d4; padding: 0 0 10px 0;">User Permissions</h2>

<div id="current_users">
	<h3>All active users in MFCS</h3>
	{local var="active_users"}

	<h3>All inactive users in MFCS</h3>
	{local var="inactive_users"}
</div>

<div id="student_users">
	<h3>Student Users</h3>
	{local var="student_users"}
</div>

<div id="admin_users">
	<h3>Administrator Users</h3>
	<p><em>* Administrators have access to all forms and data in the system</em></p>
	{local var="admin_users"}
</div>

<!-- Removed to organize similar content

<div id="current_users">
	<h2>All inactive users in MFCS</h2>
	{local var="inactive_users"}
</div> -->

<script>
	// Form Column Resizing 
	equalheight = function(container){

		var currentTallest = 0,
		currentRowStart    = 0,
		rowDivs            = new Array(),
		$el,
		topPosition        = 0;

		$(container).each(function() {

			$el = $(this);
			$($el).height('auto')
			topPostion = $el.position().top;

			if (currentRowStart != topPostion) {
		
				for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
					rowDivs[currentDiv].height(currentTallest);
				}

				rowDivs.length  = 0; // empty the array
				currentRowStart = topPostion;
				currentTallest  = $el.height();
	    		rowDivs.push($el);
	    	} 
	    	else {
	    		rowDivs.push($el);
	    		currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
	    	}
	    	for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
	    		rowDivs[currentDiv].height(currentTallest);
	    	}
	    });

	}

	$(document).ready(function() {
	  equalheight('#object_forms_permissions > div, #metadata_forms_permissions > div');
	});

	$(window).load(function() {
	  equalheight('#object_forms_permissions > div, #metadata_forms_permissions > div');
	});

	$(window).resize(function(){
	  equalheight('#object_forms_permissions > div, #metadata_forms_permissions > div');

	});
</script>

<div id="user_divider">
	<h2 style="font-size: 150%; border-bottom: 1px solid #d4d4d4; padding: 0 0 10px 0;">Form Permissions</h2>
</div>

<div id="object_forms_permissions">
	<h3>Object Forms</h3>
	{local var="object_form_permissions"}
</div>

<div id="metadata_forms_permissions">
	<h3>Metadata Forms</h3>
	{local var="metadata_form_permissions"}
</div>


</section>

<?php
$engine->eTemplate("include","footer");
?>

