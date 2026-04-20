<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Register - Aquarium IoT</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .register-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 350px; }
        .register-container h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #666; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { width: 100%; padding: 10px; background-color: #00bfff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-submit:hover { background-color: #009acd; }
        .error-msg { color: #e74c3c; font-size: 14px; margin-bottom: 10px; text-align: center; }
        .login-link { display: block; text-align: center; margin-top: 15px; color: #3498db; text-decoration: none; }
        /* แก้ไข: เพิ่ม note แจ้งให้ user รู้ว่า role เป็น user อัตโนมัติ */
        .role-note { font-size: 12px; color: #95a5a6; background: #ecf0f1; padding: 8px 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

    <div class="register-container">
        <h2>สร้างบัญชีผู้ใช้ใหม่</h2>

        @if($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        <form action="/register" method="POST">
            @csrf

            <div class="form-group">
                <label>Username (ชื่อผู้ใช้):</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Email (อีเมล):</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password (รหัสผ่าน 6 ตัวขึ้นไป):</label>
                <input type="password" name="password" required minlength="6">
            </div>

            {{-- แก้ไข: ลบ <select role> ออก ป้องกันไม่ให้ใครสมัครเป็น admin ได้เอง --}}
            {{-- Admin ต้องแก้ในฐานข้อมูลโดยตรง หรือผ่าน Admin Panel เท่านั้น --}}
            <div class="role-note">
                🔒 บัญชีใหม่ทุกบัญชีจะได้รับสิทธิ์ <strong>User</strong> โดยอัตโนมัติ<br>
                (ติดต่อ Admin เพื่อเพิ่มสิทธิ์การควบคุมอุปกรณ์)
            </div>

            <button type="submit" class="btn-submit">ลงทะเบียน</button>
        </form>

        <a href="/login" class="login-link">มีบัญชีอยู่แล้ว? กลับไปหน้าเข้าสู่ระบบ</a>
    </div>

</body>
</html>