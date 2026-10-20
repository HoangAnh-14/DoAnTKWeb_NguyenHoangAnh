
<?php
session_start();
include "config.php";

$MaPhim = isset($_GET["MaPhim"]) ? (int)$_GET["MaPhim"] : 0;
$phim = null;
$DaYeuThich = false;

if ($MaPhim > 0) {
    $stmt = $conn->prepare("SELECT * FROM Phim WHERE MaPhim = ?");
    $stmt->bind_param("i", $MaPhim);
    $stmt->execute();
    $result = $stmt->get_result();
    $phim = $result->fetch_assoc();
    $stmt->close();

    // Kiểm tra phim đã được tài khoản yêu thích chưa
    if ($phim && isset($_SESSION["MaTaiKhoan"])) {
        $MaTaiKhoan = (int)$_SESSION["MaTaiKhoan"];

        $stmt = $conn->prepare(
            "SELECT MaYeuThich FROM YeuThich
             WHERE MaTaiKhoan = ? AND MaPhim = ?"
        );
        $stmt->bind_param("ii", $MaTaiKhoan, $MaPhim);
        $stmt->execute();
        $resultYeuThich = $stmt->get_result();

        $DaYeuThich = $resultYeuThich->num_rows > 0;
        $stmt->close();
    }
}

if (!$phim) {
    http_response_code(404);
}

// Chuẩn hóa đường dẫn ảnh
function LayDuongDanAnhChiTiet($AnhPhim)
{
    $AnhPhim = trim((string)$AnhPhim);

    if ($AnhPhim === "") {
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

$TenTrang = $phim ? $phim["TenPhim"] : "Không tìm thấy phim";
$DuongDanAnh = $phim
    ? LayDuongDanAnhChiTiet($phim["AnhPhim"] ?? "")
    : "";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($TenTrang, ENT_QUOTES, "UTF-8"); ?>
        - WEB XEM PHIM
    </title>

    <link rel="stylesheet" href="css/style.css">

    <style>
        .movie-detail-page {
            width: min(1100px, calc(100% - 36px));
            margin: 35px auto 55px;
            color: #f5f5f5;
        }

        .movie-detail {
            display: grid;
            grid-template-columns: minmax(240px, 340px) minmax(0, 1fr);
            gap: 32px;
            align-items: start;
            padding: 25px;
            background: #202020;
            border: 1px solid #363636;
            border-radius: 14px;
            box-sizing: border-box;
        }

        .detail-image img {
            display: block;
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            background: #111;
        }

        .detail-content h1 {
            margin: 0 0 20px;
            color: #f5c518;
            font-size: clamp(25px, 3vw, 36px);
            line-height: 1.25;
        }

        .detail-content p {
            line-height: 1.7;
            margin: 10px 0;
            overflow-wrap: anywhere;
        }

        .detail-content p strong {
            color: #f5c518;
        }

        .detail-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
            align-items: center;
        }

        .watch-button {
            display: inline-block;
            box-sizing: border-box;
            padding: 12px 18px;
            border: 0;
            border-radius: 7px;
            background: #f5c518;
            color: #171717;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
        }

        .watch-button:hover {
            background: #ffdc52;
        }

        #NutYeuThich.da-yeu-thich {
            background: #d93636;
            color: white;
        }

        #NutYeuThich.da-yeu-thich:hover {
            background: #b92525;
        }

        #ThongBaoYeuThich {
            margin-top: 12px;
            font-weight: bold;
        }

        .detail-login-note {
            margin-top: 18px;
            color: #ddd;
        }

        .detail-login-note a,
        .back-home {
            color: #f5c518;
        }

        .detail-error {
            padding: 35px 24px;
            text-align: center;
            background: #202020;
            border-radius: 12px;
        }

        .detail-error h1 {
            color: #f5c518;
        }

        .back-home {
            display: inline-block;
            margin-top: 12px;
            text-decoration: none;
        }

        @media (max-width: 700px) {
            .movie-detail-page {
                width: calc(100% - 24px);
                margin-top: 20px;
            }

            .movie-detail {
                grid-template-columns: 1fr;
                gap: 22px;
                padding: 16px;
            }

            .detail-image {
                max-width: 340px;
                width: 100%;
                margin: 0 auto;
            }

            .detail-image img {
                max-height: 460px;
            }
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
            Xin chào,
            <?php
            echo htmlspecialchars(
                $_SESSION["HoTen"] ?? $_SESSION["TenDangNhap"],
                ENT_QUOTES,
                "UTF-8"
            );
            ?>
            | <a href="yeu_thich.php">Yêu thích</a>
            | <a href="dang_xuat.php">Đăng xuất</a>
        </div>
    <?php } else { ?>
        <div class="user-login">
            <a href="dang_nhap.php">Đăng nhập</a>
            | <a href="dang_ky.php">Đăng ký</a>
        </div>
    <?php } ?>
</header>

