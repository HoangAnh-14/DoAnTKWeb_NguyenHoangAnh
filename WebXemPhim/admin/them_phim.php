
<?php
session_start();
include "../config.php";

if (!isset($_SESSION["MaAdmin"])) {
    header("Location: DangNhap.php");
    exit();
}

$ThongBao = "";
$LoaiThongBao = "";
$TenPhim = "";
$NamPhatHanh = "";
$TheLoaiDaChon = [];
$LinkAnhNhap = "";
$LinkVideoNhap = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TenPhim = trim($_POST["TenPhim"] ?? "");
    $NamPhatHanh = (int)($_POST["NamPhatHanh"] ?? 0);
    $TheLoaiChon = $_POST["TheLoai"] ?? [];
    $LinkAnhNhap = trim($_POST["LinkAnh"] ?? "");
    $LinkVideoNhap = trim($_POST["LinkVideo"] ?? "");

    if (!is_array($TheLoaiChon)) {
        $TheLoaiChon = [];
    }
    $TheLoaiDaChon = $TheLoaiChon;

    if ($TenPhim == "" || $NamPhatHanh < 1900 ||
        $NamPhatHanh > 2100 || count($TheLoaiChon) == 0) {
        $ThongBao = "Vui lòng nhập tên phim, năm phát hành và chọn thể loại!";
        $LoaiThongBao = "loi";
    } else {
        $DanhSachHopLe = [];
        $KetQuaTheLoai = $conn->query("SELECT TenTheLoai FROM TheLoai");
        if ($KetQuaTheLoai) {
            while ($Dong = $KetQuaTheLoai->fetch_assoc()) {
                $DanhSachHopLe[] = $Dong["TenTheLoai"];
            }
        }

        $TheLoaiHopLe = [];
        foreach ($TheLoaiChon as $Loai) {
            if (is_string($Loai) && in_array($Loai, $DanhSachHopLe, true)) {
                $TheLoaiHopLe[] = $Loai;
            }
        }
        $TheLoaiHopLe = array_unique($TheLoaiHopLe);

        if (count($TheLoaiHopLe) == 0) {
            $ThongBao = "Vui lòng chọn thể loại hợp lệ!";
            $LoaiThongBao = "loi";
        } else {
            $ThuMucAnh = "../images/";
            $ThuMucVideo = "../videos/";
            $AnhDaTaiLen = false;
            $VideoDaTaiLen = false;
            $TenFileAnh = "";
            $VideoPhim = "";
            $Loi = "";

            // Nếu chọn file ảnh thì ưu tiên file; nếu không thì dùng đường dẫn đã nhập.
            if (isset($_FILES["FileAnh"]) &&
                $_FILES["FileAnh"]["error"] == UPLOAD_ERR_OK) {
                $DuoiAnh = strtolower(pathinfo($_FILES["FileAnh"]["name"], PATHINFO_EXTENSION));
                $DuoiAnhHopLe = ["jpg", "jpeg", "png", "gif", "webp"];
                $ThongTinAnh = finfo_open(FILEINFO_MIME_TYPE);
                $LoaiAnh = finfo_file($ThongTinAnh, $_FILES["FileAnh"]["tmp_name"]);
                finfo_close($ThongTinAnh);
                $MIMEAnhHopLe = ["image/jpeg", "image/png", "image/gif", "image/webp"];

                if (!in_array($DuoiAnh, $DuoiAnhHopLe, true) ||
                    !in_array($LoaiAnh, $MIMEAnhHopLe, true) ||
                    $_FILES["FileAnh"]["size"] > 5 * 1024 * 1024) {
                    $Loi = "Ảnh phải là JPG, PNG, GIF hoặc WEBP, tối đa 5MB!";
                } else {
                    if (!is_dir($ThuMucAnh)) {
                        mkdir($ThuMucAnh, 0755, true);
                    }
                    $TenFileAnh = uniqid("anh_", true) . "." . $DuoiAnh;
                    if (move_uploaded_file($_FILES["FileAnh"]["tmp_name"], $ThuMucAnh . $TenFileAnh)) {
                        $AnhDaTaiLen = true;
                    } else {
                        $Loi = "Không thể lưu ảnh vào thư mục images!";
                    }
                }
            } elseif ($LinkAnhNhap != "") {
                $TenFileAnh = $LinkAnhNhap;
            } else {
                $Loi = "Vui lòng nhập link ảnh hoặc chọn ảnh từ máy tính!";
            }

            // Video không bắt buộc; chọn file thì dùng file, nếu không thì dùng link đã nhập.
            if ($Loi == "" && isset($_FILES["FileVideo"]) &&
                $_FILES["FileVideo"]["error"] != UPLOAD_ERR_NO_FILE) {
                if ($_FILES["FileVideo"]["error"] != UPLOAD_ERR_OK) {
                    $Loi = "Video tải lên thất bại. Hãy kiểm tra giới hạn dung lượng PHP.";
                } else {
                    $DuoiVideo = strtolower(pathinfo($_FILES["FileVideo"]["name"], PATHINFO_EXTENSION));
                    $DuoiVideoHopLe = ["mp4", "webm", "ogg", "mov"];
                    $ThongTinVideo = finfo_open(FILEINFO_MIME_TYPE);
                    $LoaiVideo = finfo_file($ThongTinVideo, $_FILES["FileVideo"]["tmp_name"]);
                    finfo_close($ThongTinVideo);
                    $MIMEVideoHopLe = ["video/mp4", "video/webm", "video/ogg", "video/quicktime"];

                    if (!in_array($DuoiVideo, $DuoiVideoHopLe, true) ||
                        !in_array($LoaiVideo, $MIMEVideoHopLe, true)) {
                        $Loi = "Video phải có định dạng MP4, WEBM, OGG hoặc MOV!";
                    } elseif ($_FILES["FileVideo"]["size"] > 500 * 1024 * 1024) {
                        $Loi = "Video không được vượt quá 500MB!";
                    } else {
                        if (!is_dir($ThuMucVideo)) {
                            mkdir($ThuMucVideo, 0755, true);
                        }
                        $TenFileVideo = uniqid("video_", true) . "." . $DuoiVideo;
                        if (move_uploaded_file($_FILES["FileVideo"]["tmp_name"], $ThuMucVideo . $TenFileVideo)) {
                            $VideoPhim = "videos/" . $TenFileVideo;
                            $VideoDaTaiLen = true;
                        } else {
                            $Loi = "Không thể lưu video vào thư mục videos!";
                        }
                    }
                }
            } elseif ($LinkVideoNhap != "") {
                $VideoPhim = $LinkVideoNhap;
            }

            if ($Loi != "") {
                if ($AnhDaTaiLen && file_exists($ThuMucAnh . $TenFileAnh)) {
                    unlink($ThuMucAnh . $TenFileAnh);
                }
                $ThongBao = $Loi;
                $LoaiThongBao = "loi";
            } else {
                $TheLoai = implode(", ", $TheLoaiHopLe);
                $sql = "INSERT INTO Phim (TenPhim, TheLoai, NamPhatHanh, AnhPhim, VideoPhim)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssiss", $TenPhim, $TheLoai, $NamPhatHanh, $TenFileAnh, $VideoPhim);

                if ($stmt->execute()) {
                    $ThongBao = "Thêm phim thành công!";
                    $LoaiThongBao = "thanhcong";
                    $TenPhim = "";
                    $NamPhatHanh = "";
                    $TheLoaiDaChon = [];
                    $LinkAnhNhap = "";
                    $LinkVideoNhap = "";
                } else {
                    if ($AnhDaTaiLen && file_exists($ThuMucAnh . $TenFileAnh)) {
                        unlink($ThuMucAnh . $TenFileAnh);
                    }
                    if ($VideoDaTaiLen && file_exists($ThuMucVideo . basename($VideoPhim))) {
                        unlink($ThuMucVideo . basename($VideoPhim));
                    }
                    $ThongBao = "Không thể thêm phim vào cơ sở dữ liệu!";
                    $LoaiThongBao = "loi";
                }
                $stmt->close();
            }
        }
    }
}

