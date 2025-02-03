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
            <a href="index.html" class="tip-bottom">
                <i class="icon-home"></i>View Bills
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <form id="searchForm" class="form-inline">
                <div class="form-group">
                    <label for="start_date">Select Start Date</label>
                    <input type="date" id="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Select End Date</label>
                    <input type="date" id="end_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="records_per_page">Records per page:</label>
                    <select id="records_per_page" class="form-control" onchange="changePageSize(this.value)">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Show Bills From These Dates</button>
                <button type="button" class="btn btn-warning" onclick="resetSearch()">Clear Search</button>
            </form>

            <br>
            <div id="loadingSpinner" class="spinner" style="display: none;"></div>
            <div id="billsTableContainer"></div>
            <div id="pagination" class="pagination"></div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let totalPages = 1;
let recordsPerPage = 10;

document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('start_date').value = formatDateForInput(firstDay);
    document.getElementById('end_date').value = formatDateForInput(today);
    
    loadBills(true);
    
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        loadBills(true);
    });
});

async function loadBills(useFilters = false) {
    showSpinner();
    try {
        let url = new URL('http://localhost/imsfin/IMS_API/api/sales/bills/get_bills.php', window.location.origin);
        url.searchParams.append('page', currentPage);
        url.searchParams.append('limit', recordsPerPage);
        
        if (useFilters) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                if (new Date(startDate) > new Date(endDate)) {
                    throw new Error('Start date cannot be later than end date');
                }
                url.searchParams.append('start_date', startDate);
                url.searchParams.append('end_date', endDate);
            }
        }

        const response = await fetch(url);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to load bills');
        }

        displayBills(result.data);
        updatePagination(result.pagination);
    } catch (error) {
        showError('Error loading bills: ' + error.message);
    } finally {
        hideSpinner();
    }
}

function displayBills(bills) {
    const container = document.getElementById('billsTableContainer');
    
    let html = `
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Bill No</th>
                    <th>Bill Generated By</th>
                    <th>Full Name</th>
                    <th>Bill Type</th>
                    <th>Bill Date</th>
                    <th>Bill Total</th>
                    <th>View Details</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (bills && bills.length > 0) {
        bills.forEach(bill => {
            html += `
                <tr>
                    <td>${bill.bill_no}</td>
                    <td>${bill.username}</td>
                    <td>${bill.full_name}</td>
                    <td>${bill.bill_type}</td>
                    <td>${formatDate(bill.date)}</td>
                    <td>₱${formatNumber(bill.total_amount || 0)}</td>
                    <td>
                        <a href="view_bills_details.php?id=${bill.id}" class="btn btn-info btn-mini">
                            <i class="icon-eye-open"></i> View Details
                        </a>
                    </td>
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="7" class="text-center">No bills found</td>
            </tr>
        `;
    }

    html += `
            </tbody>
        </table>
    `;

    container.innerHTML = html;
}

function updatePagination(pagination) {
    totalPages = pagination.total_pages;
    const paginationContainer = document.getElementById('pagination');
    
    let html = '<ul class="pagination">';
    
    // Previous button
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
    </li>`;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (
            i === 1 || // First page
            i === totalPages || // Last page
            (i >= currentPage - 2 && i <= currentPage + 2) // Pages around current page
        ) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>`;
        } else if (
            i === currentPage - 3 ||
            i === currentPage + 3
        ) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Next button
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
    </li>`;

    html += '</ul>';
    html += `<div class="pagination-info">Page ${currentPage} of ${totalPages} (${pagination.total_records} records)</div>`;

    paginationContainer.innerHTML = html;
}

function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadBills(true);
}

function changePageSize(size) {
    recordsPerPage = parseInt(size);
    currentPage = 1;
    loadBills(true);
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
    loadBills(true);
}

function showSpinner() {
    document.getElementById('loadingSpinner').style.display = 'flex';
}

function hideSpinner() {
    document.getElementById('loadingSpinner').style.display = 'none';
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.textContent = message;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(errorDiv, container.firstChild);
    
    setTimeout(() => errorDiv.remove(), 3000);
}
</script>
<style>
    .pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination ul {
    list-style: none;
    padding: 0;
    display: flex;
    gap: 5px;
}

.pagination .page-item {
    margin: 0 2px;
}

.pagination .page-link {
    padding: 8px 12px;
    border: 1px solid #ddd;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
}

.pagination .active .page-link {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination .disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    cursor: not-allowed;
}

.pagination-info {
    text-align: center;
    margin-top: 10px;
    color: #6c757d;
}

#records_per_page {
    width: auto;
    display: inline-block;
}

.spinner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.btn-mini {
    padding: 2px 6px;
    font-size: 11px;
}

.text-center {
    text-align: center;
}

.form-group {
    margin-right: 15px;
}

.form-control {
    margin-left: 5px;
}
</style>
<?php include 'footer.php'; ?>