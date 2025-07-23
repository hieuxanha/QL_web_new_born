<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f9f9f9;
        }

        .menu1 {
            display: flex;
            flex-direction: column;
            /* Đặt các mục theo chiều dọc */
            background-color: #009dff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .menu1 a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 20px;
            margin: 5px 0;
            /* Khoảng cách giữa các mục */
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s, transform 0.2s;
        }

        .menu1 a:hover {
            background-color: #009dff;
            transform: scale(1.05);
        }
    </style>
    <title>QUẢN LÝ</title>
</head>





<div class="menu1">

    <a href="./admin2.php">QUẢN LÝ SẢN PHẨM</a>
    <a href="./ql_donhang.php">QUẢN LÝ ĐƠN HÀNG</a>
    <a href="./ql_khchhang.php">QUẢN LÝ KHÁCH HÀNG</a>
    <a href="./Thongke.php">THỐNG KÊ</a>


    <a href="./giaodienql1.php" class="btn btn-primary">Quay lại</a>

</div>
</body>

</html>