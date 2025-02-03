<?php
session_start();
if(!isset($_SESSION['admin'])){
    ?>
    <script type="text/javascript">
        window.location= "index.php";
    </script>
    <?php
}
include "header.php"; 
include "../user/connection.php";
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="dashboard.php" class="tip-bottom">
                <i class="icon-home"></i>Dashboard
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            
            <div class="card" style="width: 18rem; border-style:solid; border-width:1px; border-radius:10px; float: left;">
                <div class="card-body">
                    <h3 class="card-title text-center">No. of Products</h3>
                    <h1 class="card-text text-center" id="productsCount">
                        <span class="loading-spinner"></span>
                    </h1>
                </div>
            </div>

            <div class="card" style="width: 18rem; border-style:solid; border-width:1px; border-radius:10px; float: left; margin-left: 5px;">
                <div class="card-body">
                    <h3 class="card-title text-center">Total Orders</h3>
                    <h1 class="card-text text-center" id="ordersCount">
                        <span class="loading-spinner"></span>
                    </h1>
                </div>
            </div>

            <div class="card" style="width: 18rem; border-style:solid; border-width:1px; border-radius:10px; float: left; margin-left: 5px;">
                <div class="card-body">
                    <h3 class="card-title text-center">Total Company</h3>
                    <h1 class="card-text text-center" id="companiesCount">
                        <span class="loading-spinner"></span>
                    </h1>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    border-top-color: #3498db;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-message {
    color: red;
    font-size: 14px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
});

async function loadDashboardStats() {
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/dashboard/get_dashboard_stats.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Failed to load dashboard statistics');
        }

        const data = await response.json();
        
        if (data.success) {
            document.getElementById('productsCount').textContent = data.data.products_count;
            document.getElementById('ordersCount').textContent = data.data.orders_count;
            document.getElementById('companiesCount').textContent = data.data.companies_count;
        } else {
            throw new Error(data.message || 'Failed to load statistics');
        }

    } catch (error) {
        console.error('Error:', error);
        const errorMessage = 'Error loading dashboard data. Please refresh the page.';
        
        document.getElementById('productsCount').innerHTML = 
            `<span class="error-message">${errorMessage}</span>`;
        document.getElementById('ordersCount').innerHTML = 
            `<span class="error-message">${errorMessage}</span>`;
        document.getElementById('companiesCount').innerHTML = 
            `<span class="error-message">${errorMessage}</span>`;
    }
}

// Refresh stats every 5 minutes
setInterval(loadDashboardStats, 300000);
</script>

<?php include "footer.php" ?>