<h2 class="danger">Delete Form</h2>

<ul class="breadcrumbs">
    <li><a href="{local var="siteRoot"}">Home</a></li>
    <li><a href="{local var="siteRoot"}formCreator/">Form Creator</a></li>
    <li> Delete Form </li>
</ul>

<form method="post" action="" id="deleteFormFrm">
    {engine name="csrf"}
    <input type="hidden" name="deleteForm" value="deleteForm">
    <br>
    <div class="alert alert-warning">
        <h1 class="danger"> WARNING </h1>
        Are you sure you want to delete this form? <br> This will permanently delete this form and all associated objects, and cannot be undone.</div>
    <input type="button" value="Cancel" class="btn" id="deleteFormBtn-Cancel">
    <input type="button" value="Delete Form" class="btn btn-danger" id="deleteFormBtn-Submit">
</form>