
<?php
session_start();
include "config.php";

// Chưa đăng nhập thì chuyển đến trang đăng nhập
if (!isset($_SESSION["MaTaiKhoan"])) {
    header("Location: dang_nhap.php");
    exit();
}

$MaTaiKhoan = (int)$_SESSION["MaTaiKhoan"];

// Lấy danh sách phim yêu thích của tài khoản
$sql = "SELECT Phim.*, YeuThich.MaYeuThich
        FROM YeuThich
        INNER JOIN Phim
            ON YeuThich.MaPhim = Phim.MaPhim
        WHERE YeuThich.MaTaiKhoan = ?
        ORDER BY YeuThich.MaYeuThich DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $MaTaiKhoan);
$stmt->execute();
$result = $stmt->get_result();

// Xử lý đường dẫn ảnh
function HienThiAnhYeuThich($AnhPhim)
{
    $AnhPhim = trim((string)$AnhPhim);

    if ($AnhPhim == "") {
        return "images/no-image.png";
    }

    if (preg_match('/^https?:\/\//i', $AnhPhim)) {
        return $AnhPhim;
    }

    $AnhPhim = str_replace("\\", "/", $AnhPhim);

    if (strpos($AnhPhim, "images/") === 0) {
        return $AnhPhim;
    }

    return "images/" . ltrim($AnhPhim, "/");
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phim yêu thích</title>
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
            <?php
            echo htmlspecialchars(
                $_SESSION["HoTen"] ?? $_SESSION["TenDangNhap"],
                ENT_QUOTES,
                'UTF-8'
            );
            ?>
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

    <h2>PHIM YÊU THÍCH</h2>

    <div class="movie-list">

        <?php if ($result && $result->num_rows > 0) { ?>

            <?php while ($row = $result->fetch_assoc()) { ?>

                <div class="movie-card">

                    <div class="movie-image">
                        <img
                            src="<?php echo htmlspecialchars(HienThiAnhYeuThich($row["AnhPhim"] ?? ""), ENT_QUOTES, 'UTF-8'); ?>"
                            alt="<?php echo htmlspecialchars($row["TenPhim"] ?? "Phim", ENT_QUOTES, 'UTF-8'); ?>"
                            onerror="this.onerror=null;this.src='images/no-image.png';"
                        >
                    </div>

                    <h3>
                        <?php
                        echo htmlspecialchars(
                            $row["TenPhim"] ?? "",
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </h3>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $row["TheLoai"] ?? "",
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </p>

                    <a
                        href="chi_tiet_phim.php?MaPhim=<?php echo (int)$row["MaPhim"]; ?>"
                        class="detail-button"
                    >
                        Xem chi tiết
                    </a>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p class="no-movies">
                Bạn chưa có phim yêu thích.
                Hãy vào trang chủ và thêm những bộ phim bạn muốn xem nhé!
            </p>

        <?php } ?>

    </div>

</section>

</body>
</html>