// Lấy danh sách thể loại
$DanhSachTheLoai = $conn->query(
    "SELECT TenTheLoai FROM TheLoai ORDER BY TenTheLoai"
);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm phim mới</title>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .admin-form {
            max-width: 600px;
            margin: 35px auto;
            padding: 25px;
            background: #292929;
            color: white;
            border-radius: 10px;
            box-sizing: border-box;
        }

        .admin-form h1 {
            color: #ffcc00;
            text-align: center;
            margin-bottom: 25px;
        }

        .admin-form label {
            display: block;
            margin-top: 12px;
        }

        .admin-form input[type="text"],
        .admin-form input[type="number"] {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin: 8px 0 16px;
            border: 1px solid #666;
            border-radius: 5px;
        }

        /* Danh sách checkbox thể loại */
        .genre-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin: 10px 0 12px;
        }

        .genre-option {
            display: flex !important;
            align-items: center;
            gap: 10px;
            padding: 12px;
            margin: 0 !important;
            background: #383838;
            border: 1px solid #555;
            border-radius: 6px;
            cursor: pointer;
        }

        .genre-option input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
            accent-color: #ffcc00;
            flex-shrink: 0;
        }

        .genre-option:has(input:checked) {
            border-color: #ffcc00;
            background: #45402b;
        }

        .form-help {
            color: #ccc;
            font-size: 13px;
            margin: 0 0 18px;
        }

        /* Ô nhập đường dẫn kèm nút chọn file */
        .path-picker {
            display: flex;
            align-items: stretch;
            gap: 8px;
            margin: 8px 0 4px;
        }

        .path-picker input[type="text"] {
            flex: 1;
            min-width: 0;
            margin: 0 0 8px;
        }

        .folder-button {
            width: 48px;
            height: 44px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0;
            border: none;
            border-radius: 5px;
            background: #1677a6;
            color: #ffffff;
            cursor: pointer;
            font-size: 22px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25);
        }

        .folder-button:hover {
            background: #105b80;
        }

        .folder-button:focus-visible {
            outline: 2px solid #ffffff;
            outline-offset: 2px;
        }

        .hidden-file {
            display: none;
        }

        .file-help {
            color: #ccc;
            font-size: 13px;
            margin: 0 0 16px;
        }

        .admin-form button[type="submit"] {
            width: 100%;
            padding: 13px 20px;
            margin-top: 12px;
            background: #ffcc00;
            color: #222;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
        }

        .admin-form button[type="submit"]:hover {
            background: #ffe066;
        }

        .admin-form a {
            color: #ffcc00;
            text-decoration: none;
        }

        .admin-form a:hover {
            color: white;
        }

        .admin-message {
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
        }

        .admin-message.thanhcong {
            color: #86efac;
            background: #173b27;
        }

        .admin-message.loi {
            color: #fca5a5;
            background: #4a2020;
        }

        @media (max-width: 650px) {
            .admin-form {
                margin: 15px;
                padding: 18px;
            }

            .genre-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="admin-form">

    <h1>THÊM PHIM MỚI</h1>

    <?php if ($ThongBao != "") { ?>
        <p class="admin-message <?php echo htmlspecialchars($LoaiThongBao); ?>">
            <?php echo htmlspecialchars($ThongBao); ?>
        </p>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">

        <label for="TenPhim">Tên phim</label>
        <input
            type="text"
            id="TenPhim"
            name="TenPhim"
            value="<?php echo htmlspecialchars($TenPhim); ?>"
            required
        >

        <label>Thể loại phim (có thể chọn nhiều)</label>

        <div class="genre-list">
            <?php if ($DanhSachTheLoai) { ?>
                <?php while ($row = $DanhSachTheLoai->fetch_assoc()) { ?>
                    <label class="genre-option">
                        <input
                            type="checkbox"
                            name="TheLoai[]"
                            value="<?php echo htmlspecialchars($row["TenTheLoai"]); ?>"
                            <?php
                            echo in_array(
                                $row["TenTheLoai"],
                                $TheLoaiDaChon,
                                true
                            ) ? "checked" : "";
                            ?>
                        >
                        <span>
                            <?php echo htmlspecialchars($row["TenTheLoai"]); ?>
                        </span>
                    </label>
                <?php } ?>
            <?php } ?>
        </div>

        <p class="form-help">
            Tích vào tất cả thể loại phù hợp với bộ phim.
        </p>

        <label for="NamPhatHanh">Năm phát hành</label>
        <input
            type="number"
            id="NamPhatHanh"
            name="NamPhatHanh"
            min="1900"
            max="2100"
            value="<?php echo htmlspecialchars((string)$NamPhatHanh); ?>"
            required
        >

        <label for="LinkAnh">Link ảnh phim</label>
        <div class="path-picker">
            <input
                type="text"
                id="LinkAnh"
                name="LinkAnh"
                placeholder="Ví dụ: images/avengers.jpg hoặc https://..."
                value="<?php echo htmlspecialchars($LinkAnhNhap); ?>"
            >
            <button type="button" class="folder-button" onclick="document.getElementById('FileAnh').click()" title="Chọn ảnh từ máy tính" aria-label="Chọn ảnh từ máy tính">📁</button>
        </div>
        <input class="hidden-file" type="file" id="FileAnh" name="FileAnh" accept=".jpg,.jpeg,.png,.gif,.webp"
               onchange="hienTenFile(this, 'LinkAnh')">
        <p class="file-help">Nhập đường dẫn ảnh hoặc bấm nút thư mục để chọn file. JPG, PNG, GIF hoặc WEBP; tối đa 5MB.</p>

        <label for="LinkVideo">Link video phim (không bắt buộc)</label>
        <div class="path-picker">
            <input
                type="text"
                id="LinkVideo"
                name="LinkVideo"
                placeholder="Ví dụ: videos/avengers.mp4 hoặc https://..."
                value="<?php echo htmlspecialchars($LinkVideoNhap); ?>"
            >
            <button type="button" class="folder-button" onclick="document.getElementById('FileVideo').click()" title="Chọn video từ máy tính" aria-label="Chọn video từ máy tính">📁</button>
        </div>
        <input class="hidden-file" type="file" id="FileVideo" name="FileVideo" accept=".mp4,.webm,.ogg,.mov"
               onchange="hienTenFile(this, 'LinkVideo')">
        <p class="file-help">Nhập đường dẫn video hoặc bấm nút thư mục để chọn file. MP4, WEBM, OGG hoặc MOV; tối đa 500MB.</p>

        <button type="submit">＋ Thêm phim</button>

    </form>

    <p><a href="phim.php">← Quay lại danh sách phim</a></p>
    <p><a href="index_admin.php">← Về trang quản trị</a></p>

</div>

<script>
function hienTenFile(input, idO) {
    if (input.files && input.files.length > 0) {
        // Hiển thị tên file đã chọn trong ô đường dẫn
        document.getElementById(idO).value = input.files[0].name;
    }
}
</script>
</body>
</html>
