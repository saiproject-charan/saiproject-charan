<?php
session_start();
require_once('condb.php');


if (!isset($_SESSION['m_id'])) {
    header('Content-Type: application/json');
    die(json_encode([
        'status' => 'error',
        'message' => 'กรุณาเข้าสู่ระบบ'
    ]));
}

$m_id = $_SESSION['m_id'];
$today = date('Y-m-d');
$current_time = date('H:i:s');
$distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
$reason = isset($_POST['reason_detail']) ? mysqli_real_escape_string($condb, $_POST['reason_detail']) : '';

try {

    $check_sql = "SELECT * FROM tbl_work_io WHERE m_id = ? AND workdate = ?";
    $stmt = mysqli_prepare($condb, $check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $m_id, $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing_record = mysqli_fetch_assoc($result);

    if (!$existing_record) {
       
        $sql = "INSERT INTO tbl_work_io (m_id, workdate, workin, checkin_distance, checkin_reason) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($condb, $sql);
        mysqli_stmt_bind_param($stmt, "sssds", $m_id, $today, $current_time, $distance, $reason);
        $message = "บันทึกเวลาเข้างาน $current_time น.";
    } else if (empty($existing_record['workout']) && $current_time >= '17:00:00') {
     
        $sql = "UPDATE tbl_work_io 
                SET workout = ?, 
                    checkout_distance = ?, 
                    checkout_reason = ? 
                WHERE m_id = ? AND workdate = ?";
        $stmt = mysqli_prepare($condb, $sql);
        mysqli_stmt_bind_param($stmt, "sdsss", $current_time, $distance, $reason, $m_id, $today);
        $message = "บันทึกเวลาออกงาน $current_time น.";
    } else {
        throw new Exception('ไม่สามารถบันทึกเวลาได้');
    }


    if (mysqli_stmt_execute($stmt)) {
        $response = [
            'status' => 'success',
            'message' => $message
        ];
    } else {
        throw new Exception('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    }

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}


header('Content-Type: application/json');
echo json_encode($response);