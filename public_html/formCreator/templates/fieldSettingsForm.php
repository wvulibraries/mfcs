<div class="#fieldSettingsForm dataSettings noHide">
    <div class="group noHide">
        <div class="control-group  " id="fieldSettings_container_name">
            <label for="fieldSettings_name">
                Field Name
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The field name is a unique value that is used to identify a field."></i>
            </label>
            <input type="text" class="input-block-level" id="fieldSettings_name" data-bindName="name" name="fieldSettings_name"/>
            <span class="help-block hidden"></span>
        </div>

        <div class="control-group " id="fieldSettings_container_label">
            <label for="fieldSettings_label">
                Field Label
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The field label tells your users what to enter in this field."></i>
            </label>
            <input type="text" class="input-block-level" id="fieldSettings_label" data-bindName="label" name="fieldSettings_label"/>
            <span class="help-block hidden"></span>
        </div>

        <div id="fieldSettings_container_value" class="control-group ">
            <label for="fieldSettings_value">
                Value
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="When the form is first displayed, this value will already be prepopulated."></i>
            </label>
            <a href="javascript:;" class="pull-right" onclick="$('#defaultValueVariables').modal('show')" id="fieldVariablesLink" style="display: none;">Variables</a>
            <input type="text" class="input-block-level" id="fieldSettings_value" name="fieldSettings_value" data-bindName="value"/>
            <span class="help-block hidden"></span>
        </div>

        <div class="control-group " id="fieldSettings_container_placeholder">
            <label for="fieldSettings_placeholder">
                Placeholder Text
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If there is no value in the field, this can tell your users what to input."></i>
            </label>
            <input type="text" class="input-block-level" id="fieldSettings_placeholder" name="fieldSettings_placeholder" data-bindName="placeholder"/>
            <span class="help-block hidden"></span>
        </div>

        <div class="control-group " id="fieldSettings_container_id">
            <label for="fieldSettings_id">
                CSS ID
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The ID is a unique value that can be used to identify a field."></i>
            </label>
            <input type="text" class="input-block-level" id="fieldSettings_id" name="fieldSettings_id" data-bindName="id"/>
            <span class="help-block hidden"></span>
        </div>

        <div class="control-group " id="fieldSettings_container_class">
            <label for="fieldSettings_class">
                CSS Classes
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Classes can be entered to give the field a different look and feel."></i>
            </label>
            <input type="text" class="input-block-level" id="fieldSettings_class" name="fieldSettings_class" data-bindName="class"/>
            <span class="help-block hidden"></span>
        </div>

        <div class="control-group " id="fieldSettings_container_style">
            <label for="fieldSettings_style">
                Local Styles
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="You can set any HTML styles and they will only apply to this field."></i>
            </label>
            <input type="text" class="input-block-level" id="fieldSettings_style" name="fieldSettings_style" data-bindName="style"/>
            <span class="help-block hidden"></span>
        </div>
    </div>

    <div class="group noHide">
        <div class="control-group" id="fieldSettings_container_style">
            <label for="fieldSettings_style">
                Field Help
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="You can set any help text you want displayed with this field. Any angle brackets in HTML text will be treated as HTML"></i>
            </label>

            <select class="input-block-level" id="fieldSettings_help_type" name="fieldSettings_help_type" data-bindName="helpType">
                <option value="">None</option>
                <option value="text">Plain text</option>
                <option value="html">HTML text</option>
                <option value="web">Webpage (URL)</option>
            </select>

            <input type="text" class="input-block-level hide" id="fieldSettings_help_text" name="fieldSettings_help_text" data-bindName="help">

            <textarea class="input-block-level hide" id="fieldSettings_help_html" name="fieldSettings_help_html" data-bindName="help"></textarea>

            <input type="text" class="input-block-level hide" id="fieldSettings_help_url" name="fieldSettings_help_url" data-bindName="help" placeholder="http://example.com">

            <span class="help-block hidden"></span>
        </div>
    </div>

    <div class="group">
        <div class="control-group" id="fieldSettings_container_choices">
            <label for="fieldSettings_choices">
                Choices
            </label>

            <select class="input-block-level" id="fieldSettings_choices_type" name="fieldSettings_choices_type" data-bindName="choicesType">
                <option value="manual">Manual</option>
                <option value="form">Another Form</option>
            </select>

            <label>
                <input type="checkbox" id="fieldSettings_choices_null" name="fieldSettings_choices_null" data-bindName="choicesNull"> Include 'Make a selection' placeholder
            </label>

            <div id="fieldSettings_choices_manual"></div>

            <div id="fieldSettings_choices_form">

                <label for="fieldSettings_choices_formSelect">
                    Select a Form
                </label>
                <select class="input-block-level" id="fieldSettings_choices_formSelect" name="fieldSettings_choices_formSelect" data-bindName="choicesForm">
                    {local var="formsOptions"}
                </select>

                <label for="fieldSettings_choices_fieldSelect">
                    Select a Field
                </label>
                <select class="input-block-level" id="fieldSettings_choices_fieldSelect" name="fieldSettings_choices_fieldSelect" data-bindName="choicesField">
                </select>

                <label for="fieldSettings_choices_fieldDefault">
                    Default Value
                </label>
                <input type="test" id="fieldSettings_choices_fieldDefault" name="fieldSettings_choices_fieldDefault" data-bindName="choicesDefault">
            </div>
            <span class="help-block hidden"></span>
        </div>
    </div>


    <div class="group">
        <div class="control-group " id="fieldSettings_container_range">
            <span class="span3">
                <label for="fieldSettings_range_min">
                    Min
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_range_min" name="fieldSettings_range_min" min="0" data-bindName="min"/>
            </span>
            <span class="span3">
                <label for="fieldSettings_range_max">
                    Max
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_range_max" name="fieldSettings_range_max" min="0" data-bindName="max" />
            </span>
            <span class="span2">
                <label for="fieldSettings_range_step">
                    Step
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_range_step" name="fieldSettings_range_step" min="0" data-bindName="step"/>
            </span>
            <span class="span6">
                <label for="fieldSettings_range_format">
                    Format
                </label>
                <select class="input-block-level" id="fieldSettings_range_format" name="fieldSettings_range_format" data-bindName="format"></select>
            </span>
        </div>
    </div>

    <div class="control-group" id="fieldSettings_container_externalUpdate">
        <label for="fieldSettings_externalUpdate">
            Update External Form
            <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="When the value of this field changes, update the selected form and field."></i>
        </label>
        <div id="fieldSettings_externalUpdate_form">
            <label for="fieldSettings_externalUpdate_formSelect">
                Select a Form
            </label>
            <select class="input-block-level" id="fieldSettings_externalUpdate_formSelect" name="fieldSettings_externalUpdate_formSelect" data-bindName="externalUpdateForm">
                <option value="">None</option>
                {local var="formsOptions"}
            </select>

            <label for="fieldSettings_externalUpdate_fieldSelect">
                Select a Field
            </label>
            <select class="input-block-level" id="fieldSettings_externalUpdate_fieldSelect" name="fieldSettings_externalUpdate_fieldSelect" data-bindName="externalUpdateField">
            </select>
        </div>
        <span class="help-block hidden"></span>
    </div>

    <div class="control-group " id="fieldSettings_container_idno">
        <label for="fieldSettings_idno_managedBy">
            ID Number Options
        </label>
        <select class="input-block-level" id="fieldSettings_idno_managedBy" name="fieldSettings_idno_managedBy" data-bindName="managedBy">
            <option value="system">Managed by System</option>
            <option value="user">Managed by User</option>
        </select>

        <div class="span6" id="fieldSettings_container_idno_format">
            <label for="fieldSettings_idno_format">
                Format
            </label>
            <input type="text" class="input-block-level" id="fieldSettings_idno_format" name="fieldSettings_idno_format" placeholder="st_###"  data-bindName="idnoFormat"/>
        </div>

        <div class="span6" id="fieldSettings_container_idno_startIncrement">
            <label for="fieldSettings_idno_startIncrement">
                Auto Increment Start
            </label>
            <input type="number" class="input-block-level" id="fieldSettings_idno_startIncrement" name="fieldSettings_idno_startIncrement" min="0"  data-bindName="startIncrement"/>
        </div>

        <div class="hidden" id="fieldSettings_container_idno_confirm">
            <label class="checkbox">
                <input type="checkbox" id="fieldSettings_idno_confirm" name="fieldSettings_idno_confirm"  data-bindName="idnoConfirm">
                Are you sure? <span class="text-warning">This change could cause potential conflicts.</span>
            </label>
        </div>
    </div>

    <div class="control-group " id="fieldSettings_container_file_allowedExtensions">
        <div id="allowedExtensionsAlert" style="display:none;" class="alert alert-error">No allowed extensions included! Currently, no files will be uploadable!</div>
        <label for="fieldSettings_file_allowedExtensions">
            Allowed Extensions
        </label>
        <div id="fieldSettings_file_allowedExtensions"></div>
        <span class="help-block hidden"></span>
    </div>

    <div class="control-group " id="fieldSettings_container_file_options">
        <p> File Upload Options </p>

        <ul class="checkboxList">
            <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_bgProcessing" name="fieldSettings_file_options_bgProcessing"  data-bindName="bgProcessing"> Process files in the background</label></li>
        </ul>

        <div class="fileTypeAdjustments">
            <div>
                <p> Image Options </p>
                <ul class="checkboxList">
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_multipleFiles" name="fieldSettings_file_options_multipleFiles" data-bindName="multipleFiles"> Allow multiple files in single upload</label></li>
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_combine" name="fieldSettings_file_options_combine" data-bindName="combine"> Combine into single PDF</label></li>
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_ocr" name="fieldSettings_file_options_ocr" data-bindName="ocr"> Optical character recognition (OCR)</label></li>
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_convert" name="fieldSettings_file_options_convert" data-bindName="convert"> Convert Image file</label></li>
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_thumbnail" name="fieldSettings_file_options_thumbnail" data-bindName="thumbnail"> Create thumbnail</label></li>
                </ul>
            </div>
            <div>
                <p> Audio Options </p>
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="More options will be added for more processing information."></i>
                <ul class="checkboxList">
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_file_options_convertAudio" name="fieldSettings_file_options_convert" data-bindName="convertAudio">
                            Convert or Modify Audio
                        </label>
                    </li>
                </ul>
            </div>
            <div>
                <p> Video Options </p>
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="More options will be added for more processing information."></i>
                <ul class="checkboxList">
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_file_options_convertVideo" name="fieldSettings_file_options_convertVideo" data-bindName="convertVideo">
                            Convert or Modify Video
                        </label>
                    </li>
                    <li>
                        <label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_videothumbnail" name="fieldSettings_file_options_videothumbnail" data-bindName="videoThumbnail">
                            Create thumbnail
                        </label>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="control-group" id="fieldSettings_container_file_convertAudio">
        <p> Audio Conversions </p>

        <div class="audio">
            <div id="fieldSettings_container_file_convert_bitrate" class="">
                <label class="span4">
                    Change BitRate:
                </label>
                <select class="bitRate span8 last" data-bindName="bitRate">
                        <option value="">  Select a BitRate  </option>
                        {local var="bitRates"}
                </select>
            </div>

            <div id="fieldSettings_container_file_convert_audioFormat" class="">
                <label class="span4 left">
                    Change Format:
                </label>
                <select class="audioFormat span8 last" data-bindName="audioFormat">
                        <option value="">    Select a Format  </option>
                        {local var="audioOptions"}
                </select>
            </div>
        </div>
    </div>


    <div class="control-group " id="fieldSettings_container_file_convertVideo">

        <p> Video Options </p>

        <div class="audio">
            <div id="fieldSettings_container_file_convert_bitrate" class="">
                <label class="span4">
                    Change BitRate:
                </label>
                <select class="videobitRate span8 last" data-bindName="videobitRate">
                        <option value="">  Select a BitRate  </option>
                        {local var="bitRates"}
                </select>
            </div>

            <div id="fieldSettings_container_file_convert_videoFormat" class="">
                <label class="span4 left">
                    Change Format:
                </label>
                <select class="videoFormat span8 last" data-bindName="videoFormat">
                        <option value="">    Select a Format  </option>
                        {local var="videoTypes"}
                </select>
            </div>

            <div>
                Video Size <i class="fa fa-question-circle formatSettings"> </i>

                <div class=" formatSettingsHelp alert alert-block">
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
            </div>

            <div class="" id="fieldSettings_file_videoThumbnail">
            <span class="span6">
                <label for="fieldSettings_file_video_height">
                    Height (px)
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_video_height" name="fieldSettings_file_video_height" min="0" data-bindName="videoHeight"/>
            </span>
            <span class="span6">
                <label for="fieldSettings_file_video_width">
                    Width (px)
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_video_width" name="fieldSettings_file_video_width" min="0"  data-bindName="videoWidth"/>
            </span>
            </div>
            <div class="" id="fieldSettings_file_videoThumbnail">
                <span class="span12">
                    <label for="fieldSettings_file_video_aspectRatio"> Aspect Ratio </label>
                        <select class="videoAspectRatio span12 last" data-bindName="aspectRatio">
                            <option value="">     Select an Aspect Ratio    </option>
                            <option value="4:3">  Standard Definition - 4:3 </option>
                            <option value="16:9"> Wide Screen - 16:9        </option>
                        </select>
                </span>
            </div>
        </div>
    </div>


    <div class="control-group " id="fieldSettings_container_file_videoThumbnail">
        <label for="fieldSettings_file_thumbnail">
            Video Thumbnail Options
            <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Video thumbnails will automatically be generated, please select
            the number of thumbnails to generate and specify the details of the thumbnails themselves.  If you need to upload your own thumbnail image
            of the video please create an extra file upload field named thumbnail and use the image settings."></i>
        </label>
        <div class="" id="fieldSettings_file_videoThumbnail">
            <span class="span4">
                <label for="fieldSettings_file_video_frames">
                    Number
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The number of frames that a thumbnail will be grabbed.  Max 10."></i>
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_video_frames" name="fieldSettings_file_video_frames" min="0" data-bindName="videoThumbFrames"/>
            </span>
            <span class="span4">
                <label for="fieldSettings_file_video_thumbheight">
                    Height (px)
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_video_thumbheight" name="fieldSettings_file_video_thumbheight" min="0" data-bindName="videoThumbHeight"/>
            </span>
            <span class="span4">
                <label for="fieldSettings_file_video_thumbwidth">
                    Width (px)
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_video_thumbwidth" name="fieldSettings_file_video_thumbwidth" min="0" data-bindName="videoThumbWidth"/>
            </span>
        </div>
        <div class="" id="fieldSettings_video_thumbnail">
            <span class="span12">
                <label for="fieldSettings_file_video_formatThumb">
                    Format
                </label>
                <select class="input-block-level" id="fieldSettings_file_video_formatThumb" name="fieldSettings_file_video_formatThumb" data-bindName="videoFormatThumb">
                    <option value="">Select Format</option>
                    {local var="videoThumbs"}
                </select>
            </span>
        </div>
    </div>


    <div class="control-group " id="fieldSettings_container_file_convert">

        <p> Conversions </p>

        <div>
            <div class="span3" id="fieldSettings_container_file_convert_height" >
                <label for="fieldSettings_file_convert_height">
                    Max Height (px)
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_convert_height" name="fieldSettings_file_convert_height" min="1" data-bindName="convertHeight"/>
            </div>

            <div class="span3" id="fieldSettings_container_file_convert_width">
                <label for="fieldSettings_file_convert_width">
                    Max Width (px)
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_convert_width" name="fieldSettings_file_convert_width" min="1" data-bindName="convertWidth"/>
            </div>

            <div class="span3" id="fieldSettings_container_file_convert_reolution">
                <label for="fieldSettings_file_convert_resolution">
                    Resolution (DPI)
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_convert_resolution" name="fieldSettings_file_convert_resolution" min="72" data-bindName="convertResolution"/>
            </div>

            <div class="span3" id="fieldSettings_container_file_convert_extension">
                <label for="fieldSettings_file_convert_format">
                    Format
                </label>
                <select class="input-block-level" id="fieldSettings_file_convert_format" name="fieldSettings_file_convert_format" data-bindName="convertFormat">
                    <option value="">Select Format</option>
                    {local var="conversionFormats"}
                </select>
            </div>
        </div>

        <ul class="checkboxList">
            <li>
                <label class="checkbox"><input type="checkbox" id="fieldSettings_file_convert_watermark" name="fieldSettings_file_convert_watermark" data-bindName="watermark">Watermark</label>
                <div class="">
                    <div class="span6">
                        <label for="fieldSettings_file_watermark_image">
                            Image
                        </label>
                        <select class="input-block-level" id="fieldSettings_file_watermark_image" name="fieldSettings_file_watermark_image" data-bindName="watermarkImage">
                            <option value="">Select Image</option>
                            {local var="watermarkList"}
                        </select>
                    </div>
                    <div class="span6">
                        <label for="fieldSettings_file_watermark_location">
                            Location
                        </label>
                        <select class="input-block-level" id="fieldSettings_file_watermark_location" name="fieldSettings_file_watermark_location" data-bindName="watermarkLocation">
                            <option value="">Select Location</option>
                            {local var="imageLocations"}
                        </select>
                    </div>
                </div>
            </li>
            <li>
                <label class="checkbox">
                    <input type="checkbox" id="fieldSettings_file_convert_border" name="fieldSettings_file_convert_border" data-bindName="border">
                    Border
                </label>
                <div class="">
                    <div class="span4">
                        <label for="fieldSettings_file_border_height">
                            Height (px)
                            <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Border width of the top and bottom."></i>
                        </label>
                        <input type="number" class="input-block-level" id="fieldSettings_file_border_height" name="fieldSettings_file_border_height" min="0" data-bindName="borderHeight"/>
                    </div>

                    <div class="span4">
                        <label for="fieldSettings_file_border_width">
                            Width (px)
                            <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Border width of the left and right."></i>
                        </label>
                        <input type="number" class="input-block-level" id="fieldSettings_file_border_width" name="fieldSettings_file_border_width" min="0" data-bindName="borderWidth"/>
                    </div>

                    <div class="span4">
                        <label for="fieldSettings_file_border_color">
                            Color
                        </label>
                        <input type="color" class="input-block-level" id="fieldSettings_file_border_color" name="fieldSettings_file_border_color" data-bindName="borderColor"/>
                    </div>
                </div>
            </li>
        </ul>
    </div>

    <div class="control-group " id="fieldSettings_container_file_thumbnail">
        <label for="fieldSettings_file_thumbnail">
            Thumbnail Options
        </label>
        <div class="" id="fieldSettings_file_thumbnail">
            <span class="span4">
                <label for="fieldSettings_file_thumbnail_height">
                    Height (px)
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_thumbnail_height" name="fieldSettings_file_thumbnail_height" min="0" data-bindName="thumbnailHeight"/>
            </span>
            <span class="span4">
                <label for="fieldSettings_file_thumbnail_width">
                    Width (px)
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_file_thumbnail_width" name="fieldSettings_file_thumbnail_width" min="0" data-bindName="thumbnailWidth"/>
            </span>
            <span class="span4">
                <label for="fieldSettings_file_thumbnail_format">
                    Format
                </label>
                <select class="input-block-level" id="fieldSettings_file_thumbnail_format" name="fieldSettings_file_thumbnail_format" data-bindName="thumbnailFormat">
                    <option value="">Select Format</option>
                    {local var="conversionFormats"}
                </select>
            </span>
        </div>
    </div>


    <div class="control-group">
        <span class="span6">
            <div class="control-group " id="fieldSettings_container_options">
                Options
                <ul class="checkboxList">
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_required" name="fieldSettings_options_required" data-bindName="required"> Required
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Field is required to be filled out."></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_duplicates" name="fieldSettings_options_duplicates" data-bindName="duplicates"> No duplicates
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Duplicate entries for this form are not allowed."></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_readonly" name="fieldSettings_options_readonly" data-bindName="readonly"> Read only
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Field is read-only, data is pulled from form definition on insert, previous revision on update. not from POST"></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_disabled" name="fieldSettings_options_disabled" data-bindName="disabled"> Disabled
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Field is disabled and not submitted to POST"></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_disabled_insert" name="fieldSettings_options_disabled_insert" data-bindName="disabledInsert"> Disabled on Insert
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Field is hideen and disabled on insert forms."></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_disabled_update" name="fieldSettings_options_disabled_update" data-bindName="disabledUpdate"> Read only on Update
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Field is set to read only on update forms. Only read and inserted into the database on insert forms."></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_publicRelease" name="fieldSettings_options_publicRelease" data-bindName="publicRelease"> Public release</label>
                            <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Dependant on Export Script: Metadata check to determine if field should be exported to XML"></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_sortable" name="fieldSettings_options_sortable" data-bindName="sortable"> Sortable
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Field is Sortable in list table in MFCS."></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_searchable" name="fieldSettings_options_searchable" data-bindName="searchable"> Searchable
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Dependant on Export: Can search on this field in public facing repository."></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_displayTable" name="fieldSettings_options_displayTable" data-bindName="displayTable"> Display in list table
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Field is displayed in the listing table in MFCS"></i>
                    </li>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_options_hidden" name="fieldSettings_options_hidden" data-bindName="hidden"> Hidden
                        </label>
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Input type is set to Hidden."></i>
                    </li>
                </ul>
            </div>
        </span>
        <span class="span6">
            <div class="control-group " id="fieldSettings_container_validation">
                <label for="fieldSettings_validation">
                    Validation
                </label>
                <select class="input-block-level" id="fieldSettings_validation" name="fieldSettings_validation" data-bindName="validation">
                    {local var="validationTypes"}
                </select>
                <input type="text" class="input-block-level" id="fieldSettings_validationRegex" name="fieldSettings_validationRegex" placeholder="Enter a Regex" data-bindName="validationRegex"/>
            </div>

            <!-- <div class="control-group " id="fieldSettings_container_access">
                <label for="fieldSettings_access">
                    Allow Access
                </label>
                <select class="input-block-level" id="fieldSettings_access" name="fieldSettings_access" multiple>
                </select>
            </div> -->
        </span>
    </div>
</div>






<!-- Modals ===================================================================  -->

 <div id="defaultValueVariables" class="modal hide" rel="modal" data-show="false">
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