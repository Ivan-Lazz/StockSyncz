<?php

session_start();
if(!isset($_SESSION['admin'])) {
    ?>
    <script type="text/javascript">
        window.location = "index.php";
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
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Update Party</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editPartyForm" class="form-horizontal">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="control-group">
                                <label class="control-label">First Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="First Name" name="firstname" id="firstname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Last Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Last Name" name="lastname" id="lastname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Business Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Business Name" name="businessname" id="businessname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Contact No. :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Contact No." name="contact" id="contact" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Address :</label>
                                <div class="controls">
                                    <textarea name="address" class="span11" id="address" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">City :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="City" name="city" id="city" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage"></span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Party updated successfully!
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
// Load party data
function loadPartyData() {
    fetch(`http://localhost/imsfin/IMS_API/api/party/read_single_party.php?id=<?php echo $id; ?>`)
        .then(response => response.json())
        .then(party => {
            if (party) {
                document.getElementById('firstname').value = party.firstname;
                document.getElementById('lastname').value = party.lastname;
                document.getElementById('businessname').value = party.businessname;
                document.getElementById('contact').value = party.contact;
                document.getElementById('address').value = party.address;
                document.getElementById('city').value = party.city;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading party data');
        });
}

// Form submission
document.getElementById('editPartyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const partyData = {};
    formData.forEach((value, key) => partyData[key] = value.trim());
    
    fetch('http://localhost/imsfin/IMS_API/api/party/update_party.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(partyData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Party was updated.") {
            document.getElementById('error').style.display = 'none';
            document.getElementById('success').style.display = 'block';
            setTimeout(() => {
                window.location.href = 'add_new_party.php';
            }, 1500);
        } else {
            document.getElementById('success').style.display = 'none';
            document.getElementById('errorMessage').textContent = data.message;
            document.getElementById('error').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('errorMessage').textContent = 'An error occurred. Please try again.';
        document.getElementById('error').style.display = 'block';
    });
});

// Load party data when page loads
document.addEventListener('DOMContentLoaded', loadPartyData);
</script>

<?php include "footer.php" ?>