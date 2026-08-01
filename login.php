<?php
session_start();

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "grocery_tracker"; 

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE name='$username' OR email='$username' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name']; 
            
            header("Location: dashboard.php"); 
            exit();
        } else {
            $error_message = "Invalid password!";
        }
    } else {
        $error_message = "User nahi mila!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Interactive Eye Tracker</title>
    
    <!-- 👑 TAB LOGO FAVICON ADDED HERE -->
    <link rel="icon" type="image/png" href="logo.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        /* 🌿 PREMIUM DEEP CYPRIAN GREEN & ABSTRACT PORTAL */
        body { 
            background: linear-gradient(135deg, #050A03, #162412, #0B1209); 
            min-height: 100vh; 
            color: #F4F6F0; 
            overflow: hidden; 
            position: relative; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            padding: 20px;
        }
        
        body::before { 
            content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(rgba(212, 175, 55, 0.04) 1.5px, transparent 0);
            background-size: 32px 32px; z-index: 1; pointer-events: none;
        }

        /* 👑 CELESTIAL ABSTRACT PARTICLES */
        .login-matrix-portal {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none; z-index: 0; overflow: hidden;
        }
        .matrix-spark {
            position: absolute;
            color: rgba(212, 175, 55, 0.15);
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.4);
            animation: cosmicTwinkleParallax 15s linear infinite;
            user-select: none;
        }
        @keyframes cosmicTwinkleParallax {
            0% { transform: translateY(105vh) rotate(0deg) scale(0.5); opacity: 0; }
            20% { opacity: 0.8; }
            80% { opacity: 0.8; }
            100% { transform: translateY(-10vh) rotate(180deg) scale(1.2); opacity: 0; }
        }
        .matrix-spark:nth-child(1) { left: 10%; animation-duration: 18s; font-size: 1.5rem; }
        .matrix-spark:nth-child(2) { left: 28%; animation-duration: 12s; animation-delay: 3s; font-size: 2.2rem; filter: blur(1px); }
        .matrix-spark:nth-child(3) { left: 45%; animation-duration: 22s; animation-delay: 1s; font-size: 1.2rem; }
        .matrix-spark:nth-child(4) { left: 65%; animation-duration: 14s; animation-delay: 5s; font-size: 2.5rem; filter: blur(2px); }
        .matrix-spark:nth-child(5) { left: 82%; animation-duration: 19s; animation-delay: 2s; font-size: 1.8rem; }
        .matrix-spark:nth-child(6) { left: 92%; animation-duration: 16s; animation-delay: 7s; font-size: 1.3rem; }

        /* 💰 SOLID GOLDEN CARD WITH AMBIENT BACKLIGHT GLOW */
        .login-container {
            width: 100%;
            max-width: 400px;
            background: #D4AF37; 
            border: 2px solid #FFFFFF;
            border-radius: 24px;
            padding: 35px 30px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.6), 0 0 40px rgba(212, 175, 55, 0.3);
            text-align: center;
            position: relative;
            z-index: 5;
        }

        .back-home {
            position: absolute;
            top: 25px;
            left: 25px;
            color: #0F2005;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }
        .back-home:hover { color: #000000; transform: translateX(-3px); }

        /* 👀 INTERACTIVE EYES CONTAINER */
        .eyes-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
            margin-top: 20px;
        }
        .eye {
            width: 50px;
            height: 50px;
            background: #FFFFFF;
            border-radius: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 3px 5px rgba(0,0,0,0.2), 0 4px 10px rgba(15, 32, 5, 0.2);
            border: 2px solid #0F2005;
        }
        .pupil {
            width: 20px;
            height: 20px;
            background: #0F2005;
            border-radius: 50%;
            position: absolute;
            left: calc(50% - 10px);
            top: calc(50% - 10px);
            transition: 0.05s ease-out;
        }
        .pupil::after {
            content: "";
            position: absolute;
            width: 5px;
            height: 5px;
            background: #FFFFFF;
            border-radius: 50%;
            top: 4px;
            left: 4px;
        }

        .login-header { margin-bottom: 20px; }
        .login-header h2 { font-size: 24px; font-weight: 800; color: #0F2005; margin-bottom: 6px; }
        .login-header p { font-size: 13px; color: #1C330E; font-weight: 600; }

        .error-alert {
            background: #721c24;
            color: #f8d7da;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }

        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #0F2005; margin-bottom: 6px; margin-left: 4px; }
        
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i { position: absolute; left: 16px; color: #243E16; font-size: 15px; }
        
        .input-wrapper input {
            width: 100%;
            padding: 13px 16px 13px 45px;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid transparent;
            border-radius: 12px;
            color: #0F2005;
            font-size: 14px;
            font-weight: 600;
            outline: none;
            transition: all 0.3s ease;
        }
        .input-wrapper input:focus {
            background: #FFFFFF;
            border-color: #0F2005;
            box-shadow: 0 0 12px rgba(15, 32, 5, 0.2);
        }
        .input-wrapper input::placeholder { color: rgba(15, 32, 5, 0.5); }

        .form-options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; font-size: 12px; }
        .remember-me { display: flex; align-items: center; gap: 6px; color: #0F2005; font-weight: 600; cursor: pointer; }
        .remember-me input { accent-color: #0F2005; cursor: pointer; }
        .forgot-link { color: #0F2005; text-decoration: none; font-weight: 700; }
        .forgot-link:hover { color: #000000; text-decoration: underline; }

        .submit-btn {
            width: 100%;
            background: #0F2005; 
            color: #D4AF37; 
            border: none;
            padding: 14px;
            border-radius: 35px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(15, 32, 5, 0.4);
        }
        .submit-btn:hover {
            background: #000000;
            color: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
        }

        .register-footer { margin-top: 22px; font-size: 13px; color: #1C330E; font-weight: 600; }
        .register-footer a { color: #0F2005; text-decoration: none; font-weight: 800; }
        .register-footer a:hover { color: #000000; text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-matrix-portal">
    <div class="matrix-spark">✦</div>
    <div class="matrix-spark">★</div>
    <div class="matrix-spark">✦</div>
    <div class="matrix-spark">✦</div>
    <div class="matrix-spark">★</div>
    <div class="matrix-spark">✦</div>
</div>

<div class="login-container">
    <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Home</a>

    <div class="eyes-container">
        <div class="eye">
            <div class="pupil"></div>
        </div>
        <div class="eye">
            <div class="pupil"></div>
        </div>
    </div>

    <div class="login-header">
        <h2>Welcome Back</h2>
        <p>Keep your eyes on your golden savings!</p>
    </div>

    <?php if(!empty($error_message)): ?>
        <div class="error-alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="text" id="username" name="username" placeholder="Enter username/email..." required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Enter password..." required>
            </div>
        </div>

        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <a href="#" class="forgot-link">Forgot Password?</a>
        </div>

        <button type="submit" class="submit-btn">Sign In Account</button>
    </form>

    <div class="register-footer">
        Don't have an account? <a href="register.php">Sign Up Free</a>
    </div>
</div>

<script>
    document.addEventListener('mousemove', (e) => {
        const pupils = document.querySelectorAll('.pupil');
        pupils.forEach(pupil => {
            const eye = pupil.parentElement;
            const eyeRect = eye.getBoundingClientRect();
            const eyeX = eyeRect.left + eyeRect.width / 2;
            const eyeY = eyeRect.top + eyeRect.height / 2;
            const angle = Math.atan2(e.clientY - eyeY, e.clientX - eyeX);
            const maxDistance = 12; 
            const moveX = Math.cos(angle) * maxDistance;
            const moveY = Math.sin(angle) * maxDistance;
            pupil.style.transform = `translate(${moveX}px, ${moveY}px)`;
        });
    });
</script>

</body>
</html>