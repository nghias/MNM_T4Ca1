<?php
// Cấu hình CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

// Thông tin kết nối
$servername = "sql100.infinityfree.com";
$username = "if0_40577807";
$password = "Nghia13052004";
$dbname = "if0_40577807_qltro";

$conn = new mysqli($servername, $username, $password, $dbname, 3306);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối thất bại: " . $conn->connect_error]));
}

// Lấy phương thức gửi lên (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// Đọc dữ liệu JSON gửi lên (dùng cho Thêm, Sửa, Xóa)
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // --- CHỨC NĂNG XEM ---
        $sql = "SELECT * FROM phong";
        $result = $conn->query($sql);
        $data = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'POST':
        // --- CHỨC NĂNG THÊM ---
        if(isset($input['Ten']) && isset($input['Gia'])){
            $ten = $input['Ten'];
            $dientich = $input['DienTich'];
            $gia = $input['Gia'];
            
            // Sử dụng Prepared Statement để tránh lỗi SQL Injection
            $stmt = $conn->prepare("INSERT INTO phong (Ten, DienTich, Gia) VALUES (?, ?, ?)");
            $stmt->bind_param("sid", $ten, $dientich, $gia); // s: string, i: int, d: double
            
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Thêm thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi: " . $stmt->error]);
            }
            $stmt->close();
        }
        break;

    case 'PUT':
        // --- CHỨC NĂNG SỬA ---
        if(isset($input['MaPhong'])){
            $id = $input['MaPhong'];
            $ten = $input['Ten'];
            $dientich = $input['DienTich'];
            $gia = $input['Gia'];

            $stmt = $conn->prepare("UPDATE phong SET Ten=?, DienTich=?, Gia=? WHERE MaPhong=?");
            $stmt->bind_param("sidi", $ten, $dientich, $gia, $id);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Cập nhật thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi: " . $stmt->error]);
            }
            $stmt->close();
        }
        break;

    case 'DELETE':
        // --- CHỨC NĂNG XÓA ---
        if(isset($input['MaPhong'])){
            $id = $input['MaPhong'];
            
            $stmt = $conn->prepare("DELETE FROM phong WHERE MaPhong=?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Xóa thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi: " . $stmt->error]);
            }
            $stmt->close();
        }
        break;
}

$conn->close();
?>