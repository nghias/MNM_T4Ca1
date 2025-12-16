<?php
// Thay dòng này bằng đúng địa chỉ trang web FE của bạn trên somee (không có dấu / ở cuối)
$allowed_origin = "http://deloyfe.somee.com"; 

header("Access-Control-Allow-Origin: $allowed_origin");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true"); // Quan trọng: Cho phép gửi cookie/xác thực

// Xử lý Preflight Request (bước kiểm tra của trình duyệt trước khi gửi dữ liệu thật)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
$conn = new mysqli("sql100.infinityfree.com", "if0_40577807", "Nghia13052004", "if0_40577807_qltro");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);
$conn->set_charset("utf8");

// --- API TRẢ VỀ DỮ LIỆU (CHO FILE HTML DÙNG JS LẤY) ---
if (isset($_GET['action']) && $_GET['action'] == 'read') {
    $result = $conn->query("SELECT * FROM phong");
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data); // Trả về dạng JSON
    exit();
}

// --- XỬ LÝ FORM (THÊM / SỬA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kieuTacVu = $_POST['formAction']; // 'add' hoặc 'edit'
    $maPhong = $_POST['MaPhong'];
    $ten = $_POST['Ten'];
    $gia = $_POST['Gia'];
    $dienTich = $_POST['DienTich'];

    if ($kieuTacVu == 'add') {
        $stmt = $conn->prepare("INSERT INTO phong (MaPhong, Ten, Gia, DienTich, HinhAnh) VALUES (?, ?, ?, ?, 'logo.png')");
        $stmt->bind_param("ssdd", $maPhong, $ten, $gia, $dienTich);
        $stmt->execute();
    } elseif ($kieuTacVu == 'edit') {
        $stmt = $conn->prepare("UPDATE phong SET Ten=?, Gia=?, DienTich=? WHERE MaPhong=?");
        $stmt->bind_param("sdds", $ten, $gia, $dienTich, $maPhong);
        $stmt->execute();
    }
    
    // Xử lý xong quay về file html
    header("Location: http://deloyfe.somee.com/index.html");
    exit();
}

// --- XỬ LÝ XÓA ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM phong WHERE MaPhong=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    header("Location: http://deloyfe.somee.com/index.html");
    exit();
}

$conn->close();
?>