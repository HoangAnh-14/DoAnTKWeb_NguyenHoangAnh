
<?php
session_start();
include "../config.php";

if (!isset($_SESSION["MaAdmin"])) {
    header("Location: DangNhap.php");
    exit();
}

$ThongBao = "";
$LoaiThongBao = "";

// Xóa thể loại
if (isset($_GET["xoa"])) {
    $MaTheLoai = (int)$_GET["xoa"];

    $stmt = $conn->prepare(
        "SELECT TenTheLoai FROM theloai WHERE MaTheLoai = ?"
    );
    $stmt->bind_param("i", $MaTheLoai);
    $stmt->execute();
    $TheLoai = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$TheLoai) {
        $ThongBao = "Không tìm thấy thể loại cần xóa.";
        $LoaiThongBao = "error";
    } else {
        $TenTheLoai = $TheLoai["TenTheLoai"];

        // Kiểm tra phim đang sử dụng thể loại
        $stmt = $conn->prepare(
            "SELECT MaPhim FROM phim
             WHERE FIND_IN_SET(?, REPLACE(TheLoai, ', ', ',')) > 0
             LIMIT 1"
        );
        $stmt->bind_param("s", $TenTheLoai);
        $stmt->execute();
        $DangSuDung = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($DangSuDung) {
            $ThongBao = "Không thể xóa vì vẫn có phim đang sử dụng thể loại này.";
            $LoaiThongBao = "error";
        } else {
            $stmt = $conn->prepare(
                "DELETE FROM theloai WHERE MaTheLoai = ?"
            );
            $stmt->bind_param("i", $MaTheLoai);

            if ($stmt->execute()) {
                $ThongBao = "Đã xóa thể loại thành công.";
                $LoaiThongBao = "success";
            } else {
                $ThongBao = "Xóa thể loại thất bại.";
                $LoaiThongBao = "error";
            }

            $stmt->close();
        }
    }
}

// Thêm thể loại
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ThemTheLoai"])) {
    $TenTheLoai = trim($_POST["TenTheLoai"] ?? "");

    if ($TenTheLoai === "") {
        $ThongBao = "Vui lòng nhập tên thể loại.";
        $LoaiThongBao = "error";
    } else {
        $stmt = $conn->prepare(
            "SELECT MaTheLoai FROM theloai WHERE TenTheLoai = ?"
        );
        $stmt->bind_param("s", $TenTheLoai);
        $stmt->execute();
        $BiTrung = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($BiTrung) {
            $ThongBao = "Tên thể loại đã tồn tại.";
            $LoaiThongBao = "error";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO theloai (TenTheLoai) VALUES (?)"
            );
            $stmt->bind_param("s", $TenTheLoai);

            if ($stmt->execute()) {
                $ThongBao = "Thêm thể loại thành công.";
                $LoaiThongBao = "success";
            } else {
                $ThongBao = "Không thể thêm thể loại.";
                $LoaiThongBao = "error";
            }

            $stmt->close();
        }
    }
}

// Sửa thể loại
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["SuaTheLoai"])) {
    $MaTheLoai = (int)($_POST["MaTheLoai"] ?? 0);
    $TenTheLoai = trim($_POST["TenTheLoai"] ?? "");

    if ($MaTheLoai <= 0 || $TenTheLoai === "") {
        $ThongBao = "Thông tin thể loại không hợp lệ.";
        $LoaiThongBao = "error";
    } else {
        $stmt = $conn->prepare(
            "SELECT MaTheLoai FROM theloai
             WHERE TenTheLoai = ? AND MaTheLoai <> ?"
        );
        $stmt->bind_param("si", $TenTheLoai, $MaTheLoai);
        $stmt->execute();
        $BiTrung = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($BiTrung) {
            $ThongBao = "Tên thể loại đã tồn tại.";
            $LoaiThongBao = "error";
        } else {
            $stmt = $conn->prepare(
                "SELECT TenTheLoai FROM theloai WHERE MaTheLoai = ?"
            );
            $stmt->bind_param("i", $MaTheLoai);
            $stmt->execute();
            $TheLoaiCu = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$TheLoaiCu) {
                $ThongBao = "Không tìm thấy thể loại cần sửa.";
                $LoaiThongBao = "error";
            } else {
                $TenCu = $TheLoaiCu["TenTheLoai"];

                // Cập nhật tên thể loại trong bảng phim
                $conn->begin_transaction();

                try {
                    $stmt = $conn->prepare(
                        "UPDATE theloai
                         SET TenTheLoai = ?
                         WHERE MaTheLoai = ?"
                    );
                    $stmt->bind_param("si", $TenTheLoai, $MaTheLoai);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare(
                        "UPDATE phim
                         SET TheLoai = TRIM(BOTH ', ' FROM
                             REPLACE(
                                 CONCAT(', ', TheLoai, ', '),
                                 CONCAT(', ', ?, ', '),
                                 CONCAT(', ', ?, ', ')
                             )
                         )
                         WHERE FIND_IN_SET(?, REPLACE(TheLoai, ', ', ',')) > 0"
                    );
                    $stmt->bind_param("sss", $TenCu, $TenTheLoai, $TenCu);
                    $stmt->execute();
                    $stmt->close();

                    $conn->commit();

                    $ThongBao = "Cập nhật thể loại thành công.";
                    $LoaiThongBao = "success";
                } catch (Exception $e) {
                    $conn->rollback();
                    $ThongBao = "Có lỗi xảy ra khi cập nhật thể loại.";
                    $LoaiThongBao = "error";
                }
            }
        }
    }
}

