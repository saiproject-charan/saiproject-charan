<?php
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "workshop_work_io");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get search parameters
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$search_id = isset($_GET['search_id']) ? $_GET['search_id'] : '';

// Build the query
$query = "SELECT w.*, e.m_firstname, e.m_name, e.m_lastname 
          FROM tbl_work_io w 
          LEFT JOIN tbl_emp e ON w.m_id = e.m_id 
          WHERE DATE(w.workdate) = '$selected_date'";

if (!empty($search_id)) {
    $query .= " AND w.m_id LIKE '%$search_id%'";
}

// Execute query
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Handle form submission for editing time records
if (isset($_POST['edit_time'])) {
    $id = $_POST['work_id'];
    $workin = $_POST['workin'];
    $workout = $_POST['workout'];
    $checkin_reason = $_POST['checkin_reason'];
    $checkout_reason = $_POST['checkout_reason'];
    
    $update_query = "UPDATE tbl_work_io SET 
                    workin = ?, 
                    workout = ?, 
                    checkin_reason = ?, 
                    checkout_reason = ? 
                    WHERE id = ?";
                    
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "ssssi", $workin, $workout, $checkin_reason, $checkout_reason, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "อัพเดทข้อมูลสำเร็จ";
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัพเดทข้อมูล";
    }
    
    mysqli_stmt_close($stmt);
    header("Location: all_employee_time.php?date=$selected_date&search_id=$search_id");
    exit();
}

function checkWorkStatus($workin) {
    $work_start = strtotime('08:30:00');
    $check_in = strtotime($workin);
    
    if (empty($workin)) {
        return 'ไม่ลงเวลา';
    } elseif ($check_in > $work_start) {
        return 'สาย';
    } else {
        return 'ปกติ';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ระบบดูการลงเวลา | WorkTime Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #4F46E5;
            --primary-light: #818CF8;
            --secondary-color: #6366F1;
            --accent-light: #EEF2FF;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --background: #F8FAFC;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, var(--background) 0%, var(--accent-light) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        .navbar {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
            box-shadow: var(--card-shadow);
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            height: 38px;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .table th {
            background: var(--primary-color) !important;
            color: white !important;
            padding: 1rem;
            font-weight: 500;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .badge {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-warning {
            background: var(--warning-color);
            border: none;
            color: white;
        }

        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .modal-header {
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px 16px 0 0;
        }

        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .table {
                font-size: 0.9rem;
            }

            .badge {
                font-size: 0.8rem;
            }

            .btn {
                font-size: 0.9rem;
                padding: 0.4rem 0.8rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="admin.php">
                <i class="fas fa-clock mr-2"></i>รายงานการมาทำงาน
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt mr-1"></i>ออกจากระบบ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-4">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">เลือกวันที่</label>
                        <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">รหัสพนักงาน</label>
                        <input type="text" name="search_id" class="form-control" value="<?php echo $search_id; ?>" 
                               placeholder="ค้นหาด้วยรหัสพนักงาน">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i>ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>รหัสพนักงาน</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เวลาเข้างาน</th>
                                <th>เวลาออกงาน</th>
                                <th>เหตุผลเข้า</th>
                                <th>เหตุผลออก</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $row['m_id']; ?></td>
                                    <td><?php echo $row['m_firstname'] . ' ' . $row['m_name'] . ' ' . $row['m_lastname']; ?></td>
                                    <td><?php echo $row['workin'] ? date('H:i', strtotime($row['workin'])) : '-'; ?></td>
                                    <td><?php echo $row['workout'] ? date('H:i', strtotime($row['workout'])) : '-'; ?></td>
                                    <td><?php echo $row['checkin_reason'] ?: '-'; ?></td>
                                    <td><?php echo $row['checkout_reason'] ?: '-'; ?></td>
                                    <td>
                                        <?php
                                        $status = checkWorkStatus($row['workin']);
                                        $badge_class = '';
                                        switch($status) {
                                            case 'ไม่ลงเวลา':
                                                $badge_class = 'badge-danger';
                                                break;
                                            case 'สาย':
                                                $badge_class = 'badge-warning';
                                                break;
                                            default:
                                                $badge_class = 'badge-success';
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm" 
                                                data-toggle="modal" 
                                                data-target="#editModal<?php echo $row['id']; ?>">
                                            <i class="fas fa-edit mr-1"></i>แก้ไข
                                        </button>
                                        
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-edit mr-2"></i>แก้ไขข้อมูลการลงเวลา
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="work_id" value="<?php echo $row['id']; ?>">
                                                            <input type="hidden" name="workdate" value="<?php echo $selected_date; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">เวลาเข้างาน</label>
                                                                <input type="text" name="workin" class="form-control" disabled
                                                                       value="<?php echo $row['workin'] ? date('H:i', strtotime($row['workin'])) : ''; ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">เวลาออกงาน</label>
                                                                <input type="text" name="workout" class="form-control" disabled
                                                                        value="<?php echo $row['workout'] ? date('H:i', strtotime($row['workout'])) : ''; ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">เหตุผลเข้า</label>
                                                                <textarea name="checkin_reason" class="form-control" rows="2"><?php echo $row['checkin_reason']; ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">เหตุผลออก</label>
                                                                <textarea name="checkout_reason" class="form-control" rows="2"><?php echo $row['checkout_reason']; ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                <i class="fas fa-times mr-1"></i>ปิด
                                                            </button>
                                                            <button type="submit" name="edit_time" class="btn btn-primary">
                                                                <i class="fas fa-save mr-1"></i>บันทึก
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($result) == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center">ไม่พบข้อมูล</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>