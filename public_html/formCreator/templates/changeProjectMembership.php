<ul class="breadcrumbs">
    <li><a href="{local var="siteRoot"}">Home</a></li>
    <li><a href="{local var="siteRoot"}formCreator/">Form Creator</a></li>
    <li> Assigned Projects </li>
</ul>


<form action="{phpself query="true"}" method="post">
    {local var="projectOptions"}
    {engine name="csrf"}
    <input type="submit" class="btn btn-primary" name="projectForm" disabled>
    <noscript><p style="color:red; text-align: center; font-weight: bold;">JavaScript failed to load!</p></noscript>
</form>