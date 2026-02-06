<?php
// index.php
require_once 'database.php';

// تنظیمات اولیه
$current_page = isset($_GET['page']) ? $_GET['page'] : 'login';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = $_SESSION['user_id'] ?? null;

// هندل کردن عملیات مختلف
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostRequest($pdo);
}

// اگر کاربر لاگین کرده، به صفحه اصلی هدایت کن
if (isLoggedIn() && ($current_page === 'login' || $current_page === 'register')) {
    redirect('index.php?page=dashboard');
}

// نمایش صفحه مناسب
includeHeader();
switch ($current_page) {
    case 'login':
        showLoginPage();
        break;
    case 'register':
        showRegisterPage();
        break;
    case 'dashboard':
        showDashboard($pdo, $user_id);
        break;
    case 'todos':
        showTodosPage($pdo, $user_id);
        break;
    case 'profile':
        showProfilePage($pdo, $user_id);
        break;
    case 'admin':
        showAdminPanel($pdo);
        break;
    case 'logout':
        logout();
        break;
    default:
        showLoginPage();
}
includeFooter();

// توابع نمایش صفحات
function includeHeader() {
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>سیستم مدیریت کارها</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            
            :root {
                --primary: #4361ee;
                --secondary: #3a0ca3;
                --success: #4cc9f0;
                --warning: #f72585;
                --light: #f8f9fa;
                --dark: #212529;
                --gray: #6c757d;
                --student: #7209b7;
                --pupil: #3a86ff;
                --employee: #fb8500;
                --admin: #d90429;
            }
            
            body {
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                min-height: 100vh;
                padding: 20px;
                color: var(--dark);
            }
            
            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                min-height: 90vh;
            }
            
            /* استایل هدر */
            .header {
                background: linear-gradient(to right, var(--primary), var(--secondary));
                color: white;
                padding: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: relative;
            }
            
            .logo {
                font-size: 1.8rem;
                font-weight: bold;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .logo i {
                font-size: 2rem;
            }
            
            .user-menu {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            
            .user-info {
                display: flex;
                align-items: center;
                gap: 10px;
                background: rgba(255, 255, 255, 0.2);
                padding: 8px 15px;
                border-radius: 50px;
            }
            
            .avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: white;
                color: var(--primary);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 1.2rem;
            }
            
            .role-badge {
                padding: 3px 10px;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: bold;
            }
            
            .student-badge { background: var(--student); color: white; }
            .pupil-badge { background: var(--pupil); color: white; }
            .employee-badge { background: var(--employee); color: white; }
            .admin-badge { background: var(--admin); color: white; }
            
            /* ناوبری */
            .nav {
                display: flex;
                background: var(--light);
                padding: 0 20px;
                border-bottom: 1px solid #dee2e6;
                flex-wrap: wrap;
            }
            
            .nav a {
                padding: 15px 20px;
                text-decoration: none;
                color: var(--gray);
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.3s;
                border-bottom: 3px solid transparent;
            }
            
            .nav a:hover, .nav a.active {
                color: var(--primary);
                border-bottom: 3px solid var(--primary);
                background: rgba(67, 97, 238, 0.05);
            }
            
            /* محتوای اصلی */
            .main-content {
                padding: 30px;
                min-height: 70vh;
            }
            
            /* صفحات لاگین و ثبت‌نام */
            .auth-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 80vh;
                padding: 20px;
            }
            
            .auth-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 450px;
                padding: 40px;
            }
            
            .auth-header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .auth-header h2 {
                color: var(--primary);
                margin-bottom: 10px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: var(--dark);
            }
            
            .form-control {
                width: 100%;
                padding: 12px 15px;
                border: 1px solid #ddd;
                border-radius: 10px;
                font-size: 1rem;
                transition: all 0.3s;
            }
            
            .form-control:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
                outline: none;
            }
            
            .btn {
                padding: 12px 25px;
                border: none;
                border-radius: 10px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            
            .btn-primary {
                background: var(--primary);
                color: white;
                width: 100%;
            }
            
            .btn-primary:hover {
                background: var(--secondary);
                transform: translateY(-2px);
            }
            
            .btn-sm {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
            
            /* کارت‌ها */
            .cards-container {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .card {
                background: white;
                border-radius: 15px;
                padding: 25px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                border: 1px solid #eee;
                transition: transform 0.3s, box-shadow 0.3s;
            }
            
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }
            
            .card-icon {
                width: 60px;
                height: 60px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.8rem;
                margin-bottom: 20px;
                color: white;
            }
            
            .card-stats {
                font-size: 2rem;
                font-weight: bold;
                margin: 10px 0;
            }
            
            /* جدول */
            .table-container {
                overflow-x: auto;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                margin-top: 20px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                min-width: 600px;
            }
            
            th, td {
                padding: 15px;
                text-align: right;
                border-bottom: 1px solid #eee;
            }
            
            th {
                background: var(--light);
                font-weight: 600;
                color: var(--dark);
            }
            
            tr:hover {
                background: #f9f9f9;
            }
            
            .priority-badge {
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: bold;
            }
            
            .priority-low { background: #d4edda; color: #155724; }
            .priority-medium { background: #fff3cd; color: #856404; }
            .priority-high { background: #f8d7da; color: #721c24; }
            .priority-urgent { background: #dc3545; color: white; }
            
            .status-badge {
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: bold;
            }
            
            .status-pending { background: #fff3cd; color: #856404; }
            .status-in_progress { background: #cce5ff; color: #004085; }
            .status-completed { background: #d4edda; color: #155724; }
            .status-cancelled { background: #f8d7da; color: #721c24; }
            
            /* فرم مدیریت کارها */
            .todo-form {
                background: var(--light);
                padding: 25px;
                border-radius: 15px;
                margin-bottom: 30px;
            }
            
            .form-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }
            
            /* دکمه‌ها */
            .btn-group {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .btn-success { background: #28a745; color: white; }
            .btn-warning { background: #ffc107; color: var(--dark); }
            .btn-danger { background: #dc3545; color: white; }
            .btn-info { background: #17a2b8; color: white; }
            
            /* فوتر */
            .footer {
                text-align: center;
                padding: 20px;
                color: var(--gray);
                border-top: 1px solid #eee;
                margin-top: 30px;
            }
            
            /* استایل‌های موبایل */
            @media (max-width: 768px) {
                .container {
                    border-radius: 10px;
                }
                
                .header {
                    flex-direction: column;
                    gap: 15px;
                    padding: 15px;
                }
                
                .user-menu {
                    width: 100%;
                    justify-content: center;
                }
                
                .nav {
                    justify-content: center;
                    padding: 0;
                }
                
                .nav a {
                    padding: 12px 15px;
                    font-size: 0.9rem;
                }
                
                .main-content {
                    padding: 20px 15px;
                }
                
                .auth-card {
                    padding: 30px 20px;
                }
                
                .cards-container {
                    grid-template-columns: 1fr;
                }
                
                .form-row {
                    grid-template-columns: 1fr;
                }
                
                .btn-group {
                    flex-direction: column;
                }
                
                .btn-group .btn {
                    width: 100%;
                }
            }
            
            @media (max-width: 480px) {
                body {
                    padding: 10px;
                }
                
                .nav a {
                    padding: 10px;
                    font-size: 0.85rem;
                }
                
                .auth-card {
                    padding: 25px 15px;
                }
            }
            
            /* انیمیشن‌ها */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .fade-in {
                animation: fadeIn 0.5s ease-out;
            }
            
            /* تگ‌های فیلتر */
            .filter-tags {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-bottom: 20px;
            }
            
            .filter-tag {
                padding: 8px 15px;
                background: var(--light);
                border-radius: 20px;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .filter-tag.active {
                background: var(--primary);
                color: white;
            }
        </style>
    </head>
    <body>
    <?php
}

function includeFooter() {
    ?>
        <script>
            // توابع جاوااسکریپت
            document.addEventListener('DOMContentLoaded', function() {
                // مدیریت تاریخ‌ها
                const dateInputs = document.querySelectorAll('input[type="date"]');
                dateInputs.forEach(input => {
                    if (!input.value) {
                        const today = new Date().toISOString().split('T')[0];
                        input.value = today;
                    }
                });
                
                // مدیریت فرم‌ها
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        const required = form.querySelectorAll('[required]');
                        let valid = true;
                        
                        required.forEach(field => {
                            if (!field.value.trim()) {
                                valid = false;
                                field.style.borderColor = '#dc3545';
                            }
                        });
                        
                        if (!valid) {
                            e.preventDefault();
                            alert('لطفا تمام فیلدهای ضروری را پر کنید');
                        }
                    });
                });
                
                // مدیریت فیلترها
                const filterTags = document.querySelectorAll('.filter-tag');
                filterTags.forEach(tag => {
                    tag.addEventListener('click', function() {
                        filterTags.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        
                        // اعمال فیلتر (اینجا می‌توانید Ajax اضافه کنید)
                        const filterType = this.dataset.filter;
                        console.log('فیلتر اعمال شد:', filterType);
                    });
                });
                
                // مدیریت وضعیت کارها
                const statusButtons = document.querySelectorAll('.change-status');
                statusButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const todoId = this.dataset.id;
                        const newStatus = this.dataset.status;
                        
                        if (confirm('آیا از تغییر وضعیت اطمینان دارید؟')) {
                            // ارسال درخواست Ajax (اینجا ساده شده)
                            window.location.href = `index.php?action=change_status&id=${todoId}&status=${newStatus}`;
                        }
                    });
                });
                
                // نمایش تاریخ فارسی
                const faDates = document.querySelectorAll('.fa-date');
                faDates.forEach(el => {
                    const date = new Date(el.dataset.date);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    el.textContent = date.toLocaleDateString('fa-IR', options);
                });
            });
            
            // تابع حذف با تایید
            function confirmDelete(id, type) {
                if (confirm(`آیا از حذف این ${type} اطمینان دارید؟`)) {
                    window.location.href = `index.php?action=delete&id=${id}&type=${type}`;
                }
            }
            
            // تابع تغییر وضعیت
            function changeStatus(id, status) {
                window.location.href = `index.php?action=change_status&id=${id}&status=${status}`;
            }
        </script>
    </body>
    </html>
    <?php
}

function showLoginPage() {
    ?>
    <div class="container fade-in">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="logo" style="justify-content: center; color: var(--primary);">
                        <i class="fas fa-tasks"></i>
                        <span>سیستم مدیریت کارها</span>
                    </div>
                    <h2>ورود به سیستم</h2>
                    <p>لطفا اطلاعات حساب کاربری خود را وارد کنید</p>
                </div>
                
                <form action="index.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> نام کاربری یا ایمیل</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> رمز عبور</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> ورود به سیستم
                        </button>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <p>حساب کاربری ندارید؟ <a href="index.php?page=register" style="color: var(--primary);">ثبت‌نام کنید</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function showRegisterPage() {
    ?>
    <div class="container fade-in">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="logo" style="justify-content: center; color: var(--primary);">
                        <i class="fas fa-tasks"></i>
                        <span>سیستم مدیریت کارها</span>
                    </div>
                    <h2>ثبت‌نام در سیستم</h2>
                    <p>لطفا اطلاعات خود را برای ثبت‌نام وارد کنید</p>
                </div>
                
                <form action="index.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label for="full_name"><i class="fas fa-id-card"></i> نام کامل</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> نام کاربری</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> ایمیل</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> رمز عبور</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="role"><i class="fas fa-user-tag"></i> نقش کاربری</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">-- انتخاب کنید --</option>
                            <option value="student">دانشجو</option>
                            <option value="pupil">دانش‌آموز</option>
                            <option value="employee">کارمند</option>
                            <option value="admin">مدیر سیستم</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> ثبت‌نام
                        </button>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <p>قبلا ثبت‌نام کرده‌اید؟ <a href="index.php?page=login" style="color: var(--primary);">وارد شوید</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function showDashboard($pdo, $user_id) {
    $role = getUserRole();
    
    // آمار کاربر
    $stats = getUserStats($pdo, $user_id, $role);
    
    // آخرین کارها
    $recent_todos = getRecentTodos($pdo, $user_id, $role);
    
    // نمایش هدر
    ?>
    <div class="container fade-in">
        <div class="header">
            <div class="logo">
                <i class="fas fa-tasks"></i>
                <span>سیستم مدیریت کارها</span>
            </div>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="avatar">
                        <?php echo substr($_SESSION['full_name'] ?? 'کاربر', 0, 1); ?>
                    </div>
                    <div>
                        <div style="font-weight: bold;"><?php echo $_SESSION['full_name'] ?? 'کاربر'; ?></div>
                        <div class="role-badge <?php echo $role; ?>-badge">
                            <?php 
                            $role_names = [
                                'student' => 'دانشجو',
                                'pupil' => 'دانش‌آموز',
                                'employee' => 'کارمند',
                                'admin' => 'مدیر سیستم'
                            ];
                            echo $role_names[$role] ?? $role;
                            ?>
                        </div>
                    </div>
                </div>
                
                <a href="index.php?page=profile" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white;">
                    <i class="fas fa-user-cog"></i> پروفایل
                </a>
                
                <a href="index.php?page=logout" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white;">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </div>
        
        <div class="nav">
            <a href="index.php?page=dashboard" class="<?php echo ($_GET['page'] ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> داشبورد
            </a>
            <a href="index.php?page=todos" class="<?php echo ($_GET['page'] ?? '') === 'todos' ? 'active' : ''; ?>">
                <i class="fas fa-list-check"></i> کارهای من
            </a>
            
            <?php if ($role === 'admin'): ?>
            <a href="index.php?page=admin" class="<?php echo ($_GET['page'] ?? '') === 'admin' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> مدیریت سیستم
            </a>
            <?php endif; ?>
        </div>
        
        <div class="main-content">
            <h2 style="margin-bottom: 20px; color: var(--primary);">
                <i class="fas fa-chart-line"></i> آمار و اطلاعات
            </h2>
            
            <div class="cards-container">
                <div class="card">
                    <div class="card-icon" style="background: var(--primary);">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>کل کارها</h3>
                    <div class="card-stats"><?php echo $stats['total_todos']; ?></div>
                    <p>تعداد کل کارهای شما</p>
                </div>
                
                <div class="card">
                    <div class="card-icon" style="background: #28a745;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>کارهای انجام شده</h3>
                    <div class="card-stats"><?php echo $stats['completed_todos']; ?></div>
                    <p>کارهای تکمیل شده</p>
                </div>
                
                <div class="card">
                    <div class="card-icon" style="background: #ffc107;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>در حال انجام</h3>
                    <div class="card-stats"><?php echo $stats['in_progress_todos']; ?></div>
                    <p>کارهای در دست اقدام</p>
                </div>
                
                <div class="card">
                    <div class="card-icon" style="background: #dc3545;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h3>فوری</h3>
                    <div class="card-stats"><?php echo $stats['urgent_todos']; ?></div>
                    <p>کارهای با اولویت فوری</p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
                <div>
                    <h3 style="margin-bottom: 15px; color: var(--primary);">
                        <i class="fas fa-list"></i> آخرین کارها
                    </h3>
                    
                    <?php if (empty($recent_todos)): ?>
                        <div class="card" style="text-align: center; padding: 40px;">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                            <p>هیچ کاری یافت نشد</p>
                            <a href="index.php?page=todos" class="btn btn-primary" style="margin-top: 15px; width: auto;">
                                <i class="fas fa-plus"></i> ایجاد کار جدید
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>عنوان</th>
                                        <th>اولویت</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_todos as $todo): ?>
                                    <tr>
                                        <td><?php echo sanitize($todo['title']); ?></td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $todo['priority']; ?>">
                                                <?php 
                                                $priority_names = [
                                                    'low' => 'کم',
                                                    'medium' => 'متوسط',
                                                    'high' => 'بالا',
                                                    'urgent' => 'فوری'
                                                ];
                                                echo $priority_names[$todo['priority']] ?? $todo['priority'];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $todo['status']; ?>">
                                                <?php 
                                                $status_names = [
                                                    'pending' => 'در انتظار',
                                                    'in_progress' => 'در حال انجام',
                                                    'completed' => 'تکمیل شده',
                                                    'cancelled' => 'لغو شده'
                                                ];
                                                echo $status_names[$todo['status']] ?? $todo['status'];
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h3 style="margin-bottom: 15px; color: var(--primary);">
                        <i class="fas fa-chart-pie"></i> نمودار وضعیت
                    </h3>
                    <div class="card" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                        <!-- در اینجا می‌توانید از کتابخانه‌ای مثل Chart.js استفاده کنید -->
                        <canvas id="statusChart" style="max-width: 100%; max-height: 250px;"></canvas>
                        <script>
                            // نمونه نمودار با Chart.js
                            const ctx = document.getElementById('statusChart');
                            if (ctx) {
                                // داده‌های نمونه
                                const data = {
                                    labels: ['تکمیل شده', 'در حال انجام', 'در انتظار', 'لغو شده'],
                                    datasets: [{
                                        data: [
                                            <?php echo $stats['completed_todos']; ?>,
                                            <?php echo $stats['in_progress_todos']; ?>,
                                            <?php echo $stats['pending_todos']; ?>,
                                            <?php echo $stats['cancelled_todos']; ?>
                                        ],
                                        backgroundColor: [
                                            '#28a745',
                                            '#17a2b8',
                                            '#ffc107',
                                            '#dc3545'
                                        ]
                                    }]
                                };
                                
                                // اگر Chart.js وجود دارد، نمودار را رسم کن
                                if (typeof Chart !== 'undefined') {
                                    new Chart(ctx, {
                                        type: 'doughnut',
                                        data: data,
                                        options: {
                                            responsive: true,
                                            plugins: {
                                                legend: {
                                                    position: 'bottom',
                                                    rtl: true
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    ctx.innerHTML = '<p>برای نمایش نمودار Chart.js را اضافه کنید</p>';
                                }
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>سیستم مدیریت کارها - © <?php echo date('Y'); ?> - نسخه ۱.۰</p>
        </div>
    </div>
    <?php
}

function showTodosPage($pdo, $user_id) {
    $role = getUserRole();
    
    // دریافت کارهای کاربر
    $todos = getUserTodos($pdo, $user_id, $role);
    
    // نمایش هدر مشابه داشبورد
    ?>
    <div class="container fade-in">
        <div class="header">
            <div class="logo">
                <i class="fas fa-tasks"></i>
                <span>سیستم مدیریت کارها</span>
            </div>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="avatar">
                        <?php echo substr($_SESSION['full_name'] ?? 'کاربر', 0, 1); ?>
                    </div>
                    <div>
                        <div style="font-weight: bold;"><?php echo $_SESSION['full_name'] ?? 'کاربر'; ?></div>
                        <div class="role-badge <?php echo $role; ?>-badge">
                            <?php 
                            $role_names = [
                                'student' => 'دانشجو',
                                'pupil' => 'دانش‌آموز',
                                'employee' => 'کارمند',
                                'admin' => 'مدیر سیستم'
                            ];
                            echo $role_names[$role] ?? $role;
                            ?>
                        </div>
                    </div>
                </div>
                
                <a href="index.php?page=profile" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white;">
                    <i class="fas fa-user-cog"></i> پروفایل
                </a>
                
                <a href="index.php?page=logout" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white;">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </div>
        
        <div class="nav">
            <a href="index.php?page=dashboard">
                <i class="fas fa-home"></i> داشبورد
            </a>
            <a href="index.php?page=todos" class="active">
                <i class="fas fa-list-check"></i> کارهای من
            </a>
            
            <?php if ($role === 'admin'): ?>
            <a href="index.php?page=admin">
                <i class="fas fa-cog"></i> مدیریت سیستم
            </a>
            <?php endif; ?>
        </div>
        
        <div class="main-content">
            <h2 style="margin-bottom: 20px; color: var(--primary);">
                <i class="fas fa-list-check"></i> مدیریت کارها
            </h2>
            
            <!-- فرم ایجاد کار جدید -->
            <div class="todo-form">
                <h3 style="margin-bottom: 20px; color: var(--secondary);">
                    <i class="fas fa-plus-circle"></i> ایجاد کار جدید
                </h3>
                
                <form action="index.php" method="POST">
                    <input type="hidden" name="action" value="add_todo">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">عنوان کار *</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">دسته‌بندی</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">-- انتخاب کنید --</option>
                                <option value="درس">درس</option>
                                <option value="کار">کار</option>
                                <option value="شخصی">شخصی</option>
                                <option value="پروژه">پروژه</option>
                                <option value="دیگر">دیگر</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="priority">اولویت</label>
                            <select id="priority" name="priority" class="form-control">
                                <option value="low">کم</option>
                                <option value="medium" selected>متوسط</option>
                                <option value="high">بالا</option>
                                <option value="urgent">فوری</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="due_date">تاریخ سررسید</label>
                            <input type="date" id="due_date" name="due_date" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">توضیحات</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ذخیره کار
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- فیلترها -->
            <div class="filter-tags">
                <div class="filter-tag active" data-filter="all">همه</div>
                <div class="filter-tag" data-filter="pending">در انتظار</div>
                <div class="filter-tag" data-filter="in_progress">در حال انجام</div>
                <div class="filter-tag" data-filter="completed">تکمیل شده</div>
                <div class="filter-tag" data-filter="urgent">فوری</div>
            </div>
            
            <!-- لیست کارها -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>دسته‌بندی</th>
                            <th>اولویت</th>
                            <th>وضعیت</th>
                            <th>تاریخ سررسید</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($todos)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px;">
                                    <i class="fas fa-inbox" style="font-size: 2rem; color: #ddd; margin-bottom: 10px; display: block;"></i>
                                    <p>هیچ کاری یافت نشد. یک کار جدید ایجاد کنید.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($todos as $todo): ?>
                            <tr>
                                <td style="max-width: 200px;">
                                    <strong><?php echo sanitize($todo['title']); ?></strong>
                                    <?php if ($todo['description']): ?>
                                        <br><small style="color: var(--gray);"><?php echo substr(sanitize($todo['description']), 0, 50); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo sanitize($todo['category']) ?: '-'; ?></td>
                                <td>
                                    <span class="priority-badge priority-<?php echo $todo['priority']; ?>">
                                        <?php 
                                        $priority_names = [
                                            'low' => 'کم',
                                            'medium' => 'متوسط',
                                            'high' => 'بالا',
                                            'urgent' => 'فوری'
                                        ];
                                        echo $priority_names[$todo['priority']] ?? $todo['priority'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $todo['status']; ?>">
                                        <?php 
                                        $status_names = [
                                            'pending' => 'در انتظار',
                                            'in_progress' => 'در حال انجام',
                                            'completed' => 'تکمیل شده',
                                            'cancelled' => 'لغو شده'
                                        ];
                                        echo $status_names[$todo['status']] ?? $todo['status'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($todo['due_date']): ?>
                                        <span class="fa-date" data-date="<?php echo $todo['due_date']; ?>">
                                            <?php 
                                            $date = new DateTime($todo['due_date']);
                                            echo $date->format('Y/m/d');
                                            ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <?php if ($todo['status'] !== 'completed'): ?>
                                            <button onclick="changeStatus(<?php echo $todo['id']; ?>, 'completed')" 
                                                    class="btn btn-success btn-sm" title="تکمیل">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($todo['status'] !== 'in_progress' && $todo['status'] !== 'completed'): ?>
                                            <button onclick="changeStatus(<?php echo $todo['id']; ?>, 'in_progress')" 
                                                    class="btn btn-info btn-sm" title="شروع">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button onclick="confirmDelete(<?php echo $todo['id']; ?>, 'todo')" 
                                                class="btn btn-danger btn-sm" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="footer">
            <p>سیستم مدیریت کارها - © <?php echo date('Y'); ?> - نسخه ۱.۰</p>
        </div>
    </div>
    <?php
}

function showAdminPanel($pdo) {
    if (getUserRole() !== 'admin') {
        echo '<script>alert("دسترسی غیرمجاز"); window.location.href="index.php";</script>';
        exit;
    }
    
    // آمار کلی سیستم
    $stats = getAdminStats($pdo);
    $all_users = getAllUsers($pdo);
    
    ?>
    <div class="container fade-in">
        <div class="header">
            <div class="logo">
                <i class="fas fa-tasks"></i>
                <span>سیستم مدیریت کارها</span>
            </div>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="avatar">
                        <?php echo substr($_SESSION['full_name'] ?? 'کاربر', 0, 1); ?>
                    </div>
                    <div>
                        <div style="font-weight: bold;"><?php echo $_SESSION['full_name'] ?? 'کاربر'; ?></div>
                        <div class="role-badge admin-badge">
                            مدیر سیستم
                        </div>
                    </div>
                </div>
                
                <a href="index.php?page=profile" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white;">
                    <i class="fas fa-user-cog"></i> پروفایل
                </a>
                
                <a href="index.php?page=logout" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white;">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </div>
        
        <div class="nav">
            <a href="index.php?page=dashboard">
                <i class="fas fa-home"></i> داشبورد
            </a>
            <a href="index.php?page=todos">
                <i class="fas fa-list-check"></i> کارهای من
            </a>
            <a href="index.php?page=admin" class="active">
                <i class="fas fa-cog"></i> مدیریت سیستم
            </a>
        </div>
        
        <div class="main-content">
            <h2 style="margin-bottom: 20px; color: var(--primary);">
                <i class="fas fa-cogs"></i> پنل مدیریت
            </h2>
            
            <!-- آمار سیستم -->
            <div class="cards-container">
                <div class="card">
                    <div class="card-icon" style="background: var(--primary);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>کاربران</h3>
                    <div class="card-stats"><?php echo $stats['total_users']; ?></div>
                    <p>تعداد کل کاربران سیستم</p>
                </div>
                
                <div class="card">
                    <div class="card-icon" style="background: #28a745;">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>کارها</h3>
                    <div class="card-stats"><?php echo $stats['total_todos']; ?></div>
                    <p>تعداد کل کارهای سیستم</p>
                </div>
                
                <div class="card">
                    <div class="card-icon" style="background: #ffc107;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>دانشجویان</h3>
                    <div class="card-stats"><?php echo $stats['student_users']; ?></div>
                    <p>کاربران دانشجو</p>
                </div>
                
                <div class="card">
                    <div class="card-icon" style="background: #dc3545;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>کارهای امروز</h3>
                    <div class="card-stats"><?php echo $stats['today_todos']; ?></div>
                    <p>کارهای ایجاد شده امروز</p>
                </div>
            </div>
            
            <!-- مدیریت کاربران -->
            <h3 style="margin-top: 40px; margin-bottom: 20px; color: var(--secondary);">
                <i class="fas fa-user-friends"></i> مدیریت کاربران
            </h3>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>نام کاربری</th>
                            <th>نام کامل</th>
                            <th>ایمیل</th>
                            <th>نقش</th>
                            <th>تاریخ ثبت‌نام</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_users as $user): ?>
                        <tr>
                            <td><?php echo sanitize($user['username']); ?></td>
                            <td><?php echo sanitize($user['full_name']); ?></td>
                            <td><?php echo sanitize($user['email']); ?></td>
                            <td>
                                <span class="role-badge <?php echo $user['role']; ?>-badge">
                                    <?php 
                                    $role_names = [
                                        'student' => 'دانشجو',
                                        'pupil' => 'دانش‌آموز',
                                        'employee' => 'کارمند',
                                        'admin' => 'مدیر'
                                    ];
                                    echo $role_names[$user['role']] ?? $user['role'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $date = new DateTime($user['created_at']);
                                echo $date->format('Y/m/d');
                                ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $user['status'] === 'active' ? 'status-completed' : 'status-cancelled'; ?>">
                                    <?php echo $user['status'] === 'active' ? 'فعال' : 'غیرفعال'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" 
                                            class="btn btn-<?php echo $user['status'] === 'active' ? 'warning' : 'success'; ?> btn-sm">
                                        <i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                    
                                    <button onclick="confirmDelete(<?php echo $user['id']; ?>, 'user')" 
                                            class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- ایجاد کاربر جدید -->
            <div class="todo-form" style="margin-top: 40px;">
                <h3 style="margin-bottom: 20px; color: var(--secondary);">
                    <i class="fas fa-user-plus"></i> ایجاد کاربر جدید
                </h3>
                
                <form action="index.php" method="POST">
                    <input type="hidden" name="action" value="admin_add_user">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">نام کامل *</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">نام کاربری *</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">ایمیل *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">رمز عبور *</label>
                            <input type="password" id="password" name="password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="role">نقش کاربری *</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="student">دانشجو</option>
                                <option value="pupil">دانش‌آموز</option>
                                <option value="employee">کارمند</option>
                                <option value="admin">مدیر سیستم</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">وضعیت</label>
                            <select id="status" name="status" class="form-control">
                                <option value="active" selected>فعال</option>
                                <option value="inactive">غیرفعال</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> ایجاد کاربر
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="footer">
            <p>سیستم مدیریت کارها - پنل مدیریت - © <?php echo date('Y'); ?></p>
        </div>
    </div>
    
    <script>
        function toggleUserStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            if (confirm(`آیا از تغییر وضعیت کاربر اطمینان دارید؟`)) {
                window.location.href = `index.php?action=toggle_user_status&id=${userId}&status=${newStatus}`;
            }
        }
    </script>
    <?php
}

function showProfilePage($pdo, $user_id) {
    $user = getUserProfile($pdo, $user_id);
    
    ?>
    <div class="container fade-in">
        <div class="header">
            <div class="logo">
                <i class="fas fa-tasks"></i>
                <span>سیستم مدیریت کارها</span>
            </div>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="avatar">
                        <?php echo substr($_SESSION['full_name'] ?? 'کاربر', 0, 1); ?>
                    </div>
                    <div>
                        <div style="font-weight: bold;"><?php echo $_SESSION['full_name'] ?? 'کاربر'; ?></div>
                        <div class="role-badge <?php echo getUserRole(); ?>-badge">
                            <?php 
                            $role = getUserRole();
                            $role_names = [
                                'student' => 'دانشجو',
                                'pupil' => 'دانش‌آموز',
                                'employee' => 'کارمند',
                                'admin' => 'مدیر سیستم'
                            ];
                            echo $role_names[$role] ?? $role;
                            ?>
                        </div>
                    </div>
                </div>
                
                <a href="index.php?page=dashboard" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white;">
                    <i class="fas fa-home"></i> داشبورد
                </a>
                
                <a href="index.php?page=logout" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white;">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </div>
        
        <div class="nav">
            <a href="index.php?page=dashboard">
                <i class="fas fa-home"></i> داشبورد
            </a>
            <a href="index.php?page=todos">
                <i class="fas fa-list-check"></i> کارهای من
            </a>
            
            <?php if (getUserRole() === 'admin'): ?>
            <a href="index.php?page=admin">
                <i class="fas fa-cog"></i> مدیریت سیستم
            </a>
            <?php endif; ?>
            
            <a href="index.php?page=profile" class="active">
                <i class="fas fa-user-cog"></i> پروفایل
            </a>
        </div>
        
        <div class="main-content">
            <h2 style="margin-bottom: 20px; color: var(--primary);">
                <i class="fas fa-user-circle"></i> پروفایل کاربری
            </h2>
            
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                <!-- اطلاعات کاربر -->
                <div class="card" style="text-align: center;">
                    <div style="margin-bottom: 20px;">
                        <div class="avatar" style="width: 100px; height: 100px; margin: 0 auto 15px; font-size: 2.5rem;">
                            <?php echo substr($user['full_name'], 0, 1); ?>
                        </div>
                        <h3><?php echo sanitize($user['full_name']); ?></h3>
                        <p style="color: var(--gray);"><?php echo sanitize($user['email']); ?></p>
                    </div>
                    
                    <div style="text-align: right; margin-top: 20px;">
                        <p><strong>نقش:</strong> 
                            <span class="role-badge <?php echo $user['role']; ?>-badge" style="display: inline-block; margin-right: 10px;">
                                <?php 
                                $role_names = [
                                    'student' => 'دانشجو',
                                    'pupil' => 'دانش‌آموز',
                                    'employee' => 'کارمند',
                                    'admin' => 'مدیر سیستم'
                                ];
                                echo $role_names[$user['role']] ?? $user['role'];
                                ?>
                            </span>
                        </p>
                        
                        <p><strong>عضویت از:</strong> 
                            <?php 
                            $date = new DateTime($user['created_at']);
                            echo $date->format('Y/m/d');
                            ?>
                        </p>
                        
                        <p><strong>آخرین ورود:</strong> 
                            <?php 
                            if ($user['last_login']) {
                                $date = new DateTime($user['last_login']);
                                echo $date->format('Y/m/d H:i');
                            } else {
                                echo 'اولین ورود';
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- فرم ویرایش پروفایل -->
                <div class="card">
                    <h3 style="margin-bottom: 20px; color: var(--secondary);">
                        <i class="fas fa-edit"></i> ویرایش اطلاعات
                    </h3>
                    
                    <form action="index.php" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">نام کامل</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" 
                                       value="<?php echo sanitize($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">ایمیل</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo sanitize($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">نام کاربری</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo sanitize($user['username']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="current_password">رمز عبور فعلی (برای تغییر رمز)</label>
                                <input type="password" id="current_password" name="current_password" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">رمز عبور جدید</label>
                                <input type="password" id="new_password" name="new_password" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">تکرار رمز عبور جدید</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> ذخیره تغییرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>سیستم مدیریت کارها - © <?php echo date('Y'); ?> - نسخه ۱.۰</p>
        </div>
    </div>
    <?php
}

// توایع کمکی برای دسترسی به پایگاه داده
function getUserStats($pdo, $user_id, $role) {
    $stats = [
        'total_todos' => 0,
        'completed_todos' => 0,
        'in_progress_todos' => 0,
        'pending_todos' => 0,
        'cancelled_todos' => 0,
        'urgent_todos' => 0
    ];
    
    try {
        // آمار بر اساس نقش کاربر
        if ($role === 'admin') {
            // ادمین آمار همه کاربران را می‌بیند
            $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent
                FROM todos";
            $stmt = $pdo->query($sql);
        } else {
            // کاربران عادی فقط آمار خود را می‌بینند
            $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent
                FROM todos WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
        }
        
        $result = $stmt->fetch();
        
        if ($result) {
            $stats = [
                'total_todos' => $result['total'] ?? 0,
                'completed_todos' => $result['completed'] ?? 0,
                'in_progress_todos' => $result['in_progress'] ?? 0,
                'pending_todos' => $result['pending'] ?? 0,
                'cancelled_todos' => $result['cancelled'] ?? 0,
                'urgent_todos' => $result['urgent'] ?? 0
            ];
        }
    } catch (Exception $e) {
        error_log("خطا در دریافت آمار: " . $e->getMessage());
    }
    
    return $stats;
}

function getRecentTodos($pdo, $user_id, $role, $limit = 5) {
    try {
        if ($role === 'admin') {
            $sql = "SELECT t.*, u.full_name FROM todos t 
                    JOIN users u ON t.user_id = u.id 
                    ORDER BY t.created_at DESC LIMIT ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$limit]);
        } else {
            $sql = "SELECT * FROM todos WHERE user_id = ? 
                    ORDER BY created_at DESC LIMIT ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $limit]);
        }
        
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("خطا در دریافت کارهای اخیر: " . $e->getMessage());
        return [];
    }
}

function getUserTodos($pdo, $user_id, $role) {
    try {
        if ($role === 'admin') {
            $sql = "SELECT t.*, u.full_name FROM todos t 
                    JOIN users u ON t.user_id = u.id 
                    ORDER BY t.priority DESC, t.due_date ASC";
            $stmt = $pdo->query($sql);
        } else {
            $sql = "SELECT * FROM todos WHERE user_id = ? 
                    ORDER BY priority DESC, due_date ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
        }
        
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("خطا در دریافت کارهای کاربر: " . $e->getMessage());
        return [];
    }
}

function getAdminStats($pdo) {
    $stats = [
        'total_users' => 0,
        'total_todos' => 0,
        'student_users' => 0,
        'today_todos' => 0
    ];
    
    try {
        $sql = "SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM todos) as total_todos,
            (SELECT COUNT(*) FROM users WHERE role = 'student') as student_users,
            (SELECT COUNT(*) FROM todos WHERE DATE(created_at) = CURDATE()) as today_todos";
        
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch();
        
        if ($result) {
            $stats = $result;
        }
    } catch (Exception $e) {
        error_log("خطا در دریافت آمار ادمین: " . $e->getMessage());
    }
    
    return $stats;
}

function getAllUsers($pdo) {
    try {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("خطا در دریافت کاربران: " . $e->getMessage());
        return [];
    }
}

function getUserProfile($pdo, $user_id) {
    try {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("خطا در دریافت پروفایل: " . $e->getMessage());
        return null;
    }
}

function handlePostRequest($pdo) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            handleLogin($pdo);
            break;
        case 'register':
            handleRegister($pdo);
            break;
        case 'add_todo':
            handleAddTodo($pdo);
            break;
        case 'update_profile':
            handleUpdateProfile($pdo);
            break;
        case 'admin_add_user':
            handleAdminAddUser($pdo);
            break;
    }
}

function handleLogin($pdo) {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    try {
        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            
            // آپدیت زمان آخرین ورود
            $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$user['id']]);
            
            redirect('index.php?page=dashboard');
        } else {
            echo '<script>alert("نام کاربری یا رمز عبور اشتباه است");</script>';
        }
    } catch (Exception $e) {
        error_log("خطا در ورود: " . $e->getMessage());
        echo '<script>alert("خطا در ورود به سیستم");</script>';
    }
}

function handleRegister($pdo) {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'student');
    
    // اعتبارسنجی
    if (strlen($password) < 6) {
        echo '<script>alert("رمز عبور باید حداقل ۶ کاراکتر باشد");</script>';
        return;
    }
    
    try {
        // بررسی تکراری نبودن نام کاربری و ایمیل
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$username, $email]);
        
        if ($check_stmt->fetch()) {
            echo '<script>alert("نام کاربری یا ایمیل قبلا ثبت شده است");</script>';
            return;
        }
        
        // هش کردن رمز عبور
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (full_name, username, email, password, role) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $username, $email, $hashed_password, $role]);
        
        echo '<script>alert("ثبت‌نام با موفقیت انجام شد. اکنون وارد شوید."); window.location.href="index.php";</script>';
    } catch (Exception $e) {
        error_log("خطا در ثبت‌نام: " . $e->getMessage());
        echo '<script>alert("خطا در ثبت‌نام");</script>';
    }
}

function handleAddTodo($pdo) {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $priority = sanitize($_POST['priority'] ?? 'medium');
    $category = sanitize($_POST['category'] ?? '');
    $due_date = $_POST['due_date'] ?? null;
    
    try {
        $sql = "INSERT INTO todos (user_id, title, description, priority, category, due_date) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $title, $description, $priority, $category, $due_date]);
        
        redirect('index.php?page=todos');
    } catch (Exception $e) {
        error_log("خطا در ایجاد کار: " . $e->getMessage());
        echo '<script>alert("خطا در ایجاد کار");</script>';
    }
}

function handleUpdateProfile($pdo) {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        // بررسی تکراری نبودن نام کاربری و ایمیل
        $check_sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$username, $email, $user_id]);
        
        if ($check_stmt->fetch()) {
            echo '<script>alert("نام کاربری یا ایمیل قبلا ثبت شده است");</script>';
            return;
        }
        
        // اگر رمز جدید وارد شده باشد
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                echo '<script>alert("رمز عبور جدید با تکرار آن مطابقت ندارد");</script>';
                return;
            }
            
            // بررسی رمز فعلی
            $user_sql = "SELECT password FROM users WHERE id = ?";
            $user_stmt = $pdo->prepare($user_sql);
            $user_stmt->execute([$user_id]);
            $user = $user_stmt->fetch();
            
            if (!password_verify($current_password, $user['password'])) {
                echo '<script>alert("رمز عبور فعلی اشتباه است");</script>';
                return;
            }
            
            // آپدیت با رمز جدید
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET full_name = ?, email = ?, username = ?, password = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $email, $username, $hashed_password, $user_id]);
        } else {
            // آپدیت بدون تغییر رمز
            $sql = "UPDATE users SET full_name = ?, email = ?, username = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $email, $username, $user_id]);
        }
        
        // آپدیت session
        $_SESSION['full_name'] = $full_name;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        echo '<script>alert("اطلاعات با موفقیت به‌روزرسانی شد"); window.location.href="index.php?page=profile";</script>';
    } catch (Exception $e) {
        error_log("خطا در به‌روزرسانی پروفایل: " . $e->getMessage());
        echo '<script>alert("خطا در به‌روزرسانی اطلاعات");</script>';
    }
}

