function showLoginForm() {
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('registerForm').style.display = 'none';
}

// Hàm hiển thị form đăng ký và ẩn form đăng nhập
function showRegisterForm() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
}

// Kiểm tra mật khẩu và mật khẩu xác nhận trong form đăng ký
document.getElementById('registerForm').addEventListener('submit', function(event) {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm-password').value;

    if (password !== confirmPassword) {
        event.preventDefault(); // Ngăn không cho form gửi đi
        alert('Mật khẩu và mật khẩu xác nhận không khớp!');
    } else {
        alert('Đăng ký thành công!');
    }
});

// Sự kiện khi nhấn vào nút đăng nhập (có thể thêm các kiểm tra khác nếu cần)
document.getElementById('loginForm').addEventListener('submit', function(event) {
    alert('Đăng nhập thành công!');
});