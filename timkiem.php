<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Kết nối cơ sở dữ liệu thất bại']);
    exit;
}

if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);

    if (empty($keyword)) {
        echo json_encode([]);
        exit;
    }

    // Truy vấn tìm kiếm sản phẩm
    $stmt = $conn->prepare("SELECT id, ten_san_pham, gia, anh_san_pham FROM sanpham WHERE ten_san_pham LIKE ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi truy vấn']);
        exit;
    }

    $searchTerm = "%" . $keyword . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'ten_san_pham' => htmlspecialchars($row['ten_san_pham']),
            'gia' => (float)$row['gia'], // Đảm bảo giá là số
            'anh_san_pham' => htmlspecialchars($row['anh_san_pham'])
        ];
    }

    echo json_encode($products);
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu tham số keyword']);
}

$conn->close();
?>