```php
<?php
session_start();
include "config.php";

$TuKhoa = trim($_GET["TuKhoa"] ?? "");
$TuKhoaTim = "%" . $TuKhoa . "%";

$sql = "SELECT * FROM Phim
        WHERE TenPhim LIKE ?
        OR TheLoai LIKE ?
        ORDER BY MaPhim DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $TuKhoaTim, $TuKhoaTim);
$stmt->execute();
$result = $stmt->get_result();

function HienThiAnhTimKiem($AnhPhim)
{
    $AnhPhim = trim((string)$AnhPhim);

    if ($AnhPhim == "") {
        return "images/no-image.png";
    }

    // Nếu là đường dẫn ảnh trên mạng
    if (preg_match('/^https?:\/\//i', $AnhPhim)) {
        return $AnhPhim;
    }

    // Chuẩn hóa đường dẫn ảnh
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
    <title>Tìm kiếm phim</title>
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
                value="<?php echo htmlspecialchars($TuKhoa, ENT_QUOTES, 'UTF-8'); ?>"
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

    <h2>
        <?php if ($TuKhoa != "") { ?>
            KẾT QUẢ TÌM KIẾM:
            "<?php echo htmlspecialchars($TuKhoa, ENT_QUOTES, 'UTF-8'); ?>"
        <?php } else { ?>
            TÌM KIẾM PHIM
        <?php } ?>
    </h2>

    <div class="movie-list">

        <?php if ($result && $result->num_rows > 0) { ?>

            <?php while ($row = $result->fetch_assoc()) { ?>

                <div class="movie-card">

                    <div class="movie-image">
                        <img
                            src="<?php echo htmlspecialchars(HienThiAnhTimKiem($row["AnhPhim"] ?? ""), ENT_QUOTES, 'UTF-8'); ?>"
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
                <?php if ($TuKhoa == "") { ?>

                    Bạn hãy nhập tên phim hoặc thể loại vào ô tìm kiếm.

                <?php } else { ?>

                    Không tìm thấy phim phù hợp với từ khóa
                    "<?php echo htmlspecialchars($TuKhoa, ENT_QUOTES, 'UTF-8'); ?>".

                <?php } ?>
            </p>

        <?php } ?>

    </div>

</section>

</body>
</html>
```