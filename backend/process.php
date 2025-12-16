<?php
// ===== CẤU HÌNH CORS =====
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

// ===== CẤU HÌNH DATABASE (TiDB) =====
$servername = "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
$username   = "4MoqUaUd1wnWMGN.root";
$password   = "EeQm8Gx6DWUidjQi"; // Hãy đổi mật khẩu mới
$dbname     = "test";
$port       = 4000;

// Khởi tạo đối tượng mysqli
$conn = mysqli_init();

// Cấu hình thời gian chờ (timeout)
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

// Quan trọng: TiDB yêu cầu SSL. Dòng này kích hoạt SSL (không cần file chứng chỉ cụ thể trên đa số server)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// Thực hiện kết nối
$conn->real_connect($servername, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);

// Kiểm tra lỗi kết nối
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Kết nối TiDB thất bại: " . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8");

// ===== XỬ LÝ YÊU CẦU =====
$method = $_SERVER['REQUEST_METHOD'];

// Đọc dữ liệu JSON gửi lên (Thay thế cho $_POST để dùng được với fetch/axios)
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {

    // ================== GET: HIỂN THỊ ==================
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


    // ================== POST: THÊM ==================
    case 'POST':
        // Lấy dữ liệu từ JSON (chuẩn hơn $_POST)
        $ten      = $input['Ten'] ?? null;
        $dientich = $input['DienTich'] ?? null;
        $gia      = $input['Gia'] ?? null;

        if (!$ten || !$gia) {
            echo json_encode([
                "status" => "error",
                "message" => "Thiếu dữ liệu (Cần Ten, Gia)"
            ]);
            break;
        }

        // TiDB dùng tương thích MySQL nên câu lệnh giống hệt
        $stmt = $conn->prepare("INSERT INTO phong (Ten, DienTich, Gia) VALUES (?, ?, ?)");
        $stmt->bind_param("sid", $ten, $dientich, $gia);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Thêm thành công vào TiDB"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Lỗi SQL: " . $stmt->error
            ]);
        }
        $stmt->close();
        break;
        
    // ================== CÁC METHOD KHÁC ==================
    default:
        echo json_encode([
            "status" => "error",
            "message" => "Method không hỗ trợ"
        ]);
        break;
}

$conn->close();
?>