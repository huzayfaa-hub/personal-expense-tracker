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
$session_username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$message = "";

// Handle Budget & Income Updates
if (isset($_POST['update_limits'])) {
    $new_budget = mysqli_real_escape_string($conn, $_POST['monthly_budget']);
    $new_income = mysqli_real_escape_string($conn, $_POST['monthly_income']);
    mysqli_query($conn, "UPDATE users SET monthly_budget = '$new_budget', monthly_income = '$new_income' WHERE id = '$user_id'");
    header("Location: dashboard.php?status=success");
    exit();
}

// Handle AJAX/Normal POST Request (Add Expense)
if (isset($_POST['add_expense_ajax'])) {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $expense_date = mysqli_real_escape_string($conn, $_POST['expense_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $query = "INSERT INTO expenses (user_id, item_name, amount, category, expense_date, description) VALUES ('$user_id', '$item_name', '$amount', '$category', '$expense_date', '$description')";
    if (mysqli_query($conn, $query)) {
        header("Location: dashboard.php?status=success");
        exit();
    } else {
        $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Error adding record.</div>";
    }
}

// Handle Delete Expense
if (isset($_GET['delete_expense'])) {
    $expense_id = mysqli_real_escape_string($conn, $_GET['delete_expense']);
    mysqli_query($conn, "DELETE FROM expenses WHERE id = '$expense_id' AND user_id = '$user_id'");
    header("Location: dashboard.php?status=deleted");
    exit();
}

// Fetch User Profile Limits (Budget & Income)
$user_q = mysqli_query($conn, "SELECT monthly_budget, monthly_income FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$monthly_budget = $user_data['monthly_budget'] ?? 20000.00;
$monthly_income = $user_data['monthly_income'] ?? 50000.00;

// Analytical Totals
$total_expense = 0; $total_records = 0;

$q1 = mysqli_query($conn,"SELECT SUM(amount) as total FROM expenses WHERE user_id='$user_id'");
if($row = mysqli_fetch_assoc($q1)){ $total_expense = $row['total'] ?? 0; }

$q2 = mysqli_query($conn,"SELECT COUNT(*) as records FROM expenses WHERE user_id='$user_id'");
if($row = mysqli_fetch_assoc($q2)){ $total_records = $row['records'] ?? 0; }

$current_month = date('Y-m');
$q3 = mysqli_query($conn, "SELECT SUM(amount) as month_total FROM expenses WHERE user_id='$user_id' AND expense_date LIKE '$current_month%'");
$month_total = 0;
if($row = mysqli_fetch_assoc($q3)){ $month_total = $row['month_total'] ?? 0; }

$q4 = mysqli_query($conn, "SELECT category FROM expenses WHERE user_id='$user_id' GROUP BY category ORDER BY SUM(amount) DESC LIMIT 1");
$top_category = "None";
if($row = mysqli_fetch_assoc($q4)){ $top_category = $row['category'] ?? "None"; }

// Net Savings Calculation
$net_savings = $monthly_income - $month_total;

// Category-wise Breakdown for Chart
$chart_categories = [];
$chart_amounts = [];
$chart_q = mysqli_query($conn, "SELECT category, SUM(amount) as total FROM expenses WHERE user_id='$user_id' GROUP BY category");
while ($c_row = mysqli_fetch_assoc($chart_q)) {
    $chart_categories[] = $c_row['category'];
    $chart_amounts[] = (float)$c_row['total'];
}

// Fetch Latest logs for the local search table
$logs_q = mysqli_query($conn, "SELECT * FROM expenses WHERE user_id='$user_id' ORDER BY expense_date DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Personal Expense Tracker Pro</title>

<link rel="icon" type="image/png" href="logo.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    html { scroll-behavior: smooth; }
    *{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; transition: background 0.4s ease, color 0.4s ease, box-shadow 0.4s ease, border-color 0.3s ease; }
    
    body { background-color: #0A0F08; min-height:100vh; color: #F4F6F0; display: flex; flex-direction: column; position: relative; overflow-x: hidden; }
    body::before { content: ""; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-image: radial-gradient(rgba(212, 175, 55, 0.03) 1.5px, transparent 0); background-size: 28px 24px; z-index: 1; pointer-events: none; }

    /* ==========================================================
       👑 10x BETTER 3D DYNAMIC FLOATING MULTI-CURRENCY PARTICLES
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
        color: rgba(212, 175, 55, 0.12); /* Ultra elegant transparent gold */
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
    .floating-asset:nth-child(1) { left: 5%; animation-duration: 25s; animation-delay: 0s; font-size: 3rem; }
    .floating-asset:nth-child(2) { left: 18%; animation-duration: 18s; animation-delay: 4s; font-size: 4.5rem; filter: blur(3px); color: rgba(212, 175, 55, 0.06); }
    .floating-asset:nth-child(3) { left: 32%; animation-duration: 28s; animation-delay: 1s; font-size: 2.2rem; }
    .floating-asset:nth-child(4) { left: 50%; animation-duration: 16s; animation-delay: 7s; font-size: 5rem; filter: blur(4px); color: rgba(212, 175, 55, 0.04); }
    .floating-asset:nth-child(5) { left: 68%; animation-duration: 22s; animation-delay: 2s; font-size: 3.8rem; filter: blur(1px); }
    .floating-asset:nth-child(6) { left: 82%; animation-duration: 26s; animation-delay: 10s; font-size: 2.5rem; }
    .floating-asset:nth-child(7) { left: 93%; animation-duration: 20s; animation-delay: 5s; font-size: 4rem; filter: blur(2px); }
    .floating-asset:nth-child(8) { left: 25%; animation-duration: 24s; animation-delay: 12s; font-size: 3.2rem; }

    /* 🎨 INTERACTIVE PALETTES */
    @keyframes jugnuBreathe {
        0% { box-shadow: 0 15px 40px rgba(0,0,0,0.9), 0 0 12px rgba(163, 230, 53, 0.25); border-color: rgba(163, 230, 53, 0.35); }
        50% { box-shadow: 0 15px 45px rgba(0,0,0,0.95), 0 0 25px rgba(163, 230, 53, 0.6); border-color: rgba(163, 230, 53, 0.8); }
        100% { box-shadow: 0 15px 40px rgba(0,0,0,0.9), 0 0 12px rgba(163, 230, 53, 0.25); border-color: rgba(163, 230, 53, 0.35); }
    }

    body.card-mode-rosegold {
        --body-bg: linear-gradient(135deg, #050A03, #121A0F);
        --card-bg: #0D140B; --text-primary: #D4AF37; --text-muted: #CBD5E1; --glow-color: rgba(212, 175, 55, 0.25); --border-subtle: rgba(212, 175, 55, 0.25); --input-bg: rgba(0,0,0,0.4);
    }
    body.card-mode-mint {
        --body-bg: linear-gradient(135deg, #081205, #1A2914); --card-bg: #E5C158; --text-primary: #0F2005; --text-muted: #2C471B; --glow-color: rgba(229, 193, 88, 0.3); --inner-bg: rgba(255, 255, 255, 0.85); --border-subtle: #FFFFFF; --input-bg: rgba(255, 255, 255, 0.9);
    }
    body.card-mode-amber {
        --body-bg: linear-gradient(135deg, #010401, #081005); 
        --card-bg: #040803; 
        --text-primary: #FFFFFF; 
        --text-muted: #94A3B8; 
        --glow-color: rgba(163, 230, 53, 0.3); 
        --border-subtle: rgba(163, 230, 53, 0.35); 
        --input-bg: rgba(255, 255, 255, 0.95);
    }
    
    body.card-mode-amber .stat-box h2,
    body.card-mode-amber .slide-content h4,
    body.card-mode-amber .card h3,
    body.card-mode-amber .custom-panel h2,
    body.card-mode-amber .custom-panel h3 { color: #FFFFFF !important; }
    body.card-mode-amber .stat-box p,
    body.card-mode-amber .slide-content p,
    body.card-mode-amber .card p { color: #CBD5E1 !important; }

    body.card-mode-amber .modern-slider-container,
    body.card-mode-amber .stat-box,
    body.card-mode-amber .card,
    body.card-mode-amber .custom-panel { animation: jugnuBreathe 4s ease-in-out infinite; }
    body.card-mode-amber .stat-box i,
    body.card-mode-amber .card i,
    body.card-mode-amber .slide i,
    body.card-mode-amber .custom-panel i { color: #A3E61D !important; text-shadow: 0 0 15px #A3E61D; }

    body { background: var(--body-bg); }
    
    .modern-slider-container, .stat-box, .card, .custom-panel { 
        background: var(--card-bg); 
        border: 2px solid var(--border-subtle); 
        box-shadow: 0 20px 45px rgba(0,0,0,0.5), 0 0 25px var(--glow-color); 
        color: var(--text-primary);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        position: relative;
        z-index: 5;
    }

    /* CUSTOM PANEL STYLE */
    .custom-panel { border-radius: 20px; padding: 25px; display: flex; flex-direction: column; gap: 20px; }
    .custom-panel h2 { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px; }

    /* BRANDING & HEADER */
    .custom-brand-logo { display: flex; align-items: center; gap: 12px; cursor: pointer; }
    .cropped-logo-icon {
        width: 46px; height: 46px; border-radius: 50%; overflow: hidden; border: 2px solid #D4AF37;
        box-shadow: 0 0 12px rgba(212, 175, 55, 0.4); background-color: #0F2005; display: flex; align-items: center; justify-content: center;
    }
    .cropped-logo-icon img { width: 100%; height: 100%; object-fit: cover; }
    .brand-titles { display: flex; flex-direction: column; line-height: 1.1; }
    .main-title { font-size: 16px; font-weight: 800; color: #D4AF37 !important; letter-spacing: 0.5px; }
    .sub-title { font-size: 11px; font-weight: 600; color: #FFFFFF !important; letter-spacing: 3px; }

    /* WELCOME CARD */
    .welcome-section { 
        display: flex; justify-content: space-between; align-items: center; padding: 45px 40px; border-radius: 20px; position: relative; z-index: 5;
        background-image: linear-gradient(to right, rgba(15, 32, 5, 0.95) 35%, rgba(28, 40, 22, 0.6)), 
                          url('https://images.unsplash.com/photo-1639762681485-074b7f938ba0?q=80&w=1400&auto=format&fit=crop');
        background-size: cover; background-position: center center; background-repeat: no-repeat; border: 2px solid #D4AF37;
        box-shadow: 0 15px 35px rgba(0,0,0,0.5), 0 0 20px rgba(212, 175, 55, 0.2); overflow: hidden;
    }
    .welcome-text { position: relative; z-index: 2; }
    .welcome-text h1{ font-size:30px; font-weight:700; color: #D4AF37; text-shadow: 2px 2px 8px rgba(0,0,0,0.9); }
    .welcome-text p{ font-size:15px; color: #F4F6F0; font-weight: 500; margin-top: 8px; text-shadow: 1px 1px 4px rgba(0,0,0,0.9); letter-spacing: 0.3px; }
    .live-clock{ position: relative; z-index: 2; font-size:18px; font-weight:700; color: #0F2005; background: #D4AF37; padding:12px 24px; border-radius:12px; border: 1px solid #FFFFFF; box-shadow: 0 8px 20px rgba(0,0,0,0.4); }

    /* Navbar */
    .navbar{ display:flex; justify-content:space-between; align-items:center; padding:20px 50px; background: rgba(10, 15, 8, 0.85); backdrop-filter: blur(15px); border-bottom: 2px solid #D4AF37; box-shadow: 0 4px 30px rgba(0,0,0,0.4); position:sticky; top:0; z-index:100; }
    
    .theme-picker-box { display: flex; align-items: center; gap: 14px; background: rgba(0,0,0,0.4); padding: 8px 16px; border-radius: 30px; border: 1px solid #D4AF37; }
    .theme-btn { width: 22px; height: 22px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: 0.2s; }
    .theme-btn.active { border-color: white !important; transform: scale(1.15); box-shadow: 0 0 12px #D4AF37; }

    .user-profile { display:flex; align-items:center; gap:12px; background: rgba(255,255,255,0.1); padding:8px 18px; border-radius:30px; border: 1px solid rgba(212,175,55,0.3); color: #F4F6F0; }
    .user-profile i { font-size:18px; color: #D4AF37; }
    .logout-btn { background: #D4AF37; color: #0F2005; padding:8px 18px; border-radius:30px; text-decoration:none; font-weight:600; font-size:14px; border: 1px solid #FFFFFF; transition: 0.3s; }
    .logout-btn:hover { background: #000000; color: #FFFFFF; border-color: #000000; box-shadow: 0 0 15px rgba(0,0,0,0.4); }

    .main-container{ width:85%; max-width:1400px; margin:40px auto; display:flex; flex-direction:column; gap:35px; flex: 1; position: relative; z-index: 5; }

    /* Slider Component */
    .modern-slider-container { position: relative; width: 100%; height: 120px; border-radius: 20px; overflow: hidden; display: flex; align-items: center; padding: 0 40px; }
    .slider-track { display: flex; width: 100%; height: 100%; position: relative; }
    
    .slide { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; gap: 25px; opacity: 0; transform: scale(0.97) translateY(5px); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none; }
    .slide.active { opacity: 1; transform: scale(1) translateY(0); pointer-events: auto; }
    
    .slider-dots { position: absolute; right: 40px; display: flex; gap: 10px; }
    .dot { width: 10px; height: 10px; background: rgba(15, 32, 5, 0.2); border-radius: 50%; cursor: pointer; transition: 0.3s; }
    .dot.active { background: #0F2005 !important; width: 26px; border-radius: 10px; }

    /* Stats Box Grid Layout */
    .stats{ display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:25px; width:100%; }
    .stat-box { padding: 25px; border-radius: 20px; display: flex; flex-direction: column; gap: 6px; transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer; }
    .stat-box:hover { transform: translateY(-5px); }

    /* SMART PROGRESS BAR STYLING */
    .budget-progress-container { width: 100%; margin-top: 10px; }
    .budget-header-labels { display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 5px; }
    .progress-bar-bg { width: 100%; height: 14px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.2); position: relative; }
    .progress-bar-fill { height: 100%; width: 0%; border-radius: 10px; transition: width 0.8s ease-in-out, background-color 0.5s ease; }
    
    @keyframes jelloWiggle {
        0%, 100% { transform: scale(1) rotate(0deg); }
        50% { transform: scale(1.02) rotate(2deg); }
    }
    .playful-danger-mode {
        animation: jelloWiggle 0.8s ease-in-out infinite alternate !important;
        border-color: #FF6B6B !important;
        box-shadow: 0 15px 30px rgba(255, 107, 107, 0.25) !important;
    }

    /* Double Layout: Chart & Logs */
    .double-layout { display: grid; grid-template-columns: 1.2fr 1.8fr; gap: 30px; }
    
    .log-filter-header { display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; }
    .search-input-box { 
        display: flex; align-items: center; background: #FFFFFF; border: 2px solid #D4AF37; border-radius: 30px; padding: 8px 18px; gap: 10px; width: 270px;
        box-shadow: 0 0 10px rgba(212, 175, 55, 0.4);
    }
    .search-input-box i { color: #0F2005 !important; font-size: 14px; }
    .search-input-box input { background: transparent; border: none; color: #0F2005 !important; font-size: 14px; font-weight: 600; outline: none; width: 100%; }
    .search-input-box input::placeholder { color: rgba(15, 32, 5, 0.6) !important; }

    /* Data Table styling */
    .custom-table-wrapper { width: 100%; overflow-x: auto; border-radius: 12px; }
    .custom-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
    .custom-table th { background: rgba(0,0,0,0.4); padding: 12px 16px; font-weight: 600; border-bottom: 2px solid rgba(212,175,55,0.3); }
    .custom-table td { padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .custom-table tr:hover { background: rgba(255,255,255,0.02); }
    
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
    .badge-grocery { background: #22C55E; color: #FFF; }
    .badge-utilities { background: #3B82F6; color: #FFF; }
    .badge-entertainment { background: #EC4899; color: #FFF; }
    .badge-transport { background: #EAB308; color: #000; }
    .badge-other { background: #6B7280; color: #FFF; }
    
    .action-icons { display: flex; gap: 10px; }
    .action-btn { background: transparent; border: none; font-size: 14px; cursor: pointer; transition: 0.2s; }
    .action-btn.delete { color: #EF4444; }
    .action-btn.delete:hover { transform: scale(1.2); }

    .export-btns { display: flex; gap: 10px; }
    .export-btn { background: #0F2005; color: #D4AF37; padding: 8px 16px; border-radius: 30px; font-weight: 600; font-size: 12px; border: 1px solid #D4AF37; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
    .export-btn:hover { background: #D4AF37; color: #0F2005; border-color: #FFFFFF; }

    /* Premium Services Navigation Cards Layout */
    .services { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; width: 100%; }
    .card { 
        border-radius: 20px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 15px; overflow: hidden; padding-bottom: 30px; border: 2px solid var(--border-subtle);
        background: var(--card-bg); box-shadow: 0 10px 30px rgba(0,0,0,0.3); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease, border-color 0.4s ease;
    }
    .card:hover { transform: translateY(-8px); border-color: #D4AF37; box-shadow: 0 15px 35px rgba(212, 175, 55, 0.25), 0 0 20px rgba(212, 175, 55, 0.15); }
    .card-img { width: 94%; height: 180px; object-fit: cover; margin-top: 3%; border-radius: 14px; opacity: 0.85; filter: brightness(0.9) contrast(1.1); transition: transform 0.5s ease, filter 0.5s ease; }
    .card:hover .card-img { transform: scale(1.03); filter: brightness(1) contrast(1.15); }
    .card i { font-size: 20px; color: #D4AF37; background: #0F2005; border: 2px solid #FFFFFF; padding: 16px; border-radius: 50%; box-shadow: 0 6px 20px rgba(0,0,0,0.4); margin-top: -28px; z-index: 2; transition: transform 0.4s ease, background-color 0.3s ease; }
    .card:hover i { transform: rotate(360deg) scale(1.1); background-color: #000000; }
    
    .btn{ width:80%; text-align:center; background: #0F2005; color: #D4AF37; padding:11px; border-radius:30px; text-decoration:none; font-weight:600; font-size:14px; margin-top:10px; display:inline-block; border: 2px solid transparent; transition: 0.3s; }
    .btn:hover{ background: #000000; color: #FFFFFF; transform: translateY(-2px); }

    /* Floating Stack */
    .floating-actions-stack { position: fixed; bottom: 35px; right: 35px; display: flex; flex-direction: column; gap: 15px; z-index: 999; }
    .fab-btn {
        width: 54px; height: 54px; border-radius: 50%; background: #0F2005; color: #D4AF37; border: 2px solid #D4AF37; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 5px 25px rgba(0,0,0,0.6); transition: 0.3s;
    }
    .fab-btn:hover { background: #000000; color: #FFF; box-shadow: 0 0 20px rgba(212,175,55,0.6); }

    #scrollToTopBtn { opacity: 0; visibility: hidden; transform: translateY(10px); }
    #scrollToTopBtn.show-arrow { opacity: 1; visibility: visible; transform: translateY(0); }

    /* ==========================================================
       🛠️ FIXED HIGH-CONTRAST MODALS (FOR THEME 1 & THEME 3)
       ========================================================== */
    .modal { 
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0, 0, 0, 0.8) !important; backdrop-filter: blur(8px); 
        display: flex; align-items: center; justify-content: center; z-index: 2000; 
        opacity: 0; visibility: hidden; transition: all 0.3s ease; 
    }
    .modal.open { opacity: 1; visibility: visible; }
    
    .modal-content { 
        width: 500px; padding: 35px 30px; border-radius: 24px; position: relative; 
        transform: scale(0.85); transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        background: #0D140B !important; 
        border: 2px solid #D4AF37 !important; 
        box-shadow: 0 20px 50px rgba(0,0,0,0.8), 0 0 30px rgba(212, 175, 55, 0.3) !important;
        color: #F4F6F0 !important;
    }
    .modal.open .modal-content { transform: scale(1); }
    
    /* Close Modal Icon (×) */
    .close-modal { 
        position: absolute; top: 20px; right: 25px; font-size: 26px; 
        color: #D4AF37 !important; cursor: pointer; font-weight: 700; transition: 0.2s;
    }
    .close-modal:hover { color: #FFFFFF !important; transform: scale(1.1); }
    
    /* Modal Header & Title */
    .modal-content h2 { 
        font-size: 22px; color: #D4AF37 !important; margin-bottom: 22px; 
        display: flex; align-items: center; gap: 10px; font-weight: 700; 
    }
    .modal-content h2 i { color: #A3E61D !important; text-shadow: 0 0 10px rgba(163, 230, 53, 0.5); }
    
    /* Form Labels (High Contrast Bright Text) */
    .form-group { margin-bottom: 18px; display: flex; flex-direction: column; gap: 6px; }
    .form-group label { 
        font-size: 13px; color: #FFFFFF !important; font-weight: 700 !important; 
        display: flex; align-items: center; gap: 8px; letter-spacing: 0.3px;
    }
    .form-group label i { color: #D4AF37 !important; }
    
    /* Input Fields, Select & Textarea */
    .form-group input, .form-group select, .form-group textarea { 
        width: 100%; padding: 12px 16px; border-radius: 12px; 
        border: 2px solid rgba(212, 175, 55, 0.4) !important; 
        background: #FFFFFF !important; color: #0F2005 !important; 
        outline: none; font-size: 14px; font-weight: 600; 
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { 
        border-color: #D4AF37 !important; box-shadow: 0 0 12px rgba(212, 175, 55, 0.4); 
    }
    .form-group textarea { resize: none; font-family: inherit; }

    /* High-Contrast Gold/Dark Submit Button */
    .submit-btn { 
        width: 100%; background: #0F2005 !important; color: #D4AF37 !important; 
        border: 2px solid #D4AF37 !important; padding: 13px; border-radius: 35px; 
        font-weight: 700; font-size: 15px; cursor: pointer; margin-top: 12px; 
        display: flex; align-items: center; justify-content: center; gap: 10px; 
        box-shadow: 0 6px 20px rgba(0,0,0,0.5); transition: all 0.3s ease; 
    }
    .submit-btn:hover { 
        background: #D4AF37 !important; color: #000000 !important; 
        border-color: #FFFFFF !important; transform: translateY(-2px); 
        box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4); 
    }

    /* 💡 Theme 3 Specific Overrides for Bright Jugnu Text */
    body.card-mode-amber .modal-content h2 { color: #A3E61D !important; text-shadow: 0 0 10px rgba(163, 230, 53, 0.5); }
    body.card-mode-amber .form-group label { color: #FFFFFF !important; }
    body.card-mode-amber .close-modal { color: #A3E61D !important; }

    /* Top Alert Notification */
    .top-alert { position: fixed; top: 25px; left: 50%; transform: translateX(-50%); z-index: 5000; padding: 14px 30px; border-radius: 30px; font-weight: 700; font-size: 14px; background: #0F2005; color: #D4AF37; border: 2px solid #FFFFFF; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }

    /* FIXED & ALIGNED MODERN FOOTER SYSTEM */
    .footer-wrapper { width: 100%; margin-top: auto; border-top: 3px solid #D4AF37; background: rgba(10, 15, 8, 0.9); box-shadow: 0 -10px 35px rgba(0, 0, 0, 0.5); position: relative; z-index: 5; }
    .footer { max-width: 1400px; width: 85%; margin: 0 auto; padding: 50px 0; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 20px; color: #F4F6F0; }
    .footer h3 { font-size: 22px; font-weight: 700; color: #D4AF37; letter-spacing: 1px; text-shadow: 0 0 10px rgba(212, 175, 55, 0.3); }
    .footer p { font-size: 14px; color: rgba(244, 246, 240, 0.75); max-width: 600px; line-height: 1.6; }
    .social-icons { display: flex; gap: 18px; margin-top: 8px; }
    .social-icons a { 
        color: #0F2005; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 50%; text-decoration: none; background: #D4AF37; border: 2px solid #FFFFFF; font-size: 18px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), background 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease; 
    }
    .social-icons a:hover { background: #000000; color: #FFFFFF; border-color: #D4AF37; transform: translateY(-5px); box-shadow: 0 10px 20px rgba(212, 175, 55, 0.4); }
    .footer-copyright { font-size: 12px !important; color: rgba(244, 246, 240, 0.4) !important; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 15px; width: 100%; margin-top: 10px; }

    /* Chart Box Custom Alignment */
    .chart-container { position: relative; width: 100%; max-height: 250px; display: flex; justify-content: center; }

    /* FIXED AND HIGH-CONTRAST CURRENCY CONVERTER GRID STYLING */
    .converter-row-block { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: center; width: 100%; }
    .converter-group { display: flex; flex-direction: column; gap: 6px; }
    .converter-group label { font-size: 12px; font-weight: 600; color: #FFFFFF !important; opacity: 0.9; letter-spacing: 0.5px; }
    .converter-group select, .converter-group input {
        width: 100%; padding: 12px 14px; border-radius: 12px; border: 2px solid #D4AF37; background: #0F2005 !important; color: #FFFFFF !important; font-size: 14px; font-weight: 600; outline: none; box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);
    }
    .converter-group select option { background: #0F2005 !important; color: #FFFFFF !important; }
    .converter-group input:focus, .converter-group select:focus { border-color: #FFFFFF; box-shadow: 0 0 12px rgba(212, 175, 55, 0.5); }

    /* Professional Output Showcase */
    .converter-display-panel { display: flex; justify-content: space-between; align-items: center; margin-top: 25px; padding: 20px; border-radius: 16px; background: rgba(0, 0, 0, 0.45); border: 2px dashed rgba(212, 175, 55, 0.6); flex-wrap: wrap; gap: 15px; }
    .converter-display-meta { text-align: left; }
    .converter-display-meta p { font-size: 13px; opacity: 0.8; color: #F4F6F0 !important; }
    .converter-display-meta h3 { font-size: 24px; color: #D4AF37 !important; font-weight: 800; margin-top: 4px; text-shadow: 0 0 10px rgba(212, 175, 55, 0.3); }
    .live-status-badge { font-size: 11px; background: #22C55E; color: #000000 !important; padding: 6px 14px; border-radius: 30px; font-weight: 700; box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3); display: flex; align-items: center; gap: 6px; }

@media screen and (max-width: 1024px) { .main-container { width: 92%; } .footer { width: 92%; } .double-layout { grid-template-columns: 1fr; } }
@media screen and (max-width: 768px) {
    .navbar { padding: 15px 20px; flex-direction: column; gap: 15px; text-align: center; }
    .theme-picker-box { width: 100%; justify-content: center; }
    .welcome-section { flex-direction: column; gap: 20px; padding: 30px 20px; text-align: center; }
    .live-clock { width: 100%; text-align: center; }
    .modern-slider-container { height: auto; padding: 20px; }
    .slide { position: relative; flex-direction: column; gap: 10px; text-align: center; opacity: 1 !important; transform: none !important; display: none; }
    .slide.active { display: flex; }
    .slider-dots { position: relative; right: 0; margin-top: 15px; justify-content: center; }
    .stats { grid-template-columns: 1fr 1fr; gap: 15px; }
    .services { grid-template-columns: 1fr; gap: 20px; }
    .modal-content { width: 90%; padding: 25px 20px; }
}

@media screen and (max-width: 480px) { .stats { grid-template-columns: 1fr; } .welcome-text h1 { font-size: 22px; } .user-profile { width: 100%; justify-content: center; } }
</style>
</head>
<body class="card-mode-rosegold">

<div class="currency-bg-portal">
    <div class="floating-asset">₨</div>
    <div class="floating-asset">$</div>
    <div class="floating-asset">€</div>
    <div class="floating-asset">£</div>
    <div class="floating-asset">₨</div>
    <div class="floating-asset">$</div>
    <div class="floating-asset">€</div>
    <div class="floating-asset">£</div>
</div>

<?php 
if(isset($_GET['status']) && $_GET['status'] == 'success') {
    echo "<div class='top-alert' id='topAlert'><i class='fas fa-check-circle'></i> Settings/Transaction Saved Successfully!</div>";
}
if(isset($_GET['status']) && $_GET['status'] == 'deleted') {
    echo "<div class='top-alert' id='topAlert' style='background: #EF4444;'><i class='fas fa-trash-alt'></i> Record Removed Successfully!</div>";
}
?>

<!-- ADD EXPENSE MODAL -->
<div class="modal" id="quickAddModal">
    <div class="modal-content">
        <span class="close-modal" onclick="toggleModal(false, 'quickAddModal')">&times;</span>
        <h2><i class="fas fa-bolt"></i> Instant Log Expense</h2>
        
        <form id="modalForm" method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-shopping-basket"></i> Item Name</label>
                <input type="text" name="item_name" placeholder="e.g. Eggs, Coffee" required autocomplete="off">
            </div>
            <div class="form-group">
                <label><i class="fas fa-money-bill-wave"></i> Amount (Rs.)</label>
                <input type="number" name="amount" step="0.01" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-tags"></i> Category (Select or Type Custom)</label>
                <input type="text" name="category" list="categoryOptions" placeholder="Type custom or select from list..." required autocomplete="off" style="pointer-events: auto;">
                <datalist id="categoryOptions">
                    <option value="Groceries">
                    <option value="Utilities">
                    <option value="Entertainment">
                    <option value="Transportation">
                    <option value="Other">
                </datalist>
            </div>
            <div class="form-group">
                <label><i class="fas fa-calendar-day"></i> Date</label>
                <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-sticky-note"></i> Description / Notes (Optional)</label>
                <textarea name="description" rows="2" placeholder="Brief notes about this entry..."></textarea>
            </div>
            <button type="submit" name="add_expense_ajax" id="modalSubmitBtn" class="submit-btn">
                <span id="modalBtnText">Add Expense</span>
                <div id="modalSpinner" class="spinner"></div>
            </button>
        </form>
    </div>
</div>

<div class="modal" id="settingsModal">
    <div class="modal-content">
        <span class="close-modal" onclick="toggleModal(false, 'settingsModal')">&times;</span>
        <h2><i class="fas fa-sliders-h"></i> Financial Settings</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-wallet"></i> Monthly Income Vault (Rs.)</label>
                <input type="number" name="monthly_income" value="<?php echo $monthly_income; ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-bullseye"></i> Monthly Spending Alert Limit (Rs.)</label>
                <input type="number" name="monthly_budget" value="<?php echo $monthly_budget; ?>" step="0.01" required>
            </div>
            <button type="submit" name="update_limits" class="submit-btn">Save Configurations</button>
        </form>
    </div>
</div>

<div class="floating-actions-stack">
    <button class="fab-btn" onclick="toggleModal(true, 'quickAddModal')" title="Instant Add Expense"><i class="fas fa-plus"></i></button>
    <button class="fab-btn" onclick="toggleModal(true, 'settingsModal')" title="Financial Target Limits"><i class="fas fa-sliders-h"></i></button>
    <button id="scrollToTopBtn" class="fab-btn" onclick="scrollToTop()" title="Go to Top"><i class="fas fa-chevron-up"></i></button>
</div>

<div class="navbar">
    <div class="custom-brand-logo">
        <div class="cropped-logo-icon"><img src="logo.png" alt="PET Icon"></div>
        <div class="brand-titles">
            <span class="main-title">PERSONAL EXPENSE</span>
            <span class="sub-title">TRACKER PRO</span>
        </div>
    </div>
    
    <!-- ✨ ALIGNED 3 CLEAN THEME BUTTONS ONLY -->
    <div class="theme-picker-box">
        <span onclick="setCardTheme('rosegold', this)" class="theme-btn active" style="background: #0D140B;" title="Emerald Glass Theme"></span>
        <span onclick="setCardTheme('mint', this)" class="theme-btn" style="background: #E5C158;" title="Bright Amber Gold"></span>
        <span onclick="setCardTheme('amber', this)" class="theme-btn" style="background: radial-gradient(circle, #A3E61D 20%, #070F05 80%); box-shadow: 0 0 8px #A3E61D;" title="Midnight Jugnu Glow Mode"></span>
    </div>
    
    <div style="display:flex; align-items:center; gap:15px;">
        <div class="user-profile">
            <i class="fas fa-user-circle"></i>
            <span style="text-transform: capitalize; font-weight:600;">Welcome, <?php echo htmlspecialchars($session_username); ?></span>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-container">
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Personal Expense Tracker Dashboard</h1>
            <p>Track your efficiency, view logs and manage budgets smartly.</p>
        </div>
        <div class="live-clock" id="clock">00:00:00 AM</div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
        <!-- BUDGET PROGRESS THRESHOLD COMPONENT -->
        <div class="custom-panel" id="budgetWidgetPanel">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h2><i class="fas fa-shield-alt"></i> Real-time Budget Sentry</h2>
                <button class="export-btn" style="padding: 5px 12px; font-size: 11px;" onclick="toggleModal(true, 'settingsModal')"><i class="fas fa-edit"></i> Edit Targets</button>
            </div>
            <div class="budget-progress-container">
                <div class="budget-header-labels">
                    <span>Monthly Limit: Rs. <span id="budgetLimitVal"><?php echo number_format($monthly_budget, 2); ?></span></span>
                    <span id="budgetPercentVal">0% Used</span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" id="progressBarFill"></div>
                </div>
                <p style="font-size: 11px; margin-top: 8px; opacity: 0.8; display: flex; align-items: center; gap: 5px;" id="thresholdStatusMsg">
                    <i class="fas fa-check-circle"></i> Safe threshold active. Logs evaluated dynamically.
                </p>
            </div>
        </div>

        <!-- HORIZONTAL DYNAMIC CURRENCY CONVERTER -->
        <div class="custom-panel">
            <h2><i class="fas fa-globe"></i> Dynamic Currency Converter</h2>
            <div class="converter-row-block">
                <div class="converter-group">
                    <label>From Currency</label>
                    <select id="fromCurrency">
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="AED">AED - UAE Dirham</option>
                        <option value="SAR">SAR - Saudi Riyal</option>
                        <option value="PKR">PKR - Pak Rupee</option>
                    </select>
                </div>
                <div class="converter-group">
                    <label>Enter Amount</label>
                    <input type="number" id="convertAmount" value="1" step="any" oninput="performConversion()">
                </div>
                <div class="converter-group">
                    <label>To Currency</label>
                    <select id="toCurrency" onchange="performConversion()">
                        <option value="PKR">PKR - Pak Rupee</option>
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="AED">AED - UAE Dirham</option>
                        <option value="SAR">SAR - Saudi Riyal</option>
                    </select>
                </div>
            </div>
            <div class="converter-display-panel">
                <div class="converter-display-meta">
                    <p id="conversionRateMeta">1 USD = Loading...</p>
                    <h3 id="conversionResultVal">Calculating...</h3>
                </div>
                <div class="live-status-badge"><i class="fas fa-circle-notch fa-spin"></i> Rates Live</div>
            </div>
        </div>
    </div>

    <div class="modern-slider-container">
        <div class="slider-track">
            <div class="slide active">
                <i class="fas fa-lightbulb"></i>
                <div class="slide-content">
                    <h4>Smart Budgeting Tip</h4>
                    <p>💡 "Do not save what is left after spending, but spend what is left after saving." — Warren Buffett</p>
                </div>
            </div>
            <div class="slide">
                <i class="fas fa-chart-line"></i>
                <div class="slide-content">
                    <h4>The 50/30/20 Rule</h4>
                    <p>⚖️ Allocate 50% of income for your Needs, 30% for Wants, and instantly log 20% into your Savings vault. 💰</p>
                </div>
            </div>
            <div class="slide">
                <i class="fas fa-shield-halved"></i>
                <div class="slide-content">
                    <h4>Financial Awareness</h4>
                    <p>⚠️ "Beware of little expenses; a small leaky pipe will sink a massive royal ship." — Benjamin Franklin 🔍</p>
                </div>
            </div>
        </div>
        <div class="slider-dots">
            <span class="dot active" onclick="currentSlide(0)"></span>
            <span class="dot" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
        </div>
    </div>

    <div class="stats">
        <div class="stat-box" style="border-left: 5px solid #22C55E;">
            <i class="fas fa-hand-holding-usd" style="color: #22C55E;"></i>
            <h2>Rs. <?php echo number_format($monthly_income, 2); ?></h2>
            <p>Monthly Income</p>
        </div>
        <div class="stat-box" style="border-left: 5px solid #EF4444;">
            <i class="fas fa-wallet" style="color: #EF4444;"></i>
            <h2>Rs. <?php echo number_format($month_total, 2); ?></h2>
            <p>Monthly Expenses</p>
        </div>
        <div class="stat-box" style="border-left: 5px solid <?php echo ($net_savings < 0) ? '#EF4444' : '#3B82F6'; ?>;">
            <i class="fas fa-piggy-bank" style="color: <?php echo ($net_savings < 0) ? '#EF4444' : '#3B82F6'; ?>;"></i>
            <h2>Rs. <?php echo number_format($net_savings, 2); ?></h2>
            <p>Net Savings State</p>
        </div>
        <div class="stat-box">
            <i class="fas fa-fire"></i>
            <h2 style="text-transform: capitalize;"><?php echo htmlspecialchars($top_category); ?></h2>
            <p>Top Spending Tier</p>
        </div>
    </div>

    <div class="double-layout">
        <div class="custom-panel">
            <h2><i class="fas fa-chart-pie"></i> Visual Expenditure Breakdown</h2>
            <p style="font-size: 11px; opacity: 0.7; margin-top: -15px;">Real-time categorical analytics mapping (Chart.js engine)</p>
            <div class="chart-container"><canvas id="categoryExpenseChart"></canvas></div>
        </div>

        <div class="custom-panel" id="tableExportZone">
            <div class="log-filter-header">
                <h2><i class="fas fa-history"></i> Rapid Data Auditing</h2>
                <div class="search-input-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="logSearchInput" onkeyup="filterActivityLogs()" placeholder="Live search records...">
                </div>
            </div>
            <div class="export-btns">
                <button class="export-btn" onclick="exportDataExcel()"><i class="fas fa-file-excel"></i> Excel Worksheet</button>
                <button class="export-btn" onclick="exportDataPDF()"><i class="fas fa-file-pdf"></i> PDF Document</button>
            </div>
            <div class="custom-table-wrapper">
                <table class="custom-table" id="expenseLogTable">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        <?php if (mysqli_num_rows($logs_q) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($logs_q)): ?>
                                <tr class="log-record-row">
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($row['item_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower(str_replace('Groceries', 'grocery', str_replace('Transportation', 'transport', $row['category']))); ?>">
                                            <?php echo htmlspecialchars($row['category']); ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 600;">Rs. <?php echo number_format($row['amount'], 2); ?></td>
                                    <td><?php echo date('d M, Y', strtotime($row['expense_date'])); ?></td>
                                    <td class="action-icons">
                                        <button onclick="confirmRemoval(<?php echo $row['id']; ?>)" class="action-btn delete" title="Delete record"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; opacity: 0.6; padding: 20px;">No transaction logs logged in database. Click '+' to load transactions.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="services">
        <div class="card">
            <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80" alt="Add Expense" class="card-img">
            <i class="fas fa-plus-circle"></i>
            <h3>Add Expense</h3>
            <p>Quickly add new financial transactions or items into logs with categories.</p>
            <a href="add_expense.php" class="btn">Open Form</a>
        </div>
        
        <div class="card">
            <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=600&auto=format&fit=crop&q=80" alt="View History" class="card-img">
            <i class="fas fa-list"></i>
            <h3>View Expenses</h3>
            <p>View all recorded expense entries, details and remove items smoothly.</p>
            <a href="view_expenses.php" class="btn">Open Logs</a>
        </div>
        <div class="card">
            <img src="https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=600&auto=format&fit=crop&q=80" alt="Budget Guard" class="card-img">
            <i class="fas fa-shield-alt"></i>
            <h3>Budget Guard</h3>
            <p>Set custom monthly category thresholds and secure your spending parameters.</p>
            <a href="reports.php" class="btn">Open Guard Console</a>
        </div>
    </div>
</div>

<div class="footer-wrapper">
    <footer class="footer">
        <h3><i class="fas fa-wallet"></i> Personal Expense Tracker Pro</h3>
        <p>Smart and elegant way to manage your daily budget, tracks categorised grocery logs, and protects your financial parameters with high reliability.</p>
        <div class="social-icons">
            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <p class="footer-copyright">&copy; <?php echo date('Y'); ?> Personal Expense Tracker. All Rights Reserved. Crafted for Professional Excellence.</p>
    </footer>
</div>

<script>
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12; hours = hours ? hours : 12;
        document.getElementById('clock').textContent = `${String(hours).padStart(2,'0')}:${minutes}:${seconds} ${ampm}`;
    }
    setInterval(updateClock, 1000); updateClock();

    let currentSlideIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');

    function showSlide(index) {
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        currentSlideIndex = index;
    }
    function nextSlide() { showSlide((currentSlideIndex + 1) % slides.length); }
    function currentSlide(idx) { showSlide(idx); clearInterval(sliderInterval); sliderInterval = setInterval(nextSlide, 6000); }
    let sliderInterval = setInterval(nextSlide, 6000);

    function toggleModal(action, id) {
        const modal = document.getElementById(id);
        if(action) { modal.classList.add('open'); } else { modal.classList.remove('open'); }
    }

    document.getElementById('modalForm').addEventListener('submit', function() {
        document.getElementById('modalSubmitBtn').style.pointerEvents = 'none';
        document.getElementById('modalBtnText').textContent = 'Logging Entry...';
        document.getElementById('modalSpinner').style.display = 'inline-block';
    });

    const alertBanner = document.getElementById('topAlert');
    if(alertBanner) {
        setTimeout(() => {
            alertBanner.style.transition = '0.4s ease';
            alertBanner.style.opacity = '0';
            setTimeout(() => alertBanner.remove(), 400);
        }, 3500);
    }

    function setCardTheme(modeName, element) {
        document.body.className = '';
        document.body.classList.add('card-mode-' + modeName);
        localStorage.setItem('savedCardModeDistinct', 'card-mode-' + modeName);
        document.querySelectorAll('.theme-btn').forEach(b => b.classList.remove('active'));
        if(element) element.classList.add('active');
    }

    const scrollBtn = document.getElementById("scrollToTopBtn");
    window.onscroll = function() {
        if (document.body.scrollTop > 250 || document.documentElement.scrollTop > 250) {
            scrollBtn.classList.add("show-arrow");
        } else {
            scrollBtn.classList.remove("show-arrow");
        }
    };
    function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }

    window.addEventListener('DOMContentLoaded', () => {
        const savedMode = localStorage.getItem('savedCardModeDistinct');
        if (savedMode && savedMode !== 'card-mode-rosegold') {
            document.body.className = '';
            document.body.classList.add(savedMode);
            document.querySelectorAll('.theme-btn').forEach(btn => {
                if(btn.getAttribute('onclick').includes(savedMode.replace('card-mode-', ''))) {
                    btn.classList.add('active');
                } else { btn.classList.remove('active'); }
            });
        } else {
            setCardTheme('rosegold', document.querySelectorAll('.theme-btn')[0]);
        }
    });

    function confirmRemoval(expId) {
        if (confirm("Are you absolutely sure you want to remove this financial log permanently?")) {
            window.location.href = "dashboard.php?delete_expense=" + expId;
        }
    }

    function exportDataExcel() {
        const table = document.getElementById("expenseLogTable");
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Expenditures" });
        XLSX.writeFile(workbook, "Personal_Expense_Report.xlsx");
    }

    // PDF Print Logic
    function exportDataPDF() {
        const table = document.getElementById("expenseLogTable");
        let rowsHtml = "";
        const rows = table.querySelectorAll("tbody tr");
        rows.forEach(row => {
            const cells = row.querySelectorAll("td");
            if(cells.length >= 4) {
                rowsHtml += `<tr><td>${cells[0].innerText}</td><td>${cells[1].innerText}</td><td>${cells[2].innerText}</td><td>${cells[3].innerText}</td></tr>`;
            }
        });

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html><head><title>Financial Audit Report</title><style>
                body { font-family: 'Arial', sans-serif; padding: 40px; color: #333; background: #fff; }
                h2 { text-align: center; color: #1F2E19; font-size: 24px; margin-bottom: 5px; }
                p.sub { text-align: center; font-size: 13px; color: #666; margin-bottom: 30px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #F1F5F9; color: #1E293B; font-weight: bold; padding: 12px; text-align: left; border-bottom: 2px solid #CBD5E1; }
                td { padding: 12px; border-bottom: 1px solid #E2E8F0; font-size: 14px; }
                tr:nth-child(even) { background-color: #F8FAFC; }
            </style></head><body>
                <h2>Financial Audit Report</h2><p class="sub">Generated on: ${new Date().toLocaleDateString()}</p>
                <table><thead><tr><th>Item Name</th><th>Category</th><th>Amount</th><th>Date</th></tr></thead><tbody>${rowsHtml}</tbody></table>
                <script>window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 500); };<\/script>
            </body></html>
        `);
        printWindow.document.close();
    }

    function filterActivityLogs() {
        const input = document.getElementById("logSearchInput").value.toUpperCase();
        const rows = document.querySelectorAll(".log-record-row");
        rows.forEach(row => {
            const cells = row.getElementsByTagName("td");
            let rowContainsText = false;
            for(let i = 0; i < cells.length - 1; i++) {
                if(cells[i].textContent.toUpperCase().includes(input)) { rowContainsText = true; break; }
            }
            row.style.display = rowContainsText ? "" : "none";
        });
    }

    // REAL-TIME BUDGET SENTRY ACTIVE
    const currentMonthExpenses = <?php echo $month_total; ?>;
    const configuredLimit = <?php echo $monthly_budget; ?>;
    const progressFillElement = document.getElementById("progressBarFill");
    const percentValElement = document.getElementById("budgetPercentVal");
    const statusMsgElement = document.getElementById("thresholdStatusMsg");
    const widgetPanelElement = document.getElementById("budgetWidgetPanel");

    let usagePercentage = (currentMonthExpenses / configuredLimit) * 100;
    if (usagePercentage > 100) usagePercentage = 100;
    
    progressFillElement.style.width = usagePercentage.toFixed(1) + "%";
    percentValElement.textContent = usagePercentage.toFixed(1) + "% Used";

    widgetPanelElement.classList.remove("playful-danger-mode");

    if (usagePercentage < 50) {
        progressFillElement.style.backgroundColor = "#22C55E";
        statusMsgElement.innerHTML = `<i class="fas fa-smile-beam" style="color: #22C55E;"></i> All good, boss! You're still in the safe zone. 😎`;
    } else if (usagePercentage >= 50 && usagePercentage < 85) {
        progressFillElement.style.backgroundColor = "#EAB308";
        statusMsgElement.innerHTML = `<i class="fas fa-grimace" style="color: #EAB308;"></i> Heads up! Spending crossed 50%, time to slow down! 🤭`;
    } else {
        progressFillElement.style.backgroundColor = "#EF4444";
        widgetPanelElement.classList.add("playful-danger-mode");
        statusMsgElement.innerHTML = `<i class="fas fa-sad-tear" style="color: #EF4444;"></i> <b>Whoa there! Your budget is crying for a break. 🛑</b>`;
    }

    // PIE CHART ENGINE (CHART.JS)
    const chartLabels = <?php echo json_encode($chart_categories); ?>;
    const chartDataValues = <?php echo json_encode($chart_amounts); ?>;
    const ctx = document.getElementById('categoryExpenseChart').getContext('2d');
    const chartColors = ['#FF5733', '#FF8D1A', '#FF33A8', '#1AD1D5', '#E5C158', '#22C55E'];

    const expenseChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartLabels.length ? chartLabels : ['No Data Logged'],
            datasets: [{
                data: chartDataValues.length ? chartDataValues : [1],
                backgroundColor: chartLabels.length ? chartColors.slice(0, chartLabels.length) : ['rgba(255,255,255,0.15)'],
                borderColor: 'rgba(255,255,255,0.2)',
                borderWidth: 1.5,
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { color: '#FFFFFF', font: { family: 'Poppins', size: 11, weight: '500' } } } },
            cutout: '65%'
        }
    });

    // CURRENCY CONVERTER SCRIPT
    const fromSelect = document.getElementById('fromCurrency');
    const toSelect = document.getElementById('toCurrency');
    const amountInput = document.getElementById('convertAmount');
    const rateMeta = document.getElementById('conversionRateMeta');
    const resultVal = document.getElementById('conversionResultVal');
    const apiURL = "https://open.er-api.com/v6/latest/USD";
    let cachedRates = null;

    async function fetchExchangeRates() {
        try {
            const response = await fetch(apiURL);
            const data = await response.json();
            if(data && data.rates) { cachedRates = data.rates; performConversion(); } 
            else { rateMeta.textContent = "Error fetching live rates."; }
        } catch(error) {
            rateMeta.textContent = "Network error. Offline rates loaded.";
            cachedRates = { "USD": 1, "PKR": 278.50, "EUR": 0.92, "GBP": 0.79, "AED": 3.67, "SAR": 3.75 };
            performConversion();
        }
    }

    function performConversion() {
        if(!cachedRates) return;
        const fromCurrency = fromSelect.value;
        const toCurrency = toSelect.value;
        const amount = parseFloat(amountInput.value) || 0;
        const rateInUSD = cachedRates[fromCurrency];
        const amountInUSD = amount / rateInUSD;
        const targetRate = cachedRates[toCurrency];
        const finalResult = amountInUSD * targetRate;
        const unitRate = (1 / rateInUSD) * targetRate;
        rateMeta.textContent = `1 ${fromCurrency} = ${unitRate.toFixed(4)} ${toCurrency}`;
        resultVal.textContent = `${finalResult.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${toCurrency}`;
    }
    fromSelect.addEventListener('change', performConversion);
    window.addEventListener('load', fetchExchangeRates);
</script>
</body>
</html>