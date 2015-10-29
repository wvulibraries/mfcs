<ul class="breadcrumbs">
    <li><a href="{local var="siteRoot"}">Home</a></li>
    <li><a href="{local var="siteRoot"}formCreator/">Form Creator</a></li>
    <li> Navigation Creator </li>
</ul>

<div class="container-fluid">
    <div class="row-fluid" id="results">
        {local var="results"}
    </div>

    <div class="row-fluid">
        <div class="span6">
            <ul class="nav nav-tabs" id="groupingTab">
                <li><a href="#groupingsAdd" data-toggle="tab">Add</a></li>
                <li><a href="#groupingsSettings" data-toggle="tab">Settings</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane" id="groupingsAdd">
                    <ul class="unstyled draggable span6">
                        <li><a href="#" class="btn btn-block">New Grouping</a></li>
                        <li><a href="#" class="btn btn-block">Log Out</a></li>
                    </ul>
                    <ul class="unstyled draggable span6">
                        <li><a href="#" class="btn btn-block">Export Link (needs definable properties)</a></li>
                        <li><a href="#" class="btn btn-block">Link</a></li>
                    </ul>

                    {local var="metadataForms"}
                </div>

                <div class="tab-pane" id="groupingsSettings">
                    <div class="alert alert-block" id="noGroupingSelected">
                        <h4>No Grouping Selected</h4>
                        To change a grouping, click on it in the preview to the right.
                    </div>

                    <div class="control-group well well-small" id="groupingsSettings_container_grouping">
                        <label for="groupingsSettings_grouping">
                            Grouping Label
                        </label>
                        <input type="text" class="input-block-level" id="groupingsSettings_grouping" name="groupingsSettings_grouping" />
                        <span class="help-block hidden"></span>
                    </div>

                    <div class="control-group well well-small" id="groupingsSettings_container_label">
                        <label for="groupingsSettings_label">
                            Label
                        </label>
                        <input type="text" class="input-block-level" id="groupingsSettings_label" name="groupingsSettings_label" />
                        <span class="help-block hidden"></span>
                    </div>

                    <div class="control-group well well-small" id="groupingsSettings_container_url">
                        <label for="groupingsSettings_url">
                            Address
                        </label>
                        <input type="text" class="input-block-level" id="groupingsSettings_url" name="groupingsSettings_url" />
                        <span class="help-block hidden"></span>
                    </div>
                </div>
            </div>

            <div class="row-fluid">
                <form class="form form-horizontal" id="submitNavigation" name="submitNavigation" method="post">
                    <input type="hidden" name="id" value="{local var="formID"}">
                    <input type="hidden" name="groupings">
                    <input type="submit" class="btn btn-large btn-block btn-primary" name="submitNavigation" value="Update Navigation">
                    {engine name="csrf"}
                </form>
            </div>
        </div>

        <div class="span6">
            <ul class="sortable unstyled" id="GroupingsPreview">
                {local var="existingGroupings"}
            </ul>
        </div>
    </div>
</div>