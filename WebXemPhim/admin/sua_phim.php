
<?php
session_start();
include "../config.php";

if (!isset($_SESSION["MaAdmin"])) {
    header("Location: DangNhap.php");
    exit();
}

if (!isset($_GET["MaPhim"]) || !ctype_digit($_GET["MaPhim"])) {
    die("Mã phim không hợp lệ!");
}

$MaPhim = (int) $_GET["MaPhim"];
$ThongBao = "";

// Lấy thông tin phim cần sửa
$stmt = $conn->prepare("SELECT * FROM Phim WHERE MaPhim = ?");
$stmt->bind_param("i", $MaPhim);
$stmt->execute();

$result = $stmt->get_result();
$Phim = $result->fetch_assoc();
$stmt->close();

if (!$Phim) {
    die("Không tìm thấy phim!");
}

// Cập nhật thông tin phim
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $TenPhim = trim($_POST["TenPhim"]);
    $TheLoai = trim($_POST["TheLoai"]);
    $NamPhatHanh = (int) $_POST["NamPhatHanh"];
    $AnhPhim = trim($_POST["AnhPhim"]);
    $VideoPhim = trim($_POST["VideoPhim"]);

    if ($TenPhim == "" || $TheLoai == "" ||
        $NamPhatHanh < 1900 || $NamPhatHanh > 2100 ||
        $AnhPhim == "") {

        $ThongBao = "Vui lòng nhập đầy đủ thông tin hợp lệ!";

    } else {

        $sql = "UPDATE Phim
                SET TenPhim = ?,
                    TheLoai = ?,
                    NamPhatHanh = ?,
                    AnhPhim = ?,
                    VideoPhim = ?
                WHERE MaPhim = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssissi",
            $TenPhim,
            $TheLoai,
            $NamPhatHanh,
            $AnhPhim,
            $VideoPhim,
            $MaPhim
        );

        if ($stmt->execute()) {
            header("Location: phim.php");
            exit();
        } else {
            $ThongBao = "Sửa phim thất bại!";
        }

        $stmt->close();

        // Giữ lại dữ liệu đã nhập nếu xảy ra lỗi
        $Phim["TenPhim"] = $TenPhim;
        $Phim["TheLoai"] = $TheLoai;
        $Phim["NamPhatHanh"] = $NamPhatHanh;
        $Phim["AnhPhim"] = $AnhPhim;
        $Phim["VideoPhim"] = $VideoPhim;
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
    <title>Sửa phim</title>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .admin-form {
            max-width: 600px;
            margin: 35px auto;
            padding: 25px;
            background: #292929;
            color: white;
            border-radius: 10px;
        }

        .admin-form h1 {
            color: #ffcc00;
            text-align: center;
        }

        .admin-form label {
            display: block;
            margin-top: 12px;
        }

        .admin-form input,
        .admin-form select {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-top: 6px;
        }

        .admin-form button {
            margin-top: 20px;
            padding: 12px 20px;
            background: #ffcc00;
            border: none;
            cursor: pointer;
        }

        .admin-form a {
            color: #ffcc00;
        }

        .admin-message {
            color: #ffcc00;
        }
    </style>
</head>

<body>

<div class="admin-form">

    <h1>SỬA THÔNG TIN PHIM</h1>

    <?php if ($ThongBao != "") { ?>
        <p class="admin-message">
            <?php echo htmlspecialchars($ThongBao); ?>
        </p>
    <?php } ?>

    <form method="POST">

        <label>Tên phim</label>
        <input type="text" name="TenPhim"
               value="<?php echo htmlspecialchars($Phim["TenPhim"]); ?>"
               required>

        <label>Thể loại</label>
        <select name="TheLoai" required>
            <?php if ($DanhSachTheLoai) { ?>
                <?php while ($row = $DanhSachTheLoai->fetch_assoc()) { ?>
                    <option
                        value="<?php echo htmlspecialchars($row["TenTheLoai"]); ?>"
                        <?php echo $Phim["TheLoai"] == $row["TenTheLoai"] ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($row["TenTheLoai"]); ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>

        <label>Năm phát hành</label>
        <input type="number" name="NamPhatHanh"
               min="1900" max="2100"
               value="<?php echo (int)$Phim["NamPhatHanh"]; ?>"
               required>

        <label>Tên file ảnh trong thư mục images</label>
        <input type="text" name="AnhPhim"
               value="<?php echo htmlspecialchars($Phim["AnhPhim"]); ?>"
               required>

        <label>Đường dẫn video</label>
        <input type="text" name="VideoPhim"
               value="<?php echo htmlspecialchars($Phim["VideoPhim"] ?? ""); ?>">

        <button type="submit">Lưu thay đổi</button>

    </form>

    <p><a href="phim.php">Quay lại danh sách phim</a></p>

</div>

</body>
</html>
