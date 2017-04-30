<div>
    <ul class="breadcrumbs">
        <li><a href="{local var="siteRoot"}">Home</a></li>
        <li><a href="{local var="siteRoot"}formCreator/">Form Creator</a></li>
        <li> Form Creator </li>
    </ul>

    {local var="results"}

    <div>
        <div class="span5">
            <div id="leftPanel">
                <ul class="nav nav-tabs" id="fieldTab">
                    <li><a href="#fieldAdd" data-toggle="tab" class="addFieldNav">Add a Field</a></li>
                    <li><a href="#fieldSettings" data-toggle="tab">Field Settings</a></li>
                    <li><a href="#formSettings" data-toggle="tab">Form Settings</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane" id="fieldAdd">
                        <div class="mainFormElements">
                            <ul class="unstyled draggable">
                                <li><a href="#">ID Number</a></li>
                                <li><a href="#">Single Line Text</a></li>
                                <li><a href="#">Paragraph Text</a></li>
                                <li><a href="#">Radio</a></li>
                                <li><a href="#">Checkboxes</a></li>
                                <li><a href="#">Number</a></li>
                                <li><a href="#">Email</a></li>
                                <li><a href="#">Phone</a></li>
                                <li><a href="#">Dropdown</a></li>
                                <li><a href="#">Multi-Select</a></li>
                                <li><a href="#">File Upload</a></li>
                                <li><a href="#">WYSIWYG</a></li>
                                <li><a href="#">Date</a></li>
                                <li><a href="#">Time</a></li>
                                <li><a href="#">Website</a></li>
                            </ul>
                        </div>
                        <div class="fieldSet">
                            <ul class="unstyled draggable">
                                <li><a href="#">Field Set</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-pane" id="fieldSettings">

                        <div class="alert alert-warning" id="noFieldSelected">
                            <h4>No Field Selected</h4>
                            To change a field, click on it in the form preview to the right.
                        </div>

                        <form class="form form-horizontal" id="fieldSettings_fieldset_form">
                            <div class="control-group" id="fieldSettings_container_fieldset">
                                <label for="fieldSettings_fieldset">
                                    Fieldset Label
                                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If a label is entered here, the field will be surrounded by a FieldSet, and the label used."></i>
                                </label>
                                <input type="text" class="input-block-level" id="fieldSettings_fieldset" name="fieldSettings_fieldset" data-bindName="fieldset"/>
                                <span class="help-block hidden"></span>
                            </div>
                        </form>

                        <form class="form form-horizontal" id="fieldSettings_form">
                            <?php recurseInsert("templates/fieldSettingsForm.php","php"); ?>
                        </form>
                    </div>

                    <div class="tab-pane" id="formSettings">
                        <div class="row group noHide">
                            <div class="control-group " id="formSettings_formTitle_container">
                                <label for="formSettings_formTitle">
                                    Form Title
                                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The form name is a unique value that is used to identify a form."></i>
                                </label>
                                <input type="text" class="input-block-level" id="formSettings_formTitle" name="formSettings_formTitle" value="{local var="formTitle"}" />
                                <span class="help-block hidden"></span>
                            </div>

                            <div class="control-group " id="formSettings_objectDisplayTitle_container">
                                <label for="formSettings_objectDisplayTitle">
                                    Display Title
                                </label>
                                <input type="text" class="input-block-level" id="formSettings_objectDisplayTitle" name="formSettings_objectDisplayTitle" value="{local var="displayTitle"}">
                                <span class="help-block hidden"></span>
                            </div>

                            <div class="control-group " id="formSettings_linkTitle_container">
                                <label for="formSettings_linkTitle">
                                    Link Title
                                </label>
                                <input type="text" class="input-block-level" id="formSettings_linkTitle" name="formSettings_linkTitle" value="{local var="linkTitle"}">
                                <span class="help-block hidden"></span>
                            </div>

                            <div class="control-group " id="formSettings_formDescription_container">
                                <label for="formSettings_formDescription">
                                    Form Description
                                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The form description explains the purpose of this form to users."></i>
                                </label>
                                <input type="text" class="input-block-level" id="formSettings_formDescription" name="formSettings_formDescription" value="{local var="formDescription"}" />
                                <span class="help-block hidden"></span>
                            </div>
                        </div>

                        <div class="row group noHide">
                            <div class="span6">
                                <div class="control-group " id="formSettings_submitButton_container">
                                    <label for="formSettings_submitButton">
                                        Submit Button
                                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The text that is displayed on the form's submit button."></i>
                                    </label>
                                    <input type="text" class="input-block-level" id="formSettings_submitButton" name="formSettings_submitButton" value="{local var="submitButton"}" />
                                    <span class="help-block hidden"></span>
                                </div>
                            </div>

                            <div class="span6">
                                <div class="control-group " id="formSettings_updateButton_container">
                                    <label for="formSettings_updateButton">
                                        Update Button
                                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The text that is displayed on the form's update button."></i>
                                    </label>
                                    <input type="text" class="input-block-level" id="formSettings_updateButton" name="formSettings_updateButton" value="{local var="updateButton"}" />
                                    <span class="help-block hidden"></span>
                                </div>
                            </div>
                        </div>

                        <div class="control-group " id="formSettings_objectTitleField_container">
                            <label for="formSettings_objectTitleField">
                                Title Field
                            </label>
                            <select class="input-block-level" id="formSettings_objectTitleField" name="formSettings_objectTitleField">
                                {local var="objectTitleFieldOptions"}
                            </select>
                            <span class="help-block hidden"></span>
                        </div>

                        <div class="row group noHide">
                            <div class="control-group " id="formSettings_formContainer_container">
                                <ul class="checkboxList">
                                    <li><label class="checkbox" for="formSettings_formContainer"><input type="checkbox" id="formSettings_formContainer" name="formSettings_formContainer" {local var="formContainer"}> Act as Container</label></li>
                                    <li><label class="checkbox" for="formSettings_formProduction"><input type="checkbox" id="formSettings_formProduction" name="formSettings_formProduction" {local var="formProduction"}> Production Ready</label></li>
                                    <li><label class="checkbox" for="formSettings_formMetadata"><input type="checkbox" id="formSettings_formMetadata" name="formSettings_formMetadata" {local var="formMetadata"}> Metadata Form</label></li>
                                    <li><label class="checkbox" for="formSettings_exportPublic"><input type="checkbox" id="formSettings_exportPublic" name="formSettings_exportPublic" {local var="exportPublic"}> Export To Public</label></li>
                                    <li><label class="checkbox" for="formSettings_exportOAI"><input type="checkbox" id="formSettings_exportOAI" name="formSettings_exportOAI" {local var="exportOAI"}> OAI-PMH Export</label></li>
                                    <li><label class="checkbox" for="formSettings_objPublicReleaseShow"><input type="checkbox" id="formSettings_objPublicReleaseShow" name="formSettings_objPublicReleaseShow" {local var="objPublicReleaseShow"}> Display Public Release</label></li>
                                    <li><label class="checkbox" for="formSettings_objPublicReleaseDefaultTrue"><input type="checkbox" id="formSettings_objPublicReleaseDefaultTrue" name="formSettings_objPublicReleaseDefaultTrue" {local var="objPublicReleaseDefaultTrue"}> Public Release Default "Yes"</label></li>
                                </ul>
                                <span class="help-block hidden"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row-fluid">
                    <form class="form form-horizontal" id="submitForm" name="submitForm" method="post">
                        <input type="hidden" name="id" value="{local var="formID"}">
                        <input type="hidden" name="form">
                        <input type="hidden" name="fields">
                        <input type="submit" class="btn btn-large btn-block btn-primary" name="submitForm" value="{local var="thisSubmitButton"}" disabled>
                        <noscript><p style="color:red; text-align: center; font-weight: bold;">JavaScript failed to load!</p></noscript>
                        {engine name="csrf"}
                    </form>
                </div>
            </div>
        </div>

        <div class="span7">
            <div id="rightPanel">
                <form class="form-horizontal" id="formPreview_container">
                    <h2 id="formTitle"></h2>
                    <p id="formDescription"></p>
                    <ul class="unstyled sortable" id="formPreview">
                        {local var="formPreview"}
                    </ul>
                </form>
            </div>
        </div>
    </div>
</div>