<main class="movie-detail-page">

    <?php if (!$phim) { ?>

        <div class="detail-error">
            <h1>Không tìm thấy phim</h1>
            <p>Phim có thể đã bị xóa hoặc đường dẫn không hợp lệ.</p>
            <a class="back-home" href="index.php">← Quay về trang chủ</a>
        </div>

    <?php } else { ?>

        <section class="movie-detail">

            <div class="detail-image">
                <img
                    src="<?php echo htmlspecialchars($DuongDanAnh, ENT_QUOTES, "UTF-8"); ?>"
                    alt="<?php echo htmlspecialchars($phim["TenPhim"] ?? "Ảnh phim", ENT_QUOTES, "UTF-8"); ?>"
                    onerror="this.onerror=null;this.src='images/no-image.png';"
                >
            </div>

            <div class="detail-content">

                <h1>
                    <?php echo htmlspecialchars($phim["TenPhim"] ?? "", ENT_QUOTES, "UTF-8"); ?>
                </h1>

                <p>
                    <strong>Thể loại:</strong>
                    <?php echo htmlspecialchars($phim["TheLoai"] ?? "Đang cập nhật", ENT_QUOTES, "UTF-8"); ?>
                </p>

                <p>
                    <strong>Đạo diễn:</strong>
                    <?php
                    echo htmlspecialchars(
                        !empty($phim["DaoDien"]) ? $phim["DaoDien"] : "Đang cập nhật",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </p>

                <p>
                    <strong>Năm phát hành:</strong>
                    <?php
                    echo htmlspecialchars(
                        (string)($phim["NamPhatHanh"] ?? "Đang cập nhật"),
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </p>

                <p>
                    <strong>Thời lượng:</strong>
                    <?php
                    echo htmlspecialchars(
                        !empty($phim["ThoiLuong"])
                            ? $phim["ThoiLuong"] . " phút"
                            : "Đang cập nhật",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </p>

                <p>
                    <strong>Mô tả:</strong><br>
                    <?php
                    echo nl2br(htmlspecialchars(
                        !empty($phim["MoTa"])
                            ? $phim["MoTa"]
                            : "Chưa có mô tả cho phim này.",
                        ENT_QUOTES,
                        "UTF-8"
                    ));
                    ?>
                </p>

                <div class="detail-actions">

                    <a
                        href="xem_phim.php?MaPhim=<?php echo (int)$phim["MaPhim"]; ?>"
                        class="watch-button"
                    >
                        ▶ Xem phim
                    </a>

                    <?php if (!empty($_SESSION["MaTaiKhoan"])) { ?>

                        <button
                            type="button"
                            id="NutYeuThich"
                            class="watch-button <?php echo $DaYeuThich ? 'da-yeu-thich' : ''; ?>"
                            data-maphim="<?php echo (int)$phim["MaPhim"]; ?>"
                            data-liked="<?php echo $DaYeuThich ? '1' : '0'; ?>"
                        >
                            <?php echo $DaYeuThich ? '♥ Xóa khỏi yêu thích' : '♡ Thêm vào yêu thích'; ?>
                        </button>

                    <?php } ?>

                </div>

                <?php if (!empty($_SESSION["MaTaiKhoan"])) { ?>

                    <p id="ThongBaoYeuThich" role="status" aria-live="polite"></p>

                <?php } else { ?>

                    <p class="detail-login-note">
                        <a href="dang_nhap.php">Đăng nhập</a>
                        để thêm phim vào yêu thích.
                    </p>

                <?php } ?>

                <a class="back-home" href="index.php">← Quay về trang chủ</a>

            </div>
        </section>

    <?php } ?>

</main>

<?php if ($phim && !empty($_SESSION["MaTaiKhoan"])) { ?>
<script>
const NutYeuThich = document.getElementById("NutYeuThich");
const ThongBaoYeuThich = document.getElementById("ThongBaoYeuThich");

NutYeuThich.addEventListener("click", async function () {
    if (NutYeuThich.disabled) {
        return;
    }

    NutYeuThich.disabled = true;
    ThongBaoYeuThich.textContent = "Đang cập nhật...";

    try {
        const DuLieu = new URLSearchParams();
        DuLieu.append("MaPhim", NutYeuThich.dataset.maphim);

        const PhanHoi = await fetch("them_yeu_thich.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: DuLieu.toString()
        });

        const KetQua = await PhanHoi.json();

        if (KetQua.login) {
            window.location.href = "dang_nhap.php";
            return;
        }

        if (!PhanHoi.ok || !KetQua.success) {
            throw new Error(KetQua.message || "Không thể cập nhật yêu thích.");
        }

        NutYeuThich.dataset.liked = KetQua.liked ? "1" : "0";

        if (KetQua.liked) {
            NutYeuThich.textContent = "♥ Xóa khỏi yêu thích";
            NutYeuThich.classList.add("da-yeu-thich");
        } else {
            NutYeuThich.textContent = "♡ Thêm vào yêu thích";
            NutYeuThich.classList.remove("da-yeu-thich");
        }

        ThongBaoYeuThich.textContent = KetQua.message;

    } catch (Loi) {
        ThongBaoYeuThich.textContent =
            Loi.message || "Có lỗi xảy ra. Vui lòng thử lại.";
    } finally {
        NutYeuThich.disabled = false;
    }
});
</script>
<?php } ?>

</body>
</html>
