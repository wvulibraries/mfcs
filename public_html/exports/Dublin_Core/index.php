<?php

include "../../header.php";

$forms = forms::getForms(TRUE);

$form_list = "<ul>";
foreach ($forms as $form) {
  $form_list .= sprintf('<li><a href="%sexports/Dublin_Core/standard/?formID=%s">%s</a></li>',
                        localvars::get("siteRoot"),
                        $form['ID'],
                        forms::title($form['ID'])
                      );
}
$form_list .= "</ul>";

localvars::add("form_list",$form_list);

$engine->eTemplate("include","header");
?>

<section>
<p>Select a form to export as dublin core.</p>
<p>Please note that only forms with Dublin Core defined will work with this export.</p>

{local var="form_list"}
</section>
<?php
$engine->eTemplate("include","footer");
?>
