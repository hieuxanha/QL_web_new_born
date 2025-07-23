<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$error = '';
$success = '';
$name = '';
$phone = '';
$email = '';
$address = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $role = 'nhanvien';

    // Kiểm tra định dạng email
    if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
        $error = "Email không hợp lệ!";
    } else {
        $domainPart = explode('@', $email)[1];
        if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domainPart)) {
            $error = "Email không hợp lệ! Tên miền không được chứa ký tự đặc biệt.";
        }
    }

    // Kiểm tra email đã tồn tại chưa
    if (empty($error)) {
        $sql_check = "SELECT * FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error = "Email đã tồn tại!";
        } else {
            // Chèn dữ liệu mới
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO users (name, phone, email, address, password, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssssss", $name, $phone, $email, $address, $password_hashed, $role);

            if ($stmt_insert->execute()) {
                $success = "Thêm nhân viên mới thành công!";
                // Reset form fields after successful submission
                $name = '';
                $phone = '';
                $email = '';
                $address = '';
            } else {
                $error = "Lỗi khi thêm dữ liệu.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

// Lấy danh sách nhân viên
$sql = "SELECT * FROM users WHERE role = 'nhanvien'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân viên - Shop Em Bé</title>
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

        /* Form Styling */
        form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(163, 144, 116, 0.2);
            border-top: 4px solid #a39074;
        }

        form h2 {
            color: #a39074;
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
            background: none;
            border: none;
            padding: 0;
            text-shadow: none;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        .input-field {
            width: 100%;
            padding: 15px;
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
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(163, 144, 116, 0.4);
            background: linear-gradient(135deg, #8d7a5e 0%, #6d5d47 100%);
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

        /* Alert Styling */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-left: 4px solid #a39074;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(163, 144, 116, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stats-card h3 {
            color: #a39074;
            margin-bottom: 5px;
            font-size: 16px;
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

            .form-row {
                grid-template-columns: 1fr;
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

        /* Icon Colors */
        .icon-primary {
            color: #a39074;
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
    <div id="menu" class="sidebar">
        <h2><i class="fas fa-baby icon-primary"></i> Shop Bán Đồ Em Bé</h2>
        <a href="../Frontend_web/phanquyen.php">
            <i class="fas fa-user-shield"></i>
            QUẢN LÝ QUYỀN TRUY CẬP
        </a>
        <a href="../Frontend_web/thongtinnguoidung.php" class="active">
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
            <input type="text" placeholder="Tìm kiếm nhân viên..." id="searchInput" />
            <div id="searchResults" style="display: none;"></div>
        </div>
        <div style="color: #a39074; font-weight: 600;">
            <i class="fas fa-users icon-primary"></i>
            Quản lý nhân viên
        </div>
    </div>

    <div class="content">
        <h2><i class="fas fa-users icon-primary"></i> Quản Lý Nhân Viên</h2>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stats-card">
                <div>
                    <h3><i class="fas fa-users icon-primary"></i> Tổng nhân viên</h3>
                    <p style="color: #666; font-size: 14px;">Số lượng nhân viên hiện tại</p>
                </div>
                <div class="number"><?php echo $result->num_rows; ?></div>
            </div>
            <div class="stats-card">
                <div>
                    <h3><i class="fas fa-user-plus icon-primary"></i> Thêm mới</h3>
                    <p style="color: #666; font-size: 14px;">Thêm nhân viên mới</p>
                </div>
                <i class="fas fa-plus"></i>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Add Employee Form -->
        <form action="" method="POST" onsubmit="return validateForm()" novalidate>
            <h2><i class="fas fa-user-plus icon-primary"></i> Thêm Nhân Viên Mới</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user icon-primary"></i> Tên nhân viên</label>
                    <input type="text" id="name" class="input-field" name="name"
                        placeholder="Nhập tên nhân viên" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone icon-primary"></i> Số điện thoại</label>
                    <input type="tel" id="phone" class="input-field" name="phone"
                        placeholder="Nhập số điện thoại" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope icon-primary"></i> Email</label>
                    <input type="email" id="email" class="input-field" name="email"
                        placeholder="Nhập email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock icon-primary"></i> Mật khẩu</label>
                    <input type="password" id="password" class="input-field" name="password"
                        placeholder="Nhập mật khẩu" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="address"><i class="fas fa-map-marker-alt icon-primary"></i> Địa chỉ</label>
                    <input type="text" id="address" class="input-field" name="address"
                        placeholder="Nhập địa chỉ" value="<?php echo htmlspecialchars($address); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="role"><i class="fas fa-user-tag icon-primary"></i> Vai trò</label>
                    <select id="role" class="input-field" name="role" required>
                        <option value="nhanvien">Nhân viên</option>
                    </select>
                </div>
            </div>

            <button type="submit">
                <i class="fas fa-plus"></i> Thêm nhân viên
            </button>
        </form>

        <!-- Employee List -->
        <div class="table-container">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <h3 style="color: #a39074; margin: 0;">
                    <i class="fas fa-list icon-primary"></i> Danh Sách Nhân Viên
                </h3>
                <span style="color: #666; font-size: 14px;">
                    Tổng: <?php echo $result->num_rows; ?> nhân viên
                </span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> STT</th>
                        <th><i class="fas fa-user"></i> Tên nhân viên</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-phone"></i> Số điện thoại</th>
                        <th><i class="fas fa-map-marker-alt"></i> Địa chỉ</th>
                        <th><i class="fas fa-user-tag"></i> Vai trò</th>
                        <th><i class="fas fa-cogs"></i> Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $stt = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong style='color: #a39074;'>" . $stt++ . "</strong></td>";
                            echo "<td><i class='fas fa-user-circle icon-primary'></i> " . htmlspecialchars($row['name']) . "</td>";
                            echo "<td><i class='fas fa-envelope icon-primary'></i> " . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><i class='fas fa-phone icon-primary'></i> " . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td><i class='fas fa-map-marker-alt icon-primary'></i> " . htmlspecialchars($row['address']) . "</td>";
                            echo "<td><span style='background: linear-gradient(135deg, #a39074, #8d7a5e); color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 600;'>" . ucfirst(htmlspecialchars($row['role'])) . "</span></td>";
                            echo "<td>
                                <a href='../Backend_thongtinnguoidung/edit_user.php?id=" . $row['id'] . "' class='btn btn-warning'>
                                    <i class='fas fa-edit'></i> Sửa
                                </a>
                                <a href='../Backend_thongtinnguoidung/xoa_nv.php?id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Bạn có chắc chắn muốn xóa?\")'>
                                    <i class='fas fa-trash'></i> Xóa
                                </a>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center; padding: 40px; color: #a39074;'>
                            <i class='fas fa-users' style='font-size: 48px; margin-bottom: 15px; display: block; color: #a39074;'></i>
                            <strong>Chưa có nhân viên nào</strong>
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(163, 144, 116, 0.2);">
                <a href="./Menu.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> QUAY LẠI
                </a>
            </div>
        </div>
    </div>

    <script>
        // Validation function (keeping your existing validation logic)
        function validateForm() {
            const nameInput = document.querySelector('input[name="name"]').value;
            const name = nameInput.trim();
            const phoneInput = document.querySelector('input[name="phone"]').value;
            const phone = phoneInput.trim();
            const emailInput = document.querySelector('input[name="email"]').value;
            const email = emailInput.trim();
            const addressInput = document.querySelector('input[name="address"]').value;
            const address = addressInput.trim();
            const passwordInput = document.querySelector('input[name="password"]').value;
            const password = passwordInput.trim();

            // Your existing validation logic here...
            if (name === "") {
                alert('Vui lòng nhập tên nhân viên!');
                return false;
            }
            if (name.length <= 1) {
                alert('Tên quá ngắn, phải từ 5 ký tự trở lên!');
                return false;
            }
            if (name.length > 50) {
                alert('Tên quá dài! Tối đa 50 ký tự!');
                return false;
            }

            if (phone === "") {
                alert('Vui lòng nhập số điện thoại!');
                return false;
            }
            if (!/^\d{10}$/.test(phone)) {
                alert('Số điện thoại phải gồm đúng 10 chữ số!');
                return false;
            }
            if (!/^(03|05|07|08|09)\d{8}$/.test(phone)) {
                alert('Số điện thoại phải bắt đầu bằng 03, 05, 07, 08, hoặc 09!');
                return false;
            }

            if (email === "") {
                alert('Vui lòng nhập Email!');
                return false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Email không hợp lệ!');
                return false;
            }

            if (address === "") {
                alert('Vui lòng nhập địa chỉ!');
                return false;
            }
            if (address.length < 5) {
                alert('Địa chỉ quá ngắn, phải trên 5 ký tự!');
                return false;
            }

            if (password === "") {
                alert('Vui lòng nhập mật khẩu!');
                return false;
            }
            if (password.length < 8) {
                alert('Mật khẩu phải dài ít nhất 8 ký tự!');
                return false;
            }

            return true;
        }

        // Search functionality
        document.getElementById("searchInput").addEventListener("input", function() {
            const keyword = document.getElementById("searchInput").value.trim();
            const resultsContainer = document.getElementById("searchResults");

            if (keyword === "") {
                resultsContainer.style.display = "none";
                return;
            }

            resultsContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #a39074;"><div class="loading"></div> Đang tìm kiếm...</div>';
            resultsContainer.style.display = "block";

            fetch(`/QL_web_new_born/timkiemnv.php?keyword=${encodeURIComponent(keyword)}&searchBy=role`)
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
                                Không tìm thấy nhân viên nào
                            </div>`;
                        return;
                    }

                    const resultList = document.createElement("ul");
                    data.forEach(item => {
                        const listItem = document.createElement("li");
                        listItem.innerHTML = `
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <a href="/QL_web_new_born/Frontend_web/ChiTietNguoiDung.php?id=${item.id}"
                                       style="text-decoration: none; color: #2c3e50; font-weight: 600; display: block; margin-bottom: 5px;">
                                        <i class="fas fa-user-circle icon-primary"></i> ${item.name}
                                    </a>
                                    <span style="background: linear-gradient(135deg, #a39074, #8d7a5e); color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px;">
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
    </script>
</body>

</html>

<?php $conn->close(); ?>