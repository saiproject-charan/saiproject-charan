<?php
session_start();
require_once('condb.php');

if (!isset($_SESSION['m_id']) || $_SESSION['m_level'] !== 'admin') {
    header("Location: logout.php");
    exit();
}
$id = isset($_GET['id']) ? mysqli_real_escape_string($condb, $_GET['id']) : 0;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    function validateInput($input, $fieldName) {
        global $errors;
        if (empty(trim($input))) {
            $errors[$fieldName] = "กรุณากรอก" . $fieldName;
            return false;
        }
        return true;
    }

    $requiredFields = [
        'firstname' => 'คำนำหน้า',
        'name' => 'ชื่อ',
        'lastname' => 'นามสกุล',
        'username' => 'ชื่อผู้ใช้',
        'position' => 'ตำแหน่ง',
        'level' => 'ระดับผู้ใช้งาน',
        'phone' => 'เบอร์โทรศัพท์',
        'email' => 'อีเมล',
    ];

    $validData = true;
    foreach ($requiredFields as $field => $label) {
        if (!validateInput($_POST[$field] ?? '', $label)) {
            $validData = false;
        }
    }

    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "รูปแบบอีเมลไม่ถูกต้อง";
        $validData = false;
    }

    if (!empty($_POST['phone']) && !preg_match('/^[0-9]{9,10}$/', $_POST['phone'])) {
        $errors['phone'] = "เบอร์โทรศัพท์ต้องเป็นตัวเลข 9-10 หลัก";
        $validData = false;
    }

    if (!empty($_POST['salary']) && (!is_numeric($_POST['salary']) || $_POST['salary'] < 0)) {
        $errors['salary'] = "เงินเดือนต้องเป็นตัวเลขและไม่ติดลบ";
        $validData = false;
    }

    if ($validData) {
        $firstname = mysqli_real_escape_string($condb, $_POST['firstname']);
        $name = mysqli_real_escape_string($condb, $_POST['name']);
        $lastname = mysqli_real_escape_string($condb, $_POST['lastname']);
        $username = mysqli_real_escape_string($condb, $_POST['username']);
        $position = mysqli_real_escape_string($condb, $_POST['position']);
        $level = mysqli_real_escape_string($condb, $_POST['level']);
        $phone = mysqli_real_escape_string($condb, $_POST['phone']);
        $email = mysqli_real_escape_string($condb, $_POST['email']);

        $password_update = "";
        if (!empty($_POST['password'])) {
            $password = sha1($_POST['password']);
            $password_update = ", m_password = '$password'";
        }

        $img_update = "";
        if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === 0) {
            $upload_dir = 'uploads/';
            $allowed_types = ['image/jpeg', 'image/png'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['profile_img']['type'], $allowed_types)) {
                $errors['profile_img'] = "กรุณาอัพโหลดไฟล์ JPG หรือ PNG เท่านั้น";
            } elseif ($_FILES['profile_img']['size'] > $max_size) {
                $errors['profile_img'] = "ขนาดไฟล์ต้องไม่เกิน 5MB";
            } else {
                $file_ext = strtolower(pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $upload_path)) {
                    // ดึงรูปเดิมจากฐานข้อมูล
                    $sql = "SELECT m_img FROM tbl_emp WHERE m_id = '$id'";
                    $result = mysqli_query($condb, $sql);
                    
                    if ($result) {
                        $row = mysqli_fetch_assoc($result);
                        
                        // ตรวจสอบว่ามีรูปเดิม และไม่ใช่ค่า default ก่อนลบ
                        if (!empty($row['m_img']) && file_exists($row['m_img']) && 
                            $row['m_img'] != 'default_profile.png') {
                            unlink($row['m_img']);
                        }
                    }

                    // อัปเดตรูปภาพใหม่
                    $img_update = ", m_img = '$upload_path'";
                } else {
                    $errors['profile_img'] = "เกิดข้อผิดพลาดในการอัพโหลดไฟล์";
                }
            }
        }

        if (empty($errors)) {
            $sql = "UPDATE tbl_emp SET 
                    m_firstname = '$firstname',
                    m_name = '$name',
                    m_lastname = '$lastname',
                    m_username = '$username',
                    m_position = '$position',
                    m_level = '$level',
                    m_phone = '$phone',
                    m_email = '$email'
                    $password_update
                    $img_update
                    WHERE m_id = '$id'";

            if (mysqli_query($condb, $sql)) {
                $_SESSION['success'] = "บันทึกข้อมูลสำเร็จ";
                header("Location: employee_list.php");
                exit();
            } else {
                $errors['db'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($condb);
            }
        }
    }
}

