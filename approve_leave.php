<?php
session_start();
require_once('condb.php');

// ตรวจสอบการเข้าสู่ระบบและระดับผู้ใช้
if (!isset($_SESSION['m_id']) || $_SESSION['m_level'] !== 'staff') {
    header("Location: logout.php");
    exit();
}

$LEAVE_TYPES = [
    'sick' => ['limit' => 30, 'name' => 'ลาป่วย', 'require_doc' => true],
    'personal' => ['limit' => 15, 'name' => 'ลากิจ', 'require_doc' => true],
    'vacation' => ['limit' => 20, 'name' => 'ลาพักร้อน', 'require_doc' => false],
    'maternity' => ['limit' => 90, 'name' => 'ลาคลอด', 'require_doc' => true],
    'ordination' => ['limit' => 15, 'name' => 'ลาบวช', 'require_doc' => true]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = mysqli_real_escape_string($condb, $_POST['reason']);
    
    $document_path = null;
    if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
        $upload_dir = 'uploads/leave_documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $new_filename = time() . '_' . $_SESSION['m_id'] . '.' . $file_extension;
        $document_path = $upload_dir . $new_filename;
        
        move_uploaded_file($_FILES['document']['tmp_name'], $document_path);
    }

    $year = date('Y');
    $used_days_query = "SELECT COALESCE(SUM(DATEDIFF(end_date, start_date) + 1), 0) as used_days 
                        FROM tbl_leave 
                        WHERE m_id = ? AND leave_type = ? 
                        AND YEAR(start_date) = ? 
                        AND status = 'approved'";
    
    $stmt = mysqli_prepare($condb, $used_days_query);
    mysqli_stmt_bind_param($stmt, "ssi", $_SESSION['m_id'], $leave_type, $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $used_days = mysqli_fetch_assoc($result)['used_days'];
    
    $requested_days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
    
    if (($used_days + $requested_days) > $LEAVE_TYPES[$leave_type]['limit']) {
        $_SESSION['error'] = "จำนวนวันลาเกินกำหนด กรุณาตรวจสอบจำนวนวันลาที่เหลือ";
        header('Location: approve_leave.php');
        exit();
    }

    $insert_query = "INSERT INTO tbl_leave (m_id, leave_type, start_date, end_date, reason, document_path) 
                     VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($condb, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssssss", $_SESSION['m_id'], $leave_type, $start_date, $end_date, $reason, $document_path);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "บันทึกการลาเรียบร้อยแล้ว";
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
    }
    
    header('Location: approve_leave.php');
    exit();
}

$history_query = "SELECT l.*, e.m_firstname, e.m_name, e.m_lastname 
                 FROM tbl_leave l 
                 JOIN tbl_emp e ON l.m_id = e.m_id 
                 WHERE l.m_id = ? 
                 ORDER BY l.created_at DESC";

$stmt = mysqli_prepare($condb, $history_query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['m_id']);
mysqli_stmt_execute($stmt);
$history_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบการลางาน | WorkTime Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4F46E5;
            --primary-light: #818CF8;
            --secondary-color: #6366F1;
            --success-color: #059669;
            --warning-color: #D97706;
            --danger-color: #DC2626;
            --background: #F8FAFC;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --border-color: #E2E8F0;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
                          0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, var(--background) 0%, #EEF2FF 100%);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .navbar {
   background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
   padding: 1rem 0;
   box-shadow: var(--card-shadow);
}

.nav-link {
   display: inline-block;
   padding: 0.5rem 1rem;
   border-radius: 0.5rem;
   transition: all 0.3s ease;
   text-decoration: none;
}

.btn-outline-light {
   border: 2px solid rgba(255,255,255,0.9);
   color: white !important;
}

.btn-outline-light:hover {
   background: rgba(255,255,255,0.1);
   transform: translateY(-2px);
}

.btn-danger {
   background: var(--danger-color);
   border: none;
   color: white !important;
}

.btn-danger:hover {
   background: #DC2626;
   transform: translateY(-2px);
}

@media (max-width: 768px) {
   .nav-menu {
       margin-top: 1rem;
       display: flex;
       flex-direction: column;
       gap: 0.5rem;
   }
   
   .nav-link {
       width: 100%;
       text-align: center;
   }
}
        .card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            background: white;
            border-bottom: 2px solid var(--border-color);
            padding: 1.5rem;
        }

        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 0.8rem 1rem;
            font-size: 1rem;
        }

        .badge {
            padding: 0.6rem 1.2rem;
            border-radius: 999px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .nav-menu {
                margin-top: 1rem;
            }

            .nav-btn {
                width: 100%;
                justify-content: center;
            }
        }
                    .navbar {
            background: #6366F1;
            padding: 0.75rem 0;
            }

            .navbar-brand {
            color: white;
            font-size: 1.25rem;
            text-decoration: none;
            }

            .btn {
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            font-size: 14px;
            transition: all 0.2s;
            }

            .btn:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            } 

            @media (max-width: 768px) {
            .nav-btns {
                margin-top:
                gap: 12px;
            }
        }
    </style>
</head>
<body>

    <!-- เมนูนำทาง -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="profile.php">ระบบการลางาน</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8 col-md-10 mx-auto">
                <!-- การแจ้งเตือนความสำเร็จหรือข้อผิดพลาด -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php elseif (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3>บันทึกการลา</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="leave_type" class="form-label">ประเภทการลา</label>
                                <select name="leave_type" class="form-select" required>
                                    <?php foreach ($LEAVE_TYPES as $key => $leave): ?>
                                        <option value="<?= $key ?>"><?= $leave['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="start_date" class="form-label">วันที่เริ่มต้น</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="end_date" class="form-label">วันที่สิ้นสุด</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="reason" class="form-label">เหตุผลการลา</label>
                                <textarea name="reason" class="form-control" rows="4" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="document" class="form-label">เอกสารประกอบ (ถ้ามี)</label>
                                <input type="file" name="document" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-primary">บันทึกการลา</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3>ประวัติการลา</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ประเภทการลา</th>
                                    <th>วันที่เริ่มต้น</th>
                                    <th>วันที่สิ้นสุด</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($leave = mysqli_fetch_assoc($history_result)): ?>
                                    <tr>
                                        <td><?= $LEAVE_TYPES[$leave['leave_type']]['name'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($leave['start_date'])) ?></td>
                                        <td><?= date('d/m/Y', strtotime($leave['end_date'])) ?></td>
                                        <td>
                                            <?php if ($leave['status'] === 'approved'): ?>
                                                <span class="badge bg-success">อนุมัติ</span>
                                            <?php elseif ($leave['status'] === 'pending'): ?>
                                                <span class="badge bg-warning">รอการอนุมัติ</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">ปฏิเสธ</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- การเพิ่มไลบรารี JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
