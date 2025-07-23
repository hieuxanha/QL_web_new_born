<?php
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

// Khởi tạo biến thông báo
$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra xem tất cả các trường có trống không
    if (
        empty($_POST['ten_san_pham']) &&
        empty($_POST['gia']) &&
        empty($_POST['loai_san_pham']) &&
        empty($_POST['mo_ta']) &&
        empty($_POST['so_luong']) &&
        (!isset($_FILES['anh_san_pham']) || empty($_FILES['anh_san_pham']['name']))
    ) {
        $message = "Vui lòng điền đầy đủ thông tin sản phẩm.";
        $message_type = 'error';
    } else {
        // Kiểm tra từng trường riêng lẻ
        if (empty($_POST['ten_san_pham'])) {
            $message = "Tên sản phẩm không được để trống.";
            $message_type = 'error';
        } else {
            // Kiểm tra độ dài tên sản phẩm
            $ten_san_pham_length = strlen(trim($_POST['ten_san_pham']));
            if ($ten_san_pham_length < 5 || $ten_san_pham_length > 50) {
                $message = "Tên sản phẩm phải từ 5 đến 50 ký tự.";
                $message_type = 'error';
            } else {
                if (empty($_POST['gia'])) {
                    $message = "Vui lòng nhập giá sản phẩm.";
                    $message_type = 'error';
                } else {
                    // Kiểm tra giá có chứa dấu chấm hoặc dấu phẩy
                    if (preg_match('/[.,]/', $_POST['gia'])) {
                        $message = "Giá sản phẩm phải chứa số và không được chứa dấu chấm hoặc dấu phẩy.";
                        $message_type = 'error';
                    } else {
                        // Kiểm tra giá có phải là số hợp lệ
                        if (!is_numeric($_POST['gia']) || !preg_match('/^-?\d+$/', $_POST['gia'])) {
                            $message = "Giá sản phẩm phải là số.";
                            $message_type = 'error';
                        } else {
                            $gia = intval($_POST['gia']);
                            if ($gia == 0) {
                                $message = "Giá sản phẩm không thể bằng 0.";
                                $message_type = 'error';
                            } else {
                                if ($gia < 0) {
                                    $message = "Giá sản phẩm không hợp lệ.";
                                    $message_type = 'error';
                                } else {
                                    if ($gia > 1000000000) {
                                        $message = "Giá sản phẩm quá lớn, vui lòng nhập lại hợp lệ.";
                                        $message_type = 'error';
                                    } else {
                                        // Kiểm tra mô tả
                                        if (empty($_POST['mo_ta'])) {
                                            $message = "Mô tả sản phẩm không được để trống.";
                                            $message_type = 'error';
                                        } else {
                                            // Kiểm tra mô tả có chứa ký tự không phải khoảng trắng
                                            if (trim($_POST['mo_ta']) === '' || !preg_match('/\S/', $_POST['mo_ta'])) {
                                                $message = "Mô tả sản phẩm không hợp lệ.";
                                                $message_type = 'error';
                                            } else {
                                                // Kiểm tra độ dài mô tả
                                                if (strlen($_POST['mo_ta']) > 1000) {
                                                    $message = "Mô tả sản phẩm quá dài, tối đa 1000 ký tự.";
                                                    $message_type = 'error';
                                                } else {
                                                    $mo_ta_length = strlen(trim($_POST['mo_ta']));
                                                    if ($mo_ta_length < 10) {
                                                        $message = "Mô tả sản phẩm quá ngắn, tối thiểu 10 ký tự.";
                                                        $message_type = 'error';
                                                    } else {
                                                        // Kiểm tra số lượng
                                                        if (empty($_POST['so_luong'])) {
                                                            $message = "Số lượng sản phẩm không được để trống.";
                                                            $message_type = 'error';
                                                        } else {
                                                            // Kiểm tra số lượng có chứa dấu chấm (số thập phân)
                                                            if (preg_match('/\./', $_POST['so_luong'])) {
                                                                $message = "Số lượng sản phẩm phải là số nguyên.";
                                                                $message_type = 'error';
                                                            } else {
                                                                // Kiểm tra số lượng có chứa ký tự đặc biệt (không tính dấu trừ)
                                                                if (preg_match('/[!@#$%^&*(),;]/', $_POST['so_luong'])) {
                                                                    $message = "Số lượng sản phẩm phải là số, không được có kí tự đặc biệt.";
                                                                    $message_type = 'error';
                                                                } else {
                                                                    // Kiểm tra số lượng có chứa chữ cái
                                                                    if (preg_match('/[a-zA-Z]/', $_POST['so_luong'])) {
                                                                        $message = "Số lượng sản phẩm phải là số, không được có chữ cái.";
                                                                        $message_type = 'error';
                                                                    } else {
                                                                        // Kiểm tra số lượng có phải là số hợp lệ
                                                                        if (!is_numeric($_POST['so_luong']) || !preg_match('/^-?\d+$/', $_POST['so_luong'])) {
                                                                            $message = "Số lượng sản phẩm phải là số.";
                                                                            $message_type = 'error';
                                                                        } else {
                                                                            $so_luong = intval($_POST['so_luong']);
                                                                            if ($so_luong <= 0) {
                                                                                if ($so_luong == 0) {
                                                                                    $message = "Số lượng sản phẩm phải lớn hơn 0.";
                                                                                    $message_type = 'error';
                                                                                } else {
                                                                                    $message = "Số lượng sản phẩm phải là không hợp lệ, phải là số dương.";
                                                                                    $message_type = 'error';
                                                                                }
                                                                            } else {
                                                                                if (!isset($_FILES['anh_san_pham']) || empty($_FILES['anh_san_pham']['name'])) {
                                                                                    $message = "Ảnh sản phẩm không được để trống.";
                                                                                    $message_type = 'error';
                                                                                } else {
                                                                                    if (empty($_POST['loai_san_pham'])) {
                                                                                        $message = "Danh mục sản phẩm không được để trống.";
                                                                                        $message_type = 'error';
                                                                                    } else {
                                                                                        // Xử lý upload ảnh
                                                                                        $target_dir = "uploads/";
                                                                                        $target_file = $target_dir . basename($_FILES["anh_san_pham"]["name"]);
                                                                                        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                                                                                        $check = getimagesize($_FILES["anh_san_pham"]["tmp_name"]);
                                                                                        if ($check === false) {
                                                                                            $message = "Định dạng ảnh không hợp lệ, vui lòng chọn lại.";
                                                                                            $message_type = 'error';
                                                                                        } else {
                                                                                            if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif", "webp"])) {
                                                                                                $message = "Chỉ chấp nhận các định dạng JPG, JPEG, PNG, GIF, WEBP.";
                                                                                                $message_type = 'error';
                                                                                            } else {
                                                                                                if (file_exists($target_file)) {
                                                                                                    $message = "File ảnh đã tồn tại.";
                                                                                                    $message_type = 'error';
                                                                                                } else {
                                                                                                    if ($_FILES["anh_san_pham"]["size"] > 5000000) {
                                                                                                        $message = "File ảnh quá lớn. Vui lòng chọn file dưới 5MB.";
                                                                                                        $message_type = 'error';
                                                                                                    } else {
                                                                                                        if (!is_dir($target_dir)) {
                                                                                                            mkdir($target_dir, 0777, true);
                                                                                                        }

                                                                                                        if (move_uploaded_file($_FILES["anh_san_pham"]["tmp_name"], $target_file)) {
                                                                                                            $stmt = $conn->prepare("INSERT INTO sanpham (ten_san_pham, gia, loai_san_pham, mo_ta, so_luong, anh_san_pham, san_pham_noi_bat) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                                                                                            $ten_san_pham = $_POST['ten_san_pham'];
                                                                                                            $loai_san_pham = $_POST['loai_san_pham'];
                                                                                                            $mo_ta = $_POST['mo_ta'];
                                                                                                            $san_pham_noi_bat = isset($_POST['san_pham_noi_bat']) ? 1 : 0;
                                                                                                            $stmt->bind_param("sissssi", $ten_san_pham, $gia, $loai_san_pham, $mo_ta, $so_luong, $target_file, $san_pham_noi_bat);

                                                                                                            if ($stmt->execute()) {
                                                                                                                $message = "Thêm sản phẩm thành công!";
                                                                                                                $message_type = 'success';
                                                                                                            } else {
                                                                                                                $message = "Lỗi khi thêm sản phẩm: " . $stmt->error;
                                                                                                                $message_type = 'error';
                                                                                                                unlink($target_file);
                                                                                                            }
                                                                                                            $stmt->close();
                                                                                                        } else {
                                                                                                            $message = "Lỗi khi tải ảnh lên.";
                                                                                                            $message_type = 'error';
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm - Phong Cách Nous</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            /* background-color: #f9f9f9; */
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 50%, #a39074 100%);

            color: #333;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            color: #a39074;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 16px;
            color: #6d6d6d;
            margin-bottom: 10px;
            display: block;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #a39074;
            outline: none;
        }

        .form-group input[type="number"] {
            -webkit-appearance: none;
            -moz-appearance: textfield;
            appearance: textfield;
        }

        .form-group input[type="number"]::-webkit-inner-spin-button,
        .form-group input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .form-group input[type="file"] {
            font-size: 16px;
            margin-top: 10px;
        }

        .form-group img {
            width: 150px;
            margin-top: 10px;
            border-radius: 10px;
        }

        .form-group button {
            background-color: #a39074;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group button a {
            background-color: #a39074;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .form-group button:hover {
            background-color: #8d7a5e;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .message.success {
            color: green;
        }

        .message.error {
            color: red;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Thêm Sản Phẩm</h2>

        <?php if (!empty($message)) { ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <form action="addsanpham.php" method="POST" enctype="multipart/form-data" novalidate>
            <div class="form-group">
                <label for="product_name">Tên Sản Phẩm</label>
                <input type="text" id="product_name" name="ten_san_pham" placeholder="Nhập tên sản phẩm" required>
            </div>
            <div class="form-group">
                <label for="price">Giá (VNĐ)</label>
                <input type="text" id="price" name="gia" placeholder="Nhập giá sản phẩm (VD: 1250000)" required>
            </div>
            <div class="form-group">
                <label for="description">Mô Tả</label>
                <textarea id="description" name="mo_ta" rows="4" placeholder="Nhập mô tả sản phẩm" required></textarea>
            </div>
            <div class="form-group">
                <label for="product_image">Ảnh Sản Phẩm</label>
                <input type="file" id="product_image" name="anh_san_pham" accept="image/*" required>
                <img id="preview" src="" alt="Xem trước ảnh sản phẩm" style="display: none;">
            </div>
            <div class="form-group">
                <label for="quantity">Số Lượng</label>
                <input type="text" id="quantity" name="so_luong" placeholder="Nhập số lượng sản phẩm" required>
            </div>
            <div class="form-group">
                <label for="category">Danh Mục</label>
                <select id="category" name="loai_san_pham" required>
                    <option value="" disabled selected>Chọn danh mục</option>
                    <option value="Bé mặc">Bé mặc</option>
                    <option value="Bé ngủ">Bé ngủ</option>
                    <option value="Bé chơi">Bé chơi</option>
                    <option value="Bé ăn uống">Bé ăn uống</option>
                    <option value="Bé vệ sinh">Bé vệ sinh</option>
                    <option value="Bé ra ngoài">Bé ra ngoài</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>
            <div class="form-group">
                <label for="featured">Sản Phẩm Nổi Bật</label>
                <input type="checkbox" id="featured" name="san_pham_noi_bat" value="1">
            </div>
            <div class="form-group">
                <button type="submit">Thêm Sản Phẩm</button>
                <button type="button"><a href="./admin2.php">HỦY</a></button>
            </div>
        </form>

        <script>
            // Xem trước ảnh
            document.getElementById('product_image').onchange = function(event) {
                const preview = document.getElementById('preview');
                preview.style.display = 'block';
                preview.src = URL.createObjectURL(event.target.files[0]);
            }

            // Kiểm tra dữ liệu trước khi gửi form
            document.querySelector('form').addEventListener('submit', function(event) {
                const tenSanPham = document.getElementById('product_name').value.trim();
                const gia = document.getElementById('price').value.trim();
                const moTa = document.getElementById('description').value;
                const anhSanPham = document.getElementById('product_image').files.length;
                const soLuong = document.getElementById('quantity').value.trim();
                const loaiSanPham = document.getElementById('category').value;

                // Kiểm tra nếu tất cả các trường đều trống
                if (!tenSanPham && !gia && !moTa && !anhSanPham && !soLuong && !loaiSanPham) {
                    event.preventDefault();
                    alert("Vui lòng điền đầy đủ thông tin sản phẩm.");
                    return;
                }

                // Kiểm tra từng trường riêng lẻ
                if (!tenSanPham) {
                    event.preventDefault();
                    alert("Tên sản phẩm không được để trống.");
                    return;
                }

                if (tenSanPham.length < 5 || tenSanPham.length > 50) {
                    event.preventDefault();
                    alert("Tên sản phẩm phải từ 5 đến 50 ký tự.");
                    return;
                }

                if (!gia) {
                    event.preventDefault();
                    alert("Vui lòng nhập giá sản phẩm.");
                    return;
                }

                if (/[.,]/.test(gia)) {
                    event.preventDefault();
                    alert("Giá sản phẩm phải chứa số và không được chứa dấu chấm hoặc dấu phẩy.");
                    return;
                }

                if (!/^-?\d+$/.test(gia)) {
                    event.preventDefault();
                    alert("Giá sản phẩm phải là số.");
                    return;
                }

                const giaValue = parseInt(gia);
                if (giaValue == 0) {
                    event.preventDefault();
                    alert("Giá sản phẩm không thể bằng 0.");
                    return;
                }

                if (giaValue < 0) {
                    event.preventDefault();
                    alert("Giá sản phẩm không hợp lệ.");
                    return;
                }


                if (giaValue > 1000000000) {
                    event.preventDefault();
                    alert("Giá sản phẩm quá lớn, vui lòng nhập lại hợp lệ.");
                    return;
                }

                // Kiểm tra mô tả
                if (!moTa) {
                    event.preventDefault();
                    alert("Mô tả sản phẩm không được để trống.");
                    return;
                }

                if (moTa.trim() === '' || !/\S/.test(moTa)) {
                    event.preventDefault();
                    alert("Mô tả sản phẩm không hợp lệ.");
                    return;
                }

                if (moTa.length > 1000) {
                    event.preventDefault();
                    alert("Mô tả sản phẩm quá dài, tối đa 1000 ký tự.");
                    return;
                }

                if (moTa.trim().length < 10) {
                    event.preventDefault();
                    alert("Mô tả sản phẩm quá ngắn, tối thiểu 10 ký tự.");
                    return;
                }

                // Kiểm tra số lượng
                if (!soLuong) {
                    event.preventDefault();
                    alert("Vui lòng nhập số lượng sản phẩm.");
                    return;
                }

                if (/\./.test(soLuong)) {
                    event.preventDefault();
                    alert("Số lượng sản phẩm phải là số nguyên.");
                    return;
                }

                if (/[!@#$%^&*(),;]/.test(soLuong)) {
                    event.preventDefault();
                    alert("Số lượng sản phẩm phải là số, không được có kí tự đặc biệt.");
                    return;
                }

                if (/[a-zA-Z]/.test(soLuong)) {
                    event.preventDefault();
                    alert("Số lượng sản phẩm phải là số, không được có chữ cái.");
                    return;
                }

                if (!/^-?\d+$/.test(soLuong)) {
                    event.preventDefault();
                    alert("Số lượng sản phẩm phải là số.");
                    return;
                }

                const soLuongValue = parseInt(soLuong);
                if (soLuongValue <= 0) {
                    if (soLuongValue == 0) {
                        event.preventDefault();
                        alert("Số lượng sản phẩm phải lớn hơn 0.");
                        return;
                    } else {
                        event.preventDefault();
                        alert("Số lượng sản phẩm phải là không hợp lệ, phải là số dương.");
                        return;
                    }
                }

                if (!anhSanPham) {
                    event.preventDefault();
                    alert("Ảnh sản phẩm không được để trống.");
                    return;
                }

                if (!loaiSanPham) {
                    event.preventDefault();
                    alert("Vui lòng chọn danh mục sản phẩm ");
                    return;
                }
            });
        </script>
    </div>
</body>

</html>