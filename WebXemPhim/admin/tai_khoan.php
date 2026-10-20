
<?php
session_start();
include "../config.php";

if (!isset($_SESSION["MaAdmin"])) {
    header("Location: DangNhap.php");
    exit();
}

$ThongBao = "";
$LoaiThongBao = "";

// Xóa tài khoản
if (isset($_GET["xoa"])) {
    $MaTaiKhoan = (int)$_GET["xoa"];

    // Không cho xóa tài khoản đang đăng nhập
    if (
        isset($_SESSION["MaTaiKhoan"]) &&
        $MaTaiKhoan === (int)$_SESSION["MaTaiKhoan"]
    ) {
        $ThongBao = "Không thể xóa tài khoản đang đăng nhập.";
        $LoaiThongBao = "error";
    } else {
        $stmt = $conn->prepare(
            "SELECT MaTaiKhoan FROM taikhoan WHERE MaTaiKhoan = ?"
        );
        $stmt->bind_param("i", $MaTaiKhoan);
        $stmt->execute();
        $TonTai = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$TonTai) {
            $ThongBao = "Không tìm thấy tài khoản.";
            $LoaiThongBao = "error";
        } else {
            // Xóa dữ liệu yêu thích và lịch sử xem trước
            // để tránh lỗi khóa ngoại nếu có
            $stmt = $conn->prepare(
                "DELETE FROM yeuthich WHERE MaTaiKhoan = ?"
            );
            $stmt->bind_param("i", $MaTaiKhoan);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare(
                "DELETE FROM lichsuxem WHERE MaTaiKhoan = ?"
            );
            $stmt->bind_param("i", $MaTaiKhoan);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare(
                "DELETE FROM binhluan WHERE MaTaiKhoan = ?"
            );
            $stmt->bind_param("i", $MaTaiKhoan);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare(
                "DELETE FROM taikhoan WHERE MaTaiKhoan = ?"
            );
            $stmt->bind_param("i", $MaTaiKhoan);

            if ($stmt->execute()) {
                $ThongBao = "Đã xóa tài khoản thành công.";
                $LoaiThongBao = "success";
            } else {
                $ThongBao = "Không thể xóa tài khoản.";
                $LoaiThongBao = "error";
            }

            $stmt->close();
        }
    }
}

// Lấy thông tin tài khoản cần sửa
$MaDangSua = isset($_GET["sua"]) ? (int)$_GET["sua"] : 0;
$TaiKhoanDangSua = null;

if ($MaDangSua > 0) {
    $stmt = $conn->prepare(
        "SELECT MaTaiKhoan, TenDangNhap, HoTen, Email, VaiTro
         FROM taikhoan WHERE MaTaiKhoan = ?"
    );
    $stmt->bind_param("i", $MaDangSua);
    $stmt->execute();
    $TaiKhoanDangSua = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Cập nhật thông tin tài khoản
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["SuaTaiKhoan"])) {
    $MaTaiKhoan = (int)($_POST["MaTaiKhoan"] ?? 0);
    $TenDangNhap = trim($_POST["TenDangNhap"] ?? "");
    $HoTen = trim($_POST["HoTen"] ?? "");
    $Email = trim($_POST["Email"] ?? "");
    $VaiTro = $_POST["VaiTro"] ?? "User";

    if (
        $MaTaiKhoan <= 0 ||
        $TenDangNhap === "" ||
        $HoTen === "" ||
        !filter_var($Email, FILTER_VALIDATE_EMAIL) ||
        !in_array($VaiTro, ["User", "Admin"], true)
    ) {
        $ThongBao = "Vui lòng nhập đầy đủ thông tin hợp lệ.";
        $LoaiThongBao = "error";
    } else {
        // Kiểm tra tên đăng nhập đã được tài khoản khác sử dụng chưa
        $stmt = $conn->prepare(
            "SELECT MaTaiKhoan FROM taikhoan
             WHERE TenDangNhap = ? AND MaTaiKhoan <> ?"
        );
        $stmt->bind_param("si", $TenDangNhap, $MaTaiKhoan);
        $stmt->execute();
        $TrungTen = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($TrungTen) {
            $ThongBao = "Tên đăng nhập đã được sử dụng.";
            $LoaiThongBao = "error";
        } else {
            $stmt = $conn->prepare(
                "UPDATE taikhoan
                 SET TenDangNhap = ?, HoTen = ?, Email = ?, VaiTro = ?
                 WHERE MaTaiKhoan = ?"
            );
            $stmt->bind_param(
                "ssssi",
                $TenDangNhap,
                $HoTen,
                $Email,
                $VaiTro,
                $MaTaiKhoan
            );

            if ($stmt->execute()) {
                $ThongBao = "Cập nhật tài khoản thành công.";
                $LoaiThongBao = "success";
                $MaDangSua = 0;
                $TaiKhoanDangSua = null;
            } else {
                $ThongBao = "Cập nhật tài khoản thất bại.";
                $LoaiThongBao = "error";
            }

            $stmt->close();
        }
    }
}

// Tìm kiếm tài khoản
$TuKhoa = trim($_GET["timkiem"] ?? "");