$sql = "SELECT * FROM tbl_emp WHERE m_id = '$id'";
$result = mysqli_query($condb, $sql);
$employee = mysqli_fetch_assoc($result);

if(!$employee){
    header("Location: employee_list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>แก้ไขข้อมูลพนักงาน | WorkTime Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f5f5f5;
        }
        .navbar {
            background: #4F46E5;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .form-group label {
            font-weight: 500;
            color: #374151;
        }
        .form-group label .required {
            color: #EF4444;
        }
        .btn-save {
            background: #10B981;
            color: white;
            font-weight: 500;
            padding: 0.5rem 2rem;
        }
        .btn-save:hover {
            background: #059669;
            color: white;
        }
        .btn-cancel {
            background: #6B7280;
            color: white;
            font-weight: 500;
            padding: 0.5rem 2rem;
        }
        .btn-cancel:hover {
            background: #4B5563;
            color: white;
        }
        .profile-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 1rem auto;
            display: block;
            border: 3px solid #4F46E5;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #E5E7EB;
        }
        .error-message {
            color: #EF4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .is-invalid {
            border-color: #EF4444;
        }
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <span class="navbar-brand">
                <i class="fas fa-user-edit mr-2"></i>แก้ไขข้อมูลพนักงาน
            </span>
        </div>
    </nav>

    <div class="container mb-5">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong><i class="fas fa-exclamation-circle mr-1"></i>พบข้อผิดพลาด:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="" enctype="multipart/form-data" novalidate>
                    <div class="text-center mb-4">
                        <img src="<?php echo !empty($employee['m_img']) ? htmlspecialchars($employee['m_img']) : 'default_profile.png'; ?>" 
                             class="profile-preview" alt="Profile">
                        <div class="mt-3">
                            <input type="file" name="profile_img" class="form-control-file" accept="image/*">
                            <?php if (isset($errors['profile_img'])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($errors['profile_img']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="section-title">
                        <i class="fas fa-user mr-2"></i>ข้อมูลส่วนตัว
                    </h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>คำนำหน้า <span class="required">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['firstname']) ? 'is-invalid' : ''; ?>" 
                                       name="firstname" value="<?php echo htmlspecialchars($employee['m_firstname']); ?>" required>
                                <?php if (isset($errors['firstname'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['firstname']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>ชื่อ <span class="required">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                       name="name" value="<?php echo htmlspecialchars($employee['m_name']); ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['name']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>นามสกุล <span class="required">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['lastname']) ? 'is-invalid' : ''; ?>" 
                                       name="lastname" value="<?php echo htmlspecialchars($employee['m_lastname']); ?>" required>
                                <?php if (isset($errors['lastname'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['lastname']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <h5 class="section-title mt-4">
                        <i class="fas fa-lock mr-2"></i>ข้อมูลเข้าสู่ระบบ
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ชื่อผู้ใช้ <span class="required">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                       name="username" value="<?php echo htmlspecialchars($employee['m_username']); ?>" required>
                                <?php if (isset($errors['username'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['username']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>รหัสผ่าน (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
                                <input type="password" class="form-control" name="password"></div>
                        </div>
                    </div>

                    <h5 class="section-title mt-4">
                        <i class="fas fa-briefcase mr-2"></i>ข้อมูลการทำงาน
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ตำแหน่ง <span class="required">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['position']) ? 'is-invalid' : ''; ?>" 
                                       name="position" value="<?php echo htmlspecialchars($employee['m_position']); ?>" required>
                                <?php if (isset($errors['position'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['position']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ระดับผู้ใช้งาน <span class="required">*</span></label>
                                <select class="form-control <?php echo isset($errors['level']) ? 'is-invalid' : ''; ?>" 
                                        name="level" required>
                                    <option value="admin" <?php echo $employee['m_level'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="staff" <?php echo $employee['m_level'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                                </select>
                                <?php if (isset($errors['level'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['level']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <h5 class="section-title mt-4">
                        <i class="fas fa-address-card mr-2"></i>ข้อมูลติดต่อ
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>เบอร์โทรศัพท์ <span class="required">*</span></label>
                                <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                       name="phone" value="<?php echo htmlspecialchars($employee['m_phone']); ?>" required>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['phone']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>อีเมล <span class="required">*</span></label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       name="email" value="<?php echo htmlspecialchars($employee['m_email']); ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-save px-5 mr-2">
                            <i class="fas fa-save mr-1"></i>บันทึก
                        </button>
                        <a href="employee_list.php" class="btn btn-cancel px-5">
                            <i class="fas fa-times mr-1"></i>ยกเลิก
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>