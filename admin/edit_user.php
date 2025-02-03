<?php

session_start();
if(!isset($_SESSION['admin'])){
    ?>
    <script type="text/javascript">
        window.location= "index.php";
    </script>
    <?php
}

include "../user/connection.php";
include "header.php";

$id = $_GET['id'];
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>Home</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title"> <span class="icon"> <i class="icon-align-justify"></i> </span>
                        <h5>Update User</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editUserForm" class="form-horizontal" name="form1">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="control-group">
                                <label class="control-label">First Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="First name" name="firstname" id="firstname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Last Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Last name" name="lastname" id="lastname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Username :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Username" name="username" id="username" readonly/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Password</label>
                                <div class="controls">
                                    <input type="password" class="span11" placeholder="Enter Password" name="password" id="password" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Select Role</label>
                                <div class="controls">
                                    <select name="role" class="span11" id="role">
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Select Status</label>
                                <div class="controls">
                                    <select name="status" class="span11" id="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> User updated successfully!
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load user data
async function loadUserData() {
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/user/read_single_user.php?id=<?php echo $id; ?>`);
        const data = await response.json();
        
        if (data.status === 200) {
            document.getElementById('firstname').value = data.firstname;
            document.getElementById('lastname').value = data.lastname;
            document.getElementById('username').value = data.username;
            // Don't set the password field value for security
            document.getElementById('password').value = '';
            // Add placeholder to indicate password is optional
            document.getElementById('password').placeholder = 'Leave empty to keep current password';
            document.getElementById('role').value = data.role;
            document.getElementById('status').value = data.status;
        } else {
            throw new Error(data.message || 'Error loading user data');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Error loading user data');
        // Redirect back to user list if user not found
        window.location.href = 'add_new_user.php';
    }
}

// Update the form submission handler
document.getElementById('editUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const userData = {
        id: document.querySelector('input[name="id"]').value,
        firstname: document.getElementById('firstname').value,
        lastname: document.getElementById('lastname').value,
        username: document.getElementById('username').value,
        role: document.getElementById('role').value,
        status: document.getElementById('status').value
    };

    // Only include password if it's not empty
    const password = document.getElementById('password').value;
    if (password) {
        userData.password = password;
    }
    
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/user/update_user.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        const data = await response.json();
        
        if (data.status === 200 && data.message === "User was updated.") {
            document.getElementById('success').style.display = 'block';
            // Redirect after successful update
            setTimeout(() => {
                window.location.href = 'add_new_user.php';
            }, 1500);
        } else {
            throw new Error(data.message || 'Error updating user');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Error updating user. Please try again.');
    }
});

// Load user data when page loads
document.addEventListener('DOMContentLoaded', loadUserData);
</script>

<?php include "footer.php" ?>