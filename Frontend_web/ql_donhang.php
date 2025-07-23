<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhapky.php");
    exit();
}

// Query to retrieve completed orders with additional statistics
$query = "
    SELECT t.id AS order_id, t.hoTen, t.email, t.soDienThoai, t.diaChi, t.ngayThanhToan, t.tongTien
    FROM thanhtoan t
    ORDER BY t.ngayThanhToan DESC";
$result = $conn->query($query);

if (!$result) {
    die("Error retrieving orders: " . $conn->error);
}

// Calculate statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(tongTien) as total_revenue,
        AVG(tongTien) as avg_order_value,
        COUNT(DISTINCT DATE(ngayThanhToan)) as active_days
    FROM thanhtoan";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get recent orders count (last 7 days)
$recent_query = "
    SELECT COUNT(*) as recent_orders 
    FROM thanhtoan 
    WHERE ngayThanhToan >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_result = $conn->query($recent_query);
$recent_stats = $recent_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - Shop Em Bé</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #a39074 0%, #8d7a5e 50%, #6d5d47 100%);
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

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 2px solid #a39074;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .search-box input:focus {
            border-color: #8d7a5e;
            background: white;
            box-shadow: 0 0 0 3px rgba(163, 144, 116, 0.2);
        }

        .search-box::before {
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

        /* Filter Controls */
        .filter-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(163, 144, 116, 0.2);
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #a39074;
            text-transform: uppercase;
        }

        .filter-select,
        .filter-input {
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

        .filter-select:focus,
        .filter-input:focus {
            border-color: #8d7a5e;
            box-shadow: 0 0 0 3px rgba(163, 144, 116, 0.2);
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

        /* Order ID Styling */
        .order-id {
            background: linear-gradient(135deg, #a39074, #8d7a5e);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            min-width: 60px;
            display: inline-block;
        }

        /* Customer Info */
        .customer-info h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        .customer-info p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        .customer-info i {
            color: #a39074;
            margin-right: 5px;
        }

        /* Order Total */
        .order-total {
            font-weight: 700;
            color: #a39074;
            font-size: 16px;
            text-align: right;
        }

        /* Date Badge */
        .date-badge {
            background: rgba(163, 144, 116, 0.1);
            color: #a39074;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
        }

        /* Status Badge */
        .status-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
        }

        /* Button Styling */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .btn i {
            margin-right: 8px;
            font-size: 14px;
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

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }

        .pagination ul {
            list-style: none;
            display: flex;
            gap: 5px;
            padding: 0;
            margin: 0;
        }

        .pagination li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            text-decoration: none;
            border: 2px solid #a39074;
            border-radius: 8px;
            color: #a39074;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination li a:hover,
        .pagination li.active a {
            background: linear-gradient(135deg, #a39074, #8d7a5e);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(163, 144, 116, 0.3);
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

            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
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
        <a href="./ql_donhang.php" class="active">
            <i class="fas fa-shopping-cart"></i>
            QUẢN LÝ ĐƠN HÀNG
        </a>
        <a href="./ql_khchhang.php">
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
        <h3><i class="fas fa-shopping-cart icon-primary"></i> Quản Lý Đơn Hàng</h3>
        <div class="header-actions">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm kiếm đơn hàng...">
            </div>
            <div style="color: #a39074; font-weight: 600;">
                <i class="fas fa-calendar-alt icon-primary"></i>
                <?php echo date('d/m/Y'); ?>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <h2 class="page-title">
            <i class="fas fa-clipboard-list icon-primary"></i> Dashboard Đơn Hàng
        </h2>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-shopping-cart icon-primary"></i> Tổng Đơn Hàng</h3>
                    <p>Tổng số đơn hàng đã thanh toán</p>
                </div>
                <div class="number"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-money-bill-wave icon-primary"></i> Tổng Doanh Thu</h3>
                    <p>Tổng doanh thu từ các đơn hàng</p>
                </div>
                <div class="number"><?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?></div>
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-calculator icon-primary"></i> Giá Trị TB</h3>
                    <p>Giá trị trung bình mỗi đơn hàng</p>
                </div>
                <div class="number"><?php echo number_format($stats['avg_order_value'] ?? 0, 0, ',', '.'); ?></div>
                <i class="fas fa-coins"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-clock icon-primary"></i> Đơn Gần Đây</h3>
                    <p>Đơn hàng trong 7 ngày qua</p>
                </div>
                <div class="number"><?php echo number_format($recent_stats['recent_orders'] ?? 0); ?></div>
                <i class="fas fa-history"></i>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="filter-container">
            <div class="filter-group">
                <label>Lọc theo ngày</label>
                <select class="filter-select" id="dateFilter">
                    <option value="">Tất cả</option>
                    <option value="today">Hôm nay</option>
                    <option value="week">7 ngày qua</option>
                    <option value="month">30 ngày qua</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Từ ngày</label>
                <input type="date" class="filter-input" id="fromDate">
            </div>
            <div class="filter-group">
                <label>Đến ngày</label>
                <input type="date" class="filter-input" id="toDate">
            </div>
            <div class="filter-group">
                <label>Sắp xếp</label>
                <select class="filter-select" id="sortOrder">
                    <option value="desc">Mới nhất</option>
                    <option value="asc">Cũ nhất</option>
                    <option value="amount_desc">Giá cao nhất</option>
                    <option value="amount_asc">Giá thấp nhất</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="applyFilters()">
                <i class="fas fa-filter"></i> Áp dụng
            </button>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <h2><i class="fas fa-list icon-primary"></i> Danh Sách Đơn Hàng</h2>
                <div class="table-info">
                    Hiển thị <?php echo $result->num_rows; ?> đơn hàng
                </div>
            </div>
            <div class="table-wrapper">
                <table id="ordersTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID Đơn hàng</th>
                            <th><i class="fas fa-user"></i> Thông tin khách hàng</th>
                            <th><i class="fas fa-phone"></i> Liên hệ</th>
                            <th><i class="fas fa-map-marker-alt"></i> Địa chỉ</th>
                            <th><i class="fas fa-calendar"></i> Ngày thanh toán</th>
                            <th><i class="fas fa-money-bill"></i> Tổng tiền</th>
                            <th><i class="fas fa-check-circle"></i> Trạng thái</th>
                            <th><i class="fas fa-cogs"></i> Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($order = $result->fetch_assoc()) {
                                echo "<tr data-order-id='" . $order['order_id'] . "' data-customer='" . htmlspecialchars($order['hoTen']) . "' data-date='" . $order['ngayThanhToan'] . "' data-amount='" . $order['tongTien'] . "'>";

                                // Order ID
                                echo "<td><span class='order-id'>#" . htmlspecialchars($order['order_id']) . "</span></td>";

                                // Customer Info
                                echo "<td>";
                                echo "<div class='customer-info'>";
                                echo "<h4><i class='fas fa-user-circle icon-primary'></i> " . htmlspecialchars($order['hoTen']) . "</h4>";
                                echo "<p><i class='fas fa-envelope'></i> " . htmlspecialchars($order['email']) . "</p>";
                                echo "</div>";
                                echo "</td>";

                                // Contact
                                echo "<td>";
                                echo "<p><i class='fas fa-phone icon-primary'></i> " . htmlspecialchars($order['soDienThoai']) . "</p>";
                                echo "</td>";

                                // Address
                                echo "<td>";
                                echo "<p><i class='fas fa-map-marker-alt icon-primary'></i> " . htmlspecialchars($order['diaChi']) . "</p>";
                                echo "</td>";

                                // Date
                                echo "<td><span class='date-badge'>" . date('d/m/Y H:i', strtotime($order['ngayThanhToan'])) . "</span></td>";

                                // Total
                                echo "<td><span class='order-total'>" . number_format($order['tongTien'], 0, ',', '.') . " VNĐ</span></td>";

                                // Status
                                echo "<td><span class='status-badge'>Đã thanh toán</span></td>";

                                // Actions
                                echo "<td>";
                                echo "<div style='display: flex; gap: 5px;'>";
                                echo "<a href='order_details.php?id=" . $order['order_id'] . "' class='btn btn-info' style='padding: 6px 12px; font-size: 12px;'>";
                                echo "<i class='fas fa-eye'></i>";
                                echo "</a>";
                                echo "<a href='print_order.php?id=" . $order['order_id'] . "' class='btn btn-success' style='padding: 6px 12px; font-size: 12px;'>";
                                echo "<i class='fas fa-print'></i>";
                                echo "</a>";
                                echo "</div>";
                                echo "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>";
                            echo "<div class='empty-state'>";
                            echo "<i class='fas fa-shopping-cart'></i>";
                            echo "<h3>Chưa có đơn hàng nào</h3>";
                            echo "<p>Hiện tại chưa có đơn hàng nào được thanh toán</p>";
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
            <button type="button" class="btn btn-success" onclick="exportOrders()">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </button>
            <button type="button" class="btn btn-info" onclick="printOrders()">
                <i class="fas fa-print"></i> In danh sách
            </button>
            <button type="button" class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> Làm mới
            </button>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#ordersTable tbody tr');

            rows.forEach(row => {
                const customer = row.getAttribute('data-customer').toLowerCase();
                const orderId = row.getAttribute('data-order-id').toLowerCase();
                const rowText = row.textContent.toLowerCase();

                if (customer.includes(searchTerm) || orderId.includes(searchTerm) || rowText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter functionality
        function applyFilters() {
            const dateFilter = document.getElementById('dateFilter').value;
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;
            const sortOrder = document.getElementById('sortOrder').value;

            // This would typically send an AJAX request to filter data
            console.log('Applying filters:', {
                dateFilter,
                fromDate,
                toDate,
                sortOrder
            });

            // For now, just show a message
            alert('Bộ lọc sẽ được áp dụng. Chức năng này cần được triển khai với AJAX.');
        }

        // Export functions
        function exportOrders() {
            alert('Chức năng xuất Excel sẽ được triển khai');
        }

        function printOrders() {
            window.print();
        }

        function refreshData() {
            location.reload();
        }

        // Mobile menu toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }

        // Set today's date as default for date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('toDate').value = today;

            // Set from date to 30 days ago
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
            document.getElementById('fromDate').value = thirtyDaysAgo.toISOString().split('T')[0];
        });

        // Add click outside to close mobile menu
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const isClickInsideSidebar = sidebar.contains(event.target);

            if (!isClickInsideSidebar && window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>