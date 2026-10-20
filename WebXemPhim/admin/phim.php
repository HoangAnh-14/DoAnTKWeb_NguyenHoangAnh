
<?php
session_start();
include "../config.php";

if (!isset($_SESSION["MaAdmin"])) {
    header("Location: DangNhap.php");
    exit();
}

$sql = "SELECT * FROM Phim ORDER BY MaPhim DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phim</title>
    <link rel="stylesheet" href="../css/style.css">


<style>
    .admin-container {
        max-width: 1000px;
        margin: 30px auto;
        padding: 20px;
    }

    .admin-container h1 {
        color: #ffcc00;
        text-align: center;
        margin-bottom: 25px;
    }

    .admin-link {
        display: inline-block;
        margin-bottom: 20px;
        color: #ffcc00;
        text-decoration: none;
    }

    .admin-link:hover {
        color: white;
    }

    .movie-heading {
        margin: 10px 0 12px;
    }

    .movie-count {
        color: #ffcc00;
        font-size: 18px;
        font-weight: bold;
        margin: 0;
    }

    /* Nút thêm phim */
    a.btn-add {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-sizing: border-box;
        width: 100%;
        min-height: 48px;
        margin-bottom: 18px;
        padding: 14px;

        background: #ffcc00;
        color: #222 !important;
        text-decoration: none !important;

        border: 2px solid #ffcc00;
        border-radius: 10px;

        font-size: 17px;
        font-weight: bold;
        transition: 0.2s;
    }

    a.btn-add:hover {
        background: #ffe066;
        color: #111 !important;
        border-color: #ffe066;
    }

    .movie-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .movie-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px;
        background: #292929;
        border: 1px solid #444;
        border-radius: 10px;
        color: white;
    }

    .movie-image {
        width: 65px;
        height: 90px;
        object-fit: cover;
        border-radius: 5px;
        flex-shrink: 0;
    }

    .movie-info {
        flex: 1;
        min-width: 0;
    }

    .movie-info h3 {
        color: #ffcc00;
        margin: 0 0 8px;
        font-size: 18px;
        overflow-wrap: anywhere;
    }

    .movie-info p {
        margin: 5px 0;
        font-size: 14px;
        color: #ddd;
    }

    .movie-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-edit,
    .btn-delete {
        display: inline-block;
        min-width: 65px;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        text-align: center;
        text-decoration: none;
        font-size: 14px;
        cursor: pointer;
    }

    .btn-edit {
        background: #ffcc00;
        color: #111;
    }

    .btn-delete {
        background: #e53935;
        color: white;
    }

    .btn-edit:hover {
        background: #ffe066;
    }

    .btn-delete:hover {
        background: #c62828;
    }

    .empty-message {
        padding: 25px;
        text-align: center;
        color: #ddd;
        background: #292929;
        border-radius: 8px;
    }

    /* Giao diện điện thoại */
    @media (max-width: 600px) {
        .admin-container {
            padding: 12px;
        }

        .movie-item {
            gap: 10px;
            padding: 10px;
        }

        .movie-image {
            width: 55px;
            height: 78px;
        }

        .movie-info h3 {
            font-size: 15px;
        }

        .movie-info p {
            font-size: 12px;
        }

        .btn-edit,
        .btn-delete {
            min-width: 52px;
            padding: 7px;
            font-size: 12px;
        }

        a.btn-add {
            font-size: 15px;
            padding: 12px;
        }
    }
</style>

</head>

<body>

<div class="admin-container">

    <h1>QUẢN LÝ PHIM</h1>

    
<a class="admin-link" href="index_admin.php">
    ← Quay về trang quản trị
</a>

<div class="movie-heading">
    <p class="movie-count">Danh sách phim mới nhất</p>

    <a class="btn-add" href="them_phim.php">
        <span>＋</span> Thêm phim mới
    </a>
</div>

    <div class="movie-list">

        <?php if ($result && $result->num_rows > 0) { ?>

            <?php while ($row = $result->fetch_assoc()) { ?>

                <div class="movie-item">

                    <img
                        class="movie-image"
                        src="../images/<?php echo htmlspecialchars($row["AnhPhim"]); ?>"
                        alt="Ảnh phim"
                    >

                    <div class="movie-info">

                        <h3>
                            <?php echo htmlspecialchars($row["TenPhim"]); ?>
                        </h3>

                        <p>
                            Mã phim:
                            <?php echo (int)$row["MaPhim"]; ?>
                        </p>

                        <p>
                            Thể loại:
                            <?php echo htmlspecialchars($row["TheLoai"]); ?>
                        </p>

                        <p>
                            Năm phát hành:
                            <?php echo htmlspecialchars((string)$row["NamPhatHanh"]); ?>
                        </p>

                    </div>

                    <div class="movie-actions">

                        <a
                            class="btn-edit"
                            href="sua_phim.php?MaPhim=<?php echo (int)$row["MaPhim"]; ?>"
                        >
                            Sửa
                        </a>

                        <form
                            action="xoa_phim.php"
                            method="POST"
                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa phim này không?');"
                        >
                            <input
                                type="hidden"
                                name="MaPhim"
                                value="<?php echo (int)$row["MaPhim"]; ?>"
                            >

                            <button class="btn-delete" type="submit">
                                Xóa
                            </button>
                        </form>

                    </div>

                </div>

            <?php } ?>

        <?php } else { ?>

            <div class="empty-message">
                Chưa có phim trong cơ sở dữ liệu.
            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>
