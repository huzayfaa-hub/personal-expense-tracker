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

// --- BUDGET SETTING LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_budget'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $limit_amount = floatval($_POST['limit_amount']);
    
    $check_query = mysqli_query($conn, "SELECT id FROM budgets WHERE user_id='$user_id' AND category='$category'");
    
    if (mysqli_num_rows($check_query) > 0) {
        mysqli_query($conn, "UPDATE budgets SET limit_amount='$limit_amount' WHERE user_id='$user_id' AND category='$category'");
    } else {
        mysqli_query($conn, "INSERT INTO budgets (user_id, category, limit_amount) VALUES ('$user_id', '$category', '$limit_amount')");
    }
    header("Location: reports.php");
    exit();
}

// --- FETCH BUDGETS & ACTUAL SPENDINGS ---
$current_month = date('m');
$current_year = date('Y');

$spending_query = mysqli_query($conn, "
    SELECT category, SUM(amount) as total_spent 
    FROM expenses 
    WHERE user_id='$user_id' AND MONTH(expense_date)='$current_month' AND YEAR(expense_date)='$current_year'
    GROUP BY category
");

$spendings = [];
while ($row = mysqli_fetch_assoc($spending_query)) {
    $spendings[$row['category']] = $row['total_spent'];
}

$budget_query = mysqli_query($conn, "SELECT * FROM budgets WHERE user_id='$user_id'");
$budgets = [];
while ($row = mysqli_fetch_assoc($budget_query)) {
    $budgets[$row['category']] = $row['limit_amount'];
}

$categories_list = [
    "Groceries" => "🛒 Groceries & Essentials",
    "Restaurants" => "🍔 Restaurants & Fast Food",
    "Cafe" => "☕ Cafe & Coffee",
    "Rent" => "🏠 Rent & Housing",
    "Utilities" => "💡 Electricity & Bills",
    "Fuel" => "⛽ Fuel / Petrol",
    "Medical" => "💊 Medicines & Health",
    "Entertainment" => "🎬 Subscriptions & Movies",
    "Savings" => "💰 Savings & Investments",
    "Other" => "📌 Other Miscellaneous"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Budget Guard | Secure Spending Control</title>

<link rel="icon" type="image/png" href="logo.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    *{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; transition: background 0.4s ease, color 0.4s ease, box-shadow 0.4s ease; }
    
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
        color: rgba(212, 175, 55, 0.12);
        font-weight: 800;
        text-shadow: 0 0 15px rgba(212, 175, 55, 0.35), 0 0 35px rgba(212, 175, 55, 0.15);
        animation: ascendRotateParallax 22s linear infinite;
        user-select: none;
    }
    @keyframes ascendRotateParallax {
        0% { transform: translateY(105vh) translateX(0) rotate(0deg) scale(0.6); opacity: 0; }
        12% { opacity: 1; }
        88% { opacity: 1; }
        100% { transform: translateY(-10vh) translateX(100px) rotate(360deg) scale(1.3); opacity: 0; }
    }
    .floating-asset:nth-child(1) { left: 6%; animation-duration: 24s; animation-delay: 0s; font-size: 3rem; }
    .floating-asset:nth-child(2) { left: 24%; animation-duration: 19s; animation-delay: 4s; font-size: 4.5rem; filter: blur(3px); color: rgba(212, 175, 55, 0.06); }
    .floating-asset:nth-child(3) { left: 45%; animation-duration: 27s; animation-delay: 1s; font-size: 2.2rem; }
    .floating-asset:nth-child(4) { left: 65%; animation-duration: 17s; animation-delay: 5s; font-size: 5rem; filter: blur(4px); color: rgba(212, 175, 55, 0.04); }
    .floating-asset:nth-child(5) { left: 80%; animation-duration: 23s; animation-delay: 2s; font-size: 3.8rem; filter: blur(1px); }
    .floating-asset:nth-child(6) { left: 92%; animation-duration: 25s; animation-delay: 8s; font-size: 2.5rem; }

    /* --- DYNAMIC THEME PALETTES --- */
    body.card-mode-rosegold {
        --body-bg: linear-gradient(135deg, #050A03, #121A0F); --card-bg: #0D140B; --text-primary: #D4AF37; --glow-color: rgba(212, 175, 55, 0.3); --border-color: rgba(212, 175, 55, 0.25);
    }
    body.card-mode-ivory {
        --body-bg: linear-gradient(135deg, #0A0D08, #151B11); --card-bg: #0A0F08; --text-primary: #F3E5AB; --glow-color: rgba(243, 229, 171, 0.25); --border-color: rgba(243, 229, 171, 0.15);
    }
    body.card-mode-mint {
        --body-bg: linear-gradient(135deg, #081205, #1A2914); --card-bg: #0E180D; --text-primary: #C5A028; --glow-color: rgba(197, 160, 40, 0.25); --border-color: rgba(197, 160, 40, 0.15);
    }
    body.card-mode-amber {
        --body-bg: linear-gradient(135deg, #010401, #081005); --card-bg: #111A0E; --text-primary: #E5A93C; --glow-color: rgba(229, 169, 60, 0.3); --border-color: rgba(229, 169, 60, 0.2);
    }

    body { background: var(--body-bg); }

    /* Navbar styling */
    .navbar{ 
        display:flex; justify-content:space-between; align-items:center; padding:20px 50px; 
        background: rgba(10, 15, 8, 0.85); backdrop-filter: blur(15px);
        border-bottom: 2px solid #D4AF37; box-shadow: 0 4px 30px rgba(0,0,0,0.5); position: sticky; top: 0; z-index: 100;
    }
    .logo { font-size:22px; font-weight:700; color: #FAF8F5; text-decoration: none; display: flex; align-items: center; gap: 10px; }
    .logo i { color: #22C55E; text-shadow: 0 0 10px rgba(34, 197, 150, 0.5); }
    
    .back-btn { background: #D4AF37; color: #0F2005; padding:9px 22px; border-radius:30px; text-decoration:none; font-weight:600; font-size:14px; border: 1px solid #FFFFFF; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
    .back-btn:hover { background: #000000; color: #FFFFFF; border-color: #000000; box-shadow: 0 0 15px rgba(0,0,0,0.4); }

    /* Main Dual Grid Layout */
    .dashboard-grid {
        width: 90%; max-width: 1300px; margin: 45px auto;
        display: grid; grid-template-columns: 1fr 2fr; gap: 35px; position: relative; z-index: 5;
    }

    @media (max-width: 900px) {
        .dashboard-grid { grid-template-columns: 1fr; }
    }

    /* Left Side: Setup Card */
    .setup-card {
        background: var(--card-bg); border: 2px solid var(--border-color);
        padding: 35px; border-radius: 20px; height: fit-content;
        box-shadow: 0 20px 45px rgba(0,0,0,0.5), 0 0 20px var(--glow-color);
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    }
    .setup-card h3 { font-size: 20px; margin-bottom: 25px; color: #FFF; display: flex; align-items: center; gap: 10px; font-weight: 700; }
    .setup-card h3 i { color: var(--text-primary); }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; color: #A0AEC0; margin-bottom: 8px; font-weight: 600; }
    
    .form-group select, .form-group input {
        width: 100%; padding: 12px 15px; border-radius: 10px; border: 2px solid var(--border-color);
        background: rgba(0,0,0,0.4); color: #FFF; outline: none; font-size: 14px; font-weight: 500;
    }
    .form-group select:focus, .form-group input:focus { border-color: var(--text-primary); box-shadow: 0 0 8px var(--glow-color); }
    .form-group select option { background: #0A0F08; color: #fff; }

    .save-btn {
        width: 100%; background: #0F2005; color: #D4AF37; border: 2px solid #D4AF37; padding: 12px;
        border-radius: 35px; font-weight: 700; cursor: pointer; font-size: 14px; letter-spacing: 0.5px; transition: 0.3s;
    }
    .save-btn:hover { background: #D4AF37; color: #0F2005; border-color: #FFFFFF; box-shadow: 0 5px 15px var(--glow-color); }

    /* Right Side: Monitor Panel */
    .monitor-card {
        background: var(--card-bg); border: 2px solid var(--border-color);
        padding: 40px; border-radius: 20px; box-shadow: 0 20px 45px rgba(0,0,0,0.5), 0 0 20px var(--glow-color);
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    }
    .monitor-card h2 { font-size: 22px; margin-bottom: 30px; display: flex; align-items: center; gap: 12px; color: #FFF; font-weight: 700; }
    .monitor-card h2 i { color: #22C55E; text-shadow: 0 0 10px rgba(34, 197, 94, 0.4); }

    /* Budget Progress Stream */
    .budget-item { margin-bottom: 25px; background: rgba(0,0,0,0.2); padding: 20px; border-radius: 16px; border: 1px solid var(--border-color); }
    .budget-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .cat-title { font-weight: 700; font-size: 15px; color: #FAF8F5; }
    .spend-stat { font-size: 13px; font-weight: 600; color: #CBD5E1; }
    .spend-stat span { font-weight: 700; color: #FFF; }

    /* Custom CSS Progress Bars */
    .progress-bar-bg { width: 100%; height: 12px; background: rgba(255,255,255,0.08); border-radius: 30px; overflow: hidden; position: relative; border: 1px solid rgba(255,255,255,0.1); }
    .progress-fill { height: 100%; border-radius: 30px; width: 0%; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1); }

    /* Progressive Color Alerts */
    .status-safe { background: #22C55E; box-shadow: 0 0 10px rgba(34, 197, 94, 0.6); }
    .status-warn { background: #EAB308; box-shadow: 0 0 10px rgba(234, 179, 8, 0.6); }
    .status-danger { background: #EF4444; box-shadow: 0 0 10px rgba(239, 68, 68, 0.6); }

    .status-badge { font-size: 11px; padding: 4px 12px; border-radius: 30px; font-weight: 700; text-transform: uppercase; }
    .badge-safe { background: rgba(34,197,94,0.15); color: #22C55E; border: 1px solid rgba(34,197,94,0.3); }
    .badge-warn { background: rgba(234,179,8,0.15); color: #EAB308; border: 1px solid rgba(234,179,8,0.3); }
    .badge-danger { background: rgba(239,68,68,0.15); color: #EF4444; border: 1px solid rgba(239,68,68,0.3); }

    .empty-state { text-align: center; color: #A0AEC0; font-style: italic; padding: 50px 0; }
    .empty-state i { font-size: 40px; margin-bottom: 15px; color: var(--text-primary); opacity: 0.5; }
</style>
</head>
<body class="card-mode-rosegold">

<!-- 👑 3D FLOATING CURRENCY LAYER -->
<div class="currency-bg-portal">
    <div class="floating-asset">₨</div>
    <div class="floating-asset">$</div>
    <div class="floating-asset">€</div>
    <div class="floating-asset">£</div>
    <div class="floating-asset">₨</div>
    <div class="floating-asset">$</div>
</div>

<div class="navbar">
    <a href="dashboard.php" class="logo"><img src="logo.png" style="width:32px; height:32px; object-fit:cover; border-radius:50%; margin-right:8px; border:1px solid #D4AF37;"> Budget Guard</a>
    <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
</div>

<div class="dashboard-grid">
    
    <!-- LEFT SIDE: SETUP LIMITS -->
    <div class="setup-card">
        <h3><i class="fas fa-sliders-h"></i> Limit Protocols</h3>
        <form action="reports.php" method="POST">
            <div class="form-group">
                <label>Target Category</label>
                <select name="category" required>
                    <option value="">Select Stream</option>
                    <?php foreach($categories_list as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Monthly Threshold Limit (Rs.)</label>
                <input type="number" name="limit_amount" placeholder="e.g. 15000" min="1" step="0.01" required>
            </div>

            <button type="submit" name="set_budget" class="save-btn"><i class="fas fa-lock"></i> Activate Limit</button>
        </form>
    </div>

    <!-- RIGHT SIDE: PROGRESS MONITOR -->
    <div class="monitor-card">
        <h2><i class="fas fa-chart-line"></i> Category Over-Watch (Current Month)</h2>
        
        <?php if (empty($budgets)): ?>
            <div class="empty-state">
                <i class="fas fa-shield-virus"></i>
                <p>No active perimeter budgets established yet. Use the control panel to set threshold limits!</p>
            </div>
        <?php else: ?>
            <?php 
            foreach ($budgets as $category => $limit): 
                $spent = isset($spendings[$category]) ? $spendings[$category] : 0;
                $percentage = ($limit > 0) ? ($spent / $limit) * 100 : 0;
                $percentage_label = round($percentage, 1);
                
                $status_class = "status-safe";
                $badge_class = "badge-safe";
                $badge_text = "Safe";

                if ($percentage >= 100) {
                    $status_class = "status-danger";
                    $badge_class = "badge-danger";
                    $badge_text = "Breached!";
                } elseif ($percentage >= 80) {
                    $status_class = "status-warn";
                    $badge_class = "badge-warn";
                    $badge_text = "Warning";
                }

                $display_category_name = isset($categories_list[$category]) ? $categories_list[$category] : $category;
            ?>
                <div class="budget-item">
                    <div class="budget-meta">
                        <span class="cat-title"><?php echo $display_category_name; ?></span>
                        <span class="status-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                    </div>
                    
                    <div class="progress-bar-bg">
                        <div class="progress-fill <?php echo $status_class; ?>" style="width: <?php echo min($percentage, 100); ?>%"></div>
                    </div>

                    <div class="budget-meta" style="margin-top: 10px; margin-bottom: 0;">
                        <span class="spend-stat">Spent: <span>Rs. <?php echo number_format($spent, 2); ?></span> of Rs. <?php echo number_format($limit, 2); ?></span>
                        <span class="spend-stat" style="color: #FFF; font-weight:700;"><?php echo $percentage_label; ?>%</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        const savedMode = localStorage.getItem('savedCardModeDistinct');
        if (savedMode) {
            document.body.className = '';
            document.body.classList.add(savedMode);
        }
    });
</script>
</body>
</html>