if ($TuKhoa !== "") {
    $TuKhoaSQL = "%" . $TuKhoa . "%";

    $stmt = $conn->prepare(
        "SELECT MaTaiKhoan, TenDangNhap, HoTen, Email, VaiTro
         FROM taikhoan
         WHERE TenDangNhap LIKE ?
            OR HoTen LIKE ?
            OR Email LIKE ?
         ORDER BY MaTaiKhoan"
    );
    $stmt->bind_param("sss", $TuKhoaSQL, $TuKhoaSQL, $TuKhoaSQL);
    $stmt->execute();
    $DanhSach = $stmt->get_result();
} else {
    $DanhSach = $conn->query(
        "SELECT MaTaiKhoan, TenDangNhap, HoTen, Email, VaiTro
         FROM taikhoan ORDER BY MaTaiKhoan"
    );
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .tk-container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 20px;
            color: white;
        }

        .tk-container h1 {
            color: #ffcc00;
            text-align: center;
        }

        .tk-search {
            display: flex;
            gap: 10px;
            margin: 25px 0;
        }

        .tk-search input {
            flex: 1;
            min-width: 0;
            padding: 11px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .tk-search button,
        .tk-form button {
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            background: #ffcc00;
            cursor: pointer;
            font-weight: bold;
        }

        .tk-form {
            background: #292929;
            padding: 20px;
            margin: 25px 0;
            border-radius: 10px;
        }

        .tk-form input,
        .tk-form select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            margin: 8px 0 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .tk-table-wrap {
            overflow-x: auto;
        }

        .tk-table {
            width: 100%;
            border-collapse: collapse;
            background: #292929;
        }

        .tk-table th,
        .tk-table td {
            padding: 12px;
            border: 1px solid #555;
            text-align: left;
        }

        .tk-table th {
            color: #ffcc00;
        }

        .tk-table a {
            color: #ffcc00;
            white-space: nowrap;
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

        .tk-back {
            display: inline-block;
            margin-top: 20px;
            color: #ffcc00;
        }
    </style>
</head>

<body>
<div class="tk-container">
    <h1>QUẢN LÝ TÀI KHOẢN</h1>

    <?php if ($ThongBao !== "") { ?>
        <div class="thongbao <?php echo $LoaiThongBao; ?>">
            <?php echo htmlspecialchars($ThongBao); ?>
        </div>
    <?php } ?>

    <?php if ($TaiKhoanDangSua) { ?>
        <form method="POST" action="tai_khoan.php" class="tk-form">
            <h3>Sửa thông tin tài khoản</h3>

            <input type="hidden" name="MaTaiKhoan"
                   value="<?php echo (int)$TaiKhoanDangSua["MaTaiKhoan"]; ?>">

            <label>Tên đăng nhập</label>
            <input type="text" name="TenDangNhap" required
                   value="<?php echo htmlspecialchars($TaiKhoanDangSua["TenDangNhap"]); ?>">

            <label>Họ tên</label>
            <input type="text" name="HoTen" required
                   value="<?php echo htmlspecialchars($TaiKhoanDangSua["HoTen"]); ?>">

            <label>Email</label>
            <input type="email" name="Email" required
                   value="<?php echo htmlspecialchars($TaiKhoanDangSua["Email"]); ?>">

            <label>Vai trò</label>
            <select name="VaiTro">
                <option value="User"
                    <?php echo $TaiKhoanDangSua["VaiTro"] === "User" ? "selected" : ""; ?>>
                    User
                </option>
                <option value="Admin"
                    <?php echo $TaiKhoanDangSua["VaiTro"] === "Admin" ? "selected" : ""; ?>>
                    Admin
                </option>
            </select>

            <button type="submit" name="SuaTaiKhoan">Lưu thay đổi</button>
            <a href="tai_khoan.php">Hủy</a>
        </form>
    <?php } ?>

    <form method="GET" action="tai_khoan.php" class="tk-search">
        <input type="text" name="timkiem"
               placeholder="Tìm theo tên đăng nhập, họ tên hoặc email..."
               value="<?php echo htmlspecialchars($TuKhoa); ?>">
        <button type="submit">Tìm kiếm</button>
    </form>

    <div class="tk-table-wrap">
        <table class="tk-table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên đăng nhập</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($DanhSach && $DanhSach->num_rows > 0) { ?>
                <?php while ($Dong = $DanhSach->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo (int)$Dong["MaTaiKhoan"]; ?></td>
                        <td><?php echo htmlspecialchars($Dong["TenDangNhap"]); ?></td>
                        <td><?php echo htmlspecialchars($Dong["HoTen"]); ?></td>
                        <td><?php echo htmlspecialchars($Dong["Email"]); ?></td>
                        <td><?php echo htmlspecialchars($Dong["VaiTro"]); ?></td>
                        <td>
                            <a href="tai_khoan.php?sua=<?php echo (int)$Dong["MaTaiKhoan"]; ?>">
                                Sửa
                            </a>
                            |
                            <a href="tai_khoan.php?xoa=<?php echo (int)$Dong["MaTaiKhoan"]; ?>"
                               onclick="return confirm('Bạn có chắc muốn xóa tài khoản này không?');">
                                Xóa
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="6">Không tìm thấy tài khoản nào.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <a class="tk-back" href="index_admin.php">← Quay lại trang quản trị</a>
</div>
</body>
</html>