<?php
session_start(); // For CSRF protection

$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy người dùng.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Trả về JSON để JavaScript xử lý
        header('Content-Type: application/json');

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF token không hợp lệ.']);
            exit;
        }

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];

        // Server-side validation for name (TC02, TC03)
        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập tên người dùng!']);
            exit;
        }
        if (strlen($name) < 5) {
            echo json_encode(['status' => 'error', 'message' => 'Tên cần phải dài hơn 5 ký tự!']);
            exit;
        }
        if (strlen($name) > 50) {
            echo json_encode(['status' => 'error', 'message' => 'Tên không được dài quá 50 ký tự!']);
            exit;
        }

        // Server-side validation for email (TC04)
        if (empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Email không được để trống!']);
            exit;
        }
        if (str_contains($email, ' ')) {
            echo json_encode(['status' => 'error', 'message' => 'Email không chứa khoảng trắng!']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Email không hợp lệ!']);
            exit;
        }
        if (substr_count($email, '@') !== 1) {
            echo json_encode(['status' => 'error', 'message' => 'Email chỉ được chứa đúng một ký tự @!']);
            exit;
        }
        if (preg_match('/[!#$%^&*()=\[\]{};:"\'\\|,<>?]/', $email)) {
            echo json_encode(['status' => 'error', 'message' => 'Email chứa ký tự đặc biệt không hợp lệ!']);
            exit;
        }

        // Check if email already exists (excluding current user)
        $check_email_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_email_query);
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email đã tồn tại!']);
            exit;
        }

        // Validate role (TC05)
        if (!in_array($role, ['khachhang', 'nhanvien'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vai trò không hợp lệ!']);
            exit;
        }

        // Update user
        $update_query = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssi", $name, $email, $role, $user_id);

        if ($update_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật thông tin người dùng thành công!', 'redirect' => 'http://localhost/QL_web_new_born/Frontend_web/phanquyen.php']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi khi cập nhật thông tin người dùng.']);
            exit;
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID người dùng không hợp lệ.']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa người dùng</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* background-color: #f7f7f7; */
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 50%, #a39074 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;

        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #a39074;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .update-btn {
            background-color: #a39074;
            color: white;
        }

        .back-link {
            color: #007bff;
            text-decoration: none;
            font-size: 16px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Chỉnh sửa thông tin người dùng</h2>
        <form id="editUserForm" method="POST" action="" onsubmit="return validateAndSubmitForm(event)" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="name">Tên người dùng:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" placeholder="Vui lòng nhập tên">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Vui lòng nhập email">

            <label for="role">Vai trò:</label>
            <select id="role" name="role">
                <option value="" disabled <?php echo empty($user['role']) ? 'selected' : ''; ?>>Vui lòng chọn</option>
                <option value="khachhang" <?php echo $user['role'] === 'khachhang' ? 'selected' : ''; ?>>Khách hàng</option>
                <option value="nhanvien" <?php echo $user['role'] === 'nhanvien' ? 'selected' : ''; ?>>Nhân viên</option>
            </select>

            <div class="button-group">
                <button type="submit" class="update-btn">Cập nhật</button>
                <a href="http://localhost/QL_web_new_born/Frontend_web/phanquyen.php" class="back-link">Quay lại</a>
            </div>
        </form>
    </div>

    <script>
        const currentEmail = "<?php echo htmlspecialchars($user['email']); ?>";
        const userId = "<?php echo $user_id; ?>";

        async function checkEmailExists(email, userId) {
            const response = await fetch('check_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(email)}&user_id=${encodeURIComponent(userId)}&csrf_token=${encodeURIComponent("<?php echo $_SESSION['csrf_token']; ?>")}`
            });
            return await response.text();
        }

        async function validateAndSubmitForm(event) {
            event.preventDefault(); // Ngăn form submit ngay lập tức

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const role = document.getElementById('role').value;

            // TC02, TC03: Kiểm tra tên
            if (!name) {
                alert('Vui lòng nhập tên người dùng!');
                return false;
            }
            if (name.length < 5) {
                alert('Tên quá ngắn, phải từ 5 ký tự trờ lên!');
                return false;
            }
            if (name.length > 50) {
                alert('Tên dài quá dài, tối đa 50 ký tự!');
                return false;
            }

            // TC04: Kiểm tra email
            if (!email) {
                alert('Vui lòng nhập Email');
                return false;
            }
            if (email.includes(' ')) {
                alert('Email không chứa khoảng trắng!');
                return false;
            }
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert('Email không hợp lệ!');
                return false;
            }
            if ((email.match(/@/g) || []).length !== 1) {
                alert('Email chỉ được chứa đúng một ký tự @!');
                return false;
            }
            const invalidCharPattern = /[!#$%^&*()=\[\]{};:"'\\|,<>?]/;
            if (invalidCharPattern.test(email)) {
                alert('Email không được chứa ký tự đặc biệt nào ngoài @!');
                return false;
            }

            // Kiểm tra email đã tồn tại chưa
            if (email !== currentEmail) {
                const emailStatus = await checkEmailExists(email, userId);
                if (emailStatus === 'exists') {
                    alert('Email đã tồn tại!');
                    return false;
                } else if (emailStatus === 'csrf_invalid') {
                    alert('CSRF token không hợp lệ!');
                    return false;
                }
            }

            // TC05: Kiểm tra vai trò
            if (!role) {
                alert('Vui lòng chọn vai trò!');
                return false;
            }

            // Nếu tất cả đều hợp lệ, gửi form qua AJAX
            const form = document.getElementById('editUserForm');
            const formData = new FormData(form);

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                alert(result.message);
                window.location.href = result.redirect; // Chuyển trang chỉ khi thành công
            } else {
                alert(result.message); // Hiển thị lỗi qua alert, không chuyển trang
            }

            return false; // Ngăn form submit mặc định
        }
    </script>
</body>

</html>

<?php
// Close database connections
$stmt->close();
$conn->close();
?>