
<?php
session_start();
include "config.php";

$MaTheLoai = isset($_GET["MaTheLoai"]) ? (int)$_GET["MaTheLoai"] : 0;

// Lấy tên thể loại
$sqlTheLoai = "SELECT * FROM TheLoai WHERE MaTheLoai = ?";
$stmtTheLoai = $conn->prepare($sqlTheLoai);
$stmtTheLoai->bind_param("i", $MaTheLoai);
$stmtTheLoai->execute();
$resultTheLoai = $stmtTheLoai->get_result();
$TheLoai = $resultTheLoai->fetch_assoc();

if (!$TheLoai) {
    die("Không tìm thấy thể loại!");
}

$TenTheLoai = $TheLoai["TenTheLoai"];

// Tìm phim có chứa thể loại đang chọn
$sql = "SELECT * FROM Phim
        WHERE FIND_IN_SET(?, REPLACE(TheLoai, ', ', ',')) > 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $TenTheLoai);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($TenTheLoai); ?></title>
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
            Xin chào, <?php echo htmlspecialchars($_SESSION["HoTen"] ?? ""); ?>
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

    <h2>PHIM <?php echo htmlspecialchars(mb_strtoupper($TenTheLoai, "UTF-8")); ?></h2>

    <div class="movie-list">

        <?php if ($result->num_rows > 0) { ?>

            <?php while ($row = $result->fetch_assoc()) { ?>

                <?php
                $AnhPhim = $row["AnhPhim"] ?? "";

                if (preg_match('/^https?:\/\//i', $AnhPhim)) {
                    $DuongDanAnh = $AnhPhim;
                } elseif (strpos($AnhPhim, "images/") === 0) {
                    $DuongDanAnh = $AnhPhim;
                } elseif (strpos($AnhPhim, "images\\") === 0) {
                    $DuongDanAnh = str_replace("\\", "/", $AnhPhim);
                } else {
                    $DuongDanAnh = "images/" . $AnhPhim;
                }
                ?>

                <div class="movie-card">

                    <div class="movie-image">
                        <img src="<?php echo htmlspecialchars($DuongDanAnh); ?>"
                             alt="<?php echo htmlspecialchars($row["TenPhim"]); ?>">
                    </div>

                    <h3><?php echo htmlspecialchars($row["TenPhim"]); ?></h3>

                    <p><?php echo htmlspecialchars($row["TheLoai"]); ?></p>

                    <a href="chi_tiet_phim.php?MaPhim=<?php echo (int)$row["MaPhim"]; ?>"
                       class="detail-button">
                        Xem chi tiết
                    </a>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p>Chưa có phim thuộc thể loại này.</p>

        <?php } ?>

    </div>

</section>

</body>
</html>