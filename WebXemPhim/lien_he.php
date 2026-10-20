<?php
session_start();

$ThongBao = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $HoTen = $_POST["HoTen"];
    $Email = $_POST["Email"];
    $NoiDung = $_POST["NoiDung"];

    $ThongBao = "Gửi liên hệ thành công!";
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Liên hệ</title>

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

    <div class="search">
        <form action="tim_kiem.php" method="GET">
            <input type="text" name="TuKhoa"
                   placeholder="Tìm kiếm phim...">
            <button type="submit">Tìm</button>
        </form>
    </div>

    <?php if (isset($_SESSION["TenDangNhap"])) { ?>

        <div class="user-login">
            Xin chào, <?php echo $_SESSION["HoTen"]; ?>
            |
            <a href="yeu_thich.php">Yêu thích</a>
            |
            <a href="dang_xuat.php">Đăng xuất</a>
        </div>

    <?php } else { ?>

        <div class="user-login">
            <a href="dang_nhap.php">Đăng nhập</a>
            |
            <a href="dang_ky.php">Đăng ký</a>
        </div>

    <?php } ?>

</header>


<section class="contact-page">

    <div class="contact-box">

        <h1>LIÊN HỆ</h1>
<?php if ($ThongBao != "") { ?>
    <p class="contact-message">
        <?php echo $ThongBao; ?>
    </p>
<?php } ?>
        <p>
            Nếu bạn có câu hỏi hoặc góp ý về website,
            vui lòng liên hệ với chúng tôi.
        </p>

        <div class="contact-info">

            <p><strong>Website:</strong> Web Xem Phim</p>

            <p><strong>Email:</strong> webxemphim@gmail.com</p>

            <p><strong>Số điện thoại:</strong> 0123 456 789</p>

            <p><strong>Địa chỉ:</strong> Hà Nội, Việt Nam</p>

        </div>

       <form method="POST">
<input type="text"
       name="HoTen"
       placeholder="Họ và tên"
       required>

<input type="email"
       name="Email"
       placeholder="Email"
       required>

<textarea name="NoiDung"
          placeholder="Nội dung cần liên hệ"
          required></textarea>

            <button type="submit">
                Gửi liên hệ
            </button>

        </form>

    </div>

</section>

</body>
</html>