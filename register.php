<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "grocery_tracker");
$msg = "";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "<div class='error-msg' style='color:#ef4444;'><i class='fas fa-exclamation-circle'></i> Email already registered!</div>";
    } else {
        $query = "INSERT INTO users (name, email, password) VALUES ('$username', '$email', '$password')";
        if (mysqli_query($conn, $query)) {
            $msg = "<div class='error-msg' style='color:#D4AF37;'><i class='fas fa-check-circle'></i> Registered successfully! <a href='login.php' style='color:white;text-decoration:underline;'>Login now</a></div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Expense Tracker</title>
    
    <!-- 👑 TAB LOGO FAVICON ADDED HERE -->
    <link rel="icon" type="image/png" href="logo.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* CSS RESET */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        /* 🌿 ABSOLUTE 100% SCREEN VIEWPORT CENTER */
        html, body {
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 0;
            overflow: hidden; 
        }

        body { 
            background: linear-gradient(135deg, #050A03, #162412, #0B1209); 
            display: flex;
            justify-content: center; 
            align-items: center;     
            position: relative;
        }

        /* 👑 CELESTIAL ABSTRACT PARTICLES MATCHED WITH LOGIN */
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
        .matrix-spark:nth-child(1) { left: 8%; animation-duration: 17s; font-size: 1.4rem; }
        .matrix-spark:nth-child(2) { left: 24%; animation-duration: 13s; animation-delay: 2s; font-size: 2rem; filter: blur(1px); }
        .matrix-spark:nth-child(3) { left: 40%; animation-duration: 20s; animation-delay: 4s; font-size: 1.1rem; }
        .matrix-spark:nth-child(4) { left: 60%; animation-duration: 15s; animation-delay: 1s; font-size: 2.4rem; filter: blur(2px); }
        .matrix-spark:nth-child(5) { left: 78%; animation-duration: 18s; animation-delay: 3s; font-size: 1.7rem; }
        .matrix-spark:nth-child(6) { left: 90%; animation-duration: 14s; animation-delay: 6s; font-size: 1.2rem; }

        /* 🌟 PERFECTLY PROPORTIONED CARD */
        .auth-container { 
            width: 100%;
            max-width: 420px; 
            background: #0D140B; 
            padding: 40px; 
            border-radius: 24px; 
            border: 2px solid rgba(212, 175, 55, 0.25); 
            box-shadow: 0 30px 60px rgba(0,0,0,0.6), 0 0 30px rgba(212, 175, 55, 0.15); 
            text-align: center; 
            transition: all 0.3s ease; 
            position: relative;
            z-index: 5;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        
        .auth-container:hover { 
            border-color: #D4AF37; 
            box-shadow: 0 30px 65px rgba(0,0,0,0.7), 0 0 35px rgba(212, 175, 55, 0.3); 
        }
        
        /* LOGO ICON GLOW */
        .logo-icon { 
            font-size: 38px; 
            color: #D4AF37; 
            margin-bottom: 15px; 
            filter: drop-shadow(0 0 8px rgba(212, 175, 55, 0.35)); 
        }
        
        h2 { font-size: 24px; font-weight: 700; margin-bottom: 6px; color: #FFFFFF; }
        p.subtitle { color: #CBD5E1; font-size: 13px; margin-bottom: 28px; font-weight: 500; }
        
        /* FORM GROUPS WITH BALANCED ALIGNMENT */
        .form-group { margin-bottom: 20px; text-align: left; position: relative; }
        .form-group label { font-size: 13px; font-weight: 700; color: #D4AF37; margin-bottom: 8px; display: block; }
        
        .form-group input { 
            width: 100%; 
            padding: 12px 18px 12px 45px; 
            background: rgba(0,0,0,0.4); 
            border: 2px solid rgba(212, 175, 55, 0.2); 
            border-radius: 12px; 
            color: white; 
            font-size: 14px; 
            font-weight: 600;
            outline: none; 
            transition: all 0.3s ease; 
        }
        
        .form-group i { 
            position: absolute; 
            left: 16px; 
            top: 43px; 
            color: #CBD5E1; 
            transition: 0.3s; 
            font-size: 15px;
        }
        
        .form-group input::placeholder { color: rgba(255, 255, 255, 0.3); }
        .form-group input:hover { border-color: rgba(212, 175, 55, 0.4); }
        .form-group input:focus { border-color: #D4AF37; box-shadow: 0 0 12px rgba(212, 175, 55, 0.3); background: rgba(0,0,0,0.6); }
        .form-group input:focus + i { color: #D4AF37; text-shadow: 0 0 5px rgba(212, 175, 55, 0.4); }

        .error-msg { font-size: 13px; margin-bottom: 15px; font-weight: 600; text-align: left; display: flex; align-items: center; gap: 6px; }
        
        /* SUBMIT BUTTON WITH PREMIUM GOLD GLOW */
        .submit-btn { 
            width: 100%; padding: 13px; 
            background: #0F2005; 
            border: 2px solid #D4AF37; 
            border-radius: 30px; color: #D4AF37; 
            font-size: 15px; font-weight: 700; cursor: pointer; 
            transition: all 0.4s ease; margin-top: 10px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }
        .submit-btn:hover { 
            background: #D4AF37; color: #000000; 
            transform: translateY(-2px); 
            box-shadow: 0 8px 22px rgba(212, 175, 55, 0.4); 
            border-color: #FFFFFF;
        }
        
        .auth-footer { margin-top: 25px; font-size: 13px; color: #CBD5E1; font-weight: 500; }
        .auth-footer a { color: #D4AF37; text-decoration: none; font-weight: 700; transition: 0.2s; }
        .auth-footer a:hover { text-decoration: underline; color: #FFF; text-shadow: 0 0 8px rgba(212, 175, 55, 0.5); }
    </style>
</head>
<body>

<!-- 🌌 LAYER FOR ABSTRACT SPARKS AND STARS MATCHED WITH LOGIN -->
<div class="login-matrix-portal">
    <div class="matrix-spark">✦</div>
    <div class="matrix-spark">★</div>
    <div class="matrix-spark">✦</div>
    <div class="matrix-spark">✦</div>
    <div class="matrix-spark">★</div>
    <div class="matrix-spark">✦</div>
</div>

<div class="auth-container">
    <div class="logo-icon"><i class="fas fa-user-plus"></i></div>
    <h2>Create Account</h2>
    <p class="subtitle">Join us to organize your tracks and daily budgets.</p>

    <?php echo $msg; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="username" required placeholder="Huzaifa">
            <i class="fas fa-user"></i>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="name@example.com">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">
            <i class="fas fa-lock"></i>
        </div>
        <button type="submit" name="register" class="submit-btn">Sign Up</button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="login.php">Sign In</a>
    </div>
</div>

</body>
</html>