function handleAdminAddUser($pdo) {
    if (getUserRole() !== 'admin') {
        redirect('index.php');
    }
    
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'student');
    $status = sanitize($_POST['status'] ?? 'active');
    
    if (strlen($password) < 6) {
        echo '<script>alert("رمز عبور باید حداقل ۶ کاراکتر باشد");</script>';
        return;
    }
    
    try {
        // بررسی تکراری نبودن
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$username, $email]);
        
        if ($check_stmt->fetch()) {
            echo '<script>alert("نام کاربری یا ایمیل قبلا ثبت شده است");</script>';
            return;
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (full_name, username, email, password, role, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $username, $email, $hashed_password, $role, $status]);
        
        echo '<script>alert("کاربر با موفقیت ایجاد شد"); window.location.href="index.php?page=admin";</script>';
    } catch (Exception $e) {
        error_log("خطا در ایجاد کاربر: " . $e->getMessage());
        echo '<script>alert("خطا در ایجاد کاربر");</script>';
    }
}

function logout() {
    session_destroy();
    redirect('index.php');
}

// هندل کردن عملیات GET
if ($action) {
    switch ($action) {
        case 'delete':
            handleDelete($pdo);
            break;
        case 'change_status':
            handleChangeStatus($pdo);
            break;
        case 'toggle_user_status':
            handleToggleUserStatus($pdo);
            break;
    }
}

