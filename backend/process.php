<?php
// ===== CORS (simple request) =====
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
// ===== KẾT NỐI DATABASE =====
$servername = "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
$username   = "4MoqUaUd1wnWMGN.root";
$password   = "EeQm8Gx6DWUidjQi";
$dbname     = "test";

$conn = new mysqli($servername, $username, $password, $dbname, 4000);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Kết nối thất bại"
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

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
        // LẤY DỮ LIỆU TỪ FORM-URLENCODED
        $ten       = $_POST['Ten'] ?? null;
        $dientich  = $_POST['DienTich'] ?? null;
        $gia       = $_POST['Gia'] ?? null;

        if (!$ten || !$gia) {
            echo json_encode([
                "status" => "error",
                "message" => "Thiếu dữ liệu"
            ]);
            break;
        }

        $stmt = $conn->prepare(
            "INSERT INTO phong (Ten, DienTich, Gia) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sid", $ten, $dientich, $gia);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Thêm thành công"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Lỗi thêm dữ liệu"
            ]);
        }

        $stmt->close();
        break;


    // ================== KHÁC ==================
    default:
        echo json_encode([
            "status" => "error",
            "message" => "Method không hỗ trợ"
        ]);
}

$conn->close();
