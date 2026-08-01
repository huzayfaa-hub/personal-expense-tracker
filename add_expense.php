<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "grocery_tracker");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];
$message = "";

// Dynamic Limit Fetching to sync with dashboard alerts
$user_q = mysqli_query($conn, "SELECT monthly_budget, monthly_income FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$monthly_budget = $user_data['monthly_budget'] ?? 20000.00;
$monthly_income = $user_data['monthly_income'] ?? 50000.00;

if (isset($_POST['add_expense'])) {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $expense_date = mysqli_real_escape_string($conn, $_POST['expense_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $query = "INSERT INTO expenses (user_id, item_name, amount, category, expense_date, description) VALUES ('$user_id', '$item_name', '$amount', '$category', '$expense_date', '$description')";
    if (mysqli_query($conn, $query)) {
        $message = "<div class='alert success' id='alertBox'><i class='fas fa-check-circle'></i> Transaction Logged & Archived Successfully!</div>";
    } else {
        $message = "<div class='alert error' id='alertBox'><i class='fas fa-exclamation-circle'></i> Database Error: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Secure Entry | Personal Expense Tracker Pro</title>

<link rel="icon" type="image/png" href="logo.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    *{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; transition: background 0.4s ease, color 0.4s ease, box-shadow 0.4s ease, border-color 0.3s ease; }
    
    body { background-color: #0A0F08; min-height:100vh; color: #FAF8F5; display: flex; flex-direction: column; position: relative; overflow-x: hidden; }
    body::before { content: ""; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-image: radial-gradient(rgba(212, 175, 55, 0.03) 1.5px, transparent 0); background-size: 28px 24px; z-index: 1; pointer-events: none; }

    /* ==========================================================
       👑 3D FLOATING CURRENCY PORTAL BG (DASHBOARD HARMONY)
       ========================================================== */
    .currency-bg-portal {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        pointer-events: none;
        z-index: 0;
        overflow: hidden;
    }
    .floating-asset {
        position: absolute;
        color: rgba(212, 175, 55, 0.12); /* Balanced transparent gold */
        font-weight: 800;
        text-shadow: 0 0 15px rgba(212, 175, 55, 0.35), 0 0 35px rgba(212, 175, 55, 0.15);
        animation: ascendRotateParallax 22s linear infinite;
        user-select: none;
    }
    @keyframes ascendRotateParallax {
        0% {
            transform: translateY(105vh) translateX(0) rotate(0deg) scale(0.6);
            opacity: 0;
        }
        12% { opacity: 1; }
        88% { opacity: 1; }
        100% {
            transform: translateY(-10vh) translateX(100px) rotate(360deg) scale(1.3);
            opacity: 0;
        }
    }
    .floating-asset:nth-child(1) { left: 8%; animation-duration: 24s; animation-delay: 0s; font-size: 3rem; }
    .floating-asset:nth-child(2) { left: 22%; animation-duration: 19s; animation-delay: 3s; font-size: 4.5rem; filter: blur(3px); color: rgba(212, 175, 55, 0.06); }
    .floating-asset:nth-child(3) { left: 40%; animation-duration: 27s; animation-delay: 1s; font-size: 2.2rem; }
    .floating-asset:nth-child(4) { left: 60%; animation-duration: 17s; animation-delay: 5s; font-size: 5rem; filter: blur(4px); color: rgba(212, 175, 55, 0.04); }
    .floating-asset:nth-child(5) { left: 78%; animation-duration: 23s; animation-delay: 2s; font-size: 3.8rem; filter: blur(1px); }
    .floating-asset:nth-child(6) { left: 88%; animation-duration: 25s; animation-delay: 8s; font-size: 2.5rem; }

    /* 🎨 SYNCHRONIZED MULTI-THEMES (Direct Alignment with Dashboard Core) */
    @keyframes fairyLightRGB {
        0% { --glow-color: rgba(212, 175, 55, 0.4); --card-bg: #D4AF37; }
        33% { --glow-color: rgba(239, 68, 68, 0.4); --card-bg: #EF4444; }
        66% { --glow-color: rgba(59, 130, 246, 0.4); --card-bg: #3B82F6; }
        100% { --glow-color: rgba(212, 175, 55, 0.4); --card-bg: #D4AF37; }
    }

    @keyframes jugnuBreathe {
        0% { box-shadow: 0 15px 40px rgba(0,0,0,0.9), 0 0 12px rgba(163, 230, 53, 0.25); border-color: rgba(163, 230, 53, 0.35); }
        50% { box-shadow: 0 15px 45px rgba(0,0,0,0.95), 0 0 25px rgba(163, 230, 53, 0.6); border-color: rgba(163, 230, 53, 0.8); }
        100% { box-shadow: 0 15px 40px rgba(0,0,0,0.9), 0 0 12px rgba(163, 230, 53, 0.25); border-color: rgba(163, 230, 53, 0.35); }
    }

    body.card-mode-rosegold {
        animation: fairyLightRGB 8s linear infinite;
        --body-bg: linear-gradient(135deg, #050A03, #121A0F);
        --text-primary: #D4AF37; --glow-color: rgba(212, 175, 55, 0.3); --inner-bg: #0D140B; --border-subtle: rgba(212, 175, 55, 0.3); --input-bg: rgba(0,0,0,0.4);
    }
    body.card-mode-ivory {
        --body-bg: linear-gradient(135deg, #0A0D08, #151B11); --inner-bg: #1F2E19; --text-primary: #E2E8F0; --glow-color: rgba(31, 46, 25, 0.4); --border-subtle: rgba(255,255,255,0.15); --input-bg: rgba(20, 30, 15, 0.6);
    }
    body.card-mode-mint {
        --body-bg: linear-gradient(135deg, #081205, #1A2914); --inner-bg: #E5C158; --text-primary: #0F2005; --glow-color: rgba(229, 193, 88, 0.3); --border-subtle: #FFFFFF; --input-bg: rgba(255, 255, 255, 0.9);
    }
    body.card-mode-amber {
        --body-bg: linear-gradient(135deg, #010401, #081005); --inner-bg: #040803; --text-primary: #A3E61D; --glow-color: rgba(163, 230, 53, 0.4); --border-subtle: rgba(163, 230, 53, 0.35); --input-bg: rgba(255, 255, 255, 0.03);
    }

    body { background: var(--body-bg); }

    .navbar{ display:flex; justify-content:space-between; align-items:center; padding:20px 50px; background: rgba(10, 15, 8, 0.85); backdrop-filter: blur(15px); border-bottom: 2px solid #D4AF37; box-shadow: 0 4px 30px rgba(0,0,0,0.4); position:sticky; top:0; z-index:100; }
    
    /* BRANDING LOGO SETUP */
    .custom-brand-logo { display: flex; align-items: center; gap: 12px; cursor: pointer; text-decoration: none; }
    .cropped-logo-icon { width: 46px; height: 46px; border-radius: 50%; overflow: hidden; border: 2px solid #D4AF37; box-shadow: 0 0 12px rgba(212, 175, 55, 0.4); background-color: #0F2005; display: flex; align-items: center; justify-content: center; }
    .cropped-logo-icon img { width: 100%; height: 100%; object-fit: cover; }
    .brand-titles { display: flex; flex-direction: column; line-height: 1.1; }
    .main-title { font-size: 16px; font-weight: 800; color: #D4AF37; letter-spacing: 0.5px; }
    .sub-title { font-size: 11px; font-weight: 600; color: #FFFFFF; letter-spacing: 3px; }

    .back-btn { background: #D4AF37; color: #0F2005; padding:9px 22px; border-radius:30px; text-decoration:none; font-weight:600; font-size:14px; border: 1px solid #FFFFFF; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
    .back-btn:hover { background: #000000; color: #FFFFFF; border-color: #000000; box-shadow: 0 0 15px rgba(0,0,0,0.4); }

    /* CORE WRAPPER GLOW CONTAINER */
    .form-container { 
        background: var(--inner-bg); 
        border: 2px solid var(--border-subtle); 
        box-shadow: 0 20px 50px rgba(0,0,0,0.6), 0 0 30px var(--glow-color); 
        width: 620px; 
        margin: 50px auto; 
        padding: 40px 45px; 
        border-radius: 24px; 
        position: relative; 
        z-index: 5;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    body.card-mode-amber .form-container { animation: jugnuBreathe 4s ease-in-out infinite; }
    
    h2 { font-size: 24px; font-weight: 700; color: #FFFFFF; margin-bottom: 25px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 10px; }
    body.card-mode-amber h2 i { color: #A3E61D; text-shadow: 0 0 10px #A3E61D; }

    /* SMART LIVE VISUAL TRACKER WIDGET */
    .sentry-mini-bar { background: rgba(0,0,0,0.3); border: 1px solid var(--border-subtle); border-radius: 14px; padding: 14px; margin-bottom: 30px; }
    .mini-bar-header { display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #CBD5E1; margin-bottom: 6px; }
    .mini-bar-track { width: 100%; height: 8px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; }
    .mini-bar-fill { height: 100%; width: 0%; background: #22C55E; border-radius: 10px; transition: width 0.4s ease, background-color 0.4s ease; }

    .form-group { margin-bottom: 22px; display: flex; flex-direction: column; gap: 8px; }
    .form-group label { font-size: 13px; font-weight: 600; color: #A0AEC0; display: flex; align-items: center; gap: 8px; }
    .form-group label i { color: #D4AF37; width: 16px; }
    body.card-mode-amber .form-group label i { color: #A3E61D; }
    
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 13px 16px; border-radius: 12px; border: 1px solid var(--border-subtle); background: var(--input-bg); color: #FFFFFF; outline: none; font-size: 14px; font-weight: 500; }
    
    /* Themes alignment text corrections */
    body.card-mode-mint .form-group input, body.card-mode-mint .form-group select, body.card-mode-mint .form-group textarea { color: #000000; }
    body.card-mode-mint .form-group label { color: #1C2D16; }
    body.card-mode-mint h2 { color: #0F2005; }

    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #D4AF37; box-shadow: 0 0 10px rgba(212,175,55,0.4); background: rgba(0,0,0,0.6); }
    body.card-mode-mint .form-group input:focus, body.card-mode-mint .form-group select:focus, body.card-mode-mint .form-group textarea:focus { background: #FFFFFF; border-color: #0F2005; }
    body.card-mode-amber .form-group input:focus, body.card-mode-amber .form-group select:focus, body.card-mode-amber .form-group textarea:focus { border-color: #A3E61D; box-shadow: 0 0 12px rgba(163,230,53,0.5); }

    .form-group textarea { resize: none; font-family: inherit; }
    select option { background: #0A0F08; color: #FFF; }
    body.card-mode-mint select option { background: #FFFFFF; color: #000; }

    .submit-btn { width: 100%; background: #0F2005; color: #D4AF37; border: 2px solid #D4AF37; padding: 14px; border-radius: 35px; font-weight: 600; font-size: 15px; cursor: pointer; margin-top: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
    .submit-btn:hover { background: #D4AF37; color: #0F2005; border-color: #FFFFFF; font-weight: 700; transform: translateY(-1px); }
    body.card-mode-amber .submit-btn { background: transparent; color: #A3E61D; border-color: #A3E61D; }
    body.card-mode-amber .submit-btn:hover { background: #A3E61D; color: #000; }

    .spinner { display: none; width: 18px; height: 18px; border: 2px solid transparent; border-top-color: currentColor; border-radius: 50%; animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .alert { padding: 14px; border-radius: 12px; text-align: center; font-weight: 600; margin-bottom: 25px; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    .alert.success { background: rgba(34, 197, 94, 0.15); color: #4ADE80; border: 1px solid rgba(34, 197, 94, 0.3); }
    .alert.error { background: rgba(220,38,38, 0.15); color: #FCA5A5; border: 1px solid rgba(220,38,38, 0.3); }

    @media (max-width: 768px) {
        .navbar { padding: 15px 20px; }
        .form-container { width: 90%; padding: 30px 20px; margin: 30px auto; }
    }
</style>
</head>
<body class="card-mode-rosegold">

<!-- 👑 3D FLOATING CURRENCY LAYER DETECTED -->
<div class="currency-bg-portal">
    <div class="floating-asset">₨</div>
    <div class="floating-asset">$</div>
    <div class="floating-asset">€</div>
    <div class="floating-asset">£</div>
    <div class="floating-asset">₨</div>
    <div class="floating-asset">$</div>
</div>

<div class="navbar">
    <a href="dashboard.php" class="custom-brand-logo">
        <div class="cropped-logo-icon">
            <img src="logo.png" alt="PET Icon">
        </div>
        <div class="brand-titles">
            <span class="main-title">PERSONAL EXPENSE</span>
            <span class="sub-title">TRACKER PRO</span>
        </div>
    </a>
    <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
</div>

<div class="form-container">
    <h2><i class="fas fa-wallet" style="color: #22C55E; text-shadow: 0 0 10px rgba(34, 197, 94, 0.5);"></i> Vault Entry Protocol</h2>
    
    <!-- LIVE TARGET INSIGHT WIDGET -->
    <div class="sentry-mini-bar">
        <div class="mini-bar-header">
            <span>Sentry Spending Limit</span>
            <span>Rs. <?php echo number_format($monthly_budget, 2); ?></span>
        </div>
        <div class="mini-bar-track">
            <div class="mini-bar-fill" id="dynamicFormBar"></div>
        </div>
    </div>

    <?php echo $message; ?>
    
    <form id="expenseForm" method="POST" action="">
        <div class="form-group">
            <label><i class="fas fa-shopping-basket"></i> Item / Asset Name</label>
            <input type="text" name="item_name" placeholder="e.g. Weekly Grocery, Petrol, Netflix" required autocomplete="off">
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-money-bill-wave"></i> Transacted Amount (Rs.)</label>
            <input type="number" name="amount" id="amountField" step="0.01" placeholder="0.00" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-tags"></i> Segment Allocation Category</label>
            <select name="category" required>
                <option value="">Choose Categorical Stream</option>
                
                <!-- 🍔 Food & Dining -->
                <optgroup label="Food & Dining">
                    <option value="Groceries">🛒 Groceries & Essentials</option>
                    <option value="Restaurants">🍔 Restaurants & Fast Food</option>
                    <option value="Cafe">☕ Cafe & Coffee</option>
                    <option value="Snacks">🍿 Snacks & Drinks</option>
                </optgroup>

                <!-- 🏠 Household & Utilities -->
                <optgroup label="Household & Living">
                    <option value="Rent">🏠 Rent & Housing</option>
                    <option value="Utilities">💡 Electricity, Water & Gas</option>
                    <option value="Internet">🌐 Internet & WiFi Bills</option>
                    <option value="Mobile">📱 Mobile Recharge & Packages</option>
                    <option value="Maintenance">🛠️ Home Repair & Maintenance</option>
                </optgroup>

                <!-- 🚗 Transport & Travel -->
                <optgroup label="Transport & Commute">
                    <option value="Fuel">⛽ Fuel / Petrol</option>
                    <option value="Public Transport">🚌 Bus, Train & Metro</option>
                    <option value="Ride Sharing">🚖 Uber / Careem / Cab</option>
                    <option value="Vehicle Maintenance">🔧 Bike/Car Service & Repairs</option>
                    <option value="Travel">✈️ Flights & Long Trips</option>
                </optgroup>

                <!-- 🏥 Health & Self Care -->
                <optgroup label="Health & Personal Care">
                    <option value="Medical">💊 Medicines & Pharmacy</option>
                    <option value="Doctor">🏥 Doctor & Hospital Visits</option>
                    <option value="Gym">💪 Gym, Fitness & Sports</option>
                    <option value="Saloon">💇 Grooming, Saloon & Parlour</option>
                    <option value="Clothing">🛍️ Apparel & Clothing</option>
                </optgroup>

                <!-- 🎓 Education & Career -->
                <optgroup label="Education & Growth">
                    <option value="Fees">📚 Tuition & Course Fees</option>
                    <option value="Stationery">✏️ Books & Stationery</option>
                    <option value="Certifications">🏅 Certifications & Software</option>
                </optgroup>

                <!-- 🎮 Entertainment & Leisure -->
                <optgroup label="Entertainment & Subscriptions">
                    <option value="Streaming">🎬 Netflix, Spotify & Youtube</option>
                    <option value="Gaming">🎮 Games & Console Purchases</option>
                    <option value="Cinema">🍿 Movies & Events</option>
                    <option value="Hobbies">🎨 Hobbies & Art Supplies</option>
                </optgroup>

                <!-- 🎁 Social & Giving -->
                <optgroup label="Social & Family">
                    <option value="Gifts">🎁 Gifts & Celebrations</option>
                    <option value="Charity">🕌 Zakat, Sadqah & Charity</option>
                    <option value="Family Support">❤️ Send to Family / Remittance</option>
                </optgroup>

                <!-- 💰 Investments & Savings -->
                <optgroup label="Future Planning">
                    <option value="Savings">💰 Savings & Emergency Fund</option>
                    <option value="Investments">📈 Stocks, Gold & Crypto</option>
                    <option value="Insurance">🛡️ Health & Life Insurance</option>
                </optgroup>

                <!-- 📌 Miscellaneous -->
                <optgroup label="Others">
                    <option value="Loan Payment">🏦 Debt / Loan Repayments</option>
                    <option value="Taxes">📄 Government Taxes & Fees</option>
                    <option value="Other">📌 Miscellaneous Charges</option>
                </optgroup>
            </select>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-calendar-day"></i> Booking Date Stamp</label>
            <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-sticky-note"></i> Internal Audit Notes (Optional)</label>
            <textarea name="description" rows="3" placeholder="Append transaction tags or special notes here..."></textarea>
        </div>
        
        <button type="submit" name="add_expense" id="submitBtn" class="submit-btn">
            <span id="btnText">Save Transaction Logs</span>
            <div id="btnSpinner" class="spinner"></div>
        </button>
    </form>
</div>

<script>
    // THEME COLD SYNCHRONIZATION 
    window.addEventListener('DOMContentLoaded', () => {
        const savedMode = localStorage.getItem('savedCardModeDistinct');
        if (savedMode) {
            document.body.className = '';
            document.body.classList.add(savedMode);
        }
        
        // Auto-dismiss alert boxes cleanly after 3.5 seconds
        const alertBox = document.getElementById('alertBox');
        if(alertBox) {
            setTimeout(() => {
                alertBox.style.transition = 'opacity 0.5s ease';
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }, 3500);
        }
    });

    // DYNAMIC SENTRY FILL INDICATOR EFFECT
    const budgetLimit = <?php echo $monthly_budget; ?>;
    const amountField = document.getElementById('amountField');
    const progressFill = document.getElementById('dynamicFormBar');

    amountField.addEventListener('input', (e) => {
        const val = parseFloat(e.target.value) || 0;
        let percentage = (val / budgetLimit) * 100;
        if(percentage > 100) percentage = 100;
        
        progressFill.style.width = percentage + '%';
        if(percentage > 85) {
            progressFill.style.backgroundColor = '#EF4444'; // Danger
        } else if(percentage > 50) {
            progressFill.style.backgroundColor = '#EAB308'; // Warning
        } else {
            progressFill.style.backgroundColor = '#22C55E'; // Normal
        }
    });

    // PROCESSING STATE ANIMATION CONTROLLER
    const form = document.getElementById('expenseForm');
    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const spinner = document.getElementById('btnSpinner');

    form.addEventListener('submit', function() {
        btn.style.pointerEvents = 'none';
        btnText.textContent = 'Encrypting & Archiving Logs...';
        spinner.style.display = 'inline-block';
    });
</script>

</body>
</html>