<?php
session_start();
require_once('condb.php');

if (!isset($_SESSION['m_id']) || $_SESSION['m_level'] !== 'staff') {
    header("Location: logout.php");
    exit();
}

$m_id = $_SESSION['m_id'];
$current_year = date('Y');
$year = isset($_GET['year']) ? $_GET['year'] : $current_year;
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

// สร้าง array ของปีย้อนหลัง 5 ปี
$years = range($current_year, $current_year - 4);

$monthNames = [
    '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', 
    '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน', 
    '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน', 
    '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
];

$sql = "SELECT * FROM tbl_emp WHERE m_id = '$m_id'";
$result = mysqli_query($condb, $sql);
$emp = mysqli_fetch_assoc($result);

$today = date('Y-m-d');
$sql = "SELECT * FROM tbl_work_io WHERE m_id = '$m_id' AND workdate = '$today'";
$result = mysqli_query($condb, $sql);
$today_data = mysqli_fetch_assoc($result);

$sql = "SELECT * FROM tbl_work_io 
        WHERE m_id = '$m_id' 
        AND DATE_FORMAT(workdate, '%Y-%m') = '$year-$month' 
        ORDER BY workdate DESC";
$history = mysqli_query($condb, $sql);

$sql = "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN TIME(workin) <= '09:00:00' THEN 1 ELSE 0 END) as ontime_days,
            SUM(CASE WHEN TIME(workin) > '09:00:00' THEN 1 ELSE 0 END) as late_days
        FROM tbl_work_io 
        WHERE m_id = '$m_id' 
        AND DATE_FORMAT(workdate, '%Y-%m') = '$year-$month'";
