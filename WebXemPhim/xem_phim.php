<?php
session_start();
include "config.php";

$MaPhim = $_GET['MaPhim'];

$sql = "SELECT * FROM Phim WHERE MaPhim = $MaPhim";
$result = $conn->query($sql);

$phim = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Xem phim - <?php echo $phim['TenPhim']; ?></title>

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


<section class="watch-page">

    <h1><?php echo $phim['TenPhim']; ?></h1>

 <div class="video-box">

    <video controls>
        <source src="https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4" type="video/mp4">
        Trình duyệt của bạn không hỗ trợ phát video.
    </video>

</div>

    <div class="watch-info">

        <h2><?php echo $phim['TenPhim']; ?></h2>

        <p>
            <strong>Thể loại:</strong>
            <?php echo $phim['TheLoai']; ?>
        </p>

        <p>
            <strong>Năm phát hành:</strong>
            <?php echo $phim['NamPhatHanh']; ?>
        </p>

    </div>

</section>

</body>
</html>