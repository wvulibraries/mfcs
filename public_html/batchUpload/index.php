<?php
// header
include("../header.php");
$engine->eTemplate("include","header");

//Permissions Access
if(!mfcsPerms::evaluatePageAccess(1)){
    header('Location: /index.php?permissionFalse');
}

// Batch Uploading Logs
// log::insert("BatchUpload",0,0, "Batch upload screen was loaded.");

// Setup Form lists
localVars::add("formList", listGenerator::createFormDropDownList());
?>

<section>

    <header class="page-header">
        <h1> Batch Upload </h1>
    </header>

    <ul class="breadcrumbs">
        <li><a href="{local var="siteRoot"}">Home</a></li>
        <li><a href="{local var="siteRoot"}/batchUpload/">Batch Upload</a></li>

        <li class="pull-right noDivider">
            <a href="#batchUploadDocumentation" target="_blank">
                <i class="fa fa-book"></i> Documentation
            </a>
        </li>
    </ul>

    <div class="row-fluid batchUpload">
        <div class="span6">
            <h2> Upload Here </h2>
            <div class="selectFormContainer group control-group">
                <label> Select Form to Upload To </label>
                {local var="formList"}
            </div>

            <h3> Metadata From Filename? </h3>
            <div class="selectFormContainer group control-group">
                <label for="regExBool"> <input type="checkbox" name="regExBool" id="regExBool" value="value"/> Use RegEx to parse filename? </label>
                <br>
                <div class="toggleRegEx hide">
                    <div class="alert alert-warning"><p> Please read all documentation before attempting to use the regular expressions to parse filenames.  All file names must be similar or the metadata will not be correct. </p> </div>

                    <label for="exampleFileName"> Example File Name </label>
                    <input type="text" name="exampleFileName" id="exampleFileName" value=""/>

                    <label for="regEx"> Regular Expression </label>
                    <input type="text" name="regEx" id="regEx" value=""/>

                    <a href="javascript:void(0)" class="previewRegEx btn btn-primary pull-right"> Preview Reg Ex </a>
                    <br><br><br>

                    <div class="regExPreview pull-left"></div>
                </div>
            </div>
        </div>

        <div class="span6">
            <h2> Form Preview </h2>
            <p> Modify Me </p>
        </div>
    </div>
</section>

<script>
    $('#regExBool').change(function(){
        if ($(this).is(':checked')) {
            $('.toggleRegEx').removeClass('hide');
        } else {
            $('.toggleRegEx').addClass('hide');
        }
    });

    $('.previewRegEx').click(function(){
        var filename = $('#exampleFileName').val();
        var regEx    = $('#regEx').val();

        $('.regExPreview').html('');

        $.ajax({
            dataType: 'json',
            url:'/batchUpload/regExTester',
            data: { filename: filename, regex: regEx },
            success:function(){
                console.log('success');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus + ': ' + errorThrown);
            },
            complete:function(data){
                var jsonData = data.responseJSON;
                jsonData.shift();

                var html = "<h2> Regular Expression Preview </h2>";

                html += "<p> The following is a preview of the regular expressions that will be used in parsing your file name.  Use the variables in the form preview to setup the data automatically. </p>";

                html += "<p><strong> Example Filename : </strong>" + filename + "</p>";
                html += "<p><strong> RegEx Given : </strong>" + regEx + "</p>";

                html += "<em><srong> Variable </strong> = Value</em>";

                for(var i = 0; i < jsonData.length; i++){
                   console.log(jsonData);
                   html += "<p>";
                   html += "<strong>{" + i + "}</strong> = " + jsonData[i].toString();
                   html += "</p>";
                }

                $('.regExPreview').html(html);
            }
       });

    });

</script>

<?php
$engine->eTemplate("include","footer");
?>
