<?php
include "config.php";

$ThongBao = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $TenDangNhap = $_POST["TenDangNhap"];
    $MatKhau = $_POST["MatKhau"];
    $HoTen = $_POST["HoTen"];
    $Email = $_POST["Email"];

    $sql = "INSERT INTO TaiKhoan
            (TenDangNhap, MatKhau, HoTen, Email)
            VALUES
            ('$TenDangNhap', '$MatKhau', '$HoTen', '$Email')";

    if ($conn->query($sql)) {
        $ThongBao = "Đăng ký thành công!";
    } else {
        $ThongBao = "Tên đăng nhập đã tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Đăng ký</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="header">

    <div class="logo">WEB XEM PHIM</div>

    <nav class="menu">
       <a href="index.php">Trang chủ</a>
        <a href="phim_moi.php">Phim mới</a>
        <a href="the_loai.php">Thể loại</a>
        <a href="lien_he.php">Liên hệ</a>
    </nav>

</header>

<section class="register-page">

    <div class="register-box">

        <h1>ĐĂNG KÝ TÀI KHOẢN</h1>

        <?php if ($ThongBao != "") { ?>
            <p class="register-message">
                <?php echo $ThongBao; ?>
            </p>
        <?php } ?>

        <form method="POST">

            <input type="text"
                   name="TenDangNhap"
                   placeholder="Tên đăng nhập"
                   required>

            <input type="password"
                   name="MatKhau"
                   placeholder="Mật khẩu"
                   required>

            <input type="text"
                   name="HoTen"
                   placeholder="Họ tên"
                   required>

            <input type="email"
                   name="Email"
                   placeholder="Email"
                   required>

            <button type="submit">
                Đăng ký
            </button>

        </form>

    </div>

</section>

</body>
</html>