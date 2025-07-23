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

// Khởi tạo giỏ hàng trong session nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Thông báo nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo '<h3>Bạn chưa có tài khoản đăng nhập !!!!.</h3>';
}

// Kiểm tra nếu id được truyền qua URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Sản phẩm không tồn tại.";
        exit;
    }
} else {
    echo "Không tìm thấy sản phẩm.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm - <?= htmlspecialchars($product['ten_san_pham']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/ChiTietSanPham1.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">

    <style>
        .product-details {
            font-family: Arial, sans-serif;
            margin: 20px;
            width: 100%;
            height: auto;
        }

        .tabs {
            display: flex;
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
        }

        .tab {
            padding: 15px 25px;
            cursor: pointer;
            background: #f9f9f9;
            border: none;
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }

        .tab.active {
            border-bottom: 4px solid #333;
            color: #333;
        }

        .tab-content {
            padding: 30px;
            background: #f5f5f5;
            text-align: center;
            color: #888;
            font-size: 18px;
        }

        .rating-section {
            margin-top: 40px;
        }

        .rating-section h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .rating-overview {
            display: flex;
            align-items: center;
            gap: 50px;
            margin-bottom: 30px;
        }

        .average-rating {
            text-align: center;
            color: #333;
        }

        .average-rating .star {
            color: #ffd700;
            font-size: 64px;
        }

        .rating-score {
            font-size: 48px;
            font-weight: bold;
        }

        .rating-breakdown {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .rating-row {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .rating-row .star {
            color: #ffd700;
            font-size: 18px;
        }

        .rating-row span {
            font-size: 18px;
        }

        .progress-bar {
            width: 200px;
            height: 12px;
            background-color: #e0e0e0;
            position: relative;
            border-radius: 6px;
        }

        .progress-bar::after {
            content: "";
            position: absolute;
            height: 12px;
            background-color: #ffd700;
            width: 0%;
            border-radius: 6px;
        }

        .rate-product {
            text-align: center;
        }

        .rate-product p {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .stars {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .stars span {
            font-size: 36px;
            cursor: pointer;
            color: #ccc;
            transition: color 0.3s;
            padding: 10px;
            border: 2px solid #ccc;
            border-radius: 8px;
        }

        .stars span.selected,
        .stars span:hover,
        .stars span:hover~span {
            color: #ffd700;
            background-color: #fff6e0;
            border-color: #ffd700;
        }

        .login-prompt {
            color: gray;
            font-size: 14px;
            margin-top: 10px;
        }

        .review-link {
            color: #007bff;
            text-decoration: none;
            font-size: 16px;
        }

        .title-v {
            font-size: 12px;
        }

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

        .quantity {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }

        .quantity label {
            margin-right: 10px;
        }

        .quantity button {
            background-color: #f1f1f1;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        .quantity input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .add-to-cart {
            background-color: #DB9087;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .add-to-cart:hover {
            background-color: #b97a6b;
        }

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

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .user-name {
            cursor: pointer;
            padding: 8px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #ffffff;
            min-width: 150px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: 8px;
            padding: 8px;
            border-radius: 15px;
            top: 20px;
        }

        .dropdown-content a {
            color: black;
            text-decoration: none;
            display: block;
            padding: 8px 12px;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
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
                    echo '<div class="dropdown">';
                    echo '<span class="user-name">Xin chào, ' . htmlspecialchars($_SESSION['name']) . '</span>';
                    echo '<div class="dropdown-content">';
                    echo '<a href="../Backend_dkdn/dangxuat.php">Đăng xuất</a>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<a href="./formdangnhapky.php">Tài khoản</a>';
                }
                ?>
                <a href="#">Yêu thích</a>
                <a href="#" id="cartBtn">Giỏ hàng</a>
            </div>
        </div>
        <div class="menu">
            <a href="#">GIỚI THIỆU NOUS</a>
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
        <div class="cart-content" id="cartContent">
            <?php
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
                            <img src="<?= htmlspecialchars($row['anh_san_pham']); ?>" alt="Product Image">
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
            <form method="post" action="chitietgiohang.php">
                <button type="submit" class="checkout-btn">Đặt hàng</button>
            </form>
        </div>
    </div>
    <div id="cartOverlay" class="cart-overlay" onclick="toggleCart()"></div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Main Content -->
    <hr>
    <div class="main">
        <div class="container container_main" style="background-color:#F5F5F5; border-radius:10px;">
            <div class="content">
                <div class="title">
                    <div class="breadcrumb" style="padding-left:20px; padding-top:20px; margin: 0; font-weight: 500;">
                        <a href="./trangchu.php">Trang chủ</a> / <a href="#">Baby</a> / <?= htmlspecialchars($product['ten_san_pham']); ?>
                    </div>
                </div>
                <div class="container">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($product['anh_san_pham']); ?>" alt="<?= htmlspecialchars($product['ten_san_pham']); ?>">
                    </div>
                    <div class="product-details">
                        <h1><?= htmlspecialchars($product['ten_san_pham']); ?></h1>
                        <p class="status">Tình trạng: còn hàng</p>
                        <p class="price"><?= number_format($product['gia'], 0, ',', '.'); ?> ₫</p>

                        <!-- Phần lựa chọn màu sắc -->
                        <div class="form-options color-options">
                            <label>Màu sắc:</label>
                            <div class="color-images">
                                <img src="../img/color1.jpg" alt="Màu sắc sản phẩm 1">
                                <img src="../img/color2.jpg" alt="Màu sắc sản phẩm 2">
                            </div>
                        </div>

                        <!-- Phần lựa chọn kích thước -->
                        <div class="form-options size-options">
                            <label>Kích thước:</label>
                            <div class="sub-options">
                                <button>9M</button>
                                <button>12M</button>
                                <button>18M</button>
                                <button>2Y</button>
                            </div>
                        </div>

                        <!-- Phần tăng giảm số lượng -->
                        <div class="quantity">
                            <label for="quantity">Số lượng:</label>
                            <button onclick="decreaseQuantity()">-</button>
                            <input type="number" id="quantity" value="1" min="1">
                            <button onclick="increaseQuantity()">+</button>
                        </div>

                        <!-- Nút thêm vào giỏ hàng -->
                        <button class="add-to-cart" data-product-id="<?= htmlspecialchars($product['id']); ?>">Thêm vào giỏ hàng</button>

                        <!-- Dropdown danh sách cửa hàng -->
                        <div class="store-locator">
                            <h3>Tìm tại cửa hàng:</h3>
                            <select>
                                <option>Tất cả</option>
                                <option>NOUS Hồ Chí Minh - 79 Mạc Thị Bưởi, Quận 1</option>
                                <option>NOUS Hà Nội - 34 Quang Trung, Hoàn Kiếm</option>
                                <option>NOUS Hồ Chí Minh - 422B Nguyễn Thị Minh Khai, Quận 3</option>
                                <option>NOUS Hà Nội - 170 Cầu Giấy, Cầu Giấy</option>
                            </select>
                            <div class="store">
                                <span>Còn hàng</span> NOUS Hồ Chí Minh - 79 Mạc Thị Bưởi, Quận 1 <a href="#">Xem bản đồ</a>
                            </div>
                            <div class="store">
                                <span>Còn hàng</span> NOUS Hà Nội - 34 Quang Trung, Hoàn Kiếm <a href="#">Xem bản đồ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container container_main">
            <div class="product-details">
                <div class="tabs">
                    <button class="tab active">Mô tả</button>
                    <button class="tab">Thông số kỹ thuật</button>
                </div>
                <div class="tab-content">
                    <p><?= htmlspecialchars($product['mo_ta']) ?: 'Đang cập nhật nội dung'; ?></p>
                </div>

                <div class="rating-section">
                    <h2 style="text-align: center">Đánh giá sản phẩm</h2>
                    <div style="display: flex; justify-content: space-between">
                        <div class="rating-overview">
                            <div class="average-rating">
                                <span class="star">★</span>
                                <span class="rating-score">0.0</span>
                                <p>0 đánh giá</p>
                            </div>
                            <div class="rating-breakdown">
                                <div class="rating-row"><span>★★★★★</span>
                                    <div class="progress-bar"></div><span>0</span>
                                </div>
                                <div class="rating-row"><span>★★★★☆</span>
                                    <div class="progress-bar"></div><span>0</span>
                                </div>
                                <div class="rating-row"><span>★★★☆☆</span>
                                    <div class="progress-bar"></div><span>0</span>
                                </div>
                                <div class="rating-row"><span>★★☆☆☆</span>
                                    <div class="progress-bar"></div><span>0</span>
                                </div>
                                <div class="rating-row"><span>★☆☆☆☆</span>
                                    <div class="progress-bar"></div><span>0</span>
                                </div>
                            </div>
                        </div>
                        <div class="rate-product">
                            <p style="text-align: start; margin-top: 0">Đánh giá sản phẩm</p>
                            <div class="stars">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <p class="login-prompt title-v" style="font-size: 12px">
                                Bạn cần đăng nhập để nhận xét và đánh giá sản phẩm
                            </p>
                            <a href="#" class="review-link title-v">Hãy là người đầu tiên đánh giá sản phẩm!</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Quantity controls
        function increaseQuantity() {
            let quantityInput = document.getElementById('quantity');
            quantityInput.value = parseInt(quantityInput.value) + 1;
        }

        function decreaseQuantity() {
            let quantityInput = document.getElementById('quantity');
            if (quantityInput.value > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }

        // Star rating
        const stars = document.querySelectorAll(".rate-product .stars span");
        let rating = 0;
        stars.forEach((star, index) => {
            star.addEventListener("mouseover", () => {
                stars.forEach((s, i) => {
                    s.style.color = i <= index ? "#FFD700" : "#ccc";
                });
            });
            star.addEventListener("click", () => {
                rating = index + 1;
                stars.forEach((s, i) => {
                    s.style.color = i < rating ? "#FFD700" : "#ccc";
                });
            });
            star.addEventListener("mouseout", () => {
                stars.forEach((s, i) => {
                    s.style.color = i < rating ? "#FFD700" : "#ccc";
                });
            });
        });

        // Toggle cart
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

        // Toast notification
        function showToast(message, type) {
            const toast = document.getElementById("toast");
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.style.display = "block";
            setTimeout(() => {
                toast.style.display = "none";
            }, 3000);
        }

        // Add to cart
        document.querySelector(".add-to-cart").addEventListener("click", function(event) {
            event.preventDefault();
            const productId = this.getAttribute("data-product-id");
            const quantity = document.getElementById("quantity").value;
            fetch("../Backend_giohang/giohangnho.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `product_id=${productId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // showToast("Thêm vào giỏ hàng thành công!", "success");
                        refreshCart();
                    } else {
                        showToast(data.message, "error");
                    }
                })
                .catch(error => {
                    showToast("Lỗi khi thêm vào giỏ hàng!", "error");
                    console.error("Error:", error);
                });
        });

        // Update quantity
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
                    });
            }
        });

        // Delete item
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
                    });
            }
        });

        // Refresh cart content
        function refreshCart() {
            fetch("../Backend_giohang/get_cart.php")
                .then(response => response.text())
                .then(html => {
                    document.getElementById("cartContent").innerHTML = html;
                })
                .catch(error => {
                    showToast("Lỗi khi tải giỏ hàng!", "error");
                });
        }
    </script>
    <script src="../js/demo.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper('.swiper-container', {
            slidesPerView: 4,
            spaceBetween: 20,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            }
        });
    </script>
</body>

</html>
<?php
$stmt->close();
$conn->close();
?>