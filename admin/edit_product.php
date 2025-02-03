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
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>Update Product</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Update Product</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editProductForm" class="form-horizontal">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="control-group">
                                <label class="control-label">Select Company :</label>
                                <div class="controls">
                                    <select name="company_name" class="span11" id="companySelect" required>
                                        <option value="">Select Company</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Product Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Product Name" name="product_name" id="product_name" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Select Unit :</label>
                                <div class="controls">
                                    <select name="unit" class="span11" id="unitSelect" required>
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Packing Size :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Packing Size" name="packing_size" id="packing_size" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage">Product already exists!</span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Product updated successfully!
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
// Load companies for dropdown
function loadCompanies() {
    fetch('http://localhost/imsfin/IMS_API/api/company/get_companies.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('companySelect');
            if (Array.isArray(data)) {
                data.forEach(company => {
                    const option = document.createElement('option');
                    option.value = company.companyname;
                    option.textContent = company.companyname;
                    select.appendChild(option);
                });
                loadProductData(); // Load product data after companies are loaded
            }
        })
        .catch(error => console.error('Error:', error));
}

// Load units for dropdown
function loadUnits() {
    fetch('http://localhost/imsfin/IMS_API/api/unit/get_units.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('unitSelect');
            if (Array.isArray(data)) {
                data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.unit;
                    option.textContent = unit.unit;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

// Load product data
function loadProductData() {
    fetch(`http://localhost/imsfin/IMS_API/api/product/read_single_product.php?id=<?php echo $id; ?>`)
        .then(response => response.json())
        .then(product => {
            if (product) {
                document.getElementById('companySelect').value = product.company_name;
                document.getElementById('product_name').value = product.product_name;
                document.getElementById('unitSelect').value = product.unit;
                document.getElementById('packing_size').value = product.packing_size;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading product data');
        });
}

// Form submission
document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const productData = {};
    formData.forEach((value, key) => productData[key] = value.trim());
    
    fetch('http://localhost/imsfin/IMS_API/api/product/update_product.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(productData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Product was updated.") {
            document.getElementById('error').style.display = 'none';
            document.getElementById('success').style.display = 'block';
            setTimeout(() => {
                window.location.href = 'add_product.php';
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

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    loadCompanies();
    loadUnits();
});
</script>

<?php include "footer.php" ?>