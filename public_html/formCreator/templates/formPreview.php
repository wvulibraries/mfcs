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
                    <li><a href="#fieldAdd" data-toggle="tab">Add a Field</a></li>
                    <li><a href="#fieldSettings" data-toggle="tab">Field Settings</a></li>
                    <li><a href="#formSettings" data-toggle="tab">Form Settings</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane" id="fieldAdd">
                        <div class="row-fluid">
                            <div class="span6">
                                <ul class="unstyled draggable">
                                    <li><a href="#" class="btn btn-block">ID Number</a></li>
                                    <li><a href="#" class="btn btn-block">Single Line Text</a></li>
                                    <li><a href="#" class="btn btn-block">Paragraph Text</a></li>
                                    <li><a href="#" class="btn btn-block">Radio</a></li>
                                    <li><a href="#" class="btn btn-block">Checkboxes</a></li>
                                    <li><a href="#" class="btn btn-block">Number</a></li>
                                    <li><a href="#" class="btn btn-block">Email</a></li>
                                    <li><a href="#" class="btn btn-block">Phone</a></li>
                                </ul>
                            </div>
                            <div class="span6">
                                <ul class="unstyled draggable">
                                    <li><a href="#" class="btn btn-block">Dropdown</a></li>
                                    <li><a href="#" class="btn btn-block">Multi-Select</a></li>
                                    <li><a href="#" class="btn btn-block">File Upload</a></li>
                                    <li><a href="#" class="btn btn-block">WYSIWYG</a></li>
                                    <li><a href="#" class="btn btn-block">Date</a></li>
                                    <li><a href="#" class="btn btn-block">Time</a></li>
                                    <li><a href="#" class="btn btn-block">Website</a></li>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <ul class="unstyled draggable">
                            <li><a href="#" class="btn btn-block">Field Set</a></li>
                        </ul>
                    </div>

                    <div class="tab-pane" id="fieldSettings">

                        <div class="alert alert-warning" id="noFieldSelected">
                            <h4>No Field Selected</h4>
                            To change a field, click on it in the form preview to the right.
                        </div>

                        <form class="form form-horizontal" id="fieldSettings_fieldset_form">
                            <div class="control-group well well-small" id="fieldSettings_container_fieldset">
                                <label for="fieldSettings_fieldset">
                                    Fieldset Label
                                    <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If a label is entered here, the field will be surrounded by a FieldSet, and the label used."></i>
                                </label>
                                <input type="text" class="input-block-level" id="fieldSettings_fieldset" name="fieldSettings_fieldset"/>
                                <span class="help-block hidden"></span>
                            </div>
                        </form>

                        <form class="form form-horizontal" id="fieldSettings_form">
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
                                </span>
                            </div>

                            <div class="row-fluid noHide">
                                <span class="span6" id="fieldSettings_container_value">
                                    <div class="control-group well well-small">
                                        <label for="fieldSettings_value">
                                            Value
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="When the form is first displayed, this value will already be prepopulated."></i>
                                        </label>
                                        <a href="javascript:;" class="pull-right" onclick="$('#defaultValueVariables').modal('show')" id="fieldVariablesLink" style="display: none;">Variables</a>
                                        <input type="text" class="input-block-level" id="fieldSettings_value" name="fieldSettings_value" />
                                        <span class="help-block hidden"></span>
                                        <div id="defaultValueVariables" class="modal hide fade" rel="modal" data-show="false">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                <h3 id="myModalLabel">Available variables for default value</h3>
                                            </div>
                                            <div class="modal-body">
                                                <b>User</b>
                                                <ul style="list-style: none;">
                                                    <li><b>%userid%</b><br>The user id for the currently logged in user. (<i>Example: <?php echo forms::applyFieldVariables('%userid%') ?></i>)</li>
                                                    <li><b>%username%</b><br>The username for the currently logged in user. (<i>Example: <?php echo forms::applyFieldVariables('%username%') ?></i>)</li>
                                                    <li><b>%firstname%</b><br>The first name for the currently logged in user. (<i>Example: <?php echo forms::applyFieldVariables('%firstname%') ?></i>)</li>
                                                    <li><b>%lastname%</b><br>The last name for the currently logged in user. (<i>Example: <?php echo forms::applyFieldVariables('%lastname%') ?></i>)</li>
                                                </ul>
                                                <hr>
                                                <b>Static Date/Time</b>
                                                <ul style="list-style: none;">
                                                    <li><b>%date%</b><br>The current date as MM/DD/YYYY. (<i>Example: <?php echo forms::applyFieldVariables('%date%') ?></i>)</li>
                                                    <li><b>%time%</b><br>The current time as HH:MM:SS. (<i>Example: <?php echo forms::applyFieldVariables('%time%') ?></i>)</li>
                                                    <li><b>%time12%</b><br>The current 12-hr time. (<i>Example: <?php echo forms::applyFieldVariables('%time12%') ?></i>)</li>
                                                    <li><b>%time24%</b><br>The current 24-hr time. (<i>Example: <?php echo forms::applyFieldVariables('%time24%') ?></i>)</li>
                                                    <li><b>%timestamp%</b><br>The current UNIX system timestamp. (<i>Example: <?php echo forms::applyFieldVariables('%timestamp%') ?></i>)</li>
                                                </ul>
                                                <hr>
                                                <b>Custom Date/Time</b>
                                                <ul style="list-style: none;">
                                                    <li>
                                                        <b>%date(FORMAT)%</b><br>
                                                        You can specify a custom format when creating dates and times where FORMAT is a PHP <a href="http://us2.php.net/manual/en/function.date.php" target="_blank">date()</a> format string.
                                                        <br>
                                                        <b><i>Example:</i></b> %date(l, m j Y)% becomes <?php echo forms::applyFieldVariables('%date(l, F j Y)%') ?>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </span>

                                <span class="span6" id="fieldSettings_container_placeholder">
                                    <div class="control-group well well-small">
                                        <label for="fieldSettings_placeholder">
                                            Placeholder Text
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If there is no value in the field, this can tell your users what to input."></i>
                                        </label>
                                        <input type="text" class="input-block-level" id="fieldSettings_placeholder" name="fieldSettings_placeholder" />
                                        <span class="help-block hidden"></span>
                                    </div>
                                </span>
                            </div>

                            <div class="row-fluid noHide">
                                <span class="span6">
                                    <div class="control-group well well-small" id="fieldSettings_container_id">
                                        <label for="fieldSettings_id">
                                            CSS ID
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The ID is a unique value that can be used to identify a field."></i>
                                        </label>
                                        <input type="text" class="input-block-level" id="fieldSettings_id" name="fieldSettings_id" />
                                        <span class="help-block hidden"></span>
                                    </div>
                                </span>

                                <span class="span6">
                                    <div class="control-group well well-small" id="fieldSettings_container_class">
                                        <label for="fieldSettings_class">
                                            CSS Classes
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Classes can be entered to give the field a different look and feel."></i>
                                        </label>
                                        <input type="text" class="input-block-level" id="fieldSettings_class" name="fieldSettings_class" />
                                        <span class="help-block hidden"></span>
                                    </div>
                                </span>
                            </div>

                            <div class="row-fluid noHide">
                                <div class="control-group well well-small" id="fieldSettings_container_style">
                                    <label for="fieldSettings_style">
                                        Local Styles
                                        <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="You can set any HTML styles and they will only apply to this field."></i>
                                    </label>
                                    <input type="text" class="input-block-level" id="fieldSettings_style" name="fieldSettings_style" />
                                    <span class="help-block hidden"></span>
                                </div>
                            </div>

                            <div class="row-fluid noHide">
                                <div class="control-group well well-small" id="fieldSettings_container_style">
                                    <label for="fieldSettings_style">
                                        Field Help
                                        <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="You can set any help text you want displayed with this field. Any angle brackets in HTML text will be treated as HTML"></i>
                                    </label>
                                    <select class="input-block-level" id="fieldSettings_help_type" name="fieldSettings_help_type">
                                        <option value="">None</option>
                                        <option value="text">Plain text</option>
                                        <option value="html">HTML text</option>
                                        <option value="web">Webpage (URL)</option>
                                    </select>
                                    <input type="text" class="input-block-level" id="fieldSettings_help_text" name="fieldSettings_help_text" style="display: none;">
                                    <textarea class="input-block-level" id="fieldSettings_help_html" name="fieldSettings_help_html" style="display: none;"></textarea>
                                    <input type="text" class="input-block-level" id="fieldSettings_help_url" name="fieldSettings_help_url" style="display: none;" placeholder="http://example.com">
                                    <span class="help-block hidden"></span>
                                </div>
                                <div id="fieldHelpModal" class="modal hide fade">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 id="myModalLabel">Field Help</h3>
                                    </div>
                                    <div class="modal-body">
                                        <iframe id="fieldHelpModalURL" seamless="seamless" style="width: 100%; height: 100%;"></iframe>
                                    </div>
                                </div>
                            </div>

                            <div class="control-group well well-small" id="fieldSettings_container_choices">
                                <label for="fieldSettings_choices">
                                    Choices
                                </label>
                                <select class="input-block-level" id="fieldSettings_choices_type" name="fieldSettings_choices_type">
                                    <option value="manual">Manual</option>
                                    <option value="form">Another Form</option>
                                </select>
                                    <label style="width: 100%;"><input type="checkbox" id="fieldSettings_choices_null" name="fieldSettings_choices_null"> Include 'Make a selection' placeholder</label></span>
                                    <div id="fieldSettings_choices_manual"></div>
                                    <div id="fieldSettings_choices_form">
                                        <label for="fieldSettings_choices_formSelect">
                                            Select a Form
                                        </label>
                                        <select class="input-block-level" id="fieldSettings_choices_formSelect" name="fieldSettings_choices_formSelect">
                                            {local var="formsOptions"}
                                        </select>

                                        <label for="fieldSettings_choices_fieldSelect">
                                            Select a Field
                                        </label>
                                        <select class="input-block-level" id="fieldSettings_choices_fieldSelect" name="fieldSettings_choices_fieldSelect">
                                        </select>

                                        <label for="fieldSettings_choices_fieldDefault">
                                            Default Value
                                        </label>
                                        <input type="test" id="fieldSettings_choices_fieldDefault" name="fieldSettings_choices_fieldDefault">
                                    </div>
                                </p>
                                <span class="help-block hidden"></span>
                            </div>

                            <div class="control-group well well-small" id="fieldSettings_container_range">
                                <div class="row-fluid">
                                    <span class="span3">
                                        <label for="fieldSettings_range_min">
                                            Min
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_range_min" name="fieldSettings_range_min" min="0" />
                                    </span>
                                    <span class="span3">
                                        <label for="fieldSettings_range_max">
                                            Max
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_range_max" name="fieldSettings_range_max" min="0" />
                                    </span>
                                    <span class="span2">
                                        <label for="fieldSettings_range_step">
                                            Step
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_range_step" name="fieldSettings_range_step" min="0" />
                                    </span>
                                    <span class="span4">
                                        <label for="fieldSettings_range_format">
                                            Format
                                        </label>
                                        <select class="input-block-level" id="fieldSettings_range_format" name="fieldSettings_range_format"></select>
                                    </span>
                                </div>
                            </div>

                            <div class="control-group well well-small" id="fieldSettings_container_externalUpdate">
                                <label for="fieldSettings_externalUpdate">
                                    Update External Form
                                    <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="When the value of this field changes, update the selected form and field."></i>
                                </label>
                                <div id="fieldSettings_externalUpdate_form">
                                    <label for="fieldSettings_externalUpdate_formSelect">
                                        Select a Form
                                    </label>
                                    <select class="input-block-level" id="fieldSettings_externalUpdate_formSelect" name="fieldSettings_externalUpdate_formSelect">
                                        <option value="">None</option>
                                        {local var="formsOptions"}
                                    </select>

                                    <label for="fieldSettings_externalUpdate_fieldSelect">
                                        Select a Field
                                    </label>
                                    <select class="input-block-level" id="fieldSettings_externalUpdate_fieldSelect" name="fieldSettings_externalUpdate_fieldSelect">
                                    </select>
                                </div>
                                <span class="help-block hidden"></span>
                            </div>

                            <div class="control-group well well-small" id="fieldSettings_container_idno">
                                <label for="fieldSettings_idno_managedBy">
                                    ID Number Options
                                </label>
                                <select class="input-block-level" id="fieldSettings_idno_managedBy" name="fieldSettings_idno_managedBy">
                                    <option value="system">Managed by System</option>
                                    <option value="user">Managed by User</option>
                                </select>

                                <p>
                                    <div class="row-fluid">
                                        <div class="span6" id="fieldSettings_container_idno_format">
                                            <label for="fieldSettings_idno_format">
                                                Format
                                            </label>
                                            <input type="text" class="input-block-level" id="fieldSettings_idno_format" name="fieldSettings_idno_format" placeholder="st_###" />
                                        </div>

                                        <div class="span6" id="fieldSettings_container_idno_startIncrement">
                                            <label for="fieldSettings_idno_startIncrement">
                                                Auto Increment Start
                                            </label>
                                            <input type="number" class="input-block-level" id="fieldSettings_idno_startIncrement" name="fieldSettings_idno_startIncrement" min="0" />
                                        </div>
                                    </div>

                                    <div class="row-fluid hidden" id="fieldSettings_container_idno_confirm">
                                        <label class="checkbox">
                                            <input type="checkbox" id="fieldSettings_idno_confirm" name="fieldSettings_idno_confirm">
                                            Are you sure? <span class="text-warning">This change could cause potential conflicts.</span>
                                        </label>
                                    </div>
                                </p>
                            </div>

                            <div class="control-group well well-small" id="fieldSettings_container_file_allowedExtensions">
                                <div id="allowedExtensionsAlert" style="display:none;" class="alert alert-error">No allowed extensions included! Currently, no files will be uploadable!</div>
                                <label for="fieldSettings_file_allowedExtensions">
                                    Allowed Extensions
                                </label>
                                <div id="fieldSettings_file_allowedExtensions"></div>
                                <span class="help-block hidden"></span>
                            </div>

                            <div class="control-group well well-small" id="fieldSettings_container_file_options">
                                File Upload Options
                                <div>
                                    <ul class="checkboxList">
                                        <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_bgProcessing" name="fieldSettings_file_options_bgProcessing"> Process files in the background</label></li>
                                    </ul>

                                    <div class="fileTypeAdjustments">
                                        <div>
                                            Image Options
                                            <ul class="checkboxList">
                                                <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_multipleFiles" name="fieldSettings_file_options_multipleFiles"> Allow multiple files in single upload</label></li>
                                                <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_combine" name="fieldSettings_file_options_combine"> Combine into single PDF</label></li>
                                                <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_ocr" name="fieldSettings_file_options_ocr"> Optical character recognition (OCR)</label></li>
                                                <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_convert" name="fieldSettings_file_options_convert"> Convert Image file</label></li>
                                                <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_thumbnail" name="fieldSettings_file_options_thumbnail"> Create thumbnail</label></li>
                                            </ul>
                                        </div>
                                        <div>
                                            Audio Options
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="More options will be added for more processing information."></i>
                                            <ul class="checkboxList">
                                                <li>
                                                    <label class="checkbox">
                                                        <input type="checkbox" id="fieldSettings_file_options_convertAudio" name="fieldSettings_file_options_convert">
                                                        Convert or Modify Audio
                                                    </label>
                                                </li>
                                            </ul>
                                        </div>
                                        <div>
                                            Video Options
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="More options will be added for more processing information."></i>
                                            <ul class="checkboxList">
                                                <li>
                                                    <label class="checkbox">
                                                        <input type="checkbox" id="fieldSettings_file_options_convertVideo" name="fieldSettings_file_options_convertVideo">
                                                        Convert or Modify Video
                                                    </label>
                                                </li>
                                                <li>
                                                    <label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_videothumbnail" name="fieldSettings_file_options_videothumbnail">
                                                        Create thumbnail
                                                    </label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="control-group well well-small" id="fieldSettings_container_file_convertAudio">
                                <label for="fieldSettings_file_convertAudio">
                                    Audio Conversions
                                </label>

                                <div class="row-fluid audio">
                                    <div id="fieldSettings_container_file_convert_bitrate" class="row-fluid">
                                        <label class="span4">
                                            Change BitRate:
                                        </label>
                                        <select class="bitRate span8 last">
                                                <option value="">  Select a BitRate  </option>
                                                {local var="bitRates"}
                                        </select>
                                    </div>

                                    <div id="fieldSettings_container_file_convert_audioFormat" class="row-fluid">
                                        <label class="span4 left">
                                            Change Format:
                                        </label>
                                        <select class="audioFormat span8 last">
                                                <option value="">    Select a Format  </option>
                                                {local var="audioOptions"}
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="control-group well well-small" id="fieldSettings_container_file_convertVideo">
                                <label for="fieldSettings_file_convert">
                                    Video Options
                                </label>

                                <div class="row-fluid audio">
                                    <div id="fieldSettings_container_file_convert_bitrate" class="row-fluid">
                                        <label class="span4">
                                            Change BitRate:
                                        </label>
                                        <select class="videobitRate span8 last">
                                                <option value="">  Select a BitRate  </option>
                                                {local var="bitRates"}
                                        </select>
                                    </div>

                                    <div id="fieldSettings_container_file_convert_videoFormat" class="row-fluid">
                                        <label class="span4 left">
                                            Change Format:
                                        </label>
                                        <select class="videoFormat span8 last">
                                                <option value="">    Select a Format  </option>
                                                {local var="videoTypes"}
                                        </select>
                                    </div>

                                    <p> Video Size <i class="icon-question-sign formatSettings"> </i>

                                        <div class="row-fluid formatSettingsHelp alert alert-block">
                                            <span class="span6">
                                            <strong> Wide Screen </strong>
                                                <ul>
                                                    <li> 240p: 426x240 (16:9) </li>
                                                    <li> 360p: 640x360 (16:9) </li>
                                                    <li> 480p: 854x480 (16:9) </li>
                                                    <li> 720p: 1280x720 (16:9) </li>
                                                    <li> 1080p: 1920x1080 (16:9) </li>
                                                </ul>
                                            </span>
                                            <span class="span6">
                                            <strong> Standard Definition </strong>
                                                <ul>
                                                    <li> 426x320 (4:3) </li>
                                                    <li> 640x480 (4:3) </li>
                                                    <li> 854x640 (4:3) </li>
                                                    <li> 1280x960 (4:3) </li>
                                                </ul>
                                            </span>
                                        </div>

                                    <div class="row-fluid" id="fieldSettings_file_videoThumbnail">
                                    <span class="span6">
                                        <label for="fieldSettings_file_video_height">
                                            Height (px)
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_video_height" name="fieldSettings_file_video_height" min="0" />
                                    </span>
                                    <span class="span6">
                                        <label for="fieldSettings_file_video_width">
                                            Width (px)
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_video_width" name="fieldSettings_file_video_width" min="0" />
                                    </span>
                                    </div>
                                    <div class="row-fluid" id="fieldSettings_file_videoThumbnail">
                                    <span class="span12">
                                        <label for="fieldSettings_file_video_aspectRatio"> Aspect Ratio </label>
                                            <select class="videoAspectRatio span12 last">
                                                <option value="">     Select an Aspect Ratio    </option>
                                                <option value="4:3">  Standard Definition - 4:3 </option>
                                                <option value="16:9"> Wide Screen - 16:9        </option>
                                            </select>
                                    </span>
                                    </div>

                                </div>
                            </div>


                            <div class="control-group well well-small" id="fieldSettings_container_file_videoThumbnail">
                                <label for="fieldSettings_file_thumbnail">
                                    Video Thumbnail Options
                                    <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Video thumbnails will automatically be generated, please select
                                    the number of thumbnails to generate and specify the details of the thumbnails themselves.  If you need to upload your own thumbnail image
                                    of the video please create an extra file upload field named thumbnail and use the image settings."></i>
                                </label>
                                <p>  </p>
                                <div class="row-fluid" id="fieldSettings_file_videoThumbnail">
                                    <span class="span4">
                                        <label for="fieldSettings_file_video_frames">
                                            Number
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The number of frames that a thumbnail will be grabbed.  Max 10."></i>
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_video_frames" name="fieldSettings_file_video_frames" min="0" />
                                    </span>
                                    <span class="span4">
                                        <label for="fieldSettings_file_video_thumbheight">
                                            Height (px)
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_video_thumbheight" name="fieldSettings_file_video_thumbheight" min="0" />
                                    </span>
                                    <span class="span4">
                                        <label for="fieldSettings_file_video_thumbwidth">
                                            Width (px)
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_video_thumbwidth" name="fieldSettings_file_video_thumbwidth" min="0" />
                                    </span>
                                </div>
                                <div class="row-fluid" id="fieldSettings_video_thumbnail">
                                    <span class="span12">
                                        <label for="fieldSettings_file_video_formatThumb">
                                            Format
                                        </label>
                                        <select class="input-block-level" id="fieldSettings_file_video_formatThumb" name="fieldSettings_file_video_formatThumb">
                                            <option value="">Select Format</option>
                                            {local var="videoThumbs"}
                                        </select>
                                    </span>
                                </div>
                            </div>


                            <div class="control-group well well-small" id="fieldSettings_container_file_convert">
                                <label for="fieldSettings_file_convert">
                                    Conversions
                                </label>

                                <div class="row-fluid">
                                    <div class="span3" id="fieldSettings_container_file_convert_height">
                                        <label for="fieldSettings_file_convert_height">
                                            Max Height (px)
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_convert_height" name="fieldSettings_file_convert_height" min="1" />
                                    </div>

                                    <div class="span3" id="fieldSettings_container_file_convert_width">
                                        <label for="fieldSettings_file_convert_width">
                                            Max Width (px)
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_convert_width" name="fieldSettings_file_convert_width" min="1" />
                                    </div>

                                    <div class="span3" id="fieldSettings_container_file_convert_reolution">
                                        <label for="fieldSettings_file_convert_resolution">
                                            Resolution (DPI)
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_convert_resolution" name="fieldSettings_file_convert_resolution" min="1" />
                                    </div>

                                    <div class="span3" id="fieldSettings_container_file_convert_extension">
                                        <label for="fieldSettings_file_convert_format">
                                            Format
                                        </label>
                                    <select class="input-block-level" id="fieldSettings_file_convert_format" name="fieldSettings_file_convert_format">
                                            <option value="">Select Format</option>
                                            {local var="conversionFormats"}
                                        </select>
                                    </div>
                                </div>

                                <ul class="checkboxList">
                                    <li>
                                        <label class="checkbox"><input type="checkbox" id="fieldSettings_file_convert_watermark" name="fieldSettings_file_convert_watermark">Watermark</label>
                                        <div class="row-fluid">
                                            <div class="span6">
                                                <label for="fieldSettings_file_watermark_image">
                                                    Image
                                                </label>
                                                <select class="input-block-level" id="fieldSettings_file_watermark_image" name="fieldSettings_file_watermark_image">
                                                    <option value="">Select Image</option>
                                                    {local var="watermarkList"}
                                                </select>
                                            </div>
                                            <div class="span6">
                                                <label for="fieldSettings_file_watermark_location">
                                                    Location
                                                </label>
                                                <select class="input-block-level" id="fieldSettings_file_watermark_location" name="fieldSettings_file_watermark_location">
                                                    <option value="">Select Location</option>
                                                    {local var="imageLocations"}
                                                </select>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <label class="checkbox"><input type="checkbox" id="fieldSettings_file_convert_border" name="fieldSettings_file_convert_border"> Border</label>
                                        <div class="row-fluid">
                                            <div class="span4">
                                                <label for="fieldSettings_file_border_height">
                                                    Height (px)
                                                    <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Border width of the top and bottom."></i>
                                                </label>
                                                <input type="number" class="input-block-level" id="fieldSettings_file_border_height" name="fieldSettings_file_border_height" min="0" />
                                            </div>

                                            <div class="span4">
                                                <label for="fieldSettings_file_border_width">
                                                    Width (px)
                                                    <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Border width of the left and right."></i>
                                                </label>
                                                <input type="number" class="input-block-level" id="fieldSettings_file_border_width" name="fieldSettings_file_border_width" min="0" />
                                            </div>

                                            <div class="span4">
                                                <label for="fieldSettings_file_border_color">
                                                    Color
                                                </label>
                                                <input type="color" class="input-block-level" id="fieldSettings_file_border_color" name="fieldSettings_file_border_color" />
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>


                            <div class="control-group well well-small" id="fieldSettings_container_file_thumbnail">
                                <label for="fieldSettings_file_thumbnail">
                                    Thumbnail Options
                                </label>
                                <div class="row-fluid" id="fieldSettings_file_thumbnail">
                                    <span class="span4">
                                        <label for="fieldSettings_file_thumbnail_height">
                                            Height (px)
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_thumbnail_height" name="fieldSettings_file_thumbnail_height" min="0" />
                                    </span>
                                    <span class="span4">
                                        <label for="fieldSettings_file_thumbnail_width">
                                            Width (px)
                                            <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                                        </label>
                                        <input type="number" class="input-block-level" id="fieldSettings_file_thumbnail_width" name="fieldSettings_file_thumbnail_width" min="0" />
                                    </span>
                                    <span class="span4">
                                        <label for="fieldSettings_file_thumbnail_format">
                                            Format
                                        </label>
                                        <select class="input-block-level" id="fieldSettings_file_thumbnail_format" name="fieldSettings_file_thumbnail_format">
                                            <option value="">Select Format</option>
                                            {local var="conversionFormats"}
                                        </select>
                                    </span>
                                </div>
                            </div>



                        <div class="row-fluid noHide">
                                <span class="span6">
                                    <div class="control-group well well-small" id="fieldSettings_container_options">
                                        Options
                                        <ul class="checkboxList">
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_required" name="fieldSettings_options_required"> Required</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Field is required to be filled out."></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_duplicates" name="fieldSettings_options_duplicates"> No duplicates</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Duplicate entries for this form are not allowed."></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_readonly" name="fieldSettings_options_readonly"> Read only</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Field is read-only, data is pulled from form definition on insert, previous revision on update. not from POST"></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_disabled" name="fieldSettings_options_disabled"> Disabled</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Field is disabled and not submitted to POST"></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_disabled_insert" name="fieldSettings_options_disabled_insert"> Disabled on Insert</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Field is hideen and disabled on insert forms."></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_disabled_update" name="fieldSettings_options_disabled_update"> Read only on Update</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Field is set to read only on update forms. Only read and inserted into the database on insert forms."></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_publicRelease" name="fieldSettings_options_publicRelease"> Public release</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Dependant on Export Script: Metadata check to determine if field should be exported to XML"></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_sortable" name="fieldSettings_options_sortable"> Sortable</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Field is Sortable in list table in MFCS."></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_searchable" name="fieldSettings_options_searchable"> Searchable</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Dependant on Export: Can search on this field in public facing repository."></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_displayTable" name="fieldSettings_options_displayTable"> Display in list table</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Field is displayed in the listing table in MFCS"></i></li>
                                            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_options_hidden" name="fieldSettings_options_hidden"> Hidden</label><i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="Input type is set to Hidden."></i></li>
                                        </ul>
                                    </div>
                                </span>
                                <span class="span6">
                                    <div class="control-group well well-small" id="fieldSettings_container_validation">
                                        <label for="fieldSettings_validation">
                                            Validation
                                        </label>
                                        <select class="input-block-level" id="fieldSettings_validation" name="fieldSettings_validation">
                                            {local var="validationTypes"}
                                        </select>
                                        <input type="text" class="input-block-level" id="fieldSettings_validationRegex" name="fieldSettings_validationRegex" placeholder="Enter a Regex" />
                                    </div>

                                    <!-- <div class="control-group well well-small" id="fieldSettings_container_access">
                                        <label for="fieldSettings_access">
                                            Allow Access
                                        </label>
                                        <select class="input-block-level" id="fieldSettings_access" name="fieldSettings_access" multiple>
                                        </select>
                                    </div> -->
                                </span>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane" id="formSettings">
                        <div class="row-fluid noHide">
                            <div class="control-group well well-small" id="formSettings_formTitle_container">
                                <label for="formSettings_formTitle">
                                    Form Title
                                    <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The form name is a unique value that is used to identify a form."></i>
                                </label>
                                <input type="text" class="input-block-level" id="formSettings_formTitle" name="formSettings_formTitle" value="{local var="formTitle"}" />
                                <span class="help-block hidden"></span>
                            </div>

                            <div class="control-group well well-small" id="formSettings_objectDisplayTitle_container">
                                <label for="formSettings_objectDisplayTitle">
                                    Display Title
                                </label>
                                <input type="text" class="input-block-level" id="formSettings_objectDisplayTitle" name="formSettings_objectDisplayTitle" value="{local var="displayTitle"}">
                                <span class="help-block hidden"></span>
                            </div>

                            <div class="control-group well well-small" id="formSettings_linkTitle_container">
                                <label for="formSettings_linkTitle">
                                    Link Title
                                </label>
                                <input type="text" class="input-block-level" id="formSettings_linkTitle" name="formSettings_linkTitle" value="{local var="linkTitle"}">
                                <span class="help-block hidden"></span>
                            </div>

                            <div class="control-group well well-small" id="formSettings_formDescription_container">
                                <label for="formSettings_formDescription">
                                    Form Description
                                    <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The form description explains the purpose of this form to users."></i>
                                </label>
                                <input type="text" class="input-block-level" id="formSettings_formDescription" name="formSettings_formDescription" value="{local var="formDescription"}" />
                                <span class="help-block hidden"></span>
                            </div>
                        </div>

                        <div class="row-fluid noHide">
                            <div class="span6">
                                <div class="control-group well well-small" id="formSettings_submitButton_container">
                                    <label for="formSettings_submitButton">
                                        Submit Button
                                        <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The text that is displayed on the form's submit button."></i>
                                    </label>
                                    <input type="text" class="input-block-level" id="formSettings_submitButton" name="formSettings_submitButton" value="{local var="submitButton"}" />
                                    <span class="help-block hidden"></span>
                                </div>
                            </div>

                            <div class="span6">
                                <div class="control-group well well-small" id="formSettings_updateButton_container">
                                    <label for="formSettings_updateButton">
                                        Update Button
                                        <i class="icon-question-sign" rel="tooltip" data-placement="right" data-title="The text that is displayed on the form's update button."></i>
                                    </label>
                                    <input type="text" class="input-block-level" id="formSettings_updateButton" name="formSettings_updateButton" value="{local var="updateButton"}" />
                                    <span class="help-block hidden"></span>
                                </div>
                            </div>
                        </div>

                        <div class="control-group well well-small" id="formSettings_objectTitleField_container">
                            <label for="formSettings_objectTitleField">
                                Title Field
                            </label>
                            <select class="input-block-level" id="formSettings_objectTitleField" name="formSettings_objectTitleField">
                                {local var="objectTitleFieldOptions"}
                            </select>
                            <span class="help-block hidden"></span>
                        </div>

                        <div class="row-fluid noHide">
                            <div class="control-group well well-small" id="formSettings_formContainer_container">
                                <ul class="checkboxList">
                                    <li><label class="checkbox" for="formSettings_formContainer"><input type="checkbox" id="formSettings_formContainer" name="formSettings_formContainer" {local var="formContainer"}> Act as Container</label></li>
                                    <li><label class="checkbox" for="formSettings_formProduction"><input type="checkbox" id="formSettings_formProduction" name="formSettings_formProduction" {local var="formProduction"}> Production Ready</label></li>
                                    <li><label class="checkbox" for="formSettings_formMetadata"><input type="checkbox" id="formSettings_formMetadata" name="formSettings_formMetadata" {local var="formMetadata"}> Metadata Form</label></li>
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