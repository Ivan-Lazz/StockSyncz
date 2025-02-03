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
                        <h5>Update Company</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editCompanyForm" class="form-horizontal">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="control-group">
                                <label class="control-label">Company Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Company Name" name="companyname" id="companyname" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage">Company already exists!</span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Company updated successfully!
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
// Load company data
function loadCompanyData() {
    fetch(`http://localhost/imsfin/IMS_API/api/company/read_single_company.php?id=<?php echo $id; ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.companyname) {
                document.getElementById('companyname').value = data.companyname;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading company data');
        });
}

// Form submission
document.getElementById('editCompanyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const companyData = {
        id: <?php echo $id; ?>,
        companyname: document.getElementById('companyname').value
    };
    
    fetch('http://localhost/imsfin/IMS_API/api/company/update_company.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(companyData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Company was updated.") {
            document.getElementById('error').style.display = 'none';
            document.getElementById('success').style.display = 'block';
            setTimeout(() => {
                window.location.href = 'add_new_company.php';
            }, 1500);
        } else {
            document.getElementById('success').style.display = 'none';
            document.getElementById('errorMessage').textContent = data.message;
            document.getElementById('error').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating company. Please try again.');
    });
});

// Load company data when page loads
document.addEventListener('DOMContentLoaded', loadCompanyData);
</script>

<?php include "footer.php" ?>
