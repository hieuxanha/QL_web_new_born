<?php
// Bắt đầu phiên làm việc
session_start();

// Đảm bảo kết nối cơ sở dữ liệu đã được yêu cầu từ db.php
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

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];
    $hoTen = $_POST['hoTen'];
    $email = $_POST['email'];
    $soDienThoai = $_POST['soDienThoai'];
    $diaChi = $_POST['diaChi'];


    $tongTien = 0;

    $query = "SELECT g.so_luong, s.gia
              FROM gio_hang g
              JOIN sanpham s ON g.san_pham_id = s.id
              WHERE g.user_id = '$user_id'";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tongTien += $row['so_luong'] * $row['gia'];
        }
    }


    $query = "INSERT INTO thanhtoan (user_id, hoTen, email, soDienThoai, diaChi, ngayThanhToan, tongTien)
              VALUES ('$user_id', '$hoTen', '$email', '$soDienThoai', '$diaChi', NOW(), '$tongTien')";
    if ($conn->query($query) === TRUE) {
        $idHoaDon = $conn->insert_id;

        // Lưu chi tiết hóa đơn
        $query = "SELECT g.san_pham_id, g.so_luong, s.gia
                  FROM gio_hang g
                  JOIN sanpham s ON g.san_pham_id = s.id
                  WHERE g.user_id = '$user_id'";
        $result = $conn->query($query);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $idSanPham = $row['san_pham_id'];
                $soLuong = $row['so_luong'];
                $giaTien = $row['gia'];
                $thanhTien = $soLuong * $giaTien;

                $queryInsertChiTiet = "INSERT INTO chitiet_hoadon (hoa_don_id, san_pham_id, soLuong, giaTien, thanhTien)
                                       VALUES ('$idHoaDon', '$idSanPham', '$soLuong', '$giaTien', '$thanhTien')";
                $conn->query($queryInsertChiTiet);
            }

            // Xóa giỏ hàng
            $queryDeleteGioHang = "DELETE FROM gio_hang WHERE user_id = '$user_id'";
            $conn->query($queryDeleteGioHang);

            $_SESSION['hoa_don_id'] = $idHoaDon;

            // Gửi e
            $mail = new PHPMailer(true);


            try {
                // Cấu hình SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'nguyenconghieu7924@gmail.com';
                $mail->Password   = 'fdgmilbrhtgkxbev';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Cài đặt người gửi và người nhận
                // chủ shoppppppp
                $mail->setFrom('nguyenconghieu7924@gmail.com', 'Web New Born');
                // gủi khách    
                $mail->addAddress($email, $hoTen);

                // Nội dung email
                $mail->isHTML(true);
                $mail->Subject = 'Xac nhan don hang #' . $idHoaDon;
                $mail->Body    = "<h3>Xin chào $hoTen,</h3>
                                  <p>Đơn hàng của bạn đã được đặt thành công.</p>

                                  <p><b>Tổng tiền:</b> " . number_format($tongTien, 0, ',', '.') . " VND</p>
                                  <p><b>Địa chỉ giao hàng:</b> $diaChi</p>
                                  <p>Cảm ơn bạn đã mua hàng tại Web New Born!</p>";
                $mail->AltBody = "Đơn hàng của bạn đã được đặt thành công. Tổng tiền: " . number_format($tongTien, 0, ',', '.') . " VND.";

                $mail->send();
                echo 'Email xác nhận đã được gửi.';
            } catch (Exception $e) {

                echo "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
            }



            header('Location: ../Frontend_web/Thanhtoanthanhcong.php');
        } else {
            echo "Lỗi khi lấy giỏ hàng: " . $conn->error;
        }
    } else {
        echo "Lỗi khi tạo hóa đơn: " . $conn->error;
    }

    $conn->close();
} else {
    header('Location: ../dangnhapky.php');
    exit();
}
