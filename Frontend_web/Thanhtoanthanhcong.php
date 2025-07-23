<?php
// Bắt đầu phiên làm việc
session_start();


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

// Kiểm tra nếu người dùng đã đăng nhập và đã có id của hóa đơn
if (isset($_SESSION['user_id']) && isset($_SESSION['hoa_don_id'])) {
    $user_id = $_SESSION['user_id'];
    $hoaDonId = $_SESSION['hoa_don_id'];

    // Truy vấn lấy thông tin chi tiết hóa đơn dựa trên id hóa đơn
    $query = "SELECT c.san_pham_id, c.soLuong, c.giaTien, c.thanhTien, s.ten_san_pham, s.anh_san_pham
              FROM chitiet_hoadon c
              JOIN sanpham s ON c.san_pham_id = s.id
              WHERE c.hoa_don_id = '$hoaDonId'"; // Lọc theo ID hóa đơn

    // Kiểm tra nếu truy vấn thực thi thành công
    $result = $conn->query($query);
    if (!$result) {
        // Nếu truy vấn không thành công, in lỗi và dừng thực thi
        die("Lỗi truy vấn: " . $conn->error);
    }

    // Khởi tạo mảng để chứa các sản phẩm trong giỏ hàng
    $cartItems = [];
    $totalPrice = 0;
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $totalPrice += $row['thanhTien']; // Tính tổng tiền từ thanhTien
    }

    // Truy vấn lấy thông tin khách hàng từ bảng hóa đơn
    //    hoadon là thanhtoan
    $customerQuery = "SELECT hoTen, email, soDienThoai, diaChi FROM thanhtoan WHERE user_id = '$user_id' AND id = '$hoaDonId'";
    $customerResult = $conn->query($customerQuery);
    if (!$customerResult) {
        // Nếu truy vấn không thành công, in lỗi và dừng thực thi
        die("Lỗi truy vấn thông tin khách hàng: " . $conn->error);
    }
    $customer = $customerResult->fetch_assoc();
} else {
    echo "Thông tin người dùng hoặc hóa đơn không tồn tại.";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng Của Bạn</title>
    <link rel="stylesheet" href="../css/vanh.css">
</head>

<body>
    <div class="container">
        <h1>ĐƠN HÀNG ĐÃ ĐƯỢC TIẾP NHẬN</h1>
        <p class="order-notice">Cảm ơn quý khách đã đặt hàng tại <a href="#">Website của chúng tôi</a>.</p>

        <div class="order-summary">
            <h3>GIỎ HÀNG CỦA BẠN</h3>

            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <img src="/QL_web_new_born/Frontend_web/<?php echo htmlspecialchars($item['anh_san_pham']); ?>" alt="Sản phẩm">
                    <div class="cart-item-info">
                        <p class="cart-item-name"><?php echo htmlspecialchars($item['ten_san_pham']); ?></p>
                        <p class="cart-item-price"><?php echo number_format($item['giaTien'], 0, ',', '.') . "đ"; ?></p>
                        <p class="cart-item-quantity">Số lượng: <?php echo $item['soLuong']; ?></p>
                        <p class="cart-item-total">Thành tiền:
                            <?php echo number_format($item['thanhTien'], 0, ',', '.') . "đ"; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="total">
                <span>Tổng tiền:</span>
                <strong><?php echo number_format($totalPrice, 0, ',', '.') . "đ"; ?></strong>
            </div>
        </div>

        <div class="customer-info">
            <label>Họ tên</label>
            <input type="text" value="<?php echo htmlspecialchars($customer['hoTen']); ?>" disabled>

            <label>Địa chỉ nhận hàng</label>
            <input type="text" value="<?php echo htmlspecialchars($customer['diaChi']); ?>" disabled>

            <label>Số điện thoại</label>
            <input type="text" value="<?php echo htmlspecialchars($customer['soDienThoai']); ?>" disabled>

            <label>Email</label>
            <input type="text" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled>
        </div>
        <a href="trangchu.php" class="back-to-home">← Quay lại trang chủ</a>
    </div>
</body>

</html>