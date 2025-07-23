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

// Truy vấn dữ liệu doanh thu theo ngày cho biểu đồ
$query_chart = "
    SELECT t.ngayThanhToan, SUM(t.tongTien) AS tongDoanhThu
    FROM thanhtoan t
    GROUP BY t.ngayThanhToan
    ORDER BY t.ngayThanhToan DESC";
$result_chart = $conn->query($query_chart);

$dates = [];
$sales = [];
$totalRevenue = 0;
$orderCount = 0;

while ($row = $result_chart->fetch_assoc()) {
    $dates[] = $row['ngayThanhToan'];
    $sales[] = (float) $row['tongDoanhThu'];
    $totalRevenue += (float) $row['tongDoanhThu'];
    $orderCount++;
}

// Truy vấn dữ liệu chi tiết đơn hàng cho bảng
$query_table = "
    SELECT t.id AS order_id, t.hoTen, t.email, t.soDienThoai, t.diaChi, t.ngayThanhToan, t.tongTien
    FROM thanhtoan t
    ORDER BY t.ngayThanhToan DESC";
$result_table = $conn->query($query_table);

// Tính toán thống kê
$avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Doanh Thu - Shop Em Bé</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Chart Controls */
        .chart-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

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

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            background: linear-gradient(135deg, #5a6268, #495057);
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
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
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

        /* Chart Containers */
        .chart-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(163, 144, 116, 0.2);
            border-top: 4px solid #a39074;
            transition: all 0.3s ease;
        }

        .chart-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(163, 144, 116, 0.3);
        }

        .chart-container h4 {
            color: #a39074;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
        }

        .chart-container canvas {
            max-height: 300px;
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

        /* Icon Colors */
        .icon-primary {
            color: #a39074;
        }

        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }

            .content,
            .content * {
                visibility: visible;
            }

            .content {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                margin-left: 0;
                background: white;
            }

            .btn,
            .sidebar,
            .header,
            .chart-controls {
                display: none !important;
            }

            .chart-container {
                break-inside: avoid;
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .chart-section {
                grid-template-columns: 1fr;
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

            .chart-controls {
                justify-content: center;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 600px;
            }
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
        <a href="../Frontend_web/phanquyen.php">
            <i class="fas fa-user-shield"></i>
            QUẢN LÝ QUYỀN TRUY CẬP
        </a>
        <a href="../Frontend_web/thongtinnguoidung.php">
            <i class="fas fa-users"></i>
            QUẢN LÝ NHÂN VIÊN
        </a>
        <a href="./Thongke.php" class="active">
            <i class="fas fa-chart-bar"></i>
            THỐNG KÊ DOANH THU
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <h3><i class="fas fa-chart-line icon-primary"></i> Thống Kê Doanh Thu</h3>
        <div style="color: #a39074; font-weight: 600;">
            <i class="fas fa-calendar-alt icon-primary"></i>
            <?php echo date('d/m/Y'); ?>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <h2 class="page-title">
            <i class="fas fa-chart-bar icon-primary"></i> Dashboard Thống Kê
        </h2>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-money-bill-wave icon-primary"></i> Tổng Doanh Thu</h3>
                    <p>Tổng doanh thu từ tất cả đơn hàng</p>
                </div>
                <div class="number"><?php echo number_format($totalRevenue, 0, ',', '.'); ?></div>
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-shopping-cart icon-primary"></i> Tổng Đơn Hàng</h3>
                    <p>Số lượng đơn hàng đã xử lý</p>
                </div>
                <div class="number"><?php echo $orderCount; ?></div>
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stats-card">
                <div class="info">
                    <h3><i class="fas fa-calculator icon-primary"></i> Giá Trị TB</h3>
                    <p>Giá trị trung bình mỗi đơn hàng</p>
                </div>
                <div class="number"><?php echo number_format($avgOrderValue, 0, ',', '.'); ?></div>
                <i class="fas fa-coins"></i>
            </div>
        </div>

        <!-- Chart Controls -->
        <div class="chart-controls">
            <button class="btn btn-secondary" onclick="toggleChart('lineChartContainer')">
                <i class="fas fa-chart-line"></i> Biểu Đồ Đường
            </button>
            <button class="btn btn-secondary" onclick="toggleChart('pieChartContainer')">
                <i class="fas fa-chart-pie"></i> Biểu Đồ Tròn
            </button>
        </div>

        <!-- Charts Section -->
        <div class="chart-section">
            <div class="chart-container" id="lineChartContainer">
                <h4><i class="fas fa-chart-line icon-primary"></i> Biểu Đồ Doanh Thu Theo Thời Gian</h4>
                <canvas id="lineChart"></canvas>
            </div>
            <div class="chart-container" id="pieChartContainer">
                <h4><i class="fas fa-chart-pie icon-primary"></i> Phân Bố Doanh Thu</h4>
                <canvas id="pieChart"></canvas>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <h2><i class="fas fa-table icon-primary"></i> Chi Tiết Đơn Hàng</h2>
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> STT</th>
                        <th><i class="fas fa-user"></i> Họ tên</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-phone"></i> Số điện thoại</th>
                        <th><i class="fas fa-map-marker-alt"></i> Địa chỉ</th>
                        <th><i class="fas fa-calendar"></i> Ngày thanh toán</th>
                        <th><i class="fas fa-money-bill"></i> Tổng tiền (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_table->num_rows > 0) {
                        $stt = 1;
                        while ($row = $result_table->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong style='color: #a39074;'>" . $stt++ . "</strong></td>";
                            echo "<td><i class='fas fa-user-circle icon-primary'></i> " . htmlspecialchars($row['hoTen']) . "</td>";
                            echo "<td><i class='fas fa-envelope icon-primary'></i> " . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><i class='fas fa-phone icon-primary'></i> " . htmlspecialchars($row['soDienThoai']) . "</td>";
                            echo "<td><i class='fas fa-map-marker-alt icon-primary'></i> " . htmlspecialchars($row['diaChi']) . "</td>";
                            echo "<td><i class='fas fa-calendar icon-primary'></i> " . htmlspecialchars($row['ngayThanhToan']) . "</td>";
                            echo "<td><strong style='color: #a39074;'>" . number_format($row['tongTien'], 0, ',', '.') . " VNĐ</strong></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center; padding: 40px; color: #a39074;'>
                            <i class='fas fa-shopping-cart' style='font-size: 48px; margin-bottom: 15px; display: block; color: #a39074;'></i>
                            <strong>Không có đơn hàng nào</strong>
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="./Menu1.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> In Thống Kê
            </button>
        </div>
    </div>

    <script>
        // Hàm ẩn/hiện biểu đồ
        function toggleChart(chartId) {
            var chartContainer = document.getElementById(chartId);
            if (chartContainer.style.display === 'none' || chartContainer.style.display === '') {
                chartContainer.style.display = 'block';
            } else {
                chartContainer.style.display = 'none';
            }
        }

        // Biểu đồ đường với màu #a39074
        var ctx = document.getElementById('lineChart').getContext('2d');
        var lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Doanh thu theo ngày (VNĐ)',
                    data: <?php echo json_encode($sales); ?>,
                    borderColor: '#a39074',
                    backgroundColor: 'rgba(163, 144, 116, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#a39074',
                    pointBorderColor: '#8d7a5e',
                    pointHoverBackgroundColor: '#8d7a5e',
                    pointHoverBorderColor: '#6d5d47'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#a39074',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' VNĐ';
                            },
                            color: '#a39074'
                        },
                        grid: {
                            color: 'rgba(163, 144, 116, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#a39074'
                        },
                        grid: {
                            color: 'rgba(163, 144, 116, 0.1)'
                        }
                    }
                }
            }
        });

        // Biểu đồ tròn với màu #a39074
        var pieCtx = document.getElementById('pieChart').getContext('2d');
        var pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Tỷ lệ doanh thu',
                    data: <?php echo json_encode($sales); ?>,
                    backgroundColor: [
                        '#a39074',
                        '#8d7a5e',
                        '#6d5d47',
                        '#b5a085',
                        '#9e8769',
                        '#7a6b52',
                        '#c4b396',
                        '#8a7c63'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#a39074',
                            font: {
                                weight: 'bold'
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw.toLocaleString() + ' VNĐ';
                            }
                        }
                    }
                }
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