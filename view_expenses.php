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

// Delete Action
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']); 
    mysqli_query($conn, "DELETE FROM expenses WHERE id='$delete_id' AND user_id='$user_id'"); 
    header("Location: view_expenses.php"); 
    exit(); 
}

// Fetching all expenses
$result = mysqli_query($conn, "SELECT * FROM expenses WHERE user_id='$user_id' ORDER BY expense_date DESC"); 

$total_outflow = 0;
$peak_burn = 0;
$log_count = 0;
$expenses_array = [];

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $expenses_array[] = $row;
        $total_outflow += $row['amount'];
        if ($row['amount'] > $peak_burn) {
            $peak_burn = $row['amount'];
        }
        $log_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ledger History | Personal Expense Tracker Pro</title>

<link rel="icon" type="image/png" href="logo.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    *{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; transition: background 0.4s ease, color 0.4s ease, box-shadow 0.4s ease, transform 0.2s ease; }
    
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
    .floating-asset:nth-child(1) { left: 5%; animation-duration: 25s; animation-delay: 0s; font-size: 3rem; }
    .floating-asset:nth-child(2) { left: 18%; animation-duration: 18s; animation-delay: 4s; font-size: 4.5rem; filter: blur(3px); color: rgba(212, 175, 55, 0.06); }
    .floating-asset:nth-child(3) { left: 35%; animation-duration: 28s; animation-delay: 1s; font-size: 2.2rem; }
    .floating-asset:nth-child(4) { left: 55%; animation-duration: 16s; animation-delay: 7s; font-size: 5rem; filter: blur(4px); color: rgba(212, 175, 55, 0.04); }
    .floating-asset:nth-child(5) { left: 72%; animation-duration: 22s; animation-delay: 2s; font-size: 3.8rem; filter: blur(1px); }
    .floating-asset:nth-child(6) { left: 88%; animation-duration: 26s; animation-delay: 10s; font-size: 2.5rem; }

    /* --- DYNAMIC THEME SYSTEM --- */
    body.card-mode-rosegold {
        --body-bg: linear-gradient(135deg, #050A03, #121A0F); --card-bg: #0D140B; --text-primary: #D4AF37; --glow-color: rgba(212, 175, 55, 0.3); --table-border: rgba(212, 175, 55, 0.2); --badge-bg: rgba(212, 175, 55, 0.1);
    }
    body.card-mode-ivory {
        --body-bg: linear-gradient(135deg, #0A0D08, #151B11); --card-bg: #0A0F08; --text-primary: #F3E5AB; --glow-color: rgba(243, 229, 171, 0.25); --table-border: rgba(243, 229, 171, 0.15); --badge-bg: rgba(243, 229, 171, 0.08);
    }
    body.card-mode-mint {
        --body-bg: linear-gradient(135deg, #081205, #1A2914); --card-bg: #0E180D; --text-primary: #C5A028; --glow-color: rgba(197, 160, 40, 0.25); --table-border: rgba(197, 160, 40, 0.15); --badge-bg: rgba(197, 160, 40, 0.08);
    }
    body.card-mode-amber {
        --body-bg: linear-gradient(135deg, #010401, #081005); --card-bg: #111A0E; --text-primary: #E5A93C; --glow-color: rgba(229, 169, 60, 0.3); --table-border: rgba(229, 169, 60, 0.2); --badge-bg: rgba(229, 169, 60, 0.1);
    }

    body { background: var(--body-bg); }

    /* Navbar */
    .navbar{ 
        display:flex; justify-content:space-between; align-items:center; padding:20px 50px; 
        background: rgba(10, 15, 8, 0.85); backdrop-filter: blur(15px);
        border-bottom: 2px solid var(--text-primary); box-shadow: 0 4px 30px rgba(0,0,0,0.5);
        position: sticky; top: 0; z-index: 99;
    }
    .logo { font-size:22px; font-weight:700; color: #FAF8F5; text-decoration: none; display: flex; align-items: center; gap: 10px; }
    .logo i { color: var(--text-primary); text-shadow: 0 0 10px var(--glow-color); }
    
    .back-btn { background: #D4AF37; color: #0F2005; padding:9px 20px; border-radius:30px; text-decoration:none; font-weight:600; font-size:14px; border: 1px solid rgba(255,255,255,0.2); }
    .back-btn:hover { background: #FAF8F5; color: #000; box-shadow: 0 0 15px var(--glow-color); }

    /* Main Grid & Panels */
    .workspace-wrapper {
        width: 92%; max-width: 1400px; margin: 40px auto;
        display: flex; flex-direction: column; gap: 30px; position: relative; z-index: 5;
    }

    /* STATS ROW WIDGETS */
    .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .kpi-card {
        background: var(--card-bg); border: 2px solid var(--table-border); border-radius: 16px;
        padding: 24px; display: flex; align-items: center; justify-content: space-between;
        box-shadow: 0 8px 30px rgba(0,0,0,0.4); position: relative; overflow: hidden;
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    }
    .kpi-card::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 4px; background: var(--text-primary); }
    .kpi-details h3 { font-size: 13px; color: #CBD5E1; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; }
    .kpi-details p { font-size: 24px; font-weight: 700; color: #FFF; margin-top: 5px; }
    .kpi-icon {
        width: 50px; height: 50px; border-radius: 12px; background: rgba(255,255,255,0.03);
        display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--text-primary);
        box-shadow: inset 0 0 10px rgba(255,255,255,0.02);
    }

    /* SEARCH FILTER BAR */
    .search-control-bar {
        background: var(--card-bg); border: 2px solid var(--table-border); border-radius: 16px;
        padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3); gap: 20px;
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    }
    .search-input-box { position: relative; flex-grow: 1; max-width: 450px; }
    .search-input-box i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-primary); font-size: 15px; }
    
    .search-input-box input {
        width: 100%; padding: 12px 16px 12px 46px; border-radius: 30px;
        border: 2px solid var(--table-border); background: rgba(0,0,0,0.4);
        color: #FFF; outline: none; font-size: 14px; font-weight: 600;
    }
    .search-input-box input:focus { border-color: var(--text-primary); box-shadow: 0 0 10px var(--glow-color); }
    .search-meta { font-size: 13px; color: #CBD5E1; font-weight: 600; }

    /* Modern Dark Card Container */
    .table-container {
        background: var(--card-bg); border: 2px solid var(--table-border); 
        padding: 40px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    }
    
    .table-header-flex { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
    .table-header-flex i { font-size: 24px; color: var(--text-primary); text-shadow: 0 0 12px var(--glow-color); }
    h2 { font-size: 24px; color: #FFFFFF; font-weight: 700; letter-spacing: 0.5px; }

    /* Sleek Glow Table Design */
    table { width: 100%; border-collapse: collapse; }
    
    th { 
        padding: 18px 15px; text-align: left; font-size: 13px; 
        color: var(--text-primary); font-weight: 700; letter-spacing: 0.8px;
        border-bottom: 2px solid var(--text-primary); text-transform: uppercase;
        box-shadow: 0 2px 10px -2px var(--glow-color);
    }
    
    td { padding: 20px 15px; border-bottom: 2px solid var(--table-border); color: #E2E8F0; font-size: 14px; font-weight: 500; }
    tr { transition: transform 0.2s ease, background-color 0.2s ease; }
    tr:hover td { background: rgba(255, 255, 255, 0.04); }
    tr:hover { transform: scale(1.002); }

    /* Glowing Text & Pill Badge Fields */
    .item-name-cell { font-weight: 700; color: #FFFFFF; text-transform: capitalize; }
    .date-cell { color: #CBD5E1; white-space: nowrap; font-weight: 600; }
    
    .desc-cell { font-weight: 500; color: #E2E8F0; font-size: 13px; max-width: 280px; word-wrap: break-word; line-height: 1.5; }
    .no-desc { color: #7F8C8D; font-size: 12px; font-style: italic; }

    .amount-cell { font-weight: 700; color: var(--text-primary); text-shadow: 0 0 8px var(--glow-color); white-space: nowrap; }
    
    /* Category Capsules */
    .category-badge {
        background: rgba(0,0,0,0.3); color: #FFFFFF;
        padding: 6px 14px; border-radius: 20px; font-size: 12px;
        font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
        border: 1px solid var(--text-primary);
    }

    /* Action Delete Icon */
    .delete-btn { 
        color: #EF4444; text-decoration: none; font-size: 16px; 
        padding: 8px 12px; border-radius: 8px; display: inline-block;
        background: rgba(239, 68, 68, 0.05); transition: 0.2s;
    }
    .delete-btn:hover { background: rgba(239, 68, 68, 0.15); transform: scale(1.1); color: #F87171; }
    
    .no-data { text-align: center; padding: 60px; color: #A0AEC0; font-style: italic; font-size: 15px; }

    /* CUSTOM DYNAMIC DELETE MODAL */
    .custom-modal-overlay {
        position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.8);
        backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 1000; opacity: 0; transition: opacity 0.3s ease;
    }
    .custom-modal {
        background: #0D140B; border: 2px solid #EF4444; width: 420px; padding: 35px 30px;
        border-radius: 20px; box-shadow: 0 15px 40px rgba(239,68,68,0.2); text-align: center;
        transform: scale(0.8); transition: transform 0.3s ease;
    }
    .custom-modal i.warn-icon { font-size: 48px; color: #EF4444; margin-bottom: 20px; text-shadow: 0 0 15px rgba(239,68,68,0.4); }
    .custom-modal h4 { font-size: 20px; color: #FFF; font-weight: 600; margin-bottom: 10px; }
    .custom-modal p { font-size: 14px; color: #A0AEC0; margin-bottom: 25px; line-height: 1.5; }
    .modal-btn-row { display: flex; gap: 15px; justify-content: center; }
    .modal-btn { padding: 11px 24px; border-radius: 30px; font-weight: 600; font-size: 14px; cursor: pointer; outline: none; border: none; }
    .modal-btn.cancel { background: rgba(255,255,255,0.05); color: #FFF; border: 1px solid rgba(255,255,255,0.1); }
    .modal-btn.cancel:hover { background: rgba(255,255,255,0.1); }
    .modal-btn.confirm { background: #EF4444; color: #FFF; box-shadow: 0 4px 15px rgba(239,68,68,0.3); }
    .modal-btn.confirm:hover { background: #DC2626; }
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
    <a href="dashboard.php" class="logo"><img src="logo.png" style="width:32px; height:32px; object-fit:cover; border-radius:50%; margin-right:8px; border:1px solid #D4AF37;"> Vault Archive</a>
    <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
</div>

<div class="workspace-wrapper">
    
    <!-- STATS ROW PANEL -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-details">
                <h3>Total Ledger Outflow</h3>
                <p>Rs. <?php echo number_format($total_outflow, 2); ?></p>
            </div>
            <div class="kpi-icon"><i class="fas fa-coins"></i></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-details">
                <h3>Peak Burn / Single Item</h3>
                <p>Rs. <?php echo number_format($peak_burn, 2); ?></p>
            </div>
            <div class="kpi-icon"><i class="fas fa-fire-alt"></i></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-details">
                <h3>Archived Transactions</h3>
                <p><?php echo $log_count; ?> entries</p>
            </div>
            <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
        </div>
    </div>

    <!-- CLIENT-SIDE LIVE FILTER CONSOLE -->
    <div class="search-control-bar">
        <div class="search-input-box">
            <i class="fas fa-search"></i>
            <input type="text" id="liveSearchInput" placeholder="Filter logs by name, category, or notes..." autocomplete="off">
        </div>
        <div class="search-meta">
            Showing <span id="filteredCount"><?php echo $log_count; ?></span> of <span><?php echo $log_count; ?></span> logs
        </div>
    </div>

    <!-- MAIN GRID TABLE CARD -->
    <div class="table-container">
        <div class="table-header-flex">
            <i class="fas fa-receipt"></i>
            <h2>Transaction Records Ledger</h2>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 14%;">Date</th>
                    <th style="width: 20%;">Item Name</th>
                    <th style="width: 18%;">Category</th>
                    <th style="width: 25%;">Description / Notes</th>
                    <th style="width: 13%;">Amount</th>
                    <th style="width: 10%; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody id="ledgerTableBody">
                <?php 
                if(!empty($expenses_array)) {
                    foreach($expenses_array as $row) {
                        $desc_text = (!empty($row['description'])) ? htmlspecialchars($row['description']) : "<span class='no-desc'>No database logs available.</span>"; 
                        
                        echo "<tr class='ledger-row'>";
                        echo "<td class='date-cell'>".date('d-M-Y', strtotime($row['expense_date']))."</td>"; 
                        echo "<td class='item-name-cell'>".htmlspecialchars($row['item_name'])."</td>"; 
                        echo "<td><span class='category-badge'>".htmlspecialchars($row['category'])."</span></td>"; 
                        echo "<td class='desc-cell'>".$desc_text."</td>"; 
                        echo "<td class='amount-cell'>Rs. ".number_format($row['amount'], 2)."</td>"; 
                        echo "<td style='text-align: center;'><a href='#' data-url='view_expenses.php?delete_id=".$row['id']."' class='delete-btn custom-trigger'><i class='fas fa-trash-alt'></i></a></td>"; 
                        echo "</tr>";
                    }
                } else {
                    echo "<tr id='noDataRow'><td colspan='6' class='no-data'>No expenses recorded yet. Start tracking!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- DYNAMIC ACTION CONFIRMATION MODAL -->
<div class="custom-modal-overlay" id="deleteModal">
    <div class="custom-modal">
        <i class="fas fa-exclamation-triangle warn-icon"></i>
        <h4>Confirm Purge Action</h4>
        <p>This transaction will be permanently erased from secure archive files. This action cannot be undone.</p>
        <div class="modal-btn-row">
            <button class="modal-btn cancel" id="closeModal">Abort</button>
            <button class="modal-btn confirm" id="confirmErase">Erase Log</button>
        </div>
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

    // INSTANT LIVE SEARCH ENGINE (CLIENT SIDE)
    const searchInput = document.getElementById('liveSearchInput');
    const tableRows = document.querySelectorAll('.ledger-row');
    const filteredCount = document.getElementById('filteredCount');

    searchInput.addEventListener('input', function() {
        const filterText = this.value.toLowerCase().trim();
        let visibleCount = 0;

        tableRows.forEach(row => {
            const name = row.querySelector('.item-name-cell').textContent.toLowerCase();
            const category = row.querySelector('.category-badge').textContent.toLowerCase();
            const desc = row.querySelector('.desc-cell').textContent.toLowerCase();

            if (name.includes(filterText) || category.includes(filterText) || desc.includes(filterText)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        filteredCount.textContent = visibleCount;
    });

    // DYNAMIC INTERACTIVE CONFIRMATION MODAL CONTROL
    const triggers = document.querySelectorAll('.custom-trigger');
    const modal = document.getElementById('deleteModal');
    const closeModal = document.getElementById('closeModal');
    const confirmEraseBtn = document.getElementById('confirmErase');
    let activeDeleteUrl = "";

    triggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            activeDeleteUrl = this.getAttribute('data-url');
            
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.querySelector('.custom-modal').style.transform = 'scale(1)';
            }, 50);
        });
    });

    const hideModal = () => {
        modal.style.opacity = '0';
        modal.querySelector('.custom-modal').style.transform = 'scale(0.8)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    };

    closeModal.addEventListener('click', hideModal);
    
    confirmEraseBtn.addEventListener('click', () => {
        if(activeDeleteUrl !== "") {
            window.location.href = activeDeleteUrl;
        }
    });
</script>
</body>
</html>