<?php
session_start();
require_once('condb.php');

if (!isset($_SESSION['m_id']) || $_SESSION['m_level'] !== 'admin') {
    header("Location: logout.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($condb, $_GET['search']) : '';
$where_clause = '';
if($search) {
    $where_clause = "WHERE m_id LIKE '%$search%'";
}

$sql = "SELECT * FROM tbl_emp $where_clause ORDER BY m_id ASC";
$result = mysqli_query($condb, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รายการพนักงาน | WorkTime Pro</title>
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

        .card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
        }

        .search-form {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .table th {
            background: var(--accent-light);
            border-top: none;
            color: var(--text-primary);
            font-weight: 600;
        }

        .table td {
            color: var(--text-secondary);
            vertical-align: middle;
        }

        .btn-edit {
            background: var(--primary-light);
            color: white;
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 6px;
        }

        .form-control {
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }

            .btn-action {
                padding: 0.2rem 0.4rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="admin.php">
                <i class="fas fa-clock mr-2"></i>พนักงานทั้งหมด
            </a>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt mr-1"></i>ออกจากระบบ
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
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

        <div class="search-form">
            <form method="GET" class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="ค้นหาด้วยรหัสพนักงาน" 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php if($search): ?>
                <div class="col-md-2">
                    <a href="employee_list.php" class="btn btn-secondary">
                        <i class="fas fa-undo mr-1"></i>ล้างการค้นหา
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัส</th>
                                <th>คำนำหน้า</th>
                                <th>ชื่อ</th>
                                <th>นามสกุล</th>
                                <th>ตำแหน่ง</th>
                                <th>เบอร์โทร</th>
                                <th>อีเมล</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $row['m_id']; ?></td>
                                    <td><?php echo $row['m_firstname']; ?></td>
                                    <td><?php echo $row['m_name']; ?></td>
                                    <td><?php echo $row['m_lastname']; ?></td>
                                    <td><?php echo $row['m_position']; ?></td>
                                    <td><?php echo $row['m_phone']; ?></td>
                                    <td><?php echo $row['m_email']; ?></td>
                                    <td><?php echo $row['m_level']; ?></td>
                                    <td>
                                        <a href="edit_employee.php?id=<?php echo $row['m_id']; ?>" 
                                           class="btn btn-edit btn-action">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete('<?php echo $row['m_id']; ?>')" 
                                                class="btn btn-delete btn-action">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-exclamation-circle mr-2"></i>ไม่พบข้อมูลพนักงาน
                                    </td>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: "คุณต้องการลบข้อมูลพนักงานนี้ใช่หรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_employee.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_id';
                input.value = id;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    </script>
</body>
</html>