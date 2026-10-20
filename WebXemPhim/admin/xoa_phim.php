
<?php
session_start();
include "../config.php";

if (!isset($_SESSION["MaAdmin"])) {
    header("Location: DangNhap.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: phim.php");
    exit();
}

if (!isset($_POST["MaPhim"]) ||
    !ctype_digit((string)$_POST["MaPhim"])) {
    header("Location: phim.php");
    exit();
}

$MaPhim = (int)$_POST["MaPhim"];

$stmt = $conn->prepare("DELETE FROM Phim WHERE MaPhim = ?");
$stmt->bind_param("i", $MaPhim);
$stmt->execute();

$DaXoa = $stmt->affected_rows > 0;
$stmt->close();

$conn->close();

if ($DaXoa) {
    header("Location: phim.php?thongbao=da_xoa");
} else {
    header("Location: phim.php?thongbao=khong_xoa");
}
exit();
?>
