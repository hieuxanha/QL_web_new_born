<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

if ($conn->connect_error) {
    echo "<p>Lỗi kết nối cơ sở dữ liệu.</p>";
    exit();
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT g.id, g.san_pham_id, g.so_luong, s.ten_san_pham, s.gia, s.anh_san_pham
        FROM gio_hang g
        INNER JOIN sanpham s ON g.san_pham_id = s.id
        WHERE g.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $productTotal = $row['gia'] * $row['so_luong'];
?>
            <div class="cart-item" data-cart-id="<?= $row['id']; ?>">
                <img src="<?= htmlspecialchars($row['anh_san_pham']); ?>" alt="Product Image">
                <div class="details">
                    <h3><?= htmlspecialchars($row['ten_san_pham']); ?></h3>
                    <p>SKU: <?= htmlspecialchars($row['san_pham_id']); ?></p>
                    <p>Số lượng: <?= htmlspecialchars($row['so_luong']); ?></p>
                </div>
                <div class="quantity">
                    <button class="decrease quantity-btn" data-action="decrease">-</button>
                    <input type="number" value="<?= htmlspecialchars($row['so_luong']); ?>" readonly style="width: 50px; text-align: center;">
                    <button class="increase quantity-btn" data-action="increase">+</button>
                </div>
                <div class="price"><?= number_format($row['gia'], 0, ',', '.'); ?>₫</div>
            </div>
<?php
        }
    } else {
        echo "<p>Giỏ hàng của bạn đang trống.</p>";
    }
    $stmt->close();
} else {
    echo "<p>Vui lòng đăng nhập để xem giỏ hàng.</p>";
}

$conn->close();
?>