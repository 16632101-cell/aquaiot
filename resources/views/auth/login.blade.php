<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Login - Aquarium IoT</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 350px; }
        .login-container h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #666; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { width: 100%; padding: 10px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-submit:hover { background-color: #27ae60; }
        .error-msg { color: #e74c3c; font-size: 14px; margin-bottom: 10px; text-align: center; }
        .success-msg { color: #2ecc71; font-size: 14px; margin-bottom: 10px; text-align: center; }
        .register-link { display: block; text-align: center; margin-top: 15px; color: #3498db; text-decoration: none; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>เข้าสู่ระบบควบคุมสภาพน้ำ</h2>
        
        @if(session('success')) <div class="success-msg">{{ session('success') }}</div> @endif
        @if($errors->any()) <div class="error-msg">{{ $errors->first() }}</div> @endif
        
        <form action="/login" method="POST">
            @csrf
            <div class="form-group">
                <label>Username:</label> 
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label> 
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-submit">Login</button>
        </form>
        
        <a href="/register" class="register-link">สร้างบัญชีผู้ใช้ใหม่</a>
    </div>

</body>
</html>