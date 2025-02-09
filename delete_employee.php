<?php
session_start();
require_once('condb.php');

if (!isset($_SESSION['m_id']) || $_SESSION['m_level'] !== 'admin') {
    header("Location: logout.php");
    exit();
}

if(isset($_POST['delete_id'])) {
    $id = mysqli_real_escape_string($condb, $_POST['delete_id']);
    
    // ตรวจสอบว่าพนักงานมีอยู่จริง
    $check_sql = "SELECT m_id, m_level FROM tbl_emp WHERE m_id = '$id'";
    $result = mysqli_query($condb, $check_sql);
    $employee = mysqli_fetch_assoc($result);
    
    if ($employee) {
        // ตรวจสอบกรณีเป็น admin คนสุดท้าย
        if ($employee['m_level'] === 'admin') {
            $admin_count_sql = "SELECT COUNT(*) as admin_count FROM tbl_emp WHERE m_level = 'admin'";
            $admin_result = mysqli_query($condb, $admin_count_sql);
            $admin_count = mysqli_fetch_assoc($admin_result)['admin_count'];
            
            if ($admin_count <= 1) {
                $_SESSION['error'] = "ไม่สามารถลบผู้ดูแลระบบคนสุดท้ายได้";
                header("Location: employee_list.php");
                exit();
            }
        }
        
        // เริ่ม Transaction
        mysqli_begin_transaction($condb);
        
        try {
            // ลบข้อมูลการลงเวลาก่อน เพราะเป็น Foreign Key ที่อ้างอิง m_id
            $delete_work = mysqli_query($condb, "DELETE FROM tbl_work_io WHERE m_id = '$id'");
            if(!$delete_work) {
                throw new Exception("ไม่สามารถลบข้อมูลการลงเวลาได้: " . mysqli_error($condb));
            }

            // ลบข้อมูลพนักงาน
            $delete_emp = mysqli_query($condb, "DELETE FROM tbl_emp WHERE m_id = '$id'");
            if(!$delete_emp) {
                throw new Exception("ไม่สามารถลบข้อมูลพนักงานได้: " . mysqli_error($condb));
            }
            
            // ยืนยัน Transaction
            mysqli_commit($condb);
            $_SESSION['success'] = "ลบข้อมูลพนักงานเรียบร้อยแล้ว";
            
        } catch (Exception $e) {
            // ถ้ามีข้อผิดพลาดให้ rollback
            mysqli_rollback($condb);
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบข้อมูล: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "ไม่พบข้อมูลพนักงาน";
    }
    
    header("Location: employee_list.php");
    exit();
}

header("Location: employee_list.php");
exit();