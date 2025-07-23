<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "newborn_shop1";

// Tạo kết nối
$conn = new mysqli('127.0.0.1', 'root', '', 'newborn_shop1');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Hiển thị thông báo nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo '<h3> Bạn chưa có tài khoản đăng nhập !!!!.</h3>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/giohang.css">
    <style>
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100%;
            background-color: #f9f9f9;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5);
            transition: right 0.3s ease;
            z-index: 1000;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 999;
        }

        .cart-sidebar.active {
            right: 0;
        }

        .cart-overlay.active {
            visibility: visible;
            opacity: 1;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            background-color: #fff;
            border-bottom: 1px solid #ddd;
        }

        .cart-header h2 {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .close-btn {
            font-size: 24px;
            cursor: pointer;
            color: #333;
        }

        .cart-content {
            padding: 1.5rem;
            overflow-y: auto;
            flex-grow: 1;
        }

        .cart-content p {
            font-size: 16px;
            color: #555;
        }

        .checkout-btn {
            background-color: #DB9087;
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .checkout-btn:hover {
            background-color: #b97a6b;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #ddd;
        }

        .cart-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin-right: 10px;
        }

        .cart-item-details {
            flex: 1;
            padding-right: 10px;
        }

        .cart-item-title {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }

        .cart-item-price {
            font-size: 14px;
            color: #555;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #555;
        }

        .cart-item-quantity button {
            background-color: #f1f1f1;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .cart-item-quantity input {
            width: 40px;
            text-align: center;
            border: 1px solid #ddd;
            margin: 0 5px;
        }

        .thanhtoan {
            padding: 20px;
            background-color: white;
            border-top: 1px solid #ddd;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #777;
        }

        .thanhtoan .cart-total {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .thanhtoan .cart-total p {
            margin: 0;
            color: #999;
        }

        .thanhtoan .cart-total a {
            color: #999;
            text-decoration: none;
            font-size: 14px;
        }

        .thanhtoan .cart-total a:hover {
            color: #777;
        }

        /* Toast Notification */
        /* .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px;
            color: white;
            border-radius: 5px;
            z-index: 1000;
            display: none;
        }

        .toast.success {
            background-color: #28a745;
        }

        .toast.error {
            background-color: #dc3545;
        } */
    </style>
</head>

<body>
    <!-- Header -->
    <div class="container container_header">
        <div class="header">
            <div class="logo">
                <a href="trangchu.php"><img src="../img/logo.webp" alt="Logo-Nous" /></a>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Bạn cần tìm gì ..." />
                <button class="search-button">Tìm kiếm</button>
            </div>
            <div class="account">
                <?php
                if (isset($_SESSION['name'])) {
                    echo '<span>Xin chào, ' . htmlspecialchars($_SESSION['name']) . '</span>';
                } else {
                    echo '<a href="./fromdangnhapky.php">Tài khoản</a>';
                }
                ?>
                <a href="#">Yêu thích</a>
                <a href="#" id="cartBtn">Giỏ hàng</a>
            </div>
        </div>
        <div class="menu">
            <a href="http://localhost/QL_web_new_born/Frontend_web/trangchu.php">GIỚI THIỆU NOUS</a>
            <a href="#">BÉ MẶC</a>
            <a href="#">BÉ NGỦ</a>
            <a href="#">BÉ CHƠI</a>
            <a href="#">BÉ ĂN UỐNG</a>
            <a href="#">BÉ VỆ SINH</a>
            <a href="#">BÉ RA NGOÀI</a>
        </div>
    </div>

    <!-- Giỏ Hàng Sidebar -->
    <div id="cartSidebar" class="cart-sidebar">
        <div class="cart-header">
            <h2>Giỏ Hàng</h2>
            <span class="close-btn" onclick="toggleCart()">×</span>
        </div>
        <div class="cart-content" id="cartSidebarContent">
            <?php
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $stmt = $conn->prepare("
                    SELECT g.id, g.san_pham_id, g.so_luong, s.ten_san_pham, s.gia, s.anh_san_pham
                    FROM gio_hang g
                    INNER JOIN sanpham s ON g.san_pham_id = s.id
                    WHERE g.user_id = ?
                ");
                if ($stmt === false) {
                    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
                }
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
            ?>
        </div>
        <div class="thanhtoan">
            <form method="post" action="dat_hang.php">
                <button type="submit" class="checkout-btn">Đặt hàng</button>
            </form>
        </div>
    </div>

    <!-- Overlay -->
    <div id="cartOverlay" class="cart-overlay" onclick="toggleCart()"></div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <div class="container">
        <div class="cart">
            <div class="cart-left">
                <?php
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("
                        SELECT g.id, g.san_pham_id, g.so_luong, s.ten_san_pham, s.gia, s.anh_san_pham
                        FROM gio_hang g
                        INNER JOIN sanpham s ON g.san_pham_id = s.id
                        WHERE g.user_id = ?
                    ");
                    if ($stmt === false) {
                        die("Lỗi chuẩn bị truy vấn: " . $conn->error);
                    }
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
                } else {
                    echo "<p>Vui lòng đăng nhập để xem giỏ hàng.</p>";
                }
                ?>
            </div>
            <div class="cart-right">
                <div class="order-info">
                    <p class="abc">Phí vận chuyển sẽ được tính ở trang thanh toán. Bạn cũng có thể nhập mã giảm giá ở trang thanh toán</p>
                    <div class="total">
                        Tạm tính (<?= isset($totalPrice) ? number_format($totalPrice, 0, ',', '.') : 0; ?> ₫)
                    </div>
                    <label><input type="checkbox"> Xuất Hóa Đơn</label>
                    <form action="Thanhtoan.php" method="post">
                        <button type="submit" class="order-button">Xác nhận đặt hàng</button>
                    </form>
                </div>
            </div>
        </div>
        <h3 style="margin-top: 10px;" class="abc">Ghi chú</h3>
        <textarea placeholder="Vui lòng nhập ghi chú của bạn..."></textarea>
    </div>

    <script>
        function toggleCart() {
            const cartSidebar = document.getElementById("cartSidebar");
            const cartOverlay = document.getElementById("cartOverlay");
            cartSidebar.classList.toggle("active");
            cartOverlay.classList.toggle("active");
        }

        document.getElementById("cartBtn").addEventListener("click", function(event) {
            event.preventDefault();
            toggleCart();
        });

        // Toast notification function
        function showToast(message, type) {
            const toast = document.getElementById("toast");
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.style.display = "block";
            setTimeout(() => {
                toast.style.display = "none";
            }, 3000);
        }

        // Update quantity in main cart and sidebar
        document.addEventListener("click", function(event) {
            if (event.target.classList.contains("quantity-btn")) {
                const cartItem = event.target.closest(".cart-item");
                const cartId = cartItem.getAttribute("data-cart-id");
                const action = event.target.getAttribute("data-action");
                fetch("../Backend_giohang/update_cart.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `id=${cartId}&action=${action}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // showToast("Cập nhật giỏ hàng thành công!", "success");
                            refreshCart();
                        } else {
                            showToast(data.message, "error");
                        }
                    })
                    .catch(error => {
                        showToast("Lỗi khi cập nhật giỏ hàng!", "error");
                        console.error("Error:", error);
                    });
            }
        });

        // Delete item from cart
        document.addEventListener("click", function(event) {
            if (event.target.classList.contains("delete-btn")) {
                const productId = event.target.getAttribute("data-product-id");
                fetch("../Backend_giohang/xoa_gio_hang.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // showToast("Xóa sản phẩm thành công!", "success");
                            refreshCart();
                        } else {
                            showToast(data.message, "error");
                        }
                    })
                    .catch(error => {
                        showToast("Lỗi khi xóa sản phẩm!", "error");
                        console.error("Error:", error);
                    });
            }
        });

        // Refresh both main cart and sidebar cart
        function refreshCart() {
            // Refresh sidebar cart
            fetch("../Backend_giohang/get_cart.php")
                .then(response => response.text())
                .then(html => {
                    document.getElementById("cartSidebarContent").innerHTML = html;
                })
                .catch(error => {
                    showToast("Lỗi khi tải giỏ hàng!", "error");
                    console.error("Error:", error);
                });

            // Refresh main cart
            fetch("../Backend_giohang/get_main_cart.php")
                .then(response => response.text())
                .then(html => {
                    document.querySelector(".cart-left").innerHTML = html;
                    // Update total price in cart-right
                    const totalPriceElement = document.querySelector(".cart-right .total");
                    const totalPrice = Array.from(document.querySelectorAll(".cart-left .cart-item"))
                        .reduce((total, item) => {
                            const price = parseFloat(item.querySelector(".price").textContent.replace(/[^\d]/g, ""));
                            const quantity = parseInt(item.querySelector(".quantity input").value);
                            return total + price * quantity;
                        }, 0);
                    totalPriceElement.textContent = `Tạm tính (${totalPrice.toLocaleString()} ₫)`;
                })
                .catch(error => {
                    showToast("Lỗi khi tải giỏ hàng chính!", "error");
                    console.error("Error:", error);
                });
        }
    </script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper('.swiper-container', {
            slidesPerView: 4,
            spaceBetween: 20,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    </script>
</body>

<?php
$stmt->close();
$conn->close();
?>

</html>