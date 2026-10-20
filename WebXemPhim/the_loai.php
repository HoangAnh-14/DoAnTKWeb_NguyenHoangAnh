
<?php
session_start();
include "config.php";

$sql = "SELECT * FROM TheLoai ORDER BY TenTheLoai ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thể loại phim</title>
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
            <input
                type="text"
                name="TuKhoa"
                placeholder="Tìm kiếm phim..."
            >
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

    <h2>THỂ LOẠI PHIM</h2>

    <div class="genre-list">

        <?php if ($result && $result->num_rows > 0) { ?>

            <?php while ($row = $result->fetch_assoc()) { ?>

                <a href="phim_theo_the_loai.php?MaTheLoai=<?php
                    echo urlencode((string)$row["MaTheLoai"]);
                ?>">
                    <?php echo htmlspecialchars($row["TenTheLoai"] ?? ""); ?>
                </a>

            <?php } ?>

        <?php } else { ?>

            <p>Hiện chưa có thể loại phim nào.</p>

        <?php } ?>

    </div>

</section>

</body>
</html>
