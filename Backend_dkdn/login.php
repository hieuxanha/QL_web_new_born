<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Ensure no output before JSON
ob_start();

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For testing, adjust in production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

// Tạo kết nối
$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

// Kiểm tra kết nối
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error]);
    exit();
}

// Chỉ xử lý khi là phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Phương thức không hợp lệ"]);
    exit();
}

// Lấy và kiểm tra dữ liệu đầu vào
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Kiểm tra email và password trống
if (empty($email) && empty($password)) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Vui lòng điền đầy đủ thông tin đăng nhập !"]);
    exit();
}
if (empty($email)) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Vui lòng nhập Email!"]);
    exit();
}
if (empty($password)) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Vui lòng nhập mật khẩu!"]);
    exit();
}

// Kiểm tra khoảng trắng trong email
if (preg_match('/\s/', $email)) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Email không được chứa khoảng trắng!"]);
    exit();
}

// Kiểm tra ký tự đặc biệt trong local part của email
$localPart = explode('@', $email)[0];
if (preg_match('/[^a-zA-Z0-9._-]/', $localPart)) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Email không được chứa ký tự đặc biệt!"]);
    exit();
}

// Kiểm tra email hợp lệ
if (substr_count($email, '@') !== 1 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Email không hợp lệ!"]);
    exit();
}

// Kiểm tra độ dài mật khẩu
if (strlen($password) < 8) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Mật khẩu phải có ít nhất 8 ký tự!"]);
    exit();
}

// Kiểm tra kết nối và chuẩn bị câu lệnh truy vấn
$stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
if (!$stmt) {
    error_log('Prepare statement failed: ' . $conn->error);
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Lỗi kết nối cơ sở dữ liệu: " . $conn->error]);
    exit();
}

$stmt->bind_param("s", $email);
if (!$stmt->execute()) {
    error_log('Statement execution failed: ' . $stmt->error);
    $stmt->close();
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Lỗi truy vấn cơ sở dữ liệu: " . $stmt->error]);
    exit();
}

$stmt->store_result();

// Kiểm tra xem email có tồn tại không
if ($stmt->num_rows === 0) {
    $stmt->close();
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Email chưa được đăng ký"]);
    exit();
}

// Lấy thông tin người dùng
$stmt->bind_result($id, $name, $hashedPassword, $role);
$stmt->fetch();

// Kiểm tra mật khẩu
if (!password_verify($password, $hashedPassword)) {
    $stmt->close();
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Mật khẩu không đúng"]);
    exit();
}

// Lưu thông tin người dùng vào session
$_SESSION['user_id'] = $id;
$_SESSION['name'] = $name;
$_SESSION['role'] = $role;

// Xác định trang chuyển hướng dựa trên vai trò
$redirectPage = match ($role) {
    'admin'    => "../Frontend_web/giaodienql.php",
    'nhanvien' => "../Frontend_web/giaodienql1.php",
    default    => "../Frontend_web/trangchu.php",
};

// Phản hồi JSON
ob_end_clean();
echo json_encode([
    "status" => "success",
    "message" => "Đăng nhập thành công",
    "redirect" => $redirectPage
]);

// Đóng statement và kết nối
$stmt->close();
$conn->close();
