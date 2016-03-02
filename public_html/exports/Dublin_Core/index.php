<?php

include "../../header.php";

$forms = forms::getForms(TRUE);

$form_list = array();
foreach ($forms as $form) {

  $form_list[] = array(
    "formName" => forms::title($form['ID']),
    "normal"   => sprintf('<li><a href="%sexports/Dublin_Core/standard/?formID=%s">Normal</a></li>',
                        localvars::get("siteRoot"),
                        $form['ID']
                      ),
    "nocombine" => sprintf('<li><a href="%sexports/Dublin_Core/standard/?formID=%s&nocombine=true">Ignore Combines</a></li>',
                      localvars::get("siteRoot"),
                      $form['ID']
                    )
                  );
}

$table           = new tableObject("array");
$table->sortable = TRUE;
$table->summary  = "Dublin Core export Listing";
$table->class    = "styledTable";
$table->headers(array("Form Name","Normal DC Export","DC Export Ignoring Combines"));

localvars::add("form_list",$table->display($form_list));

$engine->eTemplate("include","header");
?>

<section>
  <header class="page-header">
    <h1 class="page-title">Dublin Core Exports</h1>
  </header>

<p>Select a form to export as dublin core.</p>
<p>Please note that only forms with Dublin Core defined will work with this export.</p>

{local var="form_list"}
</section>
<?php
$engine->eTemplate("include","footer");
?>
