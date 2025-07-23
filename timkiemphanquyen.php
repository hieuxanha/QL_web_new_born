<?php

$servername = "localhost"; // Địa chỉ server (thường là localhost)
$username = "root"; // Tên người dùng MySQL
$password = ""; // Mật khẩu MySQL
$database = "newborn_shop1"; // Tên cơ sở dữ liệu

// Tạo kết nối
$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
} 

 

if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];

    // Truy vấn tìm kiếm người dùng theo tên và vai trò 'nhanvien' hoặc 'khachhang'
    $stmt = $conn->prepare("SELECT id, name, phone, email, address, `role`, created_at 
                            FROM users 
                            WHERE name LIKE ? AND `role` IN ('nhanvien', 'khachhang')");
    $searchTerm = "%" . $keyword . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    // Trả kết quả dưới dạng JSON
    header('Content-Type: application/json');
    echo json_encode($users);
}
?>
