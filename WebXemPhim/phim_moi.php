
<?php
session_start();
include "config.php";

$sql = "SELECT * FROM Phim
        ORDER BY NamPhatHanh DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phim mới</title>
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
            Xin chào,
            <?php echo htmlspecialchars($_SESSION["HoTen"] ?? ""); ?>
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

<section class="movies">

    <h2>PHIM MỚI</h2>

    <div class="movie-list">

        <?php if ($result && $result->num_rows > 0) { ?>

            <?php while ($row = $result->fetch_assoc()) { ?>

                <?php
                // Xác định đường dẫn ảnh phim
                $DuongDanAnh = trim($row["AnhPhim"] ?? "");

                if (
                    strpos($DuongDanAnh, "http://") === 0 ||
                    strpos($DuongDanAnh, "https://") === 0
                ) {
                    $AnhHienThi = $DuongDanAnh;
                } elseif (strpos($DuongDanAnh, "images/") === 0) {
                    $AnhHienThi = $DuongDanAnh;
                } elseif (strpos($DuongDanAnh, "images\\") === 0) {
                    $AnhHienThi = str_replace("\\", "/", $DuongDanAnh);
                } else {
                    $AnhHienThi = "images/" . $DuongDanAnh;
                }
                ?>

                <div class="movie-card">

                    <div class="movie-image">
                        <img
                            src="<?php echo htmlspecialchars($AnhHienThi); ?>"
                            alt="<?php echo htmlspecialchars($row["TenPhim"] ?? ""); ?>"
                            loading="lazy"
                            onerror="this.onerror=null; this.alt='Không thể tải ảnh phim';"
                        >
                    </div>

                    <h3>
                        <?php echo htmlspecialchars($row["TenPhim"] ?? ""); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($row["TheLoai"] ?? ""); ?>
                        - <?php echo htmlspecialchars((string)($row["NamPhatHanh"] ?? "")); ?>
                    </p>

                    <a
                        href="chi_tiet_phim.php?MaPhim=<?php echo urlencode((string)$row["MaPhim"]); ?>"
                        class="detail-button">
                        Xem chi tiết
                    </a>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p>Hiện chưa có phim nào trong danh sách.</p>

        <?php } ?>

    </div>

</section>

</body>
</html>
