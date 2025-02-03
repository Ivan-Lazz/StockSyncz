<?php
session_start();
if(!isset($_SESSION['admin'])) {
    ?>
    <script type="text/javascript">
        window.location = "index.php";
    </script>
    <?php
}
include 'header.php';
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="dashboard.php" class="tip-bottom">
                <i class="icon-home"></i> Dashboard
            </a>
            <a href="#" class="current">Returned Products List</a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-th"></i></span>
                        <h5>Returned Products Report</h5>
                    </div>
                    <div class="widget-content">
                        <form id="reportForm" class="form-horizontal">
                            <div class="control-group">
                                <label class="control-label">Date Range:</label>
                                <div class="controls">
                                    <input type="date" id="start_date" class="span3" required>
                                    <span class="help-inline">to</span>
                                    <input type="date" id="end_date" class="span3" required>
                                    <button type="submit" class="btn btn-success">Show Report</button>
                                    <button type="button" class="btn btn-warning" onclick="resetSearch()">Reset</button>
                                </div>
                            </div>
                        </form>

                        <div id="loadingSpinner" class="loading-spinner" style="display: none;">
                            <i class="icon-spinner icon-spin"></i> Loading...
                        </div>
                        
                        <div id="errorMessage" class="alert alert-error" style="display: none;">
                            <button class="close" data-dismiss="alert">×</button>
                            <span></span>
                        </div>

                        <div id="reportTableContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
let currentPage = 1;
const recordsPerPage = 10;

document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('start_date').value = formatDateForInput(firstDay);
    document.getElementById('end_date').value = formatDateForInput(today);
    
    loadReport(true);
    
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadReport(true);
    });
});

async function loadReport(useFilters = false) {
    showSpinner();
    hideError();
    
    try {
        let url = 'http://localhost/imsfin/IMS_API/api/reports/get_return_report.php';
        url += `?page=${currentPage}&per_page=${recordsPerPage}`;
        
        if (useFilters) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                if (new Date(startDate) > new Date(endDate)) {
                    throw new Error('Start date cannot be later than end date');
                }
                url += `&start_date=${startDate}&end_date=${endDate}`;
            }
        }

        const response = await fetch(url);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to load report');
        }

        if (!result.success) {
            throw new Error(result.message || 'Failed to load report data');
        }

        displayReport(result.data, result.pagination);
    } catch (error) {
        showError(error.message);
        clearReport();
    } finally {
        hideSpinner();
    }
}

function displayReport(data, pagination) {
    const container = document.getElementById('reportTableContainer');
    let html = `
        <div class="widget-box">
            <div class="widget-content nopadding">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Return Date</th>
                            <th>Bill No</th>
                            <th>Returned By</th>
                            <th>Product Company</th>
                            <th>Product Name</th>
                            <th>Unit</th>
                            <th>Packing Size</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
    `;

    if (data && data.length > 0) {
        let grandTotal = 0;
        let totalQuantity = 0;

        data.forEach(item => {
            const total = parseFloat(item.product_price) * parseFloat(item.product_qty);
            grandTotal += total;
            totalQuantity += parseFloat(item.product_qty);

            html += `
                <tr>
                    <td>${formatDate(item.return_date)}</td>
                    <td>${item.bill_no}</td>
                    <td>${item.return_by}</td>
                    <td>${item.product_company}</td>
                    <td>${item.product_name}</td>
                    <td>${item.product_unit}</td>
                    <td>${item.packing_size}</td>
                    <td class="text-right">₱${formatNumber(item.product_price)}</td>
                    <td class="text-right">${formatNumber(item.product_qty)}</td>
                    <td class="text-right">₱${formatNumber(total)}</td>
                </tr>
            `;
        });

        html += `
            <tr class="total-row">
                <td colspan="8" class="text-right"><strong>Page Totals:</strong></td>
                <td class="text-right"><strong>${formatNumber(totalQuantity)}</strong></td>
                <td class="text-right"><strong>₱${formatNumber(grandTotal)}</strong></td>
            </tr>
        `;
    } else {
        html += '<tr><td colspan="10" class="text-center">No records found</td></tr>';
    }

    html += `
                    </tbody>
                </table>
            </div>
        </div>
    `;

    // Add pagination controls
    if (pagination && pagination.total_pages > 1) {
        html += `
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
                    <span class="page-numbers">
        `;

        // Show page numbers
        for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
            html += `
                <button class="btn ${i === pagination.current_page ? 'btn-info' : ''}" onclick="changePage(${i})">
                    ${i}
                </button>
            `;
        }

        html += `
                    </span>
                    <button class="btn" onclick="changePage(${pagination.current_page + 1})" ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                        Next
                    </button>
                    <button class="btn" onclick="changePage(${pagination.total_pages})" ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                        Last
                    </button>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    loadReport(true);
}

function resetSearch() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('start_date').value = formatDateForInput(firstDay);
    document.getElementById('end_date').value = formatDateForInput(today);
    currentPage = 1;
    loadReport(true);
}

function formatDate(dateString) {
    if (!dateString) return '';
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric'
    };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}

function formatNumber(number) {
    return parseFloat(number).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function resetSearch() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('start_date').value = formatDateForInput(firstDay);
    document.getElementById('end_date').value = formatDateForInput(today);
    loadReport(true);
}

function showSpinner() {
    document.getElementById('loadingSpinner').style.display = 'block';
}

function hideSpinner() {
    document.getElementById('loadingSpinner').style.display = 'none';
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.style.display = 'block';
    errorDiv.querySelector('span').textContent = message;
}

function hideError() {
    document.getElementById('errorMessage').style.display = 'none';
}

function clearReport() {
    document.getElementById('reportTableContainer').innerHTML = '';
}
</script>

<style>
.loading-spinner {
    text-align: center;
    padding: 20px;
    font-size: 16px;
}

.icon-spin {
    animation: spin 1s infinite linear;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.total-row {
    background-color: #f9f9f9;
}

.total-row td {
    font-weight: bold;
    border-top: 2px solid #ddd;
}

.alert {
    margin-bottom: 15px;
}

.widget-box {
    margin-top: 15px;
}

.controls .btn {
    margin-left: 10px;
}

.help-inline {
    padding: 0 10px;
}

.pagination-container {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
}

.dataTables_info {
    color: #666;
}

.dataTables_paginate {
    text-align: right;
}

.dataTables_paginate .btn {
    margin: 0 2px;
    padding: 4px 10px;
}

.dataTables_paginate .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-numbers {
    margin: 0 10px;
}

.page-numbers .btn {
    min-width: 35px;
}

.btn-info {
    color: #ffffff;
    background-color: #49afcd;
}
</style>

<?php include 'footer.php'; ?>