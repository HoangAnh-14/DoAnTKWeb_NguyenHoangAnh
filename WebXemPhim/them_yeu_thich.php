
<?php
session_start();
include "config.php";

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["MaTaiKhoan"])) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "login" => true,
        "message" => "Vui lòng đăng nhập."
    ]);
    exit();
}

$MaTaiKhoan = (int)$_SESSION["MaTaiKhoan"];
$MaPhim = isset($_POST["MaPhim"]) ? (int)$_POST["MaPhim"] : 0;

if ($MaPhim <= 0) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Mã phim không hợp lệ."
    ]);
    exit();
}

// Kiểm tra phim tồn tại
$stmt = $conn->prepare("SELECT MaPhim FROM Phim WHERE MaPhim = ?");
$stmt->bind_param("i", $MaPhim);
$stmt->execute();
$Phim = $stmt->get_result();

if ($Phim->num_rows == 0) {
    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Không tìm thấy phim."
    ]);
    $stmt->close();
    exit();
}
$stmt->close();

// Kiểm tra trạng thái yêu thích
$stmt = $conn->prepare(
    "SELECT MaYeuThich FROM YeuThich
     WHERE MaTaiKhoan = ? AND MaPhim = ?
     LIMIT 1"
);
$stmt->bind_param("ii", $MaTaiKhoan, $MaPhim);
$stmt->execute();
$YeuThich = $stmt->get_result();
$DongYeuThich = $YeuThich->fetch_assoc();
$stmt->close();

if ($DongYeuThich) {
    // Đã yêu thích thì xóa khỏi danh sách
    $MaYeuThich = (int)$DongYeuThich["MaYeuThich"];

    $stmt = $conn->prepare(
        "DELETE FROM YeuThich
         WHERE MaYeuThich = ? AND MaTaiKhoan = ?"
    );
    $stmt->bind_param("ii", $MaYeuThich, $MaTaiKhoan);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "liked" => false,
            "message" => "Đã xóa phim khỏi danh sách yêu thích."
        ]);
    } else {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Không thể xóa phim khỏi danh sách yêu thích."
        ]);
    }

    $stmt->close();

} else {
    // Chưa yêu thích thì thêm vào danh sách
    $stmt = $conn->prepare(
        "INSERT INTO YeuThich (MaPhim, MaTaiKhoan)
         VALUES (?, ?)"
    );
    $stmt->bind_param("ii", $MaPhim, $MaTaiKhoan);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "liked" => true,
            "message" => "Đã thêm phim vào danh sách yêu thích."
        ]);
    } else {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Không thể thêm phim vào danh sách yêu thích."
        ]);
    }

    $stmt->close();
}

$conn->close();
?>