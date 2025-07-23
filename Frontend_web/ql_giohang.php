<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy vấn dữ liệu từ bảng `gio_hang`, `users`, và `sanpham`
$sql = "
    SELECT 
        gh.id AS gio_hang_id,
        u.id AS user_id,
        u.name AS ten_nguoi_dung,
        u.email AS email_nguoi_dung,
        s.id AS san_pham_id,
        s.ten_san_pham,
        gh.so_luong,
        s.gia,
        s.anh_san_pham
    FROM gio_hang gh
    INNER JOIN users u ON gh.user_id = u.id
    INNER JOIN sanpham s ON gh.san_pham_id = s.id
    ORDER BY u.name, gh.id";
$result = $conn->query($sql);

// Tính toán thống kê
$stats_sql = "
    SELECT 
        COUNT(DISTINCT u.id) as total_users,
        COUNT(gh.id) as total_items,
        SUM(s.gia * gh.so_luong) as total_value
    FROM gio_hang gh
    INNER JOIN users u ON gh.user_id = u.id
    INNER JOIN sanpham s ON gh.san_pham_id = s.id";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Giỏ Hàng - Shop Em Bé</title>
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

        /* Search and Filter */
        .search-filter-container {
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

        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 45px;
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

        .filter-select {
            padding: 12px 20px;
            border: 2px solid #a39074;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            background: white;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
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

        .table-container h2 {
            color: #a39074;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
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

        /* Product Image */
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #a39074;
            transition: all 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.2);
            box-shadow: 0 4px 15px rgba(163, 144, 116, 0.3);
        }

        .no-image {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border: 2px dashed #a39074;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a39074;
            font-size: 12px;
            text-align: center;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #a39074, #8d7a5e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .user-details h4 {
            margin: 0;
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        .user-details p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        /* Product Info */
        .product-info h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        .product-price {
            color: #a39074;
            font-weight: 600;
            font-size: 13px;
        }

        /* Quantity Badge */
        .quantity-badge {
            background: linear-gradient(135deg, #a39074, #8d7a5e);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            min-width: 40px;
        }

        /* Total Price */
        .total-price {
            font-weight: 700;
            color: #a39074;
            font-size: 15px;
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

            .search-filter-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: auto;
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
        <a href="./ql_donhang.php">
            <i class="fas fa-shopping-cart"></i>
            QUẢN LÝ ĐƠN HÀNG
        </a>
        <a href="./ql_khchhang.php">
            <i class="fas fa-users"></i>
            QUẢN LÝ KHÁCH HÀNG
        </a>
        <a href="./cart_management.php" class="active">
            <i class="fas fa-shopping-basket"></i>
            QUẢN LÝ GIỎ HÀNG
        </a>
        <a href="./Thongke.php">
            <i class="fas fa-chart-bar"></i>
            THỐNG KÊ DOANH THU
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <h3><i class="fas fa-shopping-basket icon-primary"></i> Quản Lý Giỏ Hàng</h3>
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
            <i class="fas fa-shopping-basket icon-primary"></i> Dashboard Giỏ Hàng
        </h2>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-users icon-primary"></i> Tổng Người Dùng</h3>
                    <p>Số người dùng có giỏ hàng</p>
                </div>
                <div class="number"><?php echo $stats['total_users'] ?? 0; ?></div>
                <i class="fas fa-users"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-shopping-cart icon-primary"></i> Tổng Sản Phẩm</h3>
                    <p>Số lượng sản phẩm trong giỏ hàng</p>
                </div>
                <div class="number"><?php echo $stats['total_items'] ?? 0; ?></div>
                <i class="fas fa-box"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-money-bill-wave icon-primary"></i> Tổng Giá Trị</h3>
                    <p>Tổng giá trị các giỏ hàng</p>
                </div>
                <div class="number"><?php echo number_format($stats['total_value'] ?? 0, 0, ',', '.'); ?></div>
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm kiếm theo tên người dùng, email hoặc sản phẩm...">
            </div>
            <select class="filter-select" id="userFilter">
                <option value="">Tất cả người dùng</option>
                <?php
                // Get unique users for filter
                $users_sql = "SELECT DISTINCT u.id, u.name FROM users u INNER JOIN gio_hang gh ON u.id = gh.user_id ORDER BY u.name";
                $users_result = $conn->query($users_sql);
                while ($user = $users_result->fetch_assoc()) {
                    echo "<option value='" . $user['id'] . "'>" . htmlspecialchars($user['name']) . "</option>";
                }
                ?>
            </select>
            <button class="btn btn-primary" onclick="exportData()">
                <i class="fas fa-download"></i> Xuất Excel
            </button>
        </div>

        <!-- Table -->
        <div class="table-container">
            <h2><i class="fas fa-list icon-primary"></i> Danh Sách Giỏ Hàng</h2>
            <div class="table-wrapper">
                <table id="cartTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> STT</th>
                            <th><i class="fas fa-user"></i> Người dùng</th>
                            <th><i class="fas fa-box"></i> Sản phẩm</th>
                            <th><i class="fas fa-sort-numeric-up"></i> Số lượng</th>
                            <th><i class="fas fa-money-bill"></i> Giá (VNĐ)</th>
                            <th><i class="fas fa-calculator"></i> Tổng giá (VNĐ)</th>
                            <th><i class="fas fa-image"></i> Ảnh</th>
                            <th><i class="fas fa-cogs"></i> Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            $stt = 1;
                            $currentUser = '';
                            while ($row = $result->fetch_assoc()) {
                                $tongGia = $row['gia'] * $row['so_luong'];
                                $userInitial = strtoupper(substr($row['ten_nguoi_dung'], 0, 1));

                                echo "<tr data-user-id='" . $row['user_id'] . "' data-user-name='" . htmlspecialchars($row['ten_nguoi_dung']) . "' data-product-name='" . htmlspecialchars($row['ten_san_pham']) . "'>";
                                echo "<td><strong style='color: #a39074;'>" . $stt++ . "</strong></td>";

                                // User info with avatar
                                echo "<td>";
                                echo "<div class='user-info'>";
                                echo "<div class='user-avatar'>" . $userInitial . "</div>";
                                echo "<div class='user-details'>";
                                echo "<h4>" . htmlspecialchars($row['ten_nguoi_dung']) . "</h4>";
                                echo "<p><i class='fas fa-envelope icon-primary'></i> " . htmlspecialchars($row['email_nguoi_dung']) . "</p>";
                                echo "</div>";
                                echo "</div>";
                                echo "</td>";

                                // Product info
                                echo "<td>";
                                echo "<div class='product-info'>";
                                echo "<h4><i class='fas fa-box icon-primary'></i> " . htmlspecialchars($row['ten_san_pham']) . "</h4>";
                                echo "<p class='product-price'>Giá: " . number_format($row['gia'], 0, ',', '.') . " VNĐ</p>";
                                echo "</div>";
                                echo "</td>";

                                // Quantity with badge
                                echo "<td><span class='quantity-badge'>" . htmlspecialchars($row['so_luong']) . "</span></td>";

                                // Price
                                echo "<td><strong style='color: #a39074;'>" . number_format($row['gia'], 0, ',', '.') . " VNĐ</strong></td>";

                                // Total price
                                echo "<td><span class='total-price'>" . number_format($tongGia, 0, ',', '.') . " VNĐ</span></td>";

                                // Image
                                echo "<td>";
                                if (!empty($row['anh_san_pham'])) {
                                    echo "<img src='/QL_web_new_born/Frontend_web/" . htmlspecialchars($row['anh_san_pham']) . "' class='product-image' alt='Product Image'>";
                                } else {
                                    echo "<div class='no-image'><i class='fas fa-image'></i><br>Không có ảnh</div>";
                                }
                                echo "</td>";

                                // Actions
                                echo "<td>";
                                echo "<a href='xoa_giohang.php?id=" . $row['gio_hang_id'] . "' class='btn btn-danger' onclick='return confirm(\"Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?\")'>";
                                echo "<i class='fas fa-trash'></i> Xóa";
                                echo "</a>";
                                echo "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>";
                            echo "<div class='empty-state'>";
                            echo "<i class='fas fa-shopping-cart'></i>";
                            echo "<h3>Không có dữ liệu giỏ hàng</h3>";
                            echo "<p>Chưa có người dùng nào thêm sản phẩm vào giỏ hàng</p>";
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
            <button type="button" class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> Làm mới
            </button>
            <button type="button" class="btn btn-primary" onclick="clearAllCarts()">
                <i class="fas fa-trash-alt"></i> Xóa tất cả giỏ hàng
            </button>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#cartTable tbody tr');

            rows.forEach(row => {
                const userName = row.getAttribute('data-user-name').toLowerCase();
                const productName = row.getAttribute('data-product-name').toLowerCase();
                const emailCell = row.cells[1].textContent.toLowerCase();

                if (userName.includes(searchTerm) || productName.includes(searchTerm) || emailCell.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // User filter functionality
        document.getElementById('userFilter').addEventListener('change', function() {
            const selectedUserId = this.value;
            const rows = document.querySelectorAll('#cartTable tbody tr');

            rows.forEach(row => {
                const userId = row.getAttribute('data-user-id');

                if (selectedUserId === '' || userId === selectedUserId) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Export data function
        function exportData() {
            // This would typically send a request to a PHP script that generates Excel
            alert('Chức năng xuất Excel sẽ được triển khai');
        }

        // Refresh data function
        function refreshData() {
            location.reload();
        }

        // Clear all carts function
        function clearAllCarts() {
            if (confirm('Bạn có chắc chắn muốn xóa tất cả giỏ hàng? Hành động này không thể hoàn tác!')) {
                // This would send a request to clear all carts
                alert('Chức năng xóa tất cả giỏ hàng sẽ được triển khai');
            }
        }

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
    </script>
</body>

</html>

<?php
$conn->close();
?>