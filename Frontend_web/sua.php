<?php
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

// Lấy thông tin sản phẩm
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

if ($product_id > 0) {
    $sql = "SELECT * FROM sanpham WHERE id = $product_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Không tìm thấy sản phẩm.");
    }
} else {
    die("ID sản phẩm không hợp lệ.");
}

// Xử lý cập nhật sản phẩm
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_san_pham = mysqli_real_escape_string($conn, $_POST['ten_san_pham']);
    $gia = floatval($_POST['gia']);
    $loai_san_pham = mysqli_real_escape_string($conn, $_POST['loai_san_pham']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $so_luong = intval($_POST['so_luong']);
    $san_pham_noi_bat = isset($_POST['san_pham_noi_bat']) ? 1 : 0;

    $anh_san_pham = $product['anh_san_pham']; // Giữ ảnh cũ nếu không cập nhật
    if (isset($_FILES["anh_san_pham"]) && $_FILES["anh_san_pham"]["size"] > 0) {
        $target_dir = "Uploads/";
        $target_file = $target_dir . basename($_FILES["anh_san_pham"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["anh_san_pham"]["tmp_name"]);
        if ($check === false) {
            die("File không phải là ảnh.");
        }

        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif", "webp"])) {
            die("Chỉ chấp nhận các định dạng JPG, JPEG, PNG, GIF, WEBP.");
        }

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES["anh_san_pham"]["tmp_name"], $target_file)) {
            $anh_san_pham = $target_file; // Cập nhật đường dẫn ảnh mới
        } else {
            die("Lỗi khi tải ảnh lên.");
        }
    }

    $sql = "UPDATE sanpham SET 
                ten_san_pham = '$ten_san_pham', 
                gia = $gia, 
                loai_san_pham = '$loai_san_pham', 
                mo_ta = '$mo_ta', 
                so_luong = $so_luong, 
                anh_san_pham = '$anh_san_pham', 
                san_pham_noi_bat = $san_pham_noi_bat 
            WHERE id = $product_id";

    if ($conn->query($sql) === TRUE) {
        echo "Cập nhật sản phẩm thành công!";
        header("Location: admin2.php"); // Chuyển hướng về trang quản lý
        exit;
    } else {
        echo "Lỗi: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Sản Phẩm</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            background-color: #f9f9f9;
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
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus {
            border-color: #a39074;
            outline: none;
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

        .form-group button:hover {
            background-color: #8d7a5e;
        }

        .form-group img {
            max-width: 100px;
            margin-bottom: 10px;
        }

        .file-status {
            margin-left: 10px;
            color: #6d6d6d;
            font-size: 14px;
        }

        .custom-file-upload {
            position: relative;
            display: inline-block;
            margin-left: 10px;
        }

        .custom-file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .custom-file-upload label {
            background-color: #a39074;
            color: white;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .custom-file-upload label:hover {
            background-color: #8d7a5e;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Sửa Sản Phẩm</h2>
        <form action="" method="POST" enctype="multipart/form-data" novalidate>
            <div class="form-group">
                <label for="product_name">Tên Sản Phẩm</label>
                <input type="text" id="product_name" name="ten_san_pham" value="<?= htmlspecialchars($product['ten_san_pham']) ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Giá (VNĐ)</label>
                <input type="text" id="price" name="gia" value="<?= $product['gia'] ?>" required>
            </div>
            <div class="form-group">
                <label for="loai_san_pham">Loại sản phẩm</label>
                <input type="text" class="form-control" id="loai_san_pham" name="loai_san_pham"
                    value="<?php echo htmlspecialchars($product['loai_san_pham']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Mô Tả</label>
                <textarea id="description" name="mo_ta" rows="4" required><?= htmlspecialchars($product['mo_ta']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="quantity">Số Lượng</label>
                <input type="text" id="quantity" name="so_luong" value="<?= $product['so_luong'] ?>" required>
            </div>
            <div class="form-group">
                <label for="featured">Sản Phẩm Nổi Bật</label>
                <input type="checkbox" id="featured" name="san_pham_noi_bat" value="1" <?= $product['san_pham_noi_bat'] ? "checked" : "" ?>>
            </div>
            <div class="form-group">
                <label for="product_image">Ảnh Sản Phẩm</label>
                <div style="display: flex; align-items: center;">
                    <img id="imagePreview" src="<?= $product['anh_san_pham'] ?>" alt="Ảnh sản phẩm" width="150">
                    <div class="custom-file-upload">
                        <input type="file" id="product_image" name="anh_san_pham" accept="image/*">
                        <label for="product_image">Chọn tệp</label>
                    </div>
                    <span class="file-status" id="fileStatus">Đang sử dụng ảnh hiện tại</span>
                </div>
            </div>
            <div class="form-group">
                <button type="submit">Cập Nhật</button>
                <button type="button"><a href="./admin2.php">HỦY</a></button>
            </div>
        </form>
    </div>
    <script>
        // Kiểm tra dữ liệu trước khi gửi form sửa sản phẩm
        document.querySelector('form').addEventListener('submit', function(event) {
            const tenSanPham = document.getElementById('product_name').value.trim();
            const gia = document.getElementById('price').value.trim();
            const loaiSanPham = document.getElementById('loai_san_pham').value.trim();
            const moTa = document.getElementById('description').value;
            const soLuong = document.getElementById('quantity').value.trim();

            // Kiểm tra nếu tất cả các trường (trừ ảnh và nổi bật) đều trống
            if (!tenSanPham && !gia && !loaiSanPham && !moTa && !soLuong) {
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
                alert("Giá sản phẩm phải là số và không được chứa dấu chấm hoặc dấu phẩy.");
                return;
            }

            if (!/^-?\d+$/.test(gia)) {
                event.preventDefault();
                alert("Giá sản phẩm phải là số.");
                return;
            }

            const giaValue = parseInt(gia);
            if (giaValue === 0) {
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

            if (!loaiSanPham) {
                event.preventDefault();
                alert("Vui lòng chọn danh mục sản phẩm.");
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
                if (soLuongValue === 0) {
                    event.preventDefault();
                    alert("Số lượng sản phẩm phải lớn hơn 0.");
                    return;
                } else {
                    event.preventDefault();
                    alert("Số lượng sản phẩm không hợp lệ, phải là số dương.");
                    return;
                }
            }
        });

        // Xử lý trạng thái ảnh và cập nhật preview
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('product_image');
            const fileStatus = document.getElementById('fileStatus');
            const imagePreview = document.getElementById('imagePreview');
            const currentImage = '<?= $product['anh_san_pham'] ?>';

            // Nếu có ảnh hiện tại, hiển thị thông báo sử dụng ảnh hiện tại
            if (currentImage) {
                fileStatus.textContent = 'Đang sử dụng ảnh hiện tại';
            }

            // Cập nhật trạng thái và preview khi chọn tệp mới
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    fileStatus.textContent = 'Đã chọn: ' + file.name;

                    // Cập nhật preview ảnh
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    fileStatus.textContent = 'Đang sử dụng ảnh hiện tại';
                    imagePreview.src = currentImage;
                }
            });
        });
    </script>
</body>

</html>