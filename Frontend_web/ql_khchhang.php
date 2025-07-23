<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Fetch all "khách hàng" users with additional statistics
$sql = "SELECT * FROM users WHERE role = 'khachhang' ORDER BY created_at DESC";
$result = $conn->query($sql);

// Calculate statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_customers,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_customers_month,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_customers_week,
        COUNT(CASE WHEN phone IS NOT NULL AND phone != '' THEN 1 END) as customers_with_phone
    FROM users WHERE role = 'khachhang'";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get customer orders count
$orders_query = "
    SELECT u.id, COUNT(t.id) as order_count, COALESCE(SUM(t.tongTien), 0) as total_spent
    FROM users u 
    LEFT JOIN thanhtoan t ON u.id = t.user_id 
    WHERE u.role = 'khachhang' 
    GROUP BY u.id";
$orders_result = $conn->query($orders_query);
$customer_orders = [];
while ($row = $orders_result->fetch_assoc()) {
    $customer_orders[$row['id']] = [
        'order_count' => $row['order_count'],
        'total_spent' => $row['total_spent']
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khách Hàng - Shop Em Bé</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* background: linear-gradient(135deg, #a39074 0%, #8d7a5e 50%, #6d5d47 100%); */
            min-height: 100vh;
            color: #333;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 50%, #a39074 100%);
            height: 100vh;
            padding: 0;
            box-shadow: 4px 0 15px rgba(163, 144, 116, 0.3);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar h2 {
            color: #ecf0f1;
            text-align: center;
            padding: 25px 20px;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 1px solid rgba(163, 144, 116, 0.3);
            background: rgba(163, 144, 116, 0.2);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 18px 25px;
            color: #bdc3c7;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar a:hover {
            background: rgba(163, 144, 116, 0.2);
            color: #a39074;
            border-left-color: #a39074;
            transform: translateX(5px);
        }

        .sidebar a.active {
            background: rgba(163, 144, 116, 0.3);
            color: #a39074;
            border-left-color: #a39074;
        }

        .sidebar a i {
            margin-right: 12px;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        /* Header Styling */
        .header {
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            height: 70px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 2px 20px rgba(163, 144, 116, 0.2);
            z-index: 999;
            border-bottom: 3px solid #a39074;
        }

        .header h3 {
            color: #a39074;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .search-bar {
            position: relative;
            width: 350px;
        }

        .search-bar input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #a39074;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .search-bar input:focus {
            border-color: #8d7a5e;
            background: white;
            box-shadow: 0 0 0 3px rgba(163, 144, 116, 0.2);
        }

        .search-bar::before {
            content: '\f002';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a39074;
            font-size: 14px;
        }

        #searchResults {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #a39074;
            border-top: none;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 8px 25px rgba(163, 144, 116, 0.3);
            z-index: 1001;
            max-height: 300px;
            overflow-y: auto;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        /* Main Content */
        .content {
            margin-left: 280px;
            margin-top: 70px;
            padding: 30px;
            min-height: calc(100vh - 70px);
        }

        .page-title {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            padding: 20px;
            background: rgba(163, 144, 116, 0.2);
            border-radius: 15px;
            border-left: 5px solid #a39074;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.95);
            border-left: 4px solid #a39074;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(163, 144, 116, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(163, 144, 116, 0.3);
        }

        .stats-card .info h3 {
            color: #a39074;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 600;
        }

        .stats-card .info p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .stats-card .number {
            font-size: 28px;
            font-weight: bold;
            color: #a39074;
        }

        .stats-card i {
            font-size: 40px;
            color: #a39074;
            opacity: 0.7;
            margin-left: 15px;
        }

        /* Filter and Actions */
        .filter-actions-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(163, 144, 116, 0.2);
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid #a39074;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
            background: white;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .filter-select:focus {
            border-color: #8d7a5e;
            box-shadow: 0 0 0 3px rgba(163, 144, 116, 0.2);
        }

        .action-group {
            display: flex;
            gap: 10px;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(163, 144, 116, 0.2);
            overflow: hidden;
            border-top: 4px solid #a39074;
            margin-bottom: 30px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-header h2 {
            color: #a39074;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-info {
            color: #666;
            font-size: 14px;
        }

        /* Table Styling */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            min-width: 1000px;
        }

        th {
            background: linear-gradient(135deg, #a39074 0%, #8d7a5e 100%);
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th:first-child {
            border-top-left-radius: 10px;
        }

        th:last-child {
            border-top-right-radius: 10px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid rgba(163, 144, 116, 0.1);
            font-size: 14px;
            vertical-align: middle;
        }

        tr:hover {
            background: rgba(163, 144, 116, 0.05);
            transition: all 0.2s ease;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Customer Info */
        .customer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .customer-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #a39074, #8d7a5e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            flex-shrink: 0;
        }

        .customer-details h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }

        .customer-details p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        .customer-details i {
            color: #a39074;
            margin-right: 5px;
        }

        /* Contact Info */
        .contact-info p {
            margin: 5px 0;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .contact-info i {
            color: #a39074;
            width: 16px;
            text-align: center;
        }

        /* Stats Badges */
        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(163, 144, 116, 0.1);
            color: #a39074;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin: 2px;
        }

        .stats-badge.orders {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .stats-badge.spent {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        /* Button Styling */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .btn i {
            margin-right: 5px;
            font-size: 12px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #a39074, #8d7a5e);
            color: white;
            box-shadow: 0 4px 15px rgba(163, 144, 116, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(163, 144, 116, 0.5);
            background: linear-gradient(135deg, #8d7a5e, #6d5d47);
            color: white;
            text-decoration: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            color: white;
            text-decoration: none;
            background: linear-gradient(135deg, #c82333, #bd2130);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(163, 144, 116, 0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a39074;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            display: block;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .empty-state p {
            font-size: 16px;
            opacity: 0.8;
        }

        /* Customer Form Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(163, 144, 116, 0.3);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #a39074;
        }

        .modal-header h2 {
            color: #a39074;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .close {
            color: #a39074;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #8d7a5e;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #a39074;
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #a39074;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            border-color: #8d7a5e;
            background: white;
            box-shadow: 0 0 0 3px rgba(163, 144, 116, 0.2);
        }

        /* Icon Colors */
        .icon-primary {
            color: #a39074;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .header {
                left: 0;
                padding: 0 20px;
            }

            .content {
                margin-left: 0;
                padding: 20px;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .filter-actions-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-bar {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 10px 8px;
            }

            .customer-info {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #a39074;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #8d7a5e;
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(163, 144, 116, 0.3);
            border-radius: 50%;
            border-top-color: #a39074;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div id="menu" class="sidebar">
        <h2><i class="fas fa-baby icon-primary"></i> Shop Bán Đồ Em Bé</h2>
        <a href="./admin2.php">
            <i class="fas fa-box"></i>
            QUẢN LÝ SẢN PHẨM
        </a>
        <a href="./ql_donhang.php">
            <i class="fas fa-shopping-cart"></i>
            QUẢN LÝ ĐƠN HÀNG
        </a>
        <a href="./ql_khchhang.php" class="active">
            <i class="fas fa-users"></i>
            QUẢN LÝ KHÁCH HÀNG
        </a>
        <!-- <a href="./cart_management.php">
            <i class="fas fa-shopping-basket"></i>
            QUẢN LÝ GIỎ HÀNG
        </a>
        <a href="./Thongke.php">
            <i class="fas fa-chart-bar"></i>
            THỐNG KÊ DOANH THU
        </a> -->
    </div>

    <!-- Header -->
    <div class="header">
        <h3><i class="fas fa-users icon-primary"></i> Quản Lý Khách Hàng</h3>
        <div class="search-bar">
            <input type="text" placeholder="Tìm kiếm khách hàng..." id="searchInput" />
            <div id="searchResults" style="display: none;"></div>
        </div>
        <div class="header-actions">
            <div style="color: #a39074; font-weight: 600;">
                <i class="fas fa-calendar-alt icon-primary"></i>
                <?php echo date('d/m/Y'); ?>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <h2 class="page-title">
            <i class="fas fa-users-cog icon-primary"></i> Khách Hàng
        </h2>

        <!-- Stats Cards -->
        <!-- <div class="stats-container">
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-users icon-primary"></i> Tổng Khách Hàng</h3>
                    <p>Tổng số khách hàng đã đăng ký</p>
                </div>
                <div class="number"><?php echo number_format($stats['total_customers'] ?? 0); ?></div>
                <i class="fas fa-users"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-user-plus icon-primary"></i> KH Mới (30 ngày)</h3>
                    <p>Khách hàng đăng ký trong 30 ngày</p>
                </div>
                <div class="number"><?php echo number_format($stats['new_customers_month'] ?? 0); ?></div>
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-fire icon-primary"></i> KH Mới (7 ngày)</h3>
                    <p>Khách hàng đăng ký trong 7 ngày</p>
                </div>
                <div class="number"><?php echo number_format($stats['new_customers_week'] ?? 0); ?></div>
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-phone icon-primary"></i> Có SĐT</h3>
                    <p>Khách hàng có số điện thoại</p>
                </div>
                <div class="number"><?php echo number_format($stats['customers_with_phone'] ?? 0); ?></div>
                <i class="fas fa-address-book"></i>
            </div>
        </div> -->

        <!-- Filter and Actions -->
        <!-- <div class="filter-actions-container">
            <div class="filter-group">
                <select class="filter-select" id="sortFilter">
                    <option value="newest">Mới nhất</option>
                    <option value="oldest">Cũ nhất</option>
                    <option value="name_asc">Tên A-Z</option>
                    <option value="name_desc">Tên Z-A</option>
                    <option value="most_orders">Nhiều đơn hàng nhất</option>
                    <option value="highest_spent">Chi tiêu cao nhất</option>
                </select>
                <select class="filter-select" id="statusFilter">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Không hoạt động</option>
                    <option value="has_orders">Có đơn hàng</option>
                    <option value="no_orders">Chưa có đơn hàng</option>
                </select>
            </div>
            <div class="action-group">
                <button class="btn btn-success" onclick="openAddCustomerModal()">
                    <i class="fas fa-user-plus"></i> Thêm KH
                </button>
                <button class="btn btn-info" onclick="exportCustomers()">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </button>
                <button class="btn btn-primary" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Làm mới
                </button>
            </div>
        </div> -->

        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <h2><i class="fas fa-list icon-primary"></i> Danh Sách Khách Hàng</h2>
                <div class="table-info">
                    Hiển thị <?php echo $result->num_rows; ?> khách hàng
                </div>
            </div>
            <div class="table-wrapper">
                <table id="customersTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> STT</th>
                            <th><i class="fas fa-user"></i> Thông tin khách hàng</th>
                            <th><i class="fas fa-envelope"></i> Liên hệ</th>
                            <th><i class="fas fa-map-marker-alt"></i> Địa chỉ</th>
                            <th><i class="fas fa-shopping-cart"></i> Thống kê</th>
                            <th><i class="fas fa-calendar"></i> Ngày tham gia</th>
                            <th><i class="fas fa-cogs"></i> Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            $stt = 1;
                            while ($row = $result->fetch_assoc()) {
                                $customer_id = $row['id'];
                                $order_count = $customer_orders[$customer_id]['order_count'] ?? 0;
                                $total_spent = $customer_orders[$customer_id]['total_spent'] ?? 0;
                                $customer_initial = strtoupper(substr($row['name'], 0, 1));

                                echo "<tr data-customer-id='" . $customer_id . "' data-customer-name='" . htmlspecialchars($row['name']) . "'>";

                                // STT
                                echo "<td><strong style='color: #a39074;'>" . $stt++ . "</strong></td>";

                                // Customer Info
                                echo "<td>";
                                echo "<div class='customer-info'>";
                                echo "<div class='customer-avatar'>" . $customer_initial . "</div>";
                                echo "<div class='customer-details'>";
                                echo "<h4><i class='fas fa-user-circle icon-primary'></i> " . htmlspecialchars($row['name']) . "</h4>";
                                echo "<p><i class='fas fa-id-badge'></i> ID: " . $customer_id . "</p>";
                                echo "</div>";
                                echo "</div>";
                                echo "</td>";

                                // Contact Info
                                echo "<td>";
                                echo "<div class='contact-info'>";
                                echo "<p><i class='fas fa-envelope'></i> " . htmlspecialchars($row['email']) . "</p>";
                                if (!empty($row['phone'])) {
                                    echo "<p><i class='fas fa-phone'></i> " . htmlspecialchars($row['phone']) . "</p>";
                                } else {
                                    echo "<p><i class='fas fa-phone-slash'></i> <em>Chưa có SĐT</em></p>";
                                }
                                echo "</div>";
                                echo "</td>";

                                // Address
                                echo "<td>";
                                if (!empty($row['address'])) {
                                    echo "<p><i class='fas fa-map-marker-alt icon-primary'></i> " . htmlspecialchars($row['address']) . "</p>";
                                } else {
                                    echo "<p><i class='fas fa-map-marker-slash'></i> <em>Chưa có địa chỉ</em></p>";
                                }
                                echo "</td>";

                                // Statistics
                                echo "<td>";
                                echo "<div style='display: flex; flex-direction: column; gap: 5px;'>";
                                echo "<span class='stats-badge orders'>";
                                echo "<i class='fas fa-shopping-cart'></i> " . $order_count . " đơn hàng";
                                echo "</span>";
                                echo "<span class='stats-badge spent'>";
                                echo "<i class='fas fa-money-bill-wave'></i> " . number_format($total_spent, 0, ',', '.') . " VNĐ";
                                echo "</span>";
                                echo "</div>";
                                echo "</td>";

                                // Join Date
                                echo "<td>";
                                $join_date = isset($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : 'N/A';
                                echo "<span class='stats-badge'><i class='fas fa-calendar-plus'></i> " . $join_date . "</span>";
                                echo "</td>";

                                // Actions
                                echo "<td>";
                                echo "<div style='display: flex; gap: 5px; flex-wrap: wrap;'>";
                                echo "<button class='btn btn-info' onclick='viewCustomerDetails(" . $customer_id . ")' title='Xem chi tiết'>";
                                echo "<i class='fas fa-eye'></i>";
                                echo "</button>";
                                echo "<button class='btn btn-primary' onclick='editCustomer(" . $customer_id . ")' title='Chỉnh sửa'>";
                                echo "<i class='fas fa-edit'></i>";
                                echo "</button>";
                                echo "<a href='../Backend_thongtinnguoidung/delete_kh.php?id=" . $customer_id . "' class='btn btn-danger' onclick='return confirm(\"Bạn có chắc chắn muốn xóa khách hàng này?\")' title='Xóa'>";
                                echo "<i class='fas fa-trash'></i>";
                                echo "</a>";
                                echo "</div>";
                                echo "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>";
                            echo "<div class='empty-state'>";
                            echo "<i class='fas fa-users'></i>";
                            echo "<h3>Chưa có khách hàng nào</h3>";
                            echo "<p>Hiện tại chưa có khách hàng nào đăng ký</p>";
                            echo "</div>";
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="./Menu1.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <button type="button" class="btn btn-success" onclick="bulkActions()">
                <i class="fas fa-tasks"></i> Thao tác hàng loạt
            </button>
            <button type="button" class="btn btn-info" onclick="sendNewsletter()">
                <i class="fas fa-envelope-open"></i> Gửi Newsletter
            </button>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div id="addCustomerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus icon-primary"></i> Thêm Khách Hàng Mới</h2>
                <span class="close" onclick="closeAddCustomerModal()">&times;</span>
            </div>
            <form id="addCustomerForm" method="post" action="add_customer.php">
                <div class="form-group">
                    <label for="customerName"><i class="fas fa-user icon-primary"></i> Tên khách hàng</label>
                    <input type="text" class="form-control" id="customerName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="customerEmail"><i class="fas fa-envelope icon-primary"></i> Email</label>
                    <input type="email" class="form-control" id="customerEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="customerPhone"><i class="fas fa-phone icon-primary"></i> Số điện thoại</label>
                    <input type="tel" class="form-control" id="customerPhone" name="phone">
                </div>
                <div class="form-group">
                    <label for="customerAddress"><i class="fas fa-map-marker-alt icon-primary"></i> Địa chỉ</label>
                    <textarea class="form-control" id="customerAddress" name="address" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="customerPassword"><i class="fas fa-lock icon-primary"></i> Mật khẩu</label>
                    <input type="password" class="form-control" id="customerPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%; padding: 15px;">
                    <i class="fas fa-user-plus"></i> Thêm Khách Hàng
                </button>
            </form>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#customersTable tbody tr');

            rows.forEach(row => {
                const customerName = row.getAttribute('data-customer-name').toLowerCase();
                const rowText = row.textContent.toLowerCase();

                if (customerName.includes(searchTerm) || rowText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Modal functions
        function openAddCustomerModal() {
            document.getElementById('addCustomerModal').style.display = 'block';
        }

        function closeAddCustomerModal() {
            document.getElementById('addCustomerModal').style.display = 'none';
        }

        // Customer actions
        function viewCustomerDetails(customerId) {
            // Redirect to customer details page
            window.location.href = `customer_details.php?id=${customerId}`;
        }

        function editCustomer(customerId) {
            // Redirect to edit customer page
            window.location.href = `edit_customer.php?id=${customerId}`;
        }

        // Utility functions
        function exportCustomers() {
            alert('Chức năng xuất Excel sẽ được triển khai');
        }

        function refreshData() {
            location.reload();
        }

        function bulkActions() {
            alert('Chức năng thao tác hàng loạt sẽ được triển khai');
        }

        function sendNewsletter() {
            alert('Chức năng gửi newsletter sẽ được triển khai');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addCustomerModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Filter functionality
        document.getElementById('sortFilter').addEventListener('change', function() {
            // This would typically send an AJAX request to sort data
            console.log('Sort by:', this.value);
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            // This would typically send an AJAX request to filter data
            console.log('Filter by status:', this.value);
        });

        // Mobile menu toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }

        // Add click outside to close mobile menu
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const isClickInsideSidebar = sidebar.contains(event.target);

            if (!isClickInsideSidebar && window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        });

        // Enhanced search with API integration
        document.getElementById("searchInput").addEventListener("input", function() {
            const keyword = document.getElementById("searchInput").value.trim();
            const resultsContainer = document.getElementById("searchResults");

            if (keyword === "") {
                resultsContainer.style.display = "none";
                return;
            }

            // Show loading state
            resultsContainer.innerHTML = '<div style="padding: 15px; text-align: center;"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</div>';
            resultsContainer.style.display = "block";

            // Simulate API call (replace with actual endpoint)
            fetch(`/QL_web_new_born/timkiemkhachhang.php?keyword=${encodeURIComponent(keyword)}&searchBy=role`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Lỗi phản hồi từ máy chủ");
                    }
                    return response.json();
                })
                .then(data => {
                    resultsContainer.innerHTML = "";

                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<div style="padding: 15px; text-align: center; color: #a39074;"><i class="fas fa-search"></i> Không tìm thấy khách hàng phù hợp.</div>';
                        return;
                    }

                    const resultList = document.createElement("div");
                    resultList.style.padding = "10px";

                    data.forEach(item => {
                        const resultItem = document.createElement("div");
                        resultItem.style.cssText = `
                            padding: 12px;
                            border-bottom: 1px solid rgba(163, 144, 116, 0.2);
                            cursor: pointer;
                            transition: background 0.2s ease;
                        `;

                        resultItem.innerHTML = `
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 35px; height: 35px; background: linear-gradient(135deg, #a39074, #8d7a5e); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                    ${item.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #333; margin-bottom: 2px;">${item.name}</div>
                                    <div style="font-size: 12px; color: #666;">${item.email} • ${item.role}</div>
                                </div>
                            </div>
                        `;

                        resultItem.addEventListener('mouseenter', function() {
                            this.style.background = 'rgba(163, 144, 116, 0.1)';
                        });

                        resultItem.addEventListener('mouseleave', function() {
                            this.style.background = 'transparent';
                        });

                        resultItem.addEventListener('click', function() {
                            window.location.href = `/QL_web_new_born/Frontend_web/ql_khchhang.php?id=${item.id}`;
                        });

                        resultList.appendChild(resultItem);
                    });

                    resultsContainer.appendChild(resultList);
                })
                .catch(error => {
                    console.error("Lỗi tìm kiếm:", error);
                    resultsContainer.innerHTML = '<div style="padding: 15px; text-align: center; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra khi tìm kiếm.</div>';
                });
        });

        // Hide search results when clicking outside
        document.addEventListener("click", function(event) {
            const resultsContainer = document.getElementById("searchResults");
            const searchInput = document.getElementById("searchInput");

            if (!resultsContainer.contains(event.target) && event.target !== searchInput) {
                resultsContainer.style.display = "none";
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>