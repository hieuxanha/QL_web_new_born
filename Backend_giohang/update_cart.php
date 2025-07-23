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

if (!isset($_POST['id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
    exit();
}

$cart_id = (int)$_POST['id'];
$action = $_POST['action'];

try {
    $stmt = $conn->prepare("SELECT so_luong FROM gio_hang WHERE id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $cart_item = $result->fetch_assoc();
        $current_quantity = $cart_item['so_luong'];

        if ($action === 'increase') {
            $new_quantity = $current_quantity + 1;
        } elseif ($action === 'decrease' && $current_quantity > 1) {
            $new_quantity = $current_quantity - 1;
        } else {
            // echo json_encode(['success' => false, 'message' => 'Số lượng không thể giảm thêm!']);
            echo json_encode(['success' => false]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE gio_hang SET so_luong = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $cart_id);
        $stmt->execute();

        // echo json_encode(['success' => true, 'message' => 'Cập nhật giỏ hàng thành công!']);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng!']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
