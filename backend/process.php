<?php
session_start();

// Kiểm tra xem có yêu cầu gửi đến không
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    // 1. Xử lý THÊM MỚI
    if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';

        if (!empty($name) && !empty($email)) {
            $new_contact = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ];
            
            $_SESSION['contacts'][] = $new_contact;
            $_SESSION['message'] = "Đã thêm mới thành công!";
        }
    }

    // 2. Xử lý XÓA
    if ($action == 'delete' && isset($_GET['index'])) {
        $index = $_GET['index'];
        
        if (isset($_SESSION['contacts'][$index])) {
            unset($_SESSION['contacts'][$index]);
            // Sắp xếp lại chỉ mục mảng (để không bị lủng số 0, 1, 3...)
            $_SESSION['contacts'] = array_values($_SESSION['contacts']);
            $_SESSION['message'] = "Đã xóa liên hệ thành công!";
        }
    }
}

header("Location: https://mnm-t4ca1.free.nf/index.php?status=success");
exit();
?>
