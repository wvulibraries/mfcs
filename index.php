<?php
include("header.php");

recurseInsert("acl.php","php");

$engine->eTemplate("include","header");
?>

<section>
	<header class="page-header">
		<h1>Header</h1>
	</header>


</section>


<?php
$engine->eTemplate("include","footer");
?>