// Lấy danh sách thể loại
$DanhSach = $conn->query(
    "SELECT MaTheLoai, TenTheLoai
     FROM theloai
     ORDER BY MaTheLoai"
);

// Lấy thể loại đang được sửa
$MaDangSua = isset($_GET["sua"]) ? (int)$_GET["sua"] : 0;
$TheLoaiDangSua = null;

if ($MaDangSua > 0) {
    $stmt = $conn->prepare(
        "SELECT MaTheLoai, TenTheLoai
         FROM theloai WHERE MaTheLoai = ?"
    );
    $stmt->bind_param("i", $MaDangSua);
    $stmt->execute();
    $TheLoaiDangSua = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thể loại</title>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .theloai-container {
            max-width: 900px;
            margin: 35px auto;
            padding: 20px;
            color: white;
        }

        .theloai-container h1 {
            color: #ffcc00;
            text-align: center;
        }

        .theloai-form {
            background: #292929;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
        }

        .theloai-form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            box-sizing: border-box;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .theloai-form button {
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            background: #ffcc00;
            cursor: pointer;
            font-weight: bold;
        }

        .theloai-table {
            width: 100%;
            border-collapse: collapse;
            background: #292929;
        }

        .theloai-table th,
        .theloai-table td {
            padding: 12px;
            border: 1px solid #555;
            text-align: left;
        }

        .theloai-table th {
            color: #ffcc00;
        }

        .theloai-table a {
            color: #ffcc00;
            margin-right: 10px;
        }

        .thongbao {
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .quayve {
            display: inline-block;
            margin-top: 20px;
            color: #ffcc00;
        }
    </style>
</head>

<body>
<div class="theloai-container">
    <h1>QUẢN LÝ THỂ LOẠI PHIM</h1>

    <?php if ($ThongBao !== "") { ?>
        <div class="thongbao <?php echo $LoaiThongBao; ?>">
            <?php echo htmlspecialchars($ThongBao); ?>
        </div>
    <?php } ?>

    <form method="POST" action="theloai.php" class="theloai-form">
        <h3>
            <?php echo $TheLoaiDangSua ? "Sửa thể loại" : "Thêm thể loại mới"; ?>
        </h3>

        <input
            type="text"
            name="TenTheLoai"
            placeholder="Nhập tên thể loại"
            value="<?php echo htmlspecialchars($TheLoaiDangSua["TenTheLoai"] ?? ""); ?>"
            required
        >

        <?php if ($TheLoaiDangSua) { ?>
            <input type="hidden" name="MaTheLoai"
                   value="<?php echo (int)$TheLoaiDangSua["MaTheLoai"]; ?>">
            <button type="submit" name="SuaTheLoai">Lưu thay đổi</button>
            <a href="theloai.php">Hủy sửa</a>
        <?php } else { ?>
            <button type="submit" name="ThemTheLoai">Thêm thể loại</button>
        <?php } ?>
    </form>

    <h3>Danh sách thể loại</h3>

    <table class="theloai-table">
        <thead>
            <tr>
                <th>Mã thể loại</th>
                <th>Tên thể loại</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($Dong = $DanhSach->fetch_assoc()) { ?>
            <tr>
                <td><?php echo (int)$Dong["MaTheLoai"]; ?></td>
                <td><?php echo htmlspecialchars($Dong["TenTheLoai"]); ?></td>
                <td>
                    <a href="theloai.php?sua=<?php echo (int)$Dong["MaTheLoai"]; ?>">Sửa</a>
                    <a href="theloai.php?xoa=<?php echo (int)$Dong["MaTheLoai"]; ?>"
                       onclick="return confirm('Bạn có chắc muốn xóa thể loại này không?');">Xóa</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <a class="quayve" href="index_admin.php">← Quay lại trang quản trị</a>
</div>
</body>
</html>
