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
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>Add Product</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Add New Product</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="addProductForm" class="form-horizontal">
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
                                    <input type="text" class="span11" placeholder="Product Name" name="product_name" required/>
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
                                    <input type="text" class="span11" placeholder="Packing Size" name="packing_size" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage">Product already exists!</span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Product added successfully!
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="widget-content nopadding">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Company Name</th>
                                <th>Product Name</th>
                                <th>Unit</th>
                                <th>Packing Size</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody"></tbody>
                    </table>
                    <div id="paginationContainer"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
const recordsPerPage = 10;
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

// Load products
async function loadProducts() {
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/product/read_products.php?page=${currentPage}&per_page=${recordsPerPage}`);
        const data = await response.json();
        
        const tbody = document.getElementById('productTableBody');
        tbody.innerHTML = '';
        
        if (data.success && data.records && data.records.length > 0) {
            data.records.forEach(product => {
                tbody.innerHTML += `
                    <tr>
                        <td>${product.company_name}</td>
                        <td>${product.product_name}</td>
                        <td>${product.unit}</td>
                        <td>${product.packing_size}</td>
                        <td><center><a href="edit_product.php?id=${product.id}" class="text-success">Edit</a></center></td>
                        <td><center><a href="#" onclick="deleteProduct(${product.id})" class="text-error">Delete</a></center></td>
                    </tr>
                `;
            });

            if (data.pagination && data.pagination.total_pages > 1) {
                renderPagination(data.pagination);
            } else {
                document.getElementById('paginationContainer').innerHTML = '';
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No products found</td></tr>';
            document.getElementById('paginationContainer').innerHTML = '';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('productTableBody').innerHTML = 
            '<tr><td colspan="6" class="text-center">Error loading products. Please try again.</td></tr>';
    }
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationContainer');
    let html = `
        <div class="pagination-container">
            <div class="dataTables_info">
                Showing ${((pagination.current_page - 1) * pagination.records_per_page) + 1} to 
                ${Math.min(pagination.current_page * pagination.records_per_page, pagination.total_records)} 
                of ${pagination.total_records} entries
            </div>
            <div class="dataTables_paginate">
                <button class="btn" onclick="changePage(1)" ${pagination.current_page === 1 ? 'disabled' : ''}>
                    First
                </button>
                <button class="btn" onclick="changePage(${pagination.current_page - 1})" ${pagination.current_page === 1 ? 'disabled' : ''}>
                    Previous
                </button>
                <span class="page-numbers">`;

    for (let i = Math.max(1, pagination.current_page - 2); 
         i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
        html += `
            <button class="btn ${i === pagination.current_page ? 'btn-info' : ''}" onclick="changePage(${i})">
                ${i}
            </button>`;
    }

    html += `
                </span>
                <button class="btn" onclick="changePage(${pagination.current_page + 1})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                    Next
                </button>
                <button class="btn" onclick="changePage(${pagination.total_pages})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                    Last
                </button>
            </div>
        </div>`;

    container.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    loadProducts();
}

// Delete product
async function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }

    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/product/delete_product.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.message === "Product was deleted.") {
            currentPage = 1; // Reset to first page
            loadProducts();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Form submission
document.getElementById('addProductForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const productData = {};
    formData.forEach((value, key) => productData[key] = value.trim());
    
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/product/create_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData)
        });
        
        const data = await response.json();
        
        if (data.message === "Product was created.") {
            document.getElementById('error').style.display = 'none';
            document.getElementById('success').style.display = 'block';
            this.reset();
            currentPage = 1; // Reset to first page
            loadProducts();
            setTimeout(() => {
                document.getElementById('success').style.display = 'none';
            }, 1500);
        } else {
            document.getElementById('success').style.display = 'none';
            document.getElementById('errorMessage').textContent = data.message;
            document.getElementById('error').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('errorMessage').textContent = 'An error occurred. Please try again.';
        document.getElementById('error').style.display = 'block';
    }
});

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    loadCompanies();
    loadUnits();
    loadProducts();
});
</script>

<style>
.loading-spinner {
    text-align: center;
    padding: 20px;
    background-color: #f9f9f9;
    border-bottom: 1px solid #ddd;
}

.icon-spin {
    animation: spin 1s infinite linear;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.pagination-container {
    margin-top: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background-color: #f9f9f9;
    border-top: 1px solid #ddd;
}

.dataTables_info {
    color: #666;
    padding: 8px 0;
}

.dataTables_paginate {
    text-align: right;
}

.dataTables_paginate .btn {
    margin: 0 2px;
    padding: 4px 10px;
    border: 1px solid #ddd;
}

.dataTables_paginate .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f5f5f5;
}

.page-numbers {
    margin: 0 10px;
    display: inline-block;
}

.page-numbers .btn {
    min-width: 35px;
}

.btn-info {
    color: #ffffff;
    background-color: #49afcd;
    border-color: #2f96b4;
}
</style>

<?php include "footer.php" ?>