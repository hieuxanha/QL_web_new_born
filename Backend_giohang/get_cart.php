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
        $totalPrice = 0;
        while ($row = $result->fetch_assoc()) {
            $productTotal = $row['gia'] * $row['so_luong'];
            $totalPrice += $productTotal;
?>
            <div class="cart-item" data-cart-id="<?= $row['id']; ?>">
                <img src="/QL_web_new_born/Frontend_web/<?= htmlspecialchars($row['anh_san_pham']); ?>" alt="Product Image">
                <div class="cart-item-details">
                    <div class="cart-item-title"><?= htmlspecialchars($row['ten_san_pham']); ?></div>
                    <div class="cart-item-price"><?= number_format($row['gia'], 0, ',', '.'); ?> ₫</div>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" data-action="decrease">-</button>
                        <input type="text" value="<?= $row['so_luong']; ?>" readonly style="width: 30px; text-align: center;">
                        <button class="quantity-btn" data-action="increase">+</button>
                    </div>
                </div>
                <button class="delete-btn" data-product-id="<?= $row['san_pham_id']; ?>">XÓA</button>
            </div>
        <?php
        }
        ?>
        <div class="cart-total-price">
            <p><strong>Tổng cộng:</strong> <span id="totalPrice"><?= number_format($totalPrice, 0, ',', '.'); ?> ₫</span></p>
        </div>
<?php
    } else {
        echo "<p>Giỏ hàng của bạn đang trống.</p>";
    }
} else {
    echo "<p>Vui lòng đăng nhập để xem giỏ hàng.</p>";
}

$stmt->close();
$conn->close();
?>