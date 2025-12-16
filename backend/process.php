<?php
// --- 1. CẤU HÌNH CORS (QUAN TRỌNG NHẤT) ---
// Thay vì fix cứng 1 link, ta lấy đúng cái Origin đang gọi tới để trả về
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // Cache cấu hình này trong 1 ngày
}

// Xử lý Preflight Request (Trình duyệt hỏi trước khi gửi dữ liệu thật)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");         
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

header("Content-Type: application/json; charset=UTF-8"); // Mặc định trả về JSON

// --- 2. KẾT NỐI DATABASE ---
$servername = "sql100.infinityfree.com";
$username = "if0_40577807";
$password = "Nghia13052004";
$dbname = "if0_40577807_qltro";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // Trả về JSON lỗi thay vì die() chết trang
    http_response_code(500);
    echo json_encode(["error" => "Kết nối thất bại: " . $conn->connect_error]);
    exit();
}
$conn->set_charset("utf8");

// --- 3. API TRẢ VỀ DỮ LIỆU (READ) ---
if (isset($_GET['action']) && $_GET['action'] == 'read') {
    $result = $conn->query("SELECT * FROM phong");
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode($data);
    exit();
}

// --- 4. XỬ LÝ FORM (THÊM / SỬA) ---
// Phần này HTML của bạn dùng Form Action (submit truyền thống), nên ta giữ nguyên Redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kieuTacVu = $_POST['formAction'] ?? ''; 
    $maPhong = $_POST['MaPhong'] ?? '';
    $ten = $_POST['Ten'] ?? '';
    $gia = $_POST['Gia'] ?? 0;
    $dienTich = $_POST['DienTich'] ?? 0;

    if ($kieuTacVu == 'add') {
        $stmt = $conn->prepare("INSERT INTO phong (MaPhong, Ten, Gia, DienTich, HinhAnh) VALUES (?, ?, ?, ?, 'logo.png')");
        $stmt->bind_param("ssdd", $maPhong, $ten, $gia, $dienTich);
        $stmt->execute();
        $stmt->close();
    } elseif ($kieuTacVu == 'edit') {
        $stmt = $conn->prepare("UPDATE phong SET Ten=?, Gia=?, DienTich=? WHERE MaPhong=?");
        $stmt->bind_param("sdds", $ten, $gia, $dienTich, $maPhong);
        $stmt->execute();
        $stmt->close();
    }
    
    // Vì Form Submit là hành động chuyển trang, nên Redirect về lại trang FE là đúng
    header("Location: http://deloyfe.somee.com/index.html");
    exit();
}

// --- 5. XỬ LÝ XÓA (SỬA ĐỔI QUAN TRỌNG) ---
// HTML dùng FETCH để xóa, nên PHP KHÔNG ĐƯỢC REDIRECT, mà phải trả về JSON
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM phong WHERE MaPhong=?");
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Xóa thành công"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Lỗi không xóa được"]);
    }
    $stmt->close();
    exit();
}

$conn->close();
?>