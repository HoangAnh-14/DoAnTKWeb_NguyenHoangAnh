
<?php
session_start();

if (!isset($_SESSION["MaAdmin"])) {
    header("Location: dang_nhap.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Trang quản trị Admin</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
        .admin-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .admin-container h1 {
            text-align: center;
            color: #ffcc00;
            margin-bottom: 10px;
        }

        .admin-welcome {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .admin-card {
            background: #292929;
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }

        .admin-card h3 {
            color: #ffcc00;
        }

        .admin-card a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 18px;
            background: #ffcc00;
            color: #111;
            text-decoration: none;
            border-radius: 5px;
        }

        .admin-logout {
            display: block;
            width: fit-content;
            margin: 30px auto;
            color: #ffcc00;
        }
    </style>
</head>

<body>

    <div class="admin-container">

        <h1>TRANG QUẢN TRỊ ADMIN</h1>

        <p class="admin-welcome">
            Xin chào,
            <?php echo htmlspecialchars($_SESSION["HoTenAdmin"]); ?>!
        </p>

        <div class="admin-menu">

            <div class="admin-card">
                <h3>Quản lý phim</h3>
                <p>Thêm, sửa và xóa phim.</p>
                <a href="phim.php">Quản lý</a>
            </div>

            <div class="admin-card">
                <h3>Quản lý thể loại</h3>
                <p>Thêm, sửa và xóa thể loại.</p>
                <a href="theloai.php">Quản lý</a>
            </div>

            <div class="admin-card">
                <h3>Quản lý tài khoản</h3>
                <p>Xem và quản lý tài khoản người dùng.</p>
                <a href="tai_khoan.php">Quản lý</a>
            </div>

        </div>

        <a class="admin-logout" href="dang_xuat.php">
            Đăng xuất Admin
        </a>

        <p style="text-align:center;">
            <a href="../index.php">Quay về website xem phim</a>
        </p>

    </div>

</body>
</html>
