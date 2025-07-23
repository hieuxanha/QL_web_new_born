<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$sql = "SELECT * FROM users WHERE role IN ('nhanvien', 'khachhang')";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Shop Em Bé</title>
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
            max-height: 300px;
            overflow-y: auto;
            z-index: 1001;
            margin-top: 5px;
            border: 1px solid #a39074;
        }

        #searchResults ul {
            list-style: none;
        }

        #searchResults li {
            padding: 15px;
            border-bottom: 1px solid rgba(163, 144, 116, 0.1);
            transition: background 0.2s ease;
        }

        #searchResults li:hover {
            background: rgba(163, 144, 116, 0.1);
        }

        #searchResults a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
        }

        /* Main Content */
        .content {
            margin-left: 280px;
            margin-top: 70px;
            padding: 30px;
            min-height: calc(100vh - 70px);
        }

        .content h2 {
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

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(163, 144, 116, 0.2);
            overflow: hidden;
            border-top: 4px solid #a39074;
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
            transform: scale(1.01);
            transition: all 0.2s ease;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Button Styling */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
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
        }

        .btn-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        .btn a {
            color: inherit;
            text-decoration: none;
        }

        /* Form Styling */
        form {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(163, 144, 116, 0.2);
            border-top: 4px solid #a39074;
        }

        form h2 {
            color: #a39074;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .input-field {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid rgba(163, 144, 116, 0.3);
            border-radius: 10px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .input-field:focus {
            border-color: #a39074;
            background: white;
            box-shadow: 0 0 0 3px rgba(163, 144, 116, 0.2);
        }

        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #a39074 0%, #8d7a5e 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(163, 144, 116, 0.4);
            background: linear-gradient(135deg, #8d7a5e 0%, #6d5d47 100%);
        }

        /* Back Button */
        .form-group {
            margin-top: 30px;
            text-align: center;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-nhanvien {
            background: linear-gradient(135deg, #a39074, #8d7a5e);
            color: white;
            box-shadow: 0 2px 8px rgba(163, 144, 116, 0.3);
        }

        .status-khachhang {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
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

        /* Hover Effects */
        .hover-card {
            transition: all 0.3s ease;
        }

        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(163, 144, 116, 0.3);
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

        /* Accent Elements */
        .accent-line {
            height: 3px;
            background: linear-gradient(90deg, #a39074, #8d7a5e, #a39074);
            border-radius: 2px;
            margin: 20px 0;
        }

        /* Icon Colors */
        .icon-primary {
            color: #a39074;
        }

        /* Responsive Design */
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

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 600px;
            }

            .search-bar {
                max-width: 250px;
            }
        }

        /* Additional Styling for Better Visual Hierarchy */
        .page-title {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(163, 144, 116, 0.3);
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-left: 4px solid #a39074;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(163, 144, 116, 0.2);
        }
    </style>
</head>

<body>
    <div id="menu" class="sidebar">
        <h2><i class="fas fa-baby icon-primary"></i> Shop Bán Đồ Em Bé</h2>
        <a href="../Frontend_web/phanquyen.php" class="active">
            <i class="fas fa-user-shield"></i>
            QUẢN LÝ QUYỀN TRUY CẬP
        </a>
        <a href="../Frontend_web/thongtinnguoidung.php">
            <i class="fas fa-users"></i>
            QUẢN LÝ NHÂN VIÊN
        </a>
        <a href="./Thongke.php">
            <i class="fas fa-chart-bar"></i>
            THỐNG KÊ DOANH THU
        </a>
    </div>

    <div class="header">
        <div class="search-bar">
            <input type="text" placeholder="Tìm kiếm người dùng..." id="searchInput" />
            <div id="searchResults" style="display: none;"></div>
        </div>
        <div style="color: #a39074; font-weight: 600;">
            <i class="fas fa-user-cog icon-primary"></i>
            Admin
        </div>
    </div>

    <div class="content">
        <h2 class="page-title">
            <i class="fas fa-user-cog icon-primary"></i> Phân Quyền Người Dùng
        </h2>

        <div class="stats-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h3 style="color: #a39074; margin-bottom: 5px;">
                        <i class="fas fa-users icon-primary"></i> Tổng số người dùng
                    </h3>
                    <p style="color: #666; font-size: 14px;">Quản lý thông tin và quyền hạn</p>
                </div>
                <div style="font-size: 24px; font-weight: bold; color: #a39074;">
                    <?php echo $result->num_rows; ?>
                </div>
            </div>
        </div>

        <div class="accent-line"></div>

        <div class="table-container hover-card">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> STT</th>
                        <th><i class="fas fa-user"></i> Tên</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-phone"></i> Số điện thoại</th>
                        <th><i class="fas fa-map-marker-alt"></i> Địa chỉ</th>
                        <th><i class="fas fa-user-tag"></i> Vai trò</th>
                        <th><i class="fas fa-key"></i> Mật khẩu</th>
                        <th><i class="fas fa-cogs"></i> Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $stt = 1;
                        while ($row = $result->fetch_assoc()) {
                            $roleClass = $row['role'] == 'nhanvien' ? 'status-nhanvien' : 'status-khachhang';
                            echo "<tr>";
                            echo "<td><strong style='color: #a39074;'>" . $stt++ . "</strong></td>";
                            echo "<td><i class='fas fa-user-circle icon-primary'></i> " . htmlspecialchars($row['name']) . "</td>";
                            echo "<td><i class='fas fa-envelope icon-primary'></i> " . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><i class='fas fa-phone icon-primary'></i> " . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td><i class='fas fa-map-marker-alt icon-primary'></i> " . htmlspecialchars($row['address']) . "</td>";
                            echo "<td><span class='status-badge $roleClass'>" . ucfirst($row['role']) . "</span></td>";
                            echo "<td><span style='font-family: monospace; background: rgba(163, 144, 116, 0.1); color: #a39074; padding: 5px 8px; border-radius: 5px; border: 1px solid rgba(163, 144, 116, 0.3);'>••••••••</span></td>";
                            echo "<td>
                                <a href='../Backend_thongtinnguoidung/edit_user.php?id=" . $row['id'] . "' class='btn btn-primary'>
                                    <i class='fas fa-edit'></i> Sửa
                                </a>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align: center; padding: 40px; color: #a39074;'>
                            <i class='fas fa-users' style='font-size: 48px; margin-bottom: 15px; display: block; color: #a39074;'></i>
                            <strong>Chưa có nhân viên nào</strong>
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <div class="accent-line"></div>

            <div class="form-group">
                <button class="btn btn-primary" type="button">
                    <a href="./Menu.php">
                        <i class="fas fa-arrow-left"></i> QUAY LẠI
                    </a>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("searchInput").addEventListener("input", function() {
            const keyword = document.getElementById("searchInput").value.trim();
            const resultsContainer = document.getElementById("searchResults");

            if (keyword === "") {
                resultsContainer.style.display = "none";
                return;
            }

            // Show loading
            resultsContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #a39074;"><div class="loading"></div> Đang tìm kiếm...</div>';
            resultsContainer.style.display = "block";

            fetch(`/QL_web_new_born/timkiemphanquyen.php?keyword=${encodeURIComponent(keyword)}&searchBy=role`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Lỗi phản hồi từ máy chủ");
                    }
                    return response.json();
                })
                .then(data => {
                    resultsContainer.innerHTML = "";

                    if (data.length === 0) {
                        resultsContainer.innerHTML = `
                            <div style="padding: 20px; text-align: center; color: #a39074;">
                                <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                                Không tìm thấy kết quả nào
                            </div>`;
                        return;
                    }

                    const resultList = document.createElement("ul");
                    data.forEach(item => {
                        const listItem = document.createElement("li");
                        const roleClass = item.role === 'nhanvien' ? 'status-nhanvien' : 'status-khachhang';
                        listItem.innerHTML = `
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <a href="/QL_web_new_born/new_born/Frontend_web/ql_khchhang.php?id=${item.id}"
                                       style="text-decoration: none; color: #2c3e50; font-weight: 600; display: block; margin-bottom: 5px;">
                                        <i class="fas fa-user-circle icon-primary"></i> ${item.name}
                                    </a>
                                    <span class="status-badge ${roleClass}" style="font-size: 11px;">
                                        ${item.role}
                                    </span>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #a39074;"></i>
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

            if (!resultsContainer.contains(event.target) && event.target !== searchInput) {
                resultsContainer.style.display = "none";
            }
        });

        // Mobile menu toggle (if needed)
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>