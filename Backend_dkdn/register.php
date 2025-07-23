<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Ensure no output before JSON
ob_start();

header('Content-Type: application/json');

// Thông tin kết nối
$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Kết nối thất bại: ' . $conn->connect_error]);
    exit();
}

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit();
}

// Debug: Log toàn bộ dữ liệu POST
error_log('POST data: ' . print_r($_POST, true));

// Lấy dữ liệu đầu vào
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? ''); // Lưu ý: FE hiện không gửi confirm_password
$terms = isset($_POST['terms']) && $_POST['terms'] === 'on' ? 'on' : 'off'; // Lưu ý: FE hiện không gửi terms
$role = 'khachhang';

// Kiểm tra nếu tất cả các trường bắt buộc đều trống
if ($name === '' && $phone === '' && $email === '' && $address === '' && $password === '') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
    exit();
}

// 1. Tên
if ($name === '' || preg_match('/^\\s+$/', $name)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên của bạn!']);
    exit();
}
if (mb_strlen($name, 'UTF-8') < 5) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Tên quá ngắn, phải từ 5 ký tự trở lên!']);
    exit();
}
if (mb_strlen($name, 'UTF-8') > 50) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Tên quá dài, tối đa 50 ký tự!']);
    exit();
}

// 2. Số điện thoại
if ($phone === '') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập số điện thoại!']);
    exit();
}
if (preg_match('/\s/', $phone)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Số điện thoại không được chứa khoảng trắng!']);
    exit();
}
if (strlen($phone) !== 10) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Số điện thoại phải gồm đúng 10 chữ số!']);
    exit();
}
if (preg_match('/[a-zA-Z]/', $phone)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Số điện thoại không được chứa chữ cái!']);
    exit();
}
if (preg_match('/[^0-9]/', $phone)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Số điện thoại không được chứa ký tự đặc biệt!']);
    exit();
}
$digitCounts = array_count_values(str_split($phone));
foreach ($digitCounts as $count) {
    if ($count >= 6) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không được chứa từ 6 số giống nhau trở lên!']);
        exit();
    }
}

// 3. Email
if ($email === '') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập Email!']);
    exit();
}
if (preg_match('/\s/', $email)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Email không được chứa khoảng trắng!']);
    exit();
}
if (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Email không được chứa ký tự đặc biệt nào ngoài @!']);
    exit();
}
if (substr_count($email, '@') !== 1 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Email không hợp lệ!']);
    exit();
}

// 4. Địa chỉ
if ($address === '' || preg_match('/^\\s+$/', $address)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập địa chỉ!']);
    exit();
}
if (mb_strlen($address, 'UTF-8') < 5) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Địa chỉ quá ngắn, phải từ 5 ký tự trở lên!']);
    exit();
}
if (mb_strlen($address, 'UTF-8') > 70) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Địa chỉ không được dài hơn 70 ký tự!']);
    exit();
}
if (preg_match('/[^a-zA-Z0-9\s,.\-À-ỹà-ỹ]/u', $address)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Địa chỉ không được chứa ký tự đặc biệt!']);
    exit();
}

// 5. Mật khẩu
if ($password === '') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mật khẩu!']);
    exit();
}
if (strlen($password) < 8) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Mật khẩu phải dài ít nhất 8 ký tự!']);
    exit();
}

// Kiểm tra email đã tồn tại
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$stmt) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $conn->error]);
    exit();
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Email đã tồn tại!']);
    exit();
}
$stmt->close();

// Insert user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, phone, email, address, password, role) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error]);
    exit();
}
$stmt->bind_param("ssssss", $name, $phone, $email, $address, $hashedPassword, $role);

if ($stmt->execute()) {
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Đăng ký thành công!']);
} else {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi khi đăng ký: ' . $conn->error]);
}
$stmt->close();

$conn->close();
