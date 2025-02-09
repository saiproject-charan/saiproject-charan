<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ระบบบันทึกเวลาการทำงาน</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            
            --primary-color: #3A5BA0;      
            --secondary-color: #5C7AEA;   
            --accent-color: #B1D4E0;       
            --background-color: #F9F5EB;   
            --text-color: #2C3333;         
            --card-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
        }

        * {
            transition: all 0.3s ease;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--background-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, var(--background-color) 0%, var(--accent-color) 100%);
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            padding: 2rem;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            padding: 2.5rem;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-color);
            opacity: 0.7;
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 12px;
            border-color: var(--accent-color);
            padding: 12px;
            font-family: 'Kanit', sans-serif;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(90, 122, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px;
            width: 100%;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(58, 91, 160, 0.4);
        }

        .login-icon {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-icon i {
            font-size: 4rem;
            color: var(--secondary-color);
            opacity: 0.8;
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 1rem;
            }
            .login-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>
                    <i class="fas fa-clock me-2"></i>
                    ระบบบันทึกเวลาการทำงาน
                </h1>
                <p>กรุณาเข้าสู่ระบบ</p>
            </div>
            
            <div class="login-icon">
                <i class="fas fa-user-circle"></i>
            </div>

            <form action="authen.php" method="post">
                <div class="form-group mb-3">
                    <label for="m_username" class="form-label">
                        <i class="fas fa-id-card me-2"></i>username
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="m_username" 
                           name="m_username" 
                           placeholder="กรุณากรอกชื่อผู้ใช้" 
                           minlength="2" 
                           required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="m_password" class="form-label">
                        <i class="fas fa-lock me-2"></i>password
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="m_password" 
                           name="m_password" 
                           placeholder="กรุณากรอกรหัสผ่าน" 
                           minlength="2" 
                           required>
                </div>
                
                <button type="submit" class="btn btn-login mt-3">
                    <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>