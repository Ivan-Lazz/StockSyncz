<?php
session_start();
if(!isset($_SESSION['admin'])) {
    ?>
    <script type="text/javascript">
        window.location = "index.php";
    </script>
    <?php
}

include "header.php";
$id = $_GET['id'];
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="#" class="tip-bottom">
                <i class="icon-home"></i>Edit Stocks Price
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Edit Stocks Price</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editStockForm" class="form-horizontal">
                            <input type="hidden" id="stock_id" value="<?php echo $id; ?>">
                            
                            <div class="control-group">
                                <label class="control-label">Product Company:</label>
                                <div class="controls">
                                    <input type="text" class="span11" id="product_company" readonly/>
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label class="control-label">Product Name:</label>
                                <div class="controls">
                                    <input type="text" class="span11" id="product_name" readonly/>
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label class="control-label">Product Unit:</label>
                                <div class="controls">
                                    <input type="text" class="span11" id="product_unit" readonly/>
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label class="control-label">Packing Size:</label>
                                <div class="controls">
                                    <input type="text" class="span11" id="packing_size" readonly/>
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label class="control-label">Product Quantity:</label>
                                <div class="controls">
                                    <input type="text" class="span11" id="product_qty" readonly/>
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label class="control-label">Product Selling Price:</label>
                                <div class="controls">
                                    <input type="number" step="0.01" class="span11" id="product_selling_price" required/>
                                </div>
                            </div>

                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Stock Price Updated Successfully!
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
// Add this JavaScript to edit_stock_master.php

document.addEventListener('DOMContentLoaded', function() {
    loadStockDetails();
    
    document.getElementById('editStockForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateStockPrice();
    });
});

async function loadStockDetails() {
    const stockId = <?php echo json_encode($id); ?>;
    console.log('Loading stock details for ID:', stockId);

    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/stock/get_stock_detail.php?id=${stockId}`);
        const result = await response.json();
        console.log('API Response:', result);
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to load stock details');
        }

        // Populate form fields
        const stock = result.data;
        Object.keys(stock).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.value = stock[key];
                console.log(`Setting ${key} to ${stock[key]}`);
            }
        });

    } catch (error) {
        console.error('Error:', error);
        alert('Error loading stock details: ' + error.message);
    }
}

async function updateStockPrice() {
    try {
        const stockId = <?php echo json_encode($id); ?>;
        const newPrice = document.getElementById('product_selling_price').value;

        if (!newPrice) {
            throw new Error('Please enter a selling price');
        }

        const response = await fetch('http://localhost/imsfin/IMS_API/api/stock/update_stock_price.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: stockId,
                product_selling_price: newPrice
            })
        });

        const result = await response.json();
        console.log('Update response:', result);
        
        if (result.success) {
            document.getElementById('success').style.display = 'block';
            setTimeout(() => {
                window.location.href = 'stock_master.php';
            }, 1500);
        } else {
            throw new Error(result.message || 'Failed to update stock price');
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Error updating stock price: ' + error.message);
    }
}
</script>

<style>
.alert {
    margin-top: 20px;
}

.form-actions {
    padding: 15px;
}

input[readonly] {
    background-color: #f5f5f5;
    cursor: not-allowed;
}
</style>

<?php include "footer.php"; ?>