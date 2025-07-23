<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối thất bại: ' . $conn->connect_error]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập để thêm sản phẩm vào giỏ hàng.']);
    exit();
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];

// Kiểm tra xem sản phẩm có tồn tại trong cơ sở dữ liệu không
$stmt = $conn->prepare("SELECT id FROM sanpham WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại!']);
    $stmt->close();
    $conn->close();
    exit();
}

try {
    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    $stmt = $conn->prepare("SELECT * FROM gio_hang WHERE user_id = ? AND san_pham_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu sản phẩm đã có, tăng số lượng
        $stmt = $conn->prepare("UPDATE gio_hang SET so_luong = so_luong + 1 WHERE user_id = ? AND san_pham_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        // Nếu sản phẩm chưa có, thêm mới
        $stmt = $conn->prepare("INSERT INTO gio_hang (user_id, san_pham_id, so_luong) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $user_id, $product_id);
    }

    $stmt->execute();
    // echo json_encode(['success' => true, 'message' => 'Thêm vào giỏ hàng thành công!']);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
