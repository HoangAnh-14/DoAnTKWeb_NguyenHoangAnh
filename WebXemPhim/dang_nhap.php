<?php
session_start();
include "config.php";

$ThongBao = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $TenDangNhap = $_POST["TenDangNhap"];
    $MatKhau = $_POST["MatKhau"];

    $sql = "SELECT * FROM TaiKhoan
            WHERE TenDangNhap = '$TenDangNhap'
            AND MatKhau = '$MatKhau'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        $TaiKhoan = $result->fetch_assoc();

        $_SESSION["MaTaiKhoan"] = $TaiKhoan["MaTaiKhoan"];
        $_SESSION["TenDangNhap"] = $TaiKhoan["TenDangNhap"];
        $_SESSION["HoTen"] = $TaiKhoan["HoTen"];
        $_SESSION["VaiTro"] = $TaiKhoan["VaiTro"];

        header("Location: index.php");
        exit();

    } else {

        $ThongBao = "Tên đăng nhập hoặc mật khẩu không đúng!";

    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Đăng nhập</title>

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

<section class="login-page">

    <div class="login-box">

        <h1>ĐĂNG NHẬP</h1>

        <?php if ($ThongBao != "") { ?>
            <p class="login-message">
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

            <button type="submit">
                Đăng nhập
            </button>

        </form>

        <p class="login-register">
            Chưa có tài khoản?
            <a href="dang_ky.php">Đăng ký</a>
        </p>

    </div>

</section>

</body>
</html>