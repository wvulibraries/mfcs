<div class="#fieldSettingsForm dataSettings">

    <div class="group noHide default">
        <div class="row">
            <div class="control-group span6" id="fieldSettings_container_name">
                <label for="fieldSettings_name">
                    Field Name
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The field name is a unique value that is used to identify a field."></i>
                </label>
                <input type="text" class="input-block-level" id="fieldSettings_name" data-bindModel="name" name="fieldSettings_name"/>
                <span class="help-block hidden"></span>
            </div>

            <div class="control-group span6" id="fieldSettings_container_label">
                <label for="fieldSettings_label">
                    Field Label
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The field label tells your users what to enter in this field."></i>
                </label>
                <input type="text" class="input-block-level" id="fieldSettings_label" data-bindModel="label" name="fieldSettings_label"/>
                <span class="help-block hidden"></span>
            </div>
        </div>

        <div class="row">
            <div id="fieldSettings_container_value" class="control-group span6">
                <label for="fieldSettings_value">
                    Value
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="When the form is first displayed, this value will already be prepopulated."></i>
                </label>
                <a href="javascript:void(0);" class="pull-right" onclick="$('#defaultValueVariables').modal('show')" id="fieldVariablesLink" style="display: none;">Variables</a>
                <input type="text" class="input-block-level" id="fieldSettings_value" name="fieldSettings_value" data-bindModel="value"/>
                <span class="help-block hidden"></span>
            </div>

            <div class="control-group span6" id="fieldSettings_container_placeholder">
                <label for="fieldSettings_placeholder">
                    Placeholder Text
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If there is no value in the field, this can tell your users what to input."></i>
                </label>
                <input type="text" class="input-block-level" id="fieldSettings_placeholder" name="fieldSettings_placeholder" data-bindModel="placeholder"/>
                <span class="help-block hidden"></span>
            </div>
        </div>

        <div class="row">
            <div class="control-group span6" id="fieldSettings_container_id">
                <label for="fieldSettings_id">
                    CSS ID
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="The ID is a unique value that can be used to identify a field."></i>
                </label>
                <input type="text" class="input-block-level" id="fieldSettings_id" name="fieldSettings_id" data-bindModel="id"/>
                <span class="help-block hidden"></span>
            </div>

            <div class="control-group span6" id="fieldSettings_container_class">
                <label for="fieldSettings_class">
                    CSS Classes
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Classes can be entered to give the field a different look and feel."></i>
                </label>
                <input type="text" class="input-block-level" id="fieldSettings_class" name="fieldSettings_class" data-bindModel="class"/>
                <span class="help-block hidden"></span>
            </div>
        </div>

        <div class="row">
            <div class="control-group " id="fieldSettings_container_style">
                <label for="fieldSettings_style">
                    Local Styles
                    <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="You can set any HTML styles and they will only apply to this field."></i>
                </label>
                <input type="text" class="input-block-level" id="fieldSettings_style" name="fieldSettings_style" data-bindModel="style"/>
                <span class="help-block hidden"></span>
            </div>
        </div>
    </div>

    <div class="group noHide default">
        <div class="control-group" id="fieldSettings_container_style">
            <label for="fieldSettings_style">
                Field Help
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="You can set any help text you want displayed with this field. Any angle brackets in HTML text will be treated as HTML"></i>
            </label>

            <select class="input-block-level" id="fieldSettings_help_type" name="fieldSettings_help_type" data-bindModel="helpType">
                <option value="none">None</option>
                <option value="text">Plain text</option>
                <option value="html">HTML text</option>
                <option value="web">Webpage (URL)</option>
            </select>
            <br>
            <input type="hidden" class="input-block-level" id="fieldSettings_help_text"    name="fieldSettings_help_text" data-bindModel="help" />
            <textarea class="input-block-level" id="fieldSettings_help_textarea" name="fieldSettings_help_text" data-bindModel="helpText"> </textarea>
            <input type="url" class="input-block-level" id="fieldSettings_help_url" name="fieldSettings_help_text" placeholder="http://www.website.com" data-bindModel="helpURL"/>
            <span class="help-block hidden"></span>
        </div>
    </div>

    <div class="group default noHide">
        <div class="control-group" id="fieldSettings_container_metadataSchema">
            <label for="fieldSettings_metadataType">
               Metadata Schema
            </label>
            <input type="hidden" class="metadataStandard" name="fieldSettings_metadataStandard" data-bindModel="metadataStandard">
            <div id="metadataStandard_options" class="metadataStandard_options"> </div>
        </div>
    </div>

    <div class="group">
        <div class="control-group" id="fieldSettings_container_choices">
            <label for="fieldSettings_choices">
                Choices
            </label>
            <input type="hidden" class="choicesOptions" name="fieldSettings_choicesOptions" data-bindModel="choicesOptions">
            <select class="input-block-level" id="fieldSettings_choices_type" name="fieldSettings_choices_type" data-bindModel="choicesType" >
                <option value="manual">Manual</option>
                <option value="form">Another Form</option>
            </select>

            <label>
                <input type="checkbox" id="fieldSettings_choices_null" name="fieldSettings_choices_null" data-bindModel="choicesNull"> Include 'Make a selection' placeholder
            </label>

            <div class="manual_choices">
                <div id="fieldSettings_choices_manual"></div>
            </div>

            <div id="fieldSettings_choices_form" class="form_choices">

                <label for="fieldSettings_choices_formSelect">
                    Select a Form
                </label>
                <select class="input-block-level" id="fieldSettings_choices_formSelect" name="fieldSettings_choices_formSelect" data-bindModel="choicesForm">
                    {local var="formsOptions"}
                </select>

                <label for="fieldSettings_choices_fieldSelect">
                    Select a Field
                </label>
                <select class="input-block-level" id="fieldSettings_choices_fieldSelect" name="fieldSettings_choices_fieldSelect" data-bindModel="choicesField">
                </select>

                <label for="fieldSettings_choices_fieldDefault">
                    Default Value
                </label>
                <input type="text" id="fieldSettings_choices_fieldDefault" name="fieldSettings_choices_fieldDefault" data-bindModel="choicesFieldDefault">
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
                <input type="number" class="input-block-level" id="fieldSettings_range_min" name="fieldSettings_range_min" min="0" data-bindModel="min"/>
            </span>
            <span class="span3">
                <label for="fieldSettings_range_max">
                    Max
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_range_max" name="fieldSettings_range_max" min="0" data-bindModel="max" />
            </span>
            <span class="span2">
                <label for="fieldSettings_range_step">
                    Step
                </label>
                <input type="number" class="input-block-level" id="fieldSettings_range_step" name="fieldSettings_range_step" min="0" data-bindModel="step"/>
            </span>
            <span class="span6">
                <label for="fieldSettings_range_format">
                    Format
                </label>
                <select class="input-block-level" id="fieldSettings_range_format" name="fieldSettings_range_format" data-bindModel="format"></select>
            </span>
        </div>
    </div>

    <div class="group">
        <div class="control-group" id="fieldSettings_container_externalUpdate">
            <label for="fieldSettings_externalUpdate">
                Update External Form
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="When the value of this field changes, update the selected form and field."></i>
            </label>
            <div id="fieldSettings_externalUpdate_form">
                <label for="fieldSettings_externalUpdate_formSelect">
                    Select a Form
                </label>
                <select class="input-block-level" id="fieldSettings_externalUpdate_formSelect" name="fieldSettings_externalUpdate_formSelect" data-bindModel="externalUpdateForm">
                    <option value="">None</option>
                    {local var="formsOptions"}
                </select>

                <label for="fieldSettings_externalUpdate_fieldSelect">
                    Select a Field
                </label>
                <select class="input-block-level" id="fieldSettings_externalUpdate_fieldSelect" name="fieldSettings_externalUpdate_fieldSelect" data-bindModel="externalUpdateField">
                </select>
            </div>
            <span class="help-block hidden"></span>
        </div>
    </div>

    <div class="group">
        <div class="control-group " id="fieldSettings_container_idno">
            <label for="fieldSettings_idno_managedBy">
                ID Number Options
            </label>
            <select class="input-block-level" id="fieldSettings_idno_managedBy" name="fieldSettings_idno_managedBy" data-bindModel="managedBy">
                <option value="system">Managed by System</option>
                <option value="user">Managed by User</option>
            </select>

            <div class="row">
                <div class="span6" id="fieldSettings_container_idno_format">
                    <label for="fieldSettings_idno_format">
                        Format
                    </label>
                    <input type="text" class="input-block-level" id="fieldSettings_idno_format" name="fieldSettings_idno_format" placeholder="st_###"  data-bindModel="idnoFormat"/>
                </div>

                <div class="span6" id="fieldSettings_container_idno_startIncrement">
                    <label for="fieldSettings_idno_startIncrement">
                        Auto Increment Start
                    </label>
                    <input type="number" class="input-block-level" id="fieldSettings_idno_startIncrement" name="fieldSettings_idno_startIncrement" min="0"  data-bindModel="startIncrement"/>
                </div>
            </div>

            <div class="row idnoConfirm hidden" id="fieldSettings_container_idno_confirm">
                <label class="checkbox">
                    <input type="checkbox" id="fieldSettings_idno_confirm" name="fieldSettings_idno_confirm"  data-bindModel="idnoConfirm">
                    Are you sure? <span class="text-warning">This change could cause potential conflicts.</span>
                </label>
            </div>
        </div>
    </div>

    <div class="group">
        <div class="control-group " id="fieldSettings_container_file_allowedExtensions">
            <div id="allowedExtensionsAlert" style="display:none;" class="alert alert-error">Warning! If you delete all of the extensions no files will be uploadable! Change the options to make sure a file is uploaded or delete the file upload field. </div>
            <label for="fieldSettings_file_allowedExtensions">
                Allowed Extensions
            </label>
            <input type="hidden" class="allowedExtensions" name="fieldSettings_allowedExtensions" data-bindModel="allowedExtensions">
            <div id="fieldSettings_file_allowedExtensions"></div>
            <span class="help-block hidden"></span>
        </div>
    </div>

    <div class="group">
        <div class="control-group " id="fieldSettings_container_file_options">

            <div class="microGroup">
                <strong> File Upload Options </strong>
                <ul>
                    <li> <label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_bgProcessing" name="fieldSettings_file_options_bgProcessing"  data-bindModel="bgProcessing"> Process files in the background</label> </li>
                </ul>
            </div>

            <div class="microGroup uxOptions">
                <strong> Image Options </strong>
                <ul>
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_multipleFiles" name="fieldSettings_file_options_multipleFiles" data-bindModel="multipleFiles"> Allow multiple files in single upload</label></li>
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_combine" name="fieldSettings_file_options_combine" data-bindModel="combine"> Combine into single PDF</label></li>
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_ocr" name="fieldSettings_file_options_ocr" data-bindModel="ocr"> Optical character recognition (OCR)</label></li>
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_convert" name="fieldSettings_file_options_convert" data-bindModel="convert"> Convert Image file</label></li>
                    <li><label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_thumbnail" name="fieldSettings_file_options_thumbnail" data-bindModel="thumbnail"> Create thumbnail</label></li>
                </ul>
            </div>

            <div class="microGroup uxOptions">
                <strong> Audio Options </strong>
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="More options will be added for more processing information."></i>
                <ul>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_file_options_convertAudio" name="fieldSettings_file_options_convert" data-bindModel="convertAudio">
                            Convert or Modify Audio
                        </label>
                    </li>
                </ul>
            </div>

            <div class="microGroup uxOptions">
                <strong> Video Options </strong>
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="More options will be added for more processing information."></i>
                <ul>
                    <li>
                        <label class="checkbox">
                            <input type="checkbox" id="fieldSettings_file_options_convertVideo" name="fieldSettings_file_options_convertVideo" data-bindModel="convertVideo">
                            Convert or Modify Video
                        </label>
                    </li>
                    <li>
                        <label class="checkbox"><input type="checkbox" id="fieldSettings_file_options_videothumbnail" name="fieldSettings_file_options_videothumbnail" data-bindModel="videothumbnail">
                            Create thumbnail
                        </label>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <div class="group convertAudio">
        <div class="control-group" id="fieldSettings_container_file_convertAudio">
            <p> Audio Conversions </p>

            <div class="audio">
                <div id="fieldSettings_container_file_convert_bitrate" class="span6">
                    <label>
                        Change BitRate:
                    </label>
                    <select class="bitRate" data-bindModel="bitRate">
                            <option value="">  Select a BitRate  </option>
                            {local var="bitRates"}
                    </select>
                </div>

                <div id="fieldSettings_container_file_convert_audioFormat" class="span6">
                    <label>
                        Change Format:
                    </label>
                    <select class="audioFormat" data-bindModel="audioFormat">
                            <option value="">    Select a Format  </option>
                            {local var="audioOptions"}
                    </select>
                </div>
            </div>
        </div>
    </div>


    <div class="group convertVideo">
        <div class="control-group " id="fieldSettings_container_file_convertVideo">

            <strong> Video Options </strong>

            <div>
                <div id="fieldSettings_container_file_convert_bitrate" class="span6">
                    <label>
                        Change BitRate:
                    </label>
                    <select class="videobitRate" data-bindModel="videobitRate">
                            <option value="">  Select a BitRate  </option>
                            {local var="bitRates"}
                    </select>
                </div>

                <div id="fieldSettings_container_file_convert_videoFormat" class="span6 last">
                    <label>
                        Change Format:
                    </label>
                    <select class="videoFormat" data-bindModel="videoFormat">
                            <option value="">    Select a Format  </option>
                            {local var="videoTypes"}
                    </select>
                </div>

                <div>
                    <strong> Video Size <i class="fa fa-question-circle formatSettings"> </i> </strong>

                    <div class=" formatSettingsHelp alert alert-warning">
                        <span class="span6">
                        <strong> Wide Screen </strong>
                            <ul>
                                <li> 240p: 426x240 (16:9) </li>
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
                    <input type="number" class="input-block-level" id="fieldSettings_file_video_height" name="fieldSettings_file_video_height" min="0" data-bindModel="videoHeight"/>
                </span>
                <span class="span6">
                    <label for="fieldSettings_file_video_width">
                        Width (px)
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                    </label>
                    <input type="number" class="input-block-level" id="fieldSettings_file_video_width" name="fieldSettings_file_video_width" min="0"  data-bindModel="videoWidth"/>
                </span>
                </div>
                <div class="" id="fieldSettings_file_video_aspect">
                    <span class="span12">
                        <label for="fieldSettings_file_video_aspectRatio"> Aspect Ratio </label>
                            <select class="videoAspectRatio span12 last" data-bindModel="aspectRatio">
                                <option value="">     Select an Aspect Ratio    </option>
                                <option value="4:3">  Standard Definition - 4:3 </option>
                                <option value="16:9"> Wide Screen - 16:9        </option>
                            </select>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="group videothumbnail">
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
                    <input type="number" class="input-block-level" id="fieldSettings_file_video_frames" name="fieldSettings_file_video_frames" min="0" data-bindModel="videoThumbFrames"/>
                </span>
                <span class="span4">
                    <label for="fieldSettings_file_video_thumbheight">
                        Height (px)
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                    </label>
                    <input type="number" class="input-block-level" id="fieldSettings_file_video_thumbheight" name="fieldSettings_file_video_thumbheight" min="0" data-bindModel="videoThumbHeight"/>
                </span>
                <span class="span4">
                    <label for="fieldSettings_file_video_thumbwidth">
                        Width (px)
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                    </label>
                    <input type="number" class="input-block-level" id="fieldSettings_file_video_thumbwidth" name="fieldSettings_file_video_thumbwidth" min="0" data-bindModel="videoThumbWidth"/>
                </span>
            </div>
            <div class="" id="fieldSettings_video_thumbnail">
                <span class="span12">
                    <label for="fieldSettings_file_video_formatThumb">
                        Format
                    </label>
                    <select class="input-block-level" id="fieldSettings_file_video_formatThumb" name="fieldSettings_file_video_formatThumb" data-bindModel="videoFormatThumb">
                        <option value="">Select Format</option>
                        {local var="videoThumbs"}
                    </select>
                </span>
            </div>
        </div>
    </div>

    <div class="group convert">
        <div class="control-group " id="fieldSettings_container_file_convert">

            <strong> Image Conversions </strong>

            <div>
                <div class="span3" id="fieldSettings_container_file_convert_height" >
                    <label for="fieldSettings_file_convert_height">
                        Max Height (px)
                    </label>
                    <input type="number" class="input-block-level" id="fieldSettings_file_convert_height" name="fieldSettings_file_convert_height" min="1" data-bindModel="convertHeight"/>
                </div>

                <div class="span3" id="fieldSettings_container_file_convert_width">
                    <label for="fieldSettings_file_convert_width">
                        Max Width (px)
                    </label>
                    <input type="number" class="input-block-level" id="fieldSettings_file_convert_width" name="fieldSettings_file_convert_width" min="1" data-bindModel="convertWidth"/>
                </div>

                <div class="span3" id="fieldSettings_container_file_convert_reolution">
                    <label for="fieldSettings_file_convert_resolution">
                        Resolution (DPI)
                    </label>
                    <input type="number" class="input-block-level" id="fieldSettings_file_convert_resolution" name="fieldSettings_file_convert_resolution" min="72" data-bindModel="convertResolution"/>
                </div>

                <div class="span3" id="fieldSettings_container_file_convert_extension">
                    <label for="fieldSettings_file_convert_format">
                        Format
                    </label>
                    <select class="input-block-level" id="fieldSettings_file_convert_format" name="fieldSettings_file_convert_format" data-bindModel="convertFormat">
                        <option value="">Select Format</option>
                        {local var="conversionFormats"}
                    </select>
                </div>
            </div>

            <ul class="checkboxList">
                <li class="uxOptions">
                    <label class="checkbox"><input type="checkbox" id="fieldSettings_file_convert_watermark" name="fieldSettings_file_convert_watermark" data-bindModel="watermark">Watermark</label>
                </li>
                <li class="uxOptions">
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_file_convert_border" name="fieldSettings_file_convert_border" data-bindModel="border">
                        Border
                    </label>
                </li>
            </ul>
        </div>
    </div>

     <div class="group border">
        <span class="span4">
            <label for="fieldSettings_file_border_height">
                Height (px)
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Border width of the top and bottom."></i>
            </label>
            <input type="number" class="input-block-level" id="fieldSettings_file_border_height" name="fieldSettings_file_border_height" min="0" data-bindModel="borderHeight"/>
        </span>
        <span class="span4">
            <label for="fieldSettings_file_border_width">
                Width (px)
                <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="Border width of the left and right."></i>
            </label>
            <input type="number" class="input-block-level" id="fieldSettings_file_border_width" name="fieldSettings_file_border_width" min="0" data-bindModel="borderWidth"/>
        </span>
        <span class="span4">
            <label for="fieldSettings_file_border_color">
                Color
            </label>
            <input type="color" class="input-block-level" id="fieldSettings_file_border_color" name="fieldSettings_file_border_color" data-bindModel="borderColor"/>
        </span>
    </div>

    <div class="group watermark">
        <span class="span6">
            <label for="fieldSettings_file_watermark_image">
                Image
            </label>
            <select class="input-block-level" id="fieldSettings_file_watermark_image" name="fieldSettings_file_watermark_image" data-bindModel="watermarkImage">
                <option value="">Select Image</option>
                {local var="watermarkList"}
            </select>
        </span>
        <span class="span6">
            <label for="fieldSettings_file_watermark_location">
                Location
            </label>
            <select class="input-block-level" id="fieldSettings_file_watermark_location" name="fieldSettings_file_watermark_location" data-bindModel="watermarkLocation">
                <option value="">Select Location</option>
                {local var="imageLocations"}
            </select>
        </span>
    </div>

    <div class="group thumbnail">
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
                    <input type="number" class="input-block-level" id="fieldSettings_file_thumbnail_height" name="fieldSettings_file_thumbnail_height" min="0" data-bindModel="thumbnailHeight"/>
                </span>
                <span class="span4">
                    <label for="fieldSettings_file_thumbnail_width">
                        Width (px)
                        <i class="fa fa-question-circle" rel="tooltip" data-placement="right" data-title="If set to 0, aspect ratio will be maintained."></i>
                    </label>
                    <input type="number" class="input-block-level" id="fieldSettings_file_thumbnail_width" name="fieldSettings_file_thumbnail_width" min="0" data-bindModel="thumbnailWidth"/>
                </span>
                <span class="span4">
                    <label for="fieldSettings_file_thumbnail_format">
                        Format
                    </label>
                    <select class="input-block-level" id="fieldSettings_file_thumbnail_format" name="fieldSettings_file_thumbnail_format" data-bindModel="thumbnailFormat">
                        <option value="">Select Format</option>
                        {local var="conversionFormats"}
                    </select>
                </span>
            </div>
        </div>
    </div>


    <div class="group default">
        <div class="control-group " id="fieldSettings_container_options">
            <div> <strong> Options </strong> </div>
            <ul class="checkboxList span6">
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_required" name="fieldSettings_options_required" data-bindModel="required"> Required
                         <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Field is required to be filled out."></i>
                    </label>

                </li>
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_duplicates" name="fieldSettings_options_duplicates" data-bindModel="duplicates"> No duplicates
                         <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Duplicate entries for this form are not allowed."></i>
                    </label>

                </li>
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_readonly" name="fieldSettings_options_readonly" data-bindModel="readonly"> Read only
                         <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Field is read-only, data is pulled from form definition on insert, previous revision on update. not from POST"></i>
                    </label>

                </li>
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_disabled" name="fieldSettings_options_disabled" data-bindModel="disabled"> Disabled
                         <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Field is disabled and not submitted to POST"></i>
                    </label>

                </li>
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_disabled_insert" name="fieldSettings_options_disabled_insert" data-bindModel="disabledInsert"> Disabled on Insert
                         <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Field is hideen and disabled on insert forms."></i>
                    </label>
                </li>

                </ul>
                <ul class="checkboxList span6 last">
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_publicRelease" name="fieldSettings_options_publicRelease" data-bindModel="publicRelease"> Public release
                        <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Dependant on Export Script: Metadata check to determine if field should be exported to XML"></i>
                    </label>
                </li>
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_sortable" name="fieldSettings_options_sortable" data-bindModel="sortable"> Sortable
                        <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Field is Sortable in list table in MFCS."></i>
                    </label>

                </li>
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_searchable" name="fieldSettings_options_searchable" data-bindModel="searchable"> Searchable
                        <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Dependant on Export: Can search on this field in public facing repository."></i>
                    </label>

                </li>
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_displayTable" name="fieldSettings_options_displayTable" data-bindModel="displayTable"> Display in list table
                       <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Field is displayed in the listing table in MFCS"></i>
                    </label>

                </li>
                <li>
                    <label class="checkbox">
                        <input type="checkbox" id="fieldSettings_options_hidden" name="fieldSettings_options_hidden" data-bindModel="hidden"> Hidden
                          <i class="fa fa-question-circle" rel="tooltip"  data-placement="top" data-title="Input type is set to Hidden."></i>
                    </label>
                </li>
            </ul>
        </div>
    </div>

    <div class="group default">
        <div class="control-group " id="fieldSettings_container_validation">
            <label for="fieldSettings_validation">
                Validation
            </label>
            <select class="input-block-level" id="fieldSettings_validation" name="fieldSettings_validation" data-bindModel="validation">
                {local var="validationTypes"}
            </select>
            <br>
            <input type="text" class="input-block-level" id="fieldSettings_validationRegex" name="fieldSettings_validationRegex" placeholder="Enter a Regex" data-bindModel="validationRegex" style="display:none;"/>
        </div>
    </div>
</div>






<!-- Modals ===================================================================  -->

 <div id="defaultValueVariables" class="modal" rel="modal" data-show="false">
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