<?php
// trang list các san pham
$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy vấn dữ liệu từ bảng `sanpham`
$sql = "SELECT * FROM sanpham";
$result = $conn->query($sql);

// Xác định số sản phẩm trên mỗi trang
$products_per_page = 10;

// Lấy trang hiện tại từ query string, mặc định là trang 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

// Tính toán OFFSET
$offset = ($page - 1) * $products_per_page;

// Đếm tổng số sản phẩm
$total_products_sql = "SELECT COUNT(*) as total FROM sanpham";
$total_products_result = $conn->query($total_products_sql);
$total_products_row = $total_products_result->fetch_assoc();
$total_products = $total_products_row['total'];

// Tính tổng số trang
$total_pages = ceil($total_products / $products_per_page);

// Truy vấn sản phẩm cho trang hiện tại
$sql = "SELECT * FROM sanpham LIMIT $products_per_page OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Shop Em Bé</title>
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

        .search-bar {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-bar input[type="text"] {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #a39074;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .search-bar input[type="text"]:focus {
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

        /* Search Results */
        #searchResults {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(163, 144, 116, 0.3);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1001;
            margin-top: 5px;
            border: 1px solid #a39074;
            display: none;
        }

        #searchResults.active {
            display: block;
            animation: slideDown 0.3s ease;
        }

        #searchResults ul {
            list-style: none;
            padding: 10px;
            margin: 0;
        }

        #searchResults li {
            display: flex;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid rgba(163, 144, 116, 0.1);
            transition: background 0.2s ease;
        }

        #searchResults li:hover {
            background: rgba(163, 144, 116, 0.1);
        }

        #searchResults img {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            border: 2px solid #a39074;
            border-radius: 8px;
            object-fit: cover;
        }

        #searchResults a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        #searchResults p {
            margin: 0;
            color: #a39074;
            font-weight: 600;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Avatar Dropdown */
        .avatar {
            position: relative;
            display: inline-block;
        }

        .avatar img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #a39074;
            transition: all 0.3s ease;
        }

        .avatar img:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(163, 144, 116, 0.3);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            box-shadow: 0 10px 30px rgba(163, 144, 116, 0.3);
            z-index: 1002;
            min-width: 180px;
            border-radius: 10px;
            border: 1px solid #a39074;
            margin-top: 5px;
        }

        .dropdown-menu a {
            color: #2c3e50;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .dropdown-menu a:hover {
            background: rgba(163, 144, 116, 0.1);
        }

        .avatar:hover .dropdown-menu {
            display: block;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.95);
            border-left: 4px solid #a39074;
            padding: 20px;
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
            margin-bottom: 5px;
            font-size: 16px;
            font-weight: 600;
        }

        .stats-card .info p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .stats-card .number {
            font-size: 24px;
            font-weight: bold;
            color: #a39074;
        }

        .stats-card i {
            font-size: 32px;
            color: #a39074;
            opacity: 0.7;
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
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: linear-gradient(135deg, #a39074 0%, #8d7a5e 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        th:first-child {
            border-top-left-radius: 10px;
        }

        th:last-child {
            border-top-right-radius: 10px;
        }

        td {
            padding: 12px;
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

        /* Product Image in Table */
        td img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #a39074;
            transition: all 0.3s ease;
        }

        td img:hover {
            transform: scale(1.2);
            box-shadow: 0 4px 15px rgba(163, 144, 116, 0.3);
        }

        /* Featured Badge */
        .featured-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .featured-yes {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .featured-no {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
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
            margin: 0 3px;
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

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #212529;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
            color: #212529;
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

        .pagination li {
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

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }

            .search-bar {
                max-width: 250px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
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
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div id="menu" class="sidebar">
        <h2><i class="fas fa-baby icon-primary"></i> Shop Bán Đồ Em Bé</h2>
        <a href="./admin2.php" class="active">
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
    </div>

    <!-- Header -->
    <div class="header">
        <div class="search-bar">
            <input type="text" placeholder="Tìm kiếm sản phẩm..." id="searchInput">
            <div id="searchResults"></div>
        </div>
        <div class="avatar">
            <img src="https://via.placeholder.com/45" alt="User Avatar">
            <div class="dropdown-menu">
                <a href="#"><i class="fas fa-user icon-primary"></i> Profile</a>
                <a href="#"><i class="fas fa-cog icon-primary"></i> Settings</a>
                <a href="#"><i class="fas fa-history icon-primary"></i> Activity Log</a>
                <a href="#"><i class="fas fa-sign-out-alt icon-primary"></i> Logout</a>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <h2 class="page-title">
            <i class="fas fa-box icon-primary"></i> Quản Lý Sản Phẩm
        </h2>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-box icon-primary"></i> Tổng Sản Phẩm</h3>
                    <p>Số lượng sản phẩm trong kho</p>
                </div>
                <div class="number"><?php echo $total_products; ?></div>
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-star icon-primary"></i> Sản Phẩm Nổi Bật</h3>
                    <p>Sản phẩm được đánh dấu nổi bật</p>
                </div>
                <div class="number">
                    <?php
                    $featured_sql = "SELECT COUNT(*) as featured FROM sanpham WHERE san_pham_noi_bat = 1";
                    $featured_result = $conn->query($featured_sql);
                    $featured_row = $featured_result->fetch_assoc();
                    echo $featured_row['featured'];
                    ?>
                </div>
                <i class="fas fa-star"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-file-alt icon-primary"></i> Trang Hiện Tại</h3>
                    <p>Trang <?php echo $page; ?> / <?php echo $total_pages; ?></p>
                </div>
                <div class="number"><?php echo $page; ?></div>
                <i class="fas fa-file"></i>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <h2><i class="fas fa-list icon-primary"></i> Danh Sách Sản Phẩm</h2>
                <span style="color: #666; font-size: 14px;">
                    Hiển thị <?php echo min($products_per_page, $total_products - ($page - 1) * $products_per_page); ?> / <?php echo $total_products; ?> sản phẩm
                </span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> STT</th>
                        <th><i class="fas fa-tag"></i> Tên sản phẩm</th>
                        <th><i class="fas fa-money-bill"></i> Giá (VNĐ)</th>
                        <th><i class="fas fa-list-alt"></i> Danh mục</th>
                        <th><i class="fas fa-align-left"></i> Mô tả</th>
                        <th><i class="fas fa-warehouse"></i> Số lượng</th>
                        <th><i class="fas fa-image"></i> Ảnh</th>
                        <th><i class="fas fa-star"></i> Nổi bật</th>
                        <th><i class="fas fa-edit"></i> Sửa</th>
                        <th><i class="fas fa-trash"></i> Xóa</th>
                    </tr>
                </thead>
                <tbody id="product-list">
                    <?php
                    $stt = ($page - 1) * $products_per_page + 1;
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong style='color: #a39074;'>" . $stt++ . "</strong></td>";
                            echo "<td><i class='fas fa-box icon-primary'></i> " . htmlspecialchars($row['ten_san_pham']) . "</td>";
                            echo "<td><strong style='color: #a39074;'>" . number_format($row['gia'], 0, ',', '.') . " VNĐ</strong></td>";
                            echo "<td><span style='background: rgba(163, 144, 116, 0.1); color: #a39074; padding: 4px 8px; border-radius: 10px; font-size: 12px;'>" . htmlspecialchars($row['loai_san_pham']) . "</span></td>";
                            // text-overflow: ellipsis; white-space: nowrap; không tràn ra ngaoif và xuống dòng 
                            echo "<td style='max-width: 200px; overflow: hidden;'>" . htmlspecialchars($row['mo_ta']) . "</td>";
                            echo "<td><span style='background: " . ($row['so_luong'] > 10 ? '#28a745' : ($row['so_luong'] > 0 ? '#ffc107' : '#dc3545')) . "; color: white; padding: 4px 8px; border-radius: 10px; font-size: 12px; font-weight: 600;'>" . htmlspecialchars($row['so_luong']) . "</span></td>";

                            if (!empty($row['anh_san_pham'])) {
                                echo "<td><img src='/QL_web_new_born/Frontend_web/" . htmlspecialchars($row['anh_san_pham']) . "' alt='Product Image'></td>";
                            } else {
                                echo "<td><div style='width: 60px; height: 60px; background: #f8f9fa; border: 2px dashed #a39074; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #a39074;'><i class='fas fa-image'></i></div></td>";
                            }

                            echo "<td><span class='featured-badge " . ($row['san_pham_noi_bat'] ? 'featured-yes' : 'featured-no') . "'>" . ($row['san_pham_noi_bat'] ? 'Có' : 'Không') . "</span></td>";
                            echo "<td><a href='sua.php?id=" . $row['id'] . "' class='btn btn-warning'><i class='fas fa-edit'></i> Sửa</a></td>";
                            echo "<td><a href='../Backend_sanpham/xoa_sanpham.php?id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Bạn có chắc chắn muốn xóa sản phẩm này?\")'><i class='fas fa-trash'></i> Xóa</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' style='text-align: center; padding: 40px; color: #a39074;'>
                            <i class='fas fa-box-open' style='font-size: 48px; margin-bottom: 15px; display: block; color: #a39074;'></i>
                            <strong>Không có sản phẩm nào</strong>
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav class="pagination">
                <ul>
                    <?php if ($page > 1): ?>
                        <li>
                            <a href="?page=<?= $page - 1 ?>" aria-label="Trước">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="<?= $i == $page ? 'active' : '' ?>">
                            <a href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li>
                            <a href="?page=<?= $page + 1 ?>" aria-label="Sau">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="./addsanpham.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm Sản Phẩm Mới
            </a>
            <a href="Menu1.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>
    </div>

    <script>
        document.getElementById("searchInput").addEventListener("input", function() {
            const keyword = this.value.trim();
            const resultsContainer = document.getElementById("searchResults");

            if (keyword === "") {
                resultsContainer.classList.remove("active");
                resultsContainer.innerHTML = "";
                return;
            }

            // Show loading
            resultsContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #a39074;"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</div>';
            resultsContainer.classList.add("active");

            fetch(`/QL_web_new_born/timkiem.php?keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = "";

                    if (data.length === 0) {
                        resultsContainer.innerHTML = `
                            <div style="padding: 20px; text-align: center; color: #a39074;">
                                <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                                Không tìm thấy sản phẩm phù hợp
                            </div>`;
                        return;
                    }

                    const resultList = document.createElement("ul");
                    data.forEach(item => {
                        const listItem = document.createElement("li");
                        listItem.innerHTML = `
                            <img src="${item.anh_san_pham}" alt="${item.ten_san_pham}">
                            <div>
                                <a href="/QL_web_new_born/product.php?id=${item.id}">
                                    <i class="fas fa-box icon-primary"></i> ${item.ten_san_pham}
                                </a>
                                <p><i class="fas fa-money-bill icon-primary"></i> ${item.gia.toLocaleString()} VNĐ</p>
                            </div>
                        `;
                        resultList.appendChild(listItem);
                    });
                    resultsContainer.appendChild(resultList);
                })
                .catch(error => {
                    console.error("Lỗi tìm kiếm:", error);
                    resultsContainer.innerHTML = `
                        <div style="padding: 20px; text-align: center; color: #e74c3c;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                            Có lỗi xảy ra khi tìm kiếm
                        </div>`;
                });
        });

        // Hide search results when clicking outside
        document.addEventListener("click", function(event) {
            const resultsContainer = document.getElementById("searchResults");
            const searchInput = document.getElementById("searchInput");

            if (!resultsContainer.contains(event.target) && !searchInput.contains(event.target)) {
                resultsContainer.classList.remove("active");
            }
        });

        // Mobile menu toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>

</html>

<?php
$conn->close();
?>