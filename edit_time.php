<?php
session_start();
require_once('condb.php');

if (!isset($_SESSION['m_id']) || $_SESSION['m_level'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_time'])) {
    $work_id = $_POST['work_id'];
    $workin = $_POST['workin'];
    $workout = $_POST['workout'];
    
    $check_sql = "SELECT * FROM tbl_work_io WHERE id = ?";
    $check_stmt = mysqli_prepare($condb, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $work_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        $update_sql = "UPDATE tbl_work_io SET workin = ?, workout = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($condb, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ssi", $workin, $workout, $work_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['success'] = "อัพเดทข้อมูลสำเร็จ";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัพเดทข้อมูล";
        }
    } else {
        $emp_data = mysqli_fetch_assoc($check_result);
        $insert_sql = "INSERT INTO tbl_work_io (m_id, workdate, workin, workout) VALUES (?, CURDATE(), ?, ?)";
        $insert_stmt = mysqli_prepare($condb, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "iss", $emp_data['m_id'], $workin, $workout);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['success'] = "เพิ่มข้อมูลสำเร็จ";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
        }
    }
    
    header("Location: all_employee_time.php?date=" . $_GET['date']);
    exit();
}
?>