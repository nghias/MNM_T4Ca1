<?php
// ==========================================
// 1. CẤU HÌNH CORS (Cho phép gọi từ mọi nơi)
// ==========================================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

// Xử lý preflight request (khi trình duyệt hỏi quyền trước)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ==========================================
// 2. KẾT NỐI DATABASE (TiDB Cloud)
// ==========================================
$servername = "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
$username   = "4MoqUaUd1wnWMGN.root";
$password   = "EeQm8Gx6DWUidjQi"; // ⚠️ LƯU Ý: Đổi pass ngay sau khi test xong vì lộ trên chat
$dbname     = "test";
$port       = 4000;

// TiDB yêu cầu kết nối qua SSL/TLS, nên không dùng new mysqli() thường được
$conn = mysqli_init();

// Cấu hình timeout (đề phòng mạng lag)
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

// Thiết lập SSL (Dùng NULL để dùng chứng chỉ mặc định của hệ thống)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// Thực hiện kết nối
// Cờ MYSQLI_CLIENT_SSL là bắt buộc với TiDB Cloud
$is_connected = @$conn->real_connect($servername, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);

if (!$is_connected) {
    echo json_encode([
        "status" => "error",
        "message" => "Kết nối TiDB thất bại: " . mysqli_connect_error()
    ]);
    exit(); // Dừng chương trình nếu không kết nối được
}

$conn->set_charset("utf8");

// ==========================================
// 3. XỬ LÝ YÊU CẦU (API)
// ==========================================
$method = $_SERVER['REQUEST_METHOD'];

// Đọc dữ liệu JSON gửi lên (Thay thế cho $_POST)
$json_input = file_get_contents('php://input');
$input = json_decode($json_input, true); // Chuyển JSON thành mảng PHP

switch ($method) {

    // --- XEM DANH SÁCH (GET) ---
    case 'GET':
        $sql = "SELECT * FROM phong";
        $result = $conn->query($sql);

        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    // --- THÊM MỚI (POST) ---
    case 'POST':
        // Lấy dữ liệu từ biến $input (do đã decode JSON ở trên)
        $ten      = $input['Ten'] ?? null;
        $dientich = $input['DienTich'] ?? null;
        $gia      = $input['Gia'] ?? null;

        if (empty($ten) || empty($gia)) {
            echo json_encode([
                "status" => "error",
                "message" => "Thiếu dữ liệu (Cần nhập Tên và Giá)"
            ]);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO phong (Ten, DienTich, Gia) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sid", $ten, $dientich, $gia);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Thêm thành công vào TiDB"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi SQL: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Lỗi Prepare: " . $conn->error]);
        }
        break;

    // --- SỬA (PUT) ---
    case 'PUT':
        $id       = $input['MaPhong'] ?? null;
        $ten      = $input['Ten'] ?? null;
        $dientich = $input['DienTich'] ?? null;
        $gia      = $input['Gia'] ?? null;

        if ($id) {
            $stmt = $conn->prepare("UPDATE phong SET Ten=?, DienTich=?, Gia=? WHERE MaPhong=?");
            $stmt->bind_param("sidi", $ten, $dientich, $gia, $id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Cập nhật thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi cập nhật: " . $stmt->error]);
            }
            $stmt->close();
        }
        break;

    // --- XÓA (DELETE) ---
    case 'DELETE':
        $id = $input['MaPhong'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM phong WHERE MaPhong=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Xóa thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi xóa: " . $stmt->error]);
            }
            $stmt->close();
        }
        break;
        
    default:
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
        break;
}

$conn->close();
?>