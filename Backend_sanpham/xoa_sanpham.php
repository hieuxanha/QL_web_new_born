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

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']); // Sanitize input

    // Lấy đường dẫn ảnh sản phẩm trước khi xóa
    $sql = "SELECT anh_san_pham FROM sanpham WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagePath = $row['anh_san_pham'];

        // Xóa sản phẩm khỏi database
        $sql = "DELETE FROM sanpham WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);

        if ($stmt->execute()) {
            // Xử lý xóa file ảnh
            if (!empty($imagePath)) {
                // Chuẩn hóa đường dẫn tuyệt đối đến thư mục _sanpham/uploads/
                $baseDir = realpath(__DIR__ . '/../Frontend_web/uploads/') . '/';
                // Lấy tên file từ đường dẫn, đề phòng anh_san_pham chứa "uploads/"
                $fileName = basename($imagePath);
                $fullImagePath = $baseDir . $fileName;

                // Kiểm tra file tồn tại
                if (file_exists($fullImagePath)) {
                    // Kiểm tra quyền ghi
                    if (is_writable($fullImagePath)) {
                        @unlink($fullImagePath); // Xóa file, bỏ qua lỗi
                    }
                }
            }

            // Thông báo thành công bất kể file ảnh
            echo "<script>
                    alert('Xóa sản phẩm thành công');
                    window.location.href = 'http://localhost/QL_web_new_born/Frontend_web/admin2.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Lỗi khi xóa sản phẩm: " . addslashes($conn->error) . "');
                    window.location.href = 'http://localhost/QL_web_new_born/Frontend_web/admin2.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Sản phẩm không tồn tại');
                window.location.href = 'http://localhost/QL_web_new_born/Frontend_web/admin2.php';
              </script>";
    }

    $stmt->close();
} else {
    echo "<script>
            alert('ID sản phẩm không được cung cấp');
            window.location.href = 'http://localhost/QL_web_new_born/Frontend_web/admin2.php';
          </script>";
}

$conn->close();
