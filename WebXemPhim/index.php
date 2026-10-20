<?php
session_start();
include "config.php";

// Lay phim cho slideshow
$sqlSlide = "SELECT * FROM Phim ORDER BY MaPhim DESC LIMIT 5";
$resultSlide = $conn->query($sqlSlide);
$DanhSachSlide = [];
if ($resultSlide) {
    while ($Phim = $resultSlide->fetch_assoc()) {
        $DanhSachSlide[] = $Phim;
    }
}

// Phim noi bat: uu tien nam phat hanh moi nhat
$sqlNoiBat = "SELECT * FROM Phim ORDER BY NamPhatHanh DESC, MaPhim DESC LIMIT 8";
$resultNoiBat = $conn->query($sqlNoiBat);

// Phim moi cap nhat: uu tien phim duoc them gan day
$sqlMoi = "SELECT * FROM Phim ORDER BY MaPhim DESC LIMIT 8";
$resultMoi = $conn->query($sqlMoi);

// Danh sach the loai
$sqlTheLoai = "SELECT * FROM TheLoai ORDER BY TenTheLoai ASC LIMIT 10";
$resultTheLoai = $conn->query($sqlTheLoai);

// Xu ly duong dan anh: chap nhan ten file, duong dan images/ hoac URL
function LayDuongDanAnh($AnhPhim) {
    $AnhPhim = trim((string)$AnhPhim);

    if ($AnhPhim === "") {
        return "images/no-image.jpg";
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

function InTheLoai($TheLoai) {
    return htmlspecialchars((string)$TheLoai, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WEB XEM PHIM - Trang chủ</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Phan bo sung rieng cho trang chu */
        * { box-sizing: border-box; }
        body { margin: 0; }
        a { text-decoration: none; }

        .home-slideshow {
            position: relative;
            width: min(1200px, calc(100% - 40px));
            height: 430px;
            margin: 28px auto 38px;
            overflow: hidden;
            border-radius: 16px;
            background: #171717;
            box-shadow: 0 8px 24px rgba(0,0,0,.22);
        }
        .slide-item {
            display: none;
            position: absolute;
            inset: 0;
            color: white;
        }
        .slide-item.active { display: block; animation: slideFade .5s ease; }
        .slide-item > a { display: block; width: 100%; height: 100%; color: inherit; }
        .slide-item img {
            width: 100%; height: 100%; object-fit: cover; display: block;
            opacity: .72;
        }
        .slide-overlay {
            position: absolute; inset: 0;
            display: flex; flex-direction: column; justify-content: flex-end;
            padding: 38px 70px;
            background: linear-gradient(90deg, rgba(0,0,0,.88), rgba(0,0,0,.28) 75%),
                        linear-gradient(0deg, rgba(0,0,0,.7), transparent 65%);
        }
        .slide-label {
            display: inline-block; width: fit-content; margin-bottom: 12px;
            padding: 7px 12px; border-radius: 5px;
            background: #f5c518; color: #171717; font-weight: bold; font-size: 13px;
        }
        .slide-overlay h1 { margin: 0 0 12px; font-size: clamp(26px, 4vw, 44px); color: #fff; }
        .slide-overlay p { max-width: 650px; margin: 0 0 20px; color: #eee; line-height: 1.6; }
        .slide-watch {
            display: inline-block; width: fit-content; padding: 11px 19px;
            background: #f5c518; color: #171717; border-radius: 6px; font-weight: bold;
        }
        .slide-arrow {
            position: absolute; z-index: 2; top: 50%; transform: translateY(-50%);
            width: 42px; height: 48px; border: 0; border-radius: 7px;
            background: rgba(0,0,0,.62); color: white; font-size: 28px; cursor: pointer;
        }
        .slide-arrow:hover { background: #f5c518; color: #111; }
        .slide-prev { left: 14px; } .slide-next { right: 14px; }
        .slide-dots {
            position: absolute; z-index: 3; bottom: 17px; right: 28px;
            display: flex; gap: 8px;
        }
        .slide-dot { width: 10px; height: 10px; padding: 0; border: 0; border-radius: 50%; background: #aaa; cursor: pointer; }
        .slide-dot.active { width: 26px; border-radius: 8px; background: #f5c518; }

        .home-section {
            width: min(1200px, calc(100% - 40px));
            margin: 0 auto 42px;
        }
        .home-section-heading {
            display: flex; justify-content: space-between; align-items: center;
            gap: 15px; margin-bottom: 18px; border-bottom: 2px solid #f5c518; padding-bottom: 10px;
        }
        .home-section-heading h2 { margin: 0; font-size: 25px; }
        .home-see-all { color: #d7a900; font-weight: bold; white-space: nowrap; }
        .home-movie-list {
            display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 22px;
        }
        .home-movie-card {
            min-width: 0; overflow: hidden; border-radius: 10px;
            background: #202020; color: #fff; box-shadow: 0 4px 14px rgba(0,0,0,.15);
            transition: transform .2s, box-shadow .2s;
        }
        .home-movie-card:hover { transform: translateY(-5px); box-shadow: 0 9px 22px rgba(0,0,0,.25); }
        .home-movie-image { display: block; height: 285px; overflow: hidden; background: #333; }
        .home-movie-image img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .3s; }
        .home-movie-card:hover .home-movie-image img { transform: scale(1.04); }
        .home-movie-info { padding: 14px; }
        .home-movie-info h3 { margin: 0 0 9px; font-size: 17px; line-height: 1.4; }
        .home-movie-info h3 a { color: #fff; }
        .home-movie-info h3 a:hover { color: #f5c518; }
        .home-movie-info p { min-height: 38px; margin: 0 0 13px; color: #ccc; font-size: 13px; line-height: 1.5; }
        .home-detail-button { display: block; padding: 9px 12px; border-radius: 5px; text-align: center; background: #f5c518; color: #171717; font-weight: bold; }
        .home-detail-button:hover { background: #ffd943; }
        .home-empty { padding: 24px; border-radius: 8px; background: #222; color: #ddd; }

        .genre-list { display: flex; flex-wrap: wrap; gap: 12px; }
        .genre-chip { display: inline-block; padding: 11px 17px; border: 1px solid #f5c518; border-radius: 22px; color: #f5c518; transition: .2s; }
        .genre-chip:hover { background: #f5c518; color: #171717; }

        .home-footer { margin-top: 48px; padding: 26px 20px; text-align: center; background: #151515; color: #bbb; }
        .home-footer strong { color: #f5c518; }

        @keyframes slideFade { from { opacity: .55; } to { opacity: 1; } }
        @media (max-width: 900px) {
            .home-movie-list { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .home-slideshow { height: 360px; }
            .slide-overlay { padding: 28px 55px; }
        }
        @media (max-width: 650px) {
            .home-movie-list { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px; }
            .home-movie-image { height: 230px; }
            .home-slideshow { width: calc(100% - 24px); height: 330px; margin-top: 16px; }
            .slide-overlay { padding: 22px 45px; }
            .slide-overlay p { font-size: 13px; }
            .home-section { width: calc(100% - 24px); }
            .home-section-heading h2 { font-size: 20px; }
            .slide-arrow { width: 34px; height: 42px; font-size: 23px; }
        }
        @media (max-width: 380px) {
            .home-movie-list { grid-template-columns: 1fr; }
            .home-movie-image { height: 300px; }
        }
    </style>
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
            <input type="text" name="TuKhoa" placeholder="Tìm kiếm phim...">
            <button type="submit">Tìm</button>
        </form>
    </div>

    <?php if (isset($_SESSION["TenDangNhap"])) { ?>
        <div class="user-login">
            Xin chào, <?php echo htmlspecialchars($_SESSION["HoTen"] ?? "", ENT_QUOTES, "UTF-8"); ?>
            | <a href="yeu_thich.php">Yêu thích</a>
            | <a href="dang_xuat.php">Đăng xuất</a>
        </div>
    <?php } else { ?>
        <div class="user-login">
            <a href="dang_nhap.php">Đăng nhập</a> |
            <a href="dang_ky.php">Đăng ký</a>
        </div>
    <?php } ?>
</header>

<?php if (count($DanhSachSlide) > 0) { ?>
<section class="home-slideshow" aria-label="Phim nổi bật">
    <?php foreach ($DanhSachSlide as $ViTri => $Phim) { ?>
        <div class="slide-item <?php echo $ViTri === 0 ? 'active' : ''; ?>">
            <a href="chi_tiet_phim.php?MaPhim=<?php echo (int)$Phim['MaPhim']; ?>" aria-label="Xem chi tiết <?php echo htmlspecialchars($Phim['TenPhim'], ENT_QUOTES, 'UTF-8'); ?>">
                <img src="<?php echo htmlspecialchars(LayDuongDanAnh($Phim['AnhPhim'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo htmlspecialchars($Phim['TenPhim'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="slide-overlay">
                    <span class="slide-label">GỢI Ý HÔM NAY</span>
                    <h1><?php echo htmlspecialchars($Phim['TenPhim'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p>
                        <?php echo htmlspecialchars($Phim['TheLoai'] ?? 'Đang cập nhật thể loại', ENT_QUOTES, 'UTF-8'); ?>
                        <?php if (!empty($Phim['NamPhatHanh'])) { ?> · <?php echo (int)$Phim['NamPhatHanh']; ?><?php } ?>
                    </p>
                    <span class="slide-watch">Xem chi tiết phim &#8594;</span>
                </div>
            </a>
        </div>
    <?php } ?>

    <?php if (count($DanhSachSlide) > 1) { ?>
        <button class="slide-arrow slide-prev" type="button" aria-label="Slide trước" onclick="ChuyenSlide(-1)">&#10094;</button>
        <button class="slide-arrow slide-next" type="button" aria-label="Slide tiếp theo" onclick="ChuyenSlide(1)">&#10095;</button>
        <div class="slide-dots">
            <?php foreach ($DanhSachSlide as $ViTri => $Phim) { ?>
                <button type="button" class="slide-dot <?php echo $ViTri === 0 ? 'active' : ''; ?>"
                        aria-label="Chuyển đến slide <?php echo $ViTri + 1; ?>"
                        onclick="DenSlide(<?php echo $ViTri; ?>)"></button>
            <?php } ?>
        </div>
    <?php } ?>
</section>
<?php } ?>

<section class="home-section">
    <div class="home-section-heading">
        <h2>PHIM NỔI BẬT</h2>
        <a class="home-see-all" href="phim_moi.php">Xem thêm &#8594;</a>
    </div>

    <?php if ($resultNoiBat && $resultNoiBat->num_rows > 0) { ?>
        <div class="home-movie-list">
            <?php while ($Phim = $resultNoiBat->fetch_assoc()) { ?>
                <article class="home-movie-card">
                    <a class="home-movie-image" href="chi_tiet_phim.php?MaPhim=<?php echo (int)$Phim['MaPhim']; ?>">
                        <img src="<?php echo htmlspecialchars(LayDuongDanAnh($Phim['AnhPhim'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($Phim['TenPhim'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                    </a>
                    <div class="home-movie-info">
                        <h3><a href="chi_tiet_phim.php?MaPhim=<?php echo (int)$Phim['MaPhim']; ?>"><?php echo htmlspecialchars($Phim['TenPhim'], ENT_QUOTES, 'UTF-8'); ?></a></h3>
                        <p><?php echo htmlspecialchars($Phim['TheLoai'] ?? '', ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($Phim['NamPhatHanh'])) { ?> · <?php echo (int)$Phim['NamPhatHanh']; ?><?php } ?></p>
                        <a class="home-detail-button" href="chi_tiet_phim.php?MaPhim=<?php echo (int)$Phim['MaPhim']; ?>">Xem chi tiết</a>
                    </div>
                </article>
            <?php } ?>
        </div>
    <?php } else { ?>
        <p class="home-empty">Chưa có phim trong cơ sở dữ liệu. Hãy thêm phim trong trang quản trị.</p>
    <?php } ?>
</section>

<section class="home-section">
    <div class="home-section-heading">
        <h2>PHIM MỚI CẬP NHẬT</h2>
        <a class="home-see-all" href="phim_moi.php">Xem tất cả &#8594;</a>
    </div>

    <?php if ($resultMoi && $resultMoi->num_rows > 0) { ?>
        <div class="home-movie-list">
            <?php while ($Phim = $resultMoi->fetch_assoc()) { ?>
                <article class="home-movie-card">
                    <a class="home-movie-image" href="chi_tiet_phim.php?MaPhim=<?php echo (int)$Phim['MaPhim']; ?>">
                        <img src="<?php echo htmlspecialchars(LayDuongDanAnh($Phim['AnhPhim'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($Phim['TenPhim'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                    </a>
                    <div class="home-movie-info">
                        <h3><a href="chi_tiet_phim.php?MaPhim=<?php echo (int)$Phim['MaPhim']; ?>"><?php echo htmlspecialchars($Phim['TenPhim'], ENT_QUOTES, 'UTF-8'); ?></a></h3>
                        <p><?php echo htmlspecialchars($Phim['TheLoai'] ?? '', ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($Phim['NamPhatHanh'])) { ?> · <?php echo (int)$Phim['NamPhatHanh']; ?><?php } ?></p>
                        <a class="home-detail-button" href="chi_tiet_phim.php?MaPhim=<?php echo (int)$Phim['MaPhim']; ?>">Xem chi tiết</a>
                    </div>
                </article>
            <?php } ?>
        </div>
    <?php } else { ?>
        <p class="home-empty">Chưa có phim mới cập nhật.</p>
    <?php } ?>
</section>

<section class="home-section">
    <div class="home-section-heading">
        <h2>KHÁM PHÁ THỂ LOẠI</h2>
        <a class="home-see-all" href="the_loai.php">Tất cả thể loại &#8594;</a>
    </div>

    <?php if ($resultTheLoai && $resultTheLoai->num_rows > 0) { ?>
        <div class="genre-list">
            <?php while ($TheLoai = $resultTheLoai->fetch_assoc()) { ?>
                <a class="genre-chip" href="phim_theo_the_loai.php?MaTheLoai=<?php echo urlencode($TheLoai['MaTheLoai']); ?>">
                    <?php echo htmlspecialchars($TheLoai['TenTheLoai'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php } ?>
        </div>
    <?php } else { ?>
        <p class="home-empty">Chưa có thể loại phim. Hãy thêm thể loại trong cơ sở dữ liệu.</p>
    <?php } ?>
</section>

<footer class="home-footer">
    <strong>WEB XEM PHIM</strong> - Khám phá thế giới điện ảnh của bạn.
    <div>© <?php echo date('Y'); ?> Web Xem Phim</div>
</footer>

<script>
let ViTriSlide = 0;
let DanhSachSlide = document.querySelectorAll('.slide-item');
let DanhSachCham = document.querySelectorAll('.slide-dot');
let BoHenGioSlide;

function HienSlide(ViTriMoi) {
    if (DanhSachSlide.length === 0) return;

    if (ViTriMoi >= DanhSachSlide.length) ViTriSlide = 0;
    else if (ViTriMoi < 0) ViTriSlide = DanhSachSlide.length - 1;
    else ViTriSlide = ViTriMoi;

    DanhSachSlide.forEach(function(Slide, ViTri) {
        Slide.classList.toggle('active', ViTri === ViTriSlide);
    });

    DanhSachCham.forEach(function(Cham, ViTri) {
        Cham.classList.toggle('active', ViTri === ViTriSlide);
    });
}

function ChuyenSlide(Huong) {
    HienSlide(ViTriSlide + Huong);
    DatLaiHenGio();
}

function DenSlide(ViTri) {
    HienSlide(ViTri);
    DatLaiHenGio();
}

function DatLaiHenGio() {
    clearInterval(BoHenGioSlide);
    if (DanhSachSlide.length > 1) {
        BoHenGioSlide = setInterval(function() {
            HienSlide(ViTriSlide + 1);
        }, 5000);
    }
}

HienSlide(0);
DatLaiHenGio();
</script>

</body>
</html>
