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

// Thông báo
if (!isset($_SESSION['user_id'])) {
    echo '<h5> Vui lòng đăng nhập hoặc đăng ký! </h5>';
}

// Truy vấn sản phẩm
$sql = "SELECT * FROM sanpham";
$result = $conn->query($sql);

$featured_sql = "SELECT * FROM sanpham WHERE san_pham_noi_bat = 1";
$featured_result = $conn->query($featured_sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/demo.css" />
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">

    <style>
        a {
            text-decoration: none;
            color: rgb(88, 89, 91);
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

        #searchResults {
            position: absolute;
            top: 40px;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            transform: translateY(-20px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
            z-index: 1000;
            display: none;
        }

        #searchResults.active {
            display: block;
            transform: translateY(0);
            opacity: 1;
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
        } */

        /* 
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
                <input type="text" id="searchInput" name="keyword" placeholder="Bạn cần tìm gì ..." required />
                <button type="button" id="searchButton" class="search-button">Tìm kiếm</button>
                <div id="searchResults"></div>
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
                }
                ?>
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
            <form method="post" action="chitietgiohang.php">
                <button type="submit" class="checkout-btn">Đặt hàng</button>
            </form>
        </div>
    </div>
    <div id="cartOverlay" class="cart-overlay" onclick="toggleCart()"></div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Slide chạy -->
    <div class="slider-container">
        <div class="slider">
            <div class="slides">
                <img class="hoo" src="../img/slide1.webp" alt="Image 1" />
                <img class="hoo" src="../img/slide2.webp" alt="Image 2" />
                <img class="hoo" src="../img/slide3.webp" alt="Image 3" />
                <img class="hoo" src="../img/slide4.webp" alt="Image 4" />
            </div>
        </div>
        <button class="prev" onclick="prevSlide()">❮</button>
        <button class="next" onclick="nextSlide()">❯</button>
        <div class="dots" id="dots-container"></div>
    </div>

    <!-- Con trẻ là tuyệt vời nhất -->
    <div class="container container_introduce">
        <div class="img-baby">
            <img src="../img/con_tre_tuyet_voi_nhat.webp" alt="img" />
        </div>
        <div class="content_introduce">
            <h3>Con trẻ tuyệt nhất <br />khi thoải mái là chính mình <br /></h3>
            <div class="p">
                Mỗi thiết kế của Nous đều tuân thủ triết lý "COMFYNISTA - Thoải mái chính là thời trang", trong đó sự thoải mái của các bé được ưu tiên trong mỗi chi tiết nhỏ nhưng vẫn chứa đựng sự tinh tế và khác biệt. Vì vậy, Nous luôn được hàng triệu bà mẹ Việt Nam tin chọn nâng niu hành trình lớn khôn của bé.
                <div class="home_about_icon left">“</div>
                <div class="home_about_icon right">“</div>
            </div>
            <div class="see_more">
                <a class="see_more-link" href="#">
                    <img src="../img/xem_them.webp" alt="xemthem" />
                    <div class="img-text">XEM THÊM</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Sản phẩm nổi bật -->
    <div class="container_outermost">
        <div class="container container_products">
            <section class="featured-products">
                <h2 style="font-size: 33px;">SẢN PHẨM NỔI BẬT</h2>
                <div class="products">
                    <?php
                    if ($featured_result->num_rows > 0) {
                        while ($row = $featured_result->fetch_assoc()) {
                    ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="/QL_web_new_born/Frontend_web/<?php echo htmlspecialchars($row['anh_san_pham']); ?>" alt="<?php echo htmlspecialchars($row['ten_san_pham']); ?>" />
                                    <div class="new-tag">NEW</div>
                                    <div class="cart-icon">
                                        <button class="add-to-cart" data-product-id="<?= $row['id']; ?>"><img src="../img/cart2.svg" alt="Add to cart" /></button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <p class="product-name"><?php echo htmlspecialchars($row['ten_san_pham']); ?></p>
                                    <p class="product-price"><?php echo number_format($row['gia'], 0, ',', '.') . '₫'; ?></p>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p>Không có sản phẩm nổi bật nào.</p>";
                    }
                    ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Bé mặc -->
    <?php
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE loai_san_pham = 'Bé mặc'");
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
    <div class="container_outermost">
        <div class="container container_products">
            <section class="featured-products">
                <h2>BÉ MẶC</h2>
                <div class="products">
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <div class="product-card">
                                <a href="ChiTietSanPham.php?id=<?= $row['id']; ?>">
                                    <div class="product-image">
                                        <img src="/QL_web_new_born/Frontend_web/<?= htmlspecialchars($row['anh_san_pham']); ?>" />
                                        <div class="new-tag">NEW</div>
                                        <div class="cart-icon">
                                            <button class="add-to-cart" data-product-id="<?= $row['id']; ?>"><img src="../img/cart2.svg" alt="Add to cart" /></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <p class="product-name"><?= htmlspecialchars($row['ten_san_pham']); ?></p>
                                        <p class="product-price"><?= number_format($row['gia'], 0, ',', '.'); ?> ₫</p>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p>Không có sản phẩm nào trong danh mục này.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Bé ngủ -->
    <?php
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE loai_san_pham = 'Bé ngủ'");
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
    <div class="container_outermost">
        <div class="container container_products">
            <section class="featured-products">
                <h2>BÉ NGỦ</h2>
                <div class="products">
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <div class="product-card">
                                <a href="ChiTietSanPham.php?id=<?= $row['id']; ?>">
                                    <div class="product-image">
                                        <img src="/QL_web_new_born/Frontend_web/<?= htmlspecialchars($row['anh_san_pham']); ?>" />
                                        <div class="new-tag">NEW</div>
                                        <div class="cart-icon">
                                            <button class="add-to-cart" data-product-id="<?= $row['id']; ?>"><img src="../img/cart2.svg" alt="Add to cart" /></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <p class="product-name"><?= htmlspecialchars($row['ten_san_pham']); ?></p>
                                        <p class="product-price"><?= number_format($row['gia'], 0, ',', '.'); ?> ₫</p>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p>Không có sản phẩm nào trong danh mục này.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Bé chơi -->
    <?php
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE loai_san_pham = 'Bé chơi'");
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
    <div class="container_outermost">
        <div class="container container_products">
            <section class="featured-products">
                <h2>BÉ CHƠI</h2>
                <div class="products">
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <div class="product-card">
                                <a href="ChiTietSanPham.php?id=<?= $row['id']; ?>">
                                    <div class="product-image">
                                        <img src="/QL_web_new_born/Frontend_web/<?= htmlspecialchars($row['anh_san_pham']); ?>" />
                                        <div class="new-tag">NEW</div>
                                        <div class="cart-icon">
                                            <button class="add-to-cart" data-product-id="<?= $row['id']; ?>"><img src="../img/cart2.svg" alt="Add to cart" /></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <p class="product-name"><?= htmlspecialchars($row['ten_san_pham']); ?></p>
                                        <p class="product-price"><?= number_format($row['gia'], 0, ',', '.'); ?> ₫</p>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p>Không có sản phẩm nào trong danh mục này.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Bé ăn uống -->
    <?php
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE loai_san_pham = 'Bé ăn uống'");
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
    <div class="container_outermost">
        <div class="container container_products">
            <section class="featured-products">
                <h2>BÉ ĂN UỐNG</h2>
                <div class="products">
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <div class="product-card">
                                <a href="ChiTietSanPham.php?id=<?= $row['id']; ?>">
                                    <div class="product-image">
                                        <img src="/QL_web_new_born/Frontend_web/<?= htmlspecialchars($row['anh_san_pham']); ?>" />
                                        <div class="new-tag">NEW</div>
                                        <div class="cart-icon">
                                            <button class="add-to-cart" data-product-id="<?= $row['id']; ?>"><img src="../img/cart2.svg" alt="Add to cart" /></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <p class="product-name"><?= htmlspecialchars($row['ten_san_pham']); ?></p>
                                        <p class="product-price"><?= number_format($row['gia'], 0, ',', '.'); ?> ₫</p>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p>Không có sản phẩm nào trong danh mục này.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Bé vệ sinh -->
    <?php
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE loai_san_pham = 'Bé vệ sinh'");
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
    <div class="container_outermost">
        <div class="container container_products">
            <section class="featured-products">
                <h2>BÉ VỆ SINH</h2>
                <div class="products">
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <div class="product-card">
                                <a href="ChiTietSanPham.php?id=<?= $row['id']; ?>">
                                    <div class="product-image">
                                        <img src="/QL_web_new_born/Frontend_web/<?= htmlspecialchars($row['anh_san_pham']); ?>" />
                                        <div class="new-tag">NEW</div>
                                        <div class="cart-icon">
                                            <button class="add-to-cart" data-product-id="<?= $row['id']; ?>"><img src="../img/cart2.svg" alt="Add to cart" /></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <p class="product-name"><?= htmlspecialchars($row['ten_san_pham']); ?></p>
                                        <p class="product-price"><?= number_format($row['gia'], 0, ',', '.'); ?> ₫</p>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p>Không có sản phẩm nào trong danh mục này.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Bé ra ngoài -->
    <?php
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE loai_san_pham = 'Bé ra ngoài'");
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
    <div class="container_outermost">
        <div class="container container_products">
            <section class="featured-products">
                <h2>BÉ RA NGOÀI</h2>
                <div class="products">
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <div class="product-card">
                                <a href="ChiTietSanPham.php?id=<?= $row['id']; ?>">
                                    <div class="product-image">
                                        <img src="/QL_web_new_born/Frontend_web/<?= htmlspecialchars($row['anh_san_pham']); ?>" />
                                        <div class="new-tag">NEW</div>
                                        <div class="cart-icon">
                                            <button class="add-to-cart" data-product-id="<?= $row['id']; ?>"><img src="../img/cart2.svg" alt="Add to cart" /></button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <p class="product-name"><?= htmlspecialchars($row['ten_san_pham']); ?></p>
                                        <p class="product-price"><?= number_format($row['gia'], 0, ',', '.'); ?> ₫</p>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p>Không có sản phẩm nào trong danh mục này.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Features -->
    <div>
        <div class="features-container">
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="../icon/iccon1.webp" alt="Giao hàng nhanh">
                </div>
                <h3>Giao hàng nhanh, miễn phí</h3>
                <p>Cho đơn hàng từ 399k trở lên hoặc đăng ký thành viên để hưởng nhiều ưu đãi</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="../icon/iccon2.webp" alt="Trả hàng, Bảo hành">
                </div>
                <h3>Trả hàng, bảo hành</h3>
                <p>Đổi trả/bảo hành lên đến 30 ngày</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="../icon/iccon3.webp" alt="Thành viên">
                </div>
                <h3>Thành viên</h3>
                <p>Đăng ký thành viên để nhận được nhiều ưu đãi độc quyền</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="../icon/iccon4.webp" alt="Chính hãng">
                </div>
                <h3>Chính hãng</h3>
                <p>Sản phẩm nguồn gốc xuất xứ rõ ràng - an toàn - thoải mái</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="container">
            <div class="row g-0 row-cols-4">
                <div class="col-lg-4 col-4 mb-4">
                    <div class="sub-mid">
                        <h4>Giới Thiệu</h4>
                        <hr>
                        <li><a href="#">Giới thiệu</a></li>
                        <li><a href="#">Chính đổi trả</a></li>
                        <li><a href="#">Chính sách bảo mật</a></li>
                        <li><a href="#">Chính Sách vận chuyển</a></li>
                        <li><a href="#">Điều khoản dịch vụ</a></li>
                        <li><a href="#">Hướng dẫn mua hàng</a></li>
                        <li><a href="#">Hướng dẫn thanh toán</a></li>
                    </div>
                </div>
                <div class="col-lg-4 col-4 mb-4">
                    <div class="sub-mid">
                        <h4>Thông tin liên hệ</h4>
                        <hr>
                        <li><a href="#">Website: www.embeoi.com.vn</a></li>
                        <li><a href="#">Email:</a></li>
                        <li><a href="#">Hotline: 123456789</a></li>
                    </div>
                </div>
                <div class="col-lg-4 col-4 mb-4">
                    <div class="sub-mid">
                        <h4>Fanpage</h4>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
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

        // Add to cart
        document.querySelectorAll(".add-to-cart").forEach(button => {
            button.addEventListener("click", function(event) {
                event.preventDefault();
                const productId = this.getAttribute("data-product-id");
                fetch("../Backend_giohang/giohangnho.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `product_id=${productId}`
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

        // Search functionality
        document.getElementById("searchButton").addEventListener("click", function() {
            const keyword = document.getElementById("searchInput").value.trim();
            const resultsContainer = document.getElementById("searchResults");
            if (keyword === "") {
                alert("Vui lòng nhập từ khóa tìm kiếm!");
                resultsContainer.classList.remove("active");
                return;
            }
            fetch(`/QL_web_new_born/timkiem.php?keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = "";
                    if (data.length === 0) {
                        resultsContainer.innerHTML = "<p>Không tìm thấy sản phẩm phù hợp.</p>";
                        resultsContainer.classList.add("active");
                        return;
                    }
                    const resultList = document.createElement("ul");
                    resultList.style.listStyle = "none";
                    data.forEach(item => {
                        const listItem = document.createElement("li");
                        listItem.innerHTML = `
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <img src="${item.anh_san_pham}" alt="${item.ten_san_pham}" style="width: 50px; height: 50px; margin-right: 10px; border: 1px solid #ccc; border-radius: 5px;">
                                <div>
                                    <a href="/QL_web_new_born/Frontend_web/ChiTietSanPham.php?id=${item.id}"><strong>${item.ten_san_pham}</strong></a>
                                    <p>${item.gia.toLocaleString()} VNĐ</p>
                                </div>
                            </div>
                        `;
                        resultList.appendChild(listItem);
                    });
                    resultsContainer.appendChild(resultList);
                    resultsContainer.classList.add("active");
                })
                .catch(error => {
                    console.error("Lỗi tìm kiếm:", error);
                    alert("Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại!");
                    resultsContainer.classList.remove("active");
                });
        });

        document.addEventListener("click", function(event) {
            const resultsContainer = document.getElementById("searchResults");
            const searchInput = document.getElementById("searchInput");
            if (!resultsContainer.contains(event.target) && !searchInput.contains(event.target)) {
                resultsContainer.classList.remove("active");
            }
        });
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