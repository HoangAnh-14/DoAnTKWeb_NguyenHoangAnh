
<?php
session_start();
include "../config.php";

$ThongBao = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $TenDangNhap = trim($_POST["TenDangNhap"]);
    $MatKhau = $_POST["MatKhau"];

    $TenDangNhap = $conn->real_escape_string($TenDangNhap);

    $sql = "SELECT * FROM Admin
            WHERE TenDangNhap = '$TenDangNhap'";

    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {

        $Admin = $result->fetch_assoc();

        if ($MatKhau === $Admin["MatKhau"]) {

            session_regenerate_id(true);

            $_SESSION["MaAdmin"] = $Admin["MaAdmin"];
            $_SESSION["TenAdmin"] = $Admin["TenDangNhap"];
            $_SESSION["HoTenAdmin"] = $Admin["HoTen"];

            header("Location: index_admin.php");
            exit();

        } else {
            $ThongBao = "Mật khẩu không chính xác!";
        }

    } else {
        $ThongBao = "Tài khoản Admin không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<section class="login-container">

    <h2>ĐĂNG NHẬP ADMIN</h2>

    <?php if ($ThongBao != "") { ?>
        <p><?php echo htmlspecialchars($ThongBao); ?></p>
    <?php } ?>

    <form method="POST">

        <input type="text"
               name="TenDangNhap"
               placeholder="Tên đăng nhập Admin"
               required>

        <input type="password"
               name="MatKhau"
               placeholder="Mật khẩu"
               required>

        <button type="submit">Đăng nhập</button>

    </form>

    <p>
        <a href="../index.php">Quay về website</a>
    </p>

</section>

</body>
</html>