function handleDelete($pdo) {
    $id = $_GET['id'] ?? 0;
    $type = $_GET['type'] ?? '';
    
    if (!isLoggedIn()) {
        redirect('index.php');
    }
    
    try {
        if ($type === 'todo') {
            // بررسی مالکیت کار (مگر اینکه ادمین باشد)
            $check_sql = "SELECT user_id FROM todos WHERE id = ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$id]);
            $todo = $check_stmt->fetch();
            
            if ($todo && (getUserRole() === 'admin' || $todo['user_id'] == $_SESSION['user_id'])) {
                $sql = "DELETE FROM todos WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
            }
        } elseif ($type === 'user' && getUserRole() === 'admin') {
            // فقط ادمین می‌تواند کاربر حذف کند
            $sql = "DELETE FROM users WHERE id = ? AND id != ?"; // نمی‌توان خودش را حذف کند
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id, $_SESSION['user_id']]);
        }
        
        // برگشت به صفحه قبلی
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        redirect($referer);
    } catch (Exception $e) {
        error_log("خطا در حذف: " . $e->getMessage());
        echo '<script>alert("خطا در حذف");</script>';
    }
}

function handleChangeStatus($pdo) {
    $id = $_GET['id'] ?? 0;
    $status = $_GET['status'] ?? '';
    
    if (!isLoggedIn()) {
        redirect('index.php');
    }
    
    try {
        // بررسی مالکیت کار
        $check_sql = "SELECT user_id FROM todos WHERE id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$id]);
        $todo = $check_stmt->fetch();
        
        if ($todo && (getUserRole() === 'admin' || $todo['user_id'] == $_SESSION['user_id'])) {
            $sql = "UPDATE todos SET status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status, $id]);
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        redirect($referer);
    } catch (Exception $e) {
        error_log("خطا در تغییر وضعیت: " . $e->getMessage());
        echo '<script>alert("خطا در تغییر وضعیت");</script>';
    }
}

function handleToggleUserStatus($pdo) {
    if (getUserRole() !== 'admin') {
        redirect('index.php');
    }
    
    $id = $_GET['id'] ?? 0;
    $status = $_GET['status'] ?? 'active';
    
    try {
        $sql = "UPDATE users SET status = ? WHERE id = ? AND id != ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $id, $_SESSION['user_id']]);
        
        redirect('index.php?page=admin');
    } catch (Exception $e) {
        error_log("خطا در تغییر وضعیت کاربر: " . $e->getMessage());
        echo '<script>alert("خطا در تغییر وضعیت کاربر");</script>';
    }
}
?>
