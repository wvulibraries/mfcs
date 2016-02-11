
<h2>Form Permissions</h2>

<ul class="breadcrumbs">
    <li><a href="{local var="siteRoot"}">Home</a></li>
    <li><a href="{local var="siteRoot"}formCreator/">Form Creator</a></li>
    <li> Form Permissions </li>
</ul>

<div class="container-fluid">
    <div class="row-fluid" id="results">
        {local var="results"}
    </div>

    <div class="row-fluid">
        <form name="submitPermissions" method="post">
            {engine name="csrf"}
            <table>
                <tr>
                    <th>Data Entry Users</th>
                    <th>Data View Users</th>
                    <th>Administrators</th>
                    <th>Contacts</th>
                </tr>
                <tr>
                    <td>
                        <select name="selectedEntryUsers[]" id="selectedEntryUsers" size="5" multiple="multiple">
                            {local var="selectedEntryUsers"}
                        </select>
                        <br />
                        <select name="availableEntryUsers" id="availableEntryUsers" onchange="addItemToID('selectedEntryUsers', this.options[this.selectedIndex])">
                            {local var="availableUsersList"}
                        </select>
                        <br />
                        <input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedEntryUsers', this.form.selectedEntryUsers)" />
                    </td>
                    <td>
                        <select name="selectedViewUsers[]" id="selectedViewUsers" size="5" multiple="multiple">
                            {local var="selectedViewUsers"}
                        </select>
                        <br />
                        <select name="availableViewUsers" id="availableViewUsers" onchange="addItemToID('selectedViewUsers', this.options[this.selectedIndex])">
                            {local var="availableUsersList"}
                        </select>
                        <br />
                        <input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedViewUsers', this.form.selectedViewUsers)" />
                    </td>
                    <td>
                        <select name="selectedUsersAdmins[]" id="selectedUsersAdmins" size="5" multiple="multiple">
                            {local var="selectedUsersAdmins"}
                        </select>
                        <br />
                        <select name="availableUsersAdmins" id="availableUsersAdmins" onchange="addItemToID('selectedUsersAdmins', this.options[this.selectedIndex])">
                            {local var="availableUsersList"}
                        </select>
                        <br />
                        <input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedUsersAdmins', this.form.selectedUsersAdmins)" />
                    </td>
                    <td>
                     <select name="selectedContactUsers[]" id="selectedContactUsers" size="5" multiple="multiple">
                            {local var="selectedUsersContact"}
                        </select>
                        <br />
                        <select name="availableContactUsers" id="availableContactUsers" onchange="addItemToID('selectedContactUsers', this.options[this.selectedIndex])">
                            {local var="availableUsersList"}
                        </select>
                        <br />
                        <input type="button" name="deleteSelected" value="Remove Selected" onclick="removeItemFromID('selectedContactUsers', this.form.selectedContactUsers)" />
                    </td>
                </tr>
            </table>
            <input type="submit" class="btn btn-large btn-block btn-primary" name="submitPermissions" value="Update Permissions" />
        </form>
    </div>
</div>