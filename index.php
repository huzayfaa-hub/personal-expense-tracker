<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Expense Tracker Pro - Financial Intelligence</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        /* 🎨 PREMIUM DARK OLIVE BASE WITH DYNAMIC PARALLAX GRADIENT */
        body { 
            background: linear-gradient(135deg, #0A0F08, #162412, #070D05); 
            min-height: 100vh; 
            color: #F4F6F0; 
            overflow-x: hidden; 
            position: relative; 
            display: flex; 
            flex-direction: column; 
        }
        
        /* Textured Grid Overlay */
        body::before { 
            content: ""; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(rgba(212, 175, 55, 0.03) 1.5px, transparent 0);
            background-size: 32px 32px; z-index: 1; pointer-events: none;
        }

        /* 👑 DYNAMIC FLOATING 3D DOLLAR PARTICLES BACKGROUND SYSTEM */
        .dollar-bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .floating-dollar {
            position: absolute;
            color: rgba(212, 175, 55, 0.15); /* Semi-transparent Gold */
            font-size: 3rem;
            font-weight: 800;
            font-family: 'Poppins', sans-serif;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.4), 0 0 40px rgba(212, 175, 55, 0.2);
            animation: floatAndRotate 18s linear infinite;
            user-select: none;
        }

        @keyframes floatAndRotate {
            0% {
                transform: translateY(105vh) translateX(0) rotate(0deg) scale(0.6);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-10vh) translateX(80px) rotate(360deg) scale(1.2);
                opacity: 0;
            }
        }

        /* Distributing dollars with different speeds, sizes, and delays for 3D depth effect */
        .floating-dollar:nth-child(1) { left: 8%; animation-duration: 22s; animation-delay: 0s; font-size: 2.5rem; filter: blur(1px); }
        .floating-dollar:nth-child(2) { left: 25%; animation-duration: 18s; animation-delay: 3s; font-size: 4rem; filter: blur(3px); color: rgba(212, 175, 55, 0.08); }
        .floating-dollar:nth-child(3) { left: 45%; animation-duration: 25s; animation-delay: 1s; font-size: 2rem; }
        .floating-dollar:nth-child(4) { left: 65%; animation-duration: 16s; animation-delay: 5s; font-size: 5rem; filter: blur(4px); color: rgba(212, 175, 55, 0.05); }
        .floating-dollar:nth-child(5) { left: 85%; animation-duration: 20s; animation-delay: 2s; font-size: 3.5rem; filter: blur(1px); }
        .floating-dollar:nth-child(6) { left: 15%; animation-duration: 24s; animation-delay: 7s; font-size: 3rem; }
        .floating-dollar:nth-child(7) { left: 55%; animation-duration: 19s; animation-delay: 9s; font-size: 4.5rem; filter: blur(2px); }
        .floating-dollar:nth-child(8) { left: 78%; animation-duration: 26s; animation-delay: 4s; font-size: 2.2rem; }

        /* Navbar */
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 25px 80px; background: rgba(10, 15, 8, 0.75); 
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-bottom: 2px solid rgba(212, 175, 55, 0.25); 
            position: sticky; top: 0; z-index: 100; 
        }
        
        .custom-brand-logo { display: flex; align-items: center; gap: 14px; cursor: pointer; text-decoration: none; }
        .cropped-logo-icon {
            width: 44px; height: 44px; border-radius: 50%; overflow: hidden; border: 2px solid #D4AF37;
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.5); background-color: #0F2005; display: flex; align-items: center; justify-content: center;
        }
        .cropped-logo-icon img { width: 100%; height: 100%; object-fit: cover; }
        .brand-titles { display: flex; flex-direction: column; line-height: 1.15; text-align: left; }
        .main-title { font-size: 15px; font-weight: 800; color: #D4AF37 !important; letter-spacing: 0.5px; }
        .sub-title { font-size: 10px; font-weight: 600; color: #FFFFFF !important; letter-spacing: 3px; }
        
        .nav-buttons { display: flex; gap: 25px; align-items: center; }
        .nav-link { color: #CBD5E1; text-decoration: none; font-weight: 600; transition: 0.3s; font-size: 14px; }
        .nav-link:hover { color: #D4AF37; text-shadow: 0 0 8px rgba(212, 175, 55, 0.5); }
        .login-btn { background: rgba(212, 175, 55, 0.1); border: 1.5px solid #D4AF37; color: #D4AF37; padding: 10px 28px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; }
        .login-btn:hover { background: #D4AF37; color: black; transform: translateY(-2px); box-shadow: 0 5px 20px rgba(212, 175, 55, 0.5); }

        /* Hero Section */
        .hero { width: 85%; max-width: 1400px; margin: 80px auto; display: flex; align-items: center; justify-content: space-between; gap: 60px; flex: 1; position: relative; z-index: 5; }
        .hero-text { flex: 1.1; }
        .badge { background: rgba(212, 175, 55, 0.15); color: #D4AF37; padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 700; display: inline-block; margin-bottom: 25px; border: 1.5px solid #D4AF37; box-shadow: 0 0 15px rgba(212, 175, 55, 0.2); }
        .hero-text h1 { font-size: 52px; font-weight: 800; line-height: 1.25; margin-bottom: 25px; color: #ffffff; text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .hero-text h1 span { color: #D4AF37; text-shadow: 0 0 20px rgba(212, 175, 55, 0.4); }
        .hero-text p { color: #E2E8F0; font-size: 16px; line-height: 1.8; margin-bottom: 40px; font-weight: 400; text-shadow: 1px 1px 3px rgba(0,0,0,0.6); }
        
        .cta-container { display: flex; gap: 20px; flex-wrap: wrap; }
        .primary-btn { background: #D4AF37; color: #000000; padding: 15px 38px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 16px; transition: 0.3s; box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4); border: 2px solid transparent; }
        .primary-btn:hover { background: white; color: black; transform: translateY(-3px); box-shadow: 0 10px 25px rgba(255,255,255,0.3); }
        .secondary-btn { border: 2px solid rgba(212, 175, 55, 0.4); background: rgba(10, 15, 8, 0.6); color: white; padding: 15px 38px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 16px; transition: 0.3s; backdrop-filter: blur(5px); }
        .secondary-btn:hover { background: rgba(212, 175, 55, 0.15); transform: translateY(-2px); border-color: #D4AF37; box-shadow: 0 0 15px rgba(212, 175, 55, 0.2); }

        /* Auto-Scrolling Slider Card */
        .hero-visual { flex: 0.9; display: flex; justify-content: center; position: relative; }
        .visual-card { 
            width: 100%; 
            max-width: 420px; 
            background: #D4AF37; 
            border: 2.5px solid #FFFFFF; 
            padding: 30px 35px; 
            border-radius: 28px; 
            box-shadow: 0 30px 60px rgba(0,0,0,0.6), 0 0 30px rgba(212, 175, 55, 0.3); 
            overflow: hidden;
            height: 340px;
            position: relative;
        }

        .visual-card h3 { color: #0F2005 !important; font-weight: 800; font-size: 18px; position: relative; z-index: 5; background: #D4AF37; padding-bottom: 12px; margin-bottom: 15px; border-bottom: 1px solid rgba(15, 32, 5, 0.15); }
        
        .slider-window { height: 230px; overflow: hidden; position: relative; }
        .slider-track { display: flex; flex-direction: column; animation: scrollUp 14s linear infinite; }
        .slider-window:hover .slider-track { animation-play-state: paused; }

        @keyframes scrollUp {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }

        .visual-row { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid rgba(15, 32, 5, 0.1); height: 76px; }
        .visual-row i { font-size: 18px; color: #D4AF37; background: #0F2005; padding: 10px; border-radius: 50%; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; }
        .visual-info h4 { font-size: 14px; font-weight: 700; color: #0F2005; }
        .visual-info p { font-size: 11px; color: #2C471B; font-weight: 600; }
        .visual-amount { font-weight: 800; color: #b30000; font-size: 14px; text-align: right; }
        .visual-amount.plus { color: #0F2005; }

        /* Why Use Section */
        .features-section { width: 85%; max-width: 1400px; margin: 100px auto; text-align: center; position: relative; z-index: 5; }
        .features-section h2 { font-size: 36px; font-weight: 700; margin-bottom: 60px; color: #ffffff; text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 35px; }
        
        /* 🎨 RE-ENGINEERED HIGH-CONTRAST MULTICOLOR CARDS */
        .f-card { 
            padding: 45px 35px; 
            border-radius: 24px; 
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            text-align: left; 
            border: 2px solid #FFFFFF;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }
        .f-card h3 { font-size: 22px; font-weight: 800; margin-bottom: 14px; color: #0F2005 !important; }
        .f-card p { font-size: 14px; color: #1C2D16 !important; line-height: 1.7; font-weight: 600; }
        .f-card i { font-size: 32px; color: #0F2005 !important; margin-bottom: 22px; display: block; }

        /* Card 1: Mint Green */
        .card-mint { background: #A2E3B8; }
        .card-mint:hover { transform: translateY(-10px); background: #B5F1CB; box-shadow: 0 25px 45px rgba(162, 227, 184, 0.35); }

        /* Card 2: Signature Gold */
        .card-gold { background: #D4AF37; }
        .card-gold:hover { transform: translateY(-10px); background: #E8C654; box-shadow: 0 25px 45px rgba(212, 175, 55, 0.35); }

        /* Card 3: Warm Amber Orange */
        .card-amber { background: #FFC085; }
        .card-amber:hover { transform: translateY(-10px); background: #FFD2A8; box-shadow: 0 25px 45px rgba(255, 192, 133, 0.35); }

        /* How It Works Section */
        .steps-section { width: 85%; max-width: 1400px; margin: 120px auto; text-align: center; position: relative; z-index: 5; }
        .steps-section h2 { font-size: 36px; font-weight: 700; margin-bottom: 60px; color: #ffffff; text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .steps-grid { display: flex; justify-content: space-between; gap: 35px; }
        
        .step-card { 
            flex: 1; 
            border: 2px solid #FFFFFF; 
            padding: 40px 35px; 
            border-radius: 24px; 
            position: relative; 
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }
        .step-number { position: absolute; top: -18px; left: 25px; background: #0F2005; color: #FFFFFF !important; font-weight: 800; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 2px solid #FFFFFF; box-shadow: 0 4px 10px rgba(0,0,0,0.4); }
        .step-card i { font-size: 32px; color: #0F2005 !important; margin: 15px 0; display: block; }
        .step-card h3 { font-size: 20px; margin-bottom: 12px; color: #0F2005 !important; font-weight: 800; }
        .step-card p { font-size: 14px; color: #1C2D16 !important; line-height: 1.6; font-weight: 600; }

        .step-card:hover { transform: translateY(-8px); }
        .step-card.card-mint:hover { background: #B5F1CB; }
        .step-card.card-gold:hover { background: #E8C654; }
        .step-card.card-amber:hover { background: #FFD2A8; }

        /* FAQ Section */
        .faq-section { width: 60%; max-width: 900px; margin: 120px auto 150px auto; text-align: center; position: relative; z-index: 5; }
        .faq-section h2 { font-size: 36px; font-weight: 700; margin-bottom: 50px; color: #ffffff; text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .faq-container { display: flex; flex-direction: column; gap: 18px; text-align: left; }
        
        .faq-item { 
            background: #D4AF37; 
            border: 2.5px solid #FFFFFF; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            transition: all 0.3s ease; 
        }
        .faq-item:hover { background: #E8C654; }
        .faq-question { padding: 22px 25px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; font-weight: 800; font-size: 16px; color: #0F2005; user-select: none; }
        
        .faq-answer { 
            max-height: 0; padding: 0 25px; color: #1C2D16; font-size: 14px; line-height: 1.7; overflow: hidden; font-weight: 600;
            transition: max-height 0.3s ease, padding 0.3s ease; 
        }
        .faq-item.active { background: #D4AF37; border-color: #0F2005; box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
        .faq-item.active .faq-answer { max-height: 200px; padding: 0 25px 22px 25px; }
        .faq-item.active .faq-question i { transform: rotate(180deg); color: #0F2005; }
        .faq-question i { transition: transform 0.3s ease; color: #0F2005; }

        /* Back to Top Arrow */
        .back-to-top-btn {
            position: fixed; bottom: 30px; right: 30px; width: 48px; height: 48px; 
            background: rgba(10, 15, 8, 0.85); backdrop-filter: blur(10px); 
            border: 2px solid #D4AF37; color: #D4AF37; 
            border-radius: 50%; font-size: 16px; cursor: pointer; 
            display: flex; align-items: center; justify-content: center; z-index: 1000; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; visibility: hidden; transform: translateY(20px);
        }
        .back-to-top-btn:hover { background: #D4AF37; color: #000000; transform: translateY(-5px); box-shadow: 0 10px 20px rgba(212, 175, 55, 0.4); border-color: white; }
        .back-to-top-btn.show { opacity: 1; visibility: visible; transform: translateY(0); }

        /* Footer */
        .footer { background: rgba(10, 15, 8, 0.85); backdrop-filter: blur(20px); border-top: 2px solid #D4AF37; padding: 70px 80px 30px 80px; margin-top: auto; position: relative; z-index: 5; }
        .footer-main { display: grid; grid-template-columns: 2fr 1fr 2fr; gap: 50px; text-align: left; }
        
        .footer-brand p { font-size: 14px; color: #E2E8F0; line-height: 1.7; margin-bottom: 20px; margin-top: 15px; }
        .footer-links h4, .footer-newsletter h4 { font-size: 16px; font-weight: 700; color: #ffffff; margin-bottom: 20px; letter-spacing: 0.5px; }
        .footer-links ul { list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .footer-links a { color: #E2E8F0; text-decoration: none; font-size: 14px; font-weight: 500; transition: 0.3s; display: inline-block; }
        .footer-links a:hover { color: #D4AF37; transform: translateX(5px); }
        .footer-newsletter p { font-size: 14px; color: #E2E8F0; margin-bottom: 15px; line-height: 1.6; }

        /* Newsletter Form */
        .subscribe-form { display: flex; gap: 10px; background: rgba(0, 0, 0, 0.4); padding: 6px; border-radius: 30px; border: 1.5px solid rgba(212, 175, 55, 0.3); transition: 0.3s; }
        .subscribe-form:focus-within { border-color: #D4AF37; box-shadow: 0 0 15px rgba(212, 175, 55, 0.2); }
        .subscribe-form input { flex: 1; background: transparent; border: none; outline: none; padding-left: 15px; color: white; font-size: 14px; }
        .subscribe-form input::placeholder { color: rgba(255, 255, 255, 0.4); }
        .subscribe-form button { background: #D4AF37; color: black; border: none; padding: 10px 24px; border-radius: 25px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.3s; }
        .subscribe-form button:hover { background: white; color: black; }

        .footer-bottom { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(212, 175, 55, 0.15); padding-top: 25px; margin-top: 40px; }
        .footer-bottom p { font-size: 13px; color: #A4B29E; font-weight: 500; }
        .social-icons { display: flex; gap: 12px; }
        .social-icons a { color: #ffffff; background: rgba(212, 175, 55, 0.08); width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1.5px solid rgba(212, 175, 55, 0.2); text-decoration: none; transition: 0.3s ease; font-size: 14px; }
        .social-icons a:hover { background: #D4AF37; color: black; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4); }

        @media (max-width: 1024px) {
            .navbar { padding: 25px 40px; }
            .hero { width: 90%; gap: 40px; }
            .footer { padding: 50px 40px; }
        }

        @media (max-width: 768px) {
            .navbar { padding: 20px; flex-direction: column; gap: 15px; text-align: center; }
            .footer-main { grid-template-columns: 1fr; gap: 30px; }
            .footer-bottom { flex-direction: column; gap: 15px; text-align: center; }
            .steps-grid { flex-direction: column; gap: 40px; }
            .hero { flex-direction: column; text-align: center; margin-top: 40px; gap: 40px; }
            .cta-container { justify-content: center; }
            .faq-section { width: 85%; }
        }
    </style>
</head>
<body>

<!-- ✨ 3D FLOATING DOLLAR BG PARTICLES LAYER -->
<div class="dollar-bg-container">
    <div class="floating-dollar">$</div>
    <div class="floating-dollar">$</div>
    <div class="floating-dollar">$</div>
    <div class="floating-dollar">$</div>
    <div class="floating-dollar">$</div>
    <div class="floating-dollar">$</div>
    <div class="floating-dollar">$</div>
    <div class="floating-dollar">$</div>
</div>

<div class="navbar">
    <a href="#" class="custom-brand-logo">
        <div class="cropped-logo-icon">
            <img src="logo.png" alt="Icon">
        </div>
        <div class="brand-titles">
            <span class="main-title">PERSONAL EXPENSE</span>
            <span class="sub-title">TRACKER PRO</span>
        </div>
    </a>
    
    <div class="nav-buttons">
        <a href="#features" class="nav-link">Features</a>
        <a href="login.php" class="login-btn">Sign In</a>
    </div>
</div>

<div class="hero">
    <div class="hero-text">
        <span class="badge"><i class="fas fa-sparkles"></i> Enterprise Finance Vault</span>
        <h1>Take Control of Your <span>Daily Expenses</span> Smartly.</h1>
        <p>Stop wondering where your cash disappears! Dynamic tracking, custom category typing, real-time currency conversions, and interactive sentry budget guards — all encapsulated in one premium glassmorphic ecosystem.</p>
        <div class="cta-container">
            <a href="register.php" class="primary-btn">Get Started Free</a>
            <a href="login.php" class="secondary-btn">Already a Member?</a>
        </div>
    </div>

    <div class="hero-visual">
        <div class="visual-card">
            <h3><i class="fas fa-bolt"></i> Real-time Activities</h3>
            
            <div class="slider-window">
                <div class="slider-track">
                    
                    <div class="visual-row">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fas fa-shopping-basket"></i>
                            <div class="visual-info">
                                <h4>Grocery & Bread</h4>
                                <p>Today, 12:30 PM</p>
                            </div>
                        </div>
                        <div class="visual-amount">- Rs. 450</div>
                    </div>

                    <div class="visual-row">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fas fa-globe"></i>
                            <div class="visual-info">
                                <h4>USD Dollar Conversion</h4>
                                <p>Live Rate Converter</p>
                            </div>
                        </div>
                        <div class="visual-amount plus">+ $100 USD</div>
                    </div>

                    <div class="visual-row">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fas fa-gas-pump"></i>
                            <div class="visual-info">
                                <h4>Fuel Refill</h4>
                                <p>Yesterday</p>
                            </div>
                        </div>
                        <div class="visual-amount">- Rs. 2,200</div>
                    </div>

                    <div class="visual-row">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fas fa-hamburger"></i>
                            <div class="visual-info">
                                <h4>Zinger Burger Meal</h4>
                                <p>2 Days ago</p>
                            </div>
                        </div>
                        <div class="visual-amount">- Rs. 680</div>
                    </div>

                    <div class="visual-row">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fas fa-shield-alt"></i>
                            <div class="visual-info">
                                <h4>Budget Guard Active</h4>
                                <p>Threshold Sentry</p>
                            </div>
                        </div>
                        <div class="visual-amount plus">Secure</div>
                    </div>

                    <div class="visual-row">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fas fa-plug"></i>
                            <div class="visual-info">
                                <h4>Electricity Bill</h4>
                                <p>This Week</p>
                            </div>
                        </div>
                        <div class="visual-amount">- Rs. 8,450</div>
                    </div>

                    <div class="visual-row">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fas fa-arrow-trend-up"></i>
                            <div class="visual-info">
                                <h4>Monthly Savings State</h4>
                                <p>Calculated Live</p>
                            </div>
                        </div>
                        <div class="visual-amount plus">+ 22%</div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<div class="features-section" id="features">
    <h2>Why Choose Our Expense Tracker?</h2>
    <div class="features-grid">
        <div class="f-card card-mint">
            <i class="fas fa-globe"></i>
            <h3>Dynamic Currency Converter</h3>
            <p>Perform dynamic currency calculations directly inside your dashboard with active real-world exchange rate API data.</p>
        </div>
        <div class="f-card card-gold">
            <i class="fas fa-shield-alt"></i>
            <h3>Sentry Budget Sentry</h3>
            <p>Get instant color-coded warning alarms and dynamic progress bars as soon as you cross safe spending threshold zones.</p>
        </div>
        <div class="f-card card-amber">
            <i class="fas fa-keyboard"></i>
            <h3>Custom Dynamic Categories</h3>
            <p>No more dropdown constraints! Select standard categories or freely type your custom categories on the fly.</p>
        </div>
    </div>
</div>

<div class="steps-section">
    <h2>Get Started In 3 Easy Steps</h2>
    <div class="steps-grid">
        <div class="step-card card-mint">
            <div class="step-number">1</div>
            <i class="fas fa-user-plus"></i>
            <h3>Create Profile</h3>
            <p>Sign up free with your credentials to instantiate your private, cloud-secured financial ledger.</p>
        </div>
        <div class="step-card card-gold">
            <div class="step-number">2</div>
            <i class="fas fa-pen-nib"></i>
            <h3>Log Purchases</h3>
            <p>Log grocery receipts, currency rates, bills and select or custom-type categories instantly.</p>
        </div>
        <div class="step-card card-amber">
            <div class="step-number">3</div>
            <i class="fas fa-chart-line"></i>
            <h3>Audit Leakages</h3>
            <p>Monitor high-contrast charts, dynamic alerts, and export PDF sheets to optimize your financial habits.</p>
        </div>
    </div>
</div>

<div class="faq-section">
    <h2>Frequently Asked Questions</h2>
    <div class="faq-container">
        
        <div class="faq-item">
            <div class="faq-question">
                <span>Is this Personal Expense Tracker free to use?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Yes! This premium system is 100% free to organize your income, evaluate expenses, and secure parameters with absolute zero cost.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>How does the dynamic currency converter work?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                It utilizes an active currency API network to fetch live exchange rates (PKR, USD, EUR, GBP, AED, SAR) and processes instant offline fallback calculations during connectivity gaps.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>Can I enter custom categories?</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Absolutely. The hybrid input field on your dashboard lets you select pre-set categories or freely type any custom tags you want.
            </div>
        </div>

    </div>
</div>

<footer class="footer">
    <div class="footer-main">
        <div class="footer-brand">
            <div class="custom-brand-logo">
                <div class="cropped-logo-icon">
                    <img src="logo.png" alt="Icon">
                </div>
                <div class="brand-titles">
                    <span class="main-title">PERSONAL EXPENSE</span>
                    <span class="sub-title">TRACKER PRO</span>
                </div>
            </div>
            <p>A smart, seamless, and automated platform designed to organize your daily transactions, audit leaks, and protect thresholds effortlessly.</p>
        </div>

        <div class="footer-links">
            <h4>Platform</h4>
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="login.php">Sign In</a></li>
                <li><a href="register.php">Create Account</a></li>
            </ul>
        </div>

        <div class="footer-newsletter">
            <h4>Stay Updated</h4>
            <p>Subscribe to receive weekly smart financial advice, security upgrades, and dynamic logs.</p>
            <div class="subscribe-form">
                <input type="email" placeholder="Enter your email...">
                <button type="button">Subscribe</button>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2026 Personal Expense Tracker Pro • Crafted for Financial Excellence.</p>
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-github"></i></a>
        </div>
    </div>
</footer>

<button id="backToTop" class="back-to-top-btn" title="Go to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
    // Accordion Logic
    document.querySelectorAll('.faq-question').forEach(item => {
        item.addEventListener('click', () => {
            const parent = item.parentElement;
            const isActive = parent.classList.contains('active');
            
            document.querySelectorAll('.faq-item').forEach(child => {
                child.classList.remove('active');
            });

            if (!isActive) {
                parent.classList.add('active');
            }
        });
    });

    // Back to top button triggers
    const backToTopBtn = document.getElementById("backToTop");
    window.addEventListener("scroll", () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add("show");
        } else {
            backToTopBtn.classList.remove("show");
        }
    });

    backToTopBtn.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
</script>

</body>
</html>