$stats_result = mysqli_query($condb, $sql);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ระบบลงเวลาทำงาน | WorkTime Pro</title>
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
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white !important;
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

        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: var(--card-shadow);
            margin: 1rem auto;
            object-fit: cover;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            margin: 0.75rem 0;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .form-control {
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .select-year {
            width: 120px;
            padding: 10px 15px;
            font-family: 'Prompt', sans-serif;
            font-size: 1rem;
            color: var(--text-primary);
            background-color: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%234F46E5' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .select-year:hover {
            border-color: var(--primary-color);
        }

        .select-year:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .select-month {
            width: 180px;
            padding: 10px 15px;
            font-family: 'Prompt', sans-serif;
            font-size: 1rem;
            color: var(--text-primary);
            background-color: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%234F46E5' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .select-month:hover {
            border-color: var(--primary-color);
        }

        .select-month:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .custom-table thead th {
            background: #f8fafc;
            color: var(--text-primary);
            font-weight: 600;
            padding: 16px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        .custom-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .custom-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .custom-table tbody td {
            padding: 16px;
            color: var(--text-secondary);
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .status-ontime {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-late {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .btn-check-time {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: transform 0.2s ease;
        }

        .btn-check-time:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .btn-check-time[disabled] {
            background: #E2E8F0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            .table-responsive {
                border-radius: 12px;
                overflow: hidden;
            }

            .d-flex {
                flex-direction: column;
            }

            .select-year,
            .select-month {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .mr-2 {
                margin-right: 0 !important;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-clock mr-2"></i>ระบบลงเวลาทำงาน
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
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <?php if (!empty($emp['m_img']) && file_exists($emp['m_img'])): ?>
                            <img src="<?php echo $emp['m_img']; ?>" class="profile-img mb-3">
                        <?php else: ?>
                            <i class="fas fa-user-circle fa-7x mb-3 text-secondary"></i>
                        <?php endif; ?>
                        <h5 class="card-title">
                            <?php echo $emp['m_firstname'].' '.$emp['m_name'].' '.$emp['m_lastname']; ?>
                        </h5>
                        <p class="card-text text-muted"><?php echo $emp['m_position']; ?></p>
                        
                        <div class="stats-container mt-4">
                            <div class="stat-card">
                                <small class="text-muted d-block">มาตรงเวลา</small>
                                <h3 class="mb-0 text-success">
                                    <?php echo $stats['ontime_days']; ?>
                                    <small>วัน</small>
                                </h3>
                            </div>
                            <div class="stat-card">
                                <small class="text-muted d-block">มาสาย</small>
                                <h3 class="mb-0 text-danger">
                                    <?php echo $stats['late_days']; ?>
                                    <small>วัน</small>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="far fa-clock mr-2"></i>บันทึกเวลาทำงานวันนี้</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="text-muted">เวลาเข้างาน</label>
                                <input type="text" class="form-control" value="<?php echo $today_data['workin'] ?? '-'; ?>" readonly>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="text-muted">เวลาออกงาน</label>
                                <input type="text" class="form-control" value="<?php echo $today_data['workout'] ?? '-'; ?>" readonly>
                            </div>
                        </div>
                        <?php if (!$today_data): ?>
                            <button onclick="checkLocation('in')" class="btn btn-check-time btn-block">
                                <i class="fas fa-sign-in-alt mr-2"></i>บันทึกเวลาเข้างาน
                            </button>
                        <?php elseif (empty($today_data['workout']) && date('H:i:s') >= '17:00:00'): ?>
                            <button onclick="checkLocation('out')" class="btn btn-check-time btn-block">
                                <i class="fas fa-sign-out-alt mr-2"></i>บันทึกเวลาออกงาน
                            </button>
                        <?php else: ?>
                            <button class="btn btn-check-time btn-block" disabled>
                                <i class="fas fa-check mr-2"></i>บันทึกข้อมูลครบแล้ว
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="far fa-calendar-alt mr-2"></i>ประวัติการลงเวลา</h5>
                            <div class="d-flex" style="min-width: 300px;">
                                <select class="select-year mr-2" onchange="changeYearMonth(this.value, document.querySelector('.select-month').value)">
                                    <?php foreach($years as $y): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                                            <?php echo $y + 543; ?> <!-- แสดงเป็นปี พ.ศ. -->
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <select class="select-month" onchange="changeYearMonth(document.querySelector('.select-year').value, this.value)">
                                    <?php foreach($monthNames as $num => $name): ?>
                                        <option value="<?php echo $num; ?>" <?php echo $month == $num ? 'selected' : ''; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>เวลาเข้างาน</th>
                                    <th>เวลาออกงาน</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($history)): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($row['workdate'])); ?></td>
                                        <td><?php echo $row['workin']; ?></td>
                                        <td><?php echo $row['workout'] ?? '-'; ?></td>
                                        <td>
                                            <?php 
                                            $time = strtotime($row['workin']);
                                            if ($time <= strtotime('09:00:00')): ?>
                                                <span class="status-badge status-ontime">
                                                    <i class="fas fa-check-circle mr-1"></i>ตรงเวลา
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-late">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>มาสาย
                                                </span>
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

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    const OFFICE = {
        lat: 13.123456,
        lng: 100.123456,
        maxDistance: 100
    };

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c;
    }

    function checkLocation(type) {
        Swal.fire({
            title: 'กำลังตรวจสอบตำแหน่ง',
            text: 'กรุณารอสักครู่...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        position => {
                            const distance = calculateDistance(
                                position.coords.latitude,
                                position.coords.longitude,
                                OFFICE.lat,
                                OFFICE.lng
                            );

                            if (distance <= OFFICE.maxDistance) {
                                submitTime(type, distance);
                            } else {
                                Swal.fire({
                                    title: 'อยู่นอกพื้นที่',
                                    html: `คุณอยู่ห่างจากออฟฟิศ ${Math.round(distance)} เมตร<br>กรุณาระบุเหตุผล`,
                                    input: 'text',
                                    inputPlaceholder: 'ระบุเหตุผลการลงเวลานอกพื้นที่',
                                    showCancelButton: true,
                                    confirmButtonText: 'บันทึก',
                                    cancelButtonText: 'ยกเลิก',
                                    inputValidator: (value) => {
                                        if (!value) {
                                            return 'กรุณาระบุเหตุผล';
                                        }
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        submitTime(type, distance, result.value);
                                    }
                                });
                            }
                        },
                        error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'ไม่สามารถระบุตำแหน่งได้',
                                text: 'กรุณาเปิดการใช้งาน Location บนอุปกรณ์ของคุณ'
                            });
                        }
                    );
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่รองรับการระบุตำแหน่ง',
                        text: 'บราวเซอร์ของคุณไม่รองรับการระบุตำแหน่ง'
                    });
                }
            }
        });
    }

    function submitTime(type, distance, reason = '') {
        const formData = new FormData();
        formData.append('type', type);
        formData.append('distance', distance);
        formData.append('reason_detail', reason);

        fetch('save.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกข้อมูลสำเร็จ',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(result.message);
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: error.message
            });
        });
    }

    function changeYearMonth(year, month) {
        window.location.href = `profile.php?year=${year}&month=${month}`;
    }
    </script>
</body>
</html>