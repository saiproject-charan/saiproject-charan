<?php
session_start();
require_once('condb.php');

if (!isset($_SESSION['m_id']) || $_SESSION['m_level'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$sql = "SELECT MAX(CAST(m_id AS UNSIGNED)) as max_id FROM tbl_emp";
$result = mysqli_query($condb, $sql);
$row = mysqli_fetch_assoc($result);
$next_id = sprintf('%05d', ($row['max_id'] + 1)); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $m_id = mysqli_real_escape_string($condb, $next_id);
    $m_firstname = mysqli_real_escape_string($condb, $_POST['m_firstname']);
    $m_name = mysqli_real_escape_string($condb, $_POST['m_name']);
    $m_lastname = mysqli_real_escape_string($condb, $_POST['m_lastname']);
    $m_username = mysqli_real_escape_string($condb, $_POST['m_username']);
    $m_password = sha1($_POST['m_password']);
    $m_position = mysqli_real_escape_string($condb, $_POST['m_position']);
    $m_phone = mysqli_real_escape_string($condb, $_POST['m_phone']);
    $m_email = mysqli_real_escape_string($condb, $_POST['m_email']);
    $m_level = mysqli_real_escape_string($condb, $_POST['m_level']);

    $check_sql = "SELECT m_username FROM tbl_emp WHERE m_username = ?";
    $check_stmt = mysqli_prepare($condb, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $m_username);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "มีชื่อผู้ใช้นี้ในระบบแล้ว";
        header("Location: add_employee.php");
        exit();
    }

    $m_img = '';
    if (isset($_FILES['m_img']) && $_FILES['m_img']['error'] === UPLOAD_ERR_OK) {
        $upload_path = 'images/';
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['m_img']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $file_destination = $upload_path . $new_filename;

        if (move_uploaded_file($_FILES['m_img']['tmp_name'], $file_destination)) {
            $m_img = $file_destination;
        }
    }

    $insert_sql = "INSERT INTO tbl_emp (m_id, m_username, m_password, m_firstname, m_name, m_lastname, 
                   m_position, m_img, m_phone, m_email, m_level) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($condb, $insert_sql);
    mysqli_stmt_bind_param($stmt, "sssssssssss", 
        $m_id,
        $m_username,
        $m_password,
        $m_firstname,
        $m_name,
        $m_lastname,
        $m_position,
        $m_img,
        $m_phone,
        $m_email,
        $m_level
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "เพิ่มข้อมูลพนักงานเรียบร้อยแล้ว";
        header("Location: employee_list.php");
        exit();
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($condb);
        header("Location: add_employee.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เพิ่มพนักงาน | WorkTime Pro</title>
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

        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem;
        }

        .form-control {
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        select.form-control {
            height: 38px !important;
            padding: 0.3rem 0.75rem;
            font-size: 0.9rem;
            background-position: right 8px center;
            background-size: 12px;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-secondary {
            background: #E2E8F0;
            border: none;
            color: var(--text-primary);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .text-danger {
            color: var(--danger-color) !important;
        }

        .text-muted {
            color: var(--text-secondary) !important;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            select.form-control {
                height: 34px !important;
                font-size: 0.85rem;
            }

            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="admin.php">
                <i class="fas fa-clock mr-2"></i>เพิ่มข้อมูลพนักงาน
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

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus mr-2"></i>เพิ่มข้อมูลพนักงาน
                </h5>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">รหัสพนักงาน</label>
                            <input type="text" class="form-control" value="<?php echo $next_id; ?>" readonly>
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>รหัสพนักงานจะถูกกำหนดโดยอัตโนมัติ
                            </small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                            <select name="m_firstname" class="form-control" required>
                                <option value="">เลือกคำนำหน้า</option>
                                <option value="นาย">นาย</option>
                                <option value="นาง">นาง</option>
                                <option value="นางสาว">นางสาว</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" name="m_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" name="m_lastname" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                            <input type="text" name="m_username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" name="m_password" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ตำแหน่ง <span class="text-danger">*</span></label>
                            <input type="text" name="m_position" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ระดับผู้ใช้ <span class="text-danger">*</span></label>
                            <select name="m_level" class="form-control" required>
                                <option value="">เลือกระดับผู้ใช้</option>
                                <option value="admin">ผู้ดูแลระบบ</option>
                                <option value="staff">พนักงาน</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <input type="tel" name="m_phone" class="form-control" pattern="[0-9]{10}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                            <input type="email" name="m_email" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รูปโปรไฟล์</label>
                        <input type="file" name="m_img" class="form-control" accept="image/*">
                        <small class="text-muted">
                            <i class="fas fa-image mr-1"></i>รองรับไฟล์ภาพ jpg, jpeg, png, gif
                        </small>
                    </div>

                    <hr>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='admin.php'">
                            <i class="fas fa-times mr-1"></i>ยกเลิก
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>บันทึกข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>