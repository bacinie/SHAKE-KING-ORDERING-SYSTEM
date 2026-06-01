<?php
/* BLOCK 1: Database Connections & Wallet Core Initializations */
// Imports external systemic scripts to instantiate active MySQL connections ($con) and retrieve digital wallet balance parameters.
include 'includes/connect.php';
include 'includes/wallet.php';

$continue = 0;
$total = 0;

/* BLOCK 2: Customer Identity Verification & Secure Wallet Gateway Validation */
// Confirms an active client session before conditionally filtering, sanitizing, and validating payment credentials.
if($_SESSION['customer_sid'] == session_id())
{
    if($_POST['payment_type'] == 'Wallet'){
        // Sanitizes formatting characters out of card input arrays
        $_POST['cc_number'] = str_replace('-', '', $_POST['cc_number']);
        $_POST['cc_number'] = str_replace(' ', '', $_POST['cc_number']); 
        $_POST['cvv_number'] = (int)str_replace('-', '', $_POST['cvv_number']);
        
        // References input credentials against active record sets securely saved in wallet tables
        $sql1 = mysqli_query($con, "SELECT * FROM wallet_details WHERE wallet_id = $wallet_id");
        $wallet_found = false;
        while($row1 = mysqli_fetch_array($sql1)){
            $wallet_found = true;
            $card = $row1['number'];
            $cvv = $row1['cvv'];
            if($card == $_POST['cc_number'] && $cvv == $_POST['cvv_number']) {
                $continue = 1;
            } else {
                header("location:place-order.php?error=invalid_card");
                exit();
            }
        }
        if(!$wallet_found) {
            header("location:place-order.php?error=no_wallet_details");
            exit();
        }
    } else {
        $continue = 1;
    }
}

/* BLOCK 3: Customer Personal Profile Meta-Data Aggregation Query */
// Obtains identifying information (Client Name and Telephone credentials) mapping to the active logged-in User ID.
$result = mysqli_query($con, "SELECT * FROM users WHERE id = $user_id");
while($row = mysqli_fetch_array($result)){
    $name = $row['name'];
    $contact = $row['contact'];
}

// Begins conditional output rendering if secure parameters check out safely
if($continue){
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Confirm Your Order</title>
  
  <link rel="icon" href="images/SKLogo.png">
  
  <link href="css/style.css" type="text/css" rel="stylesheet">
</head>

<body class="customer-theme">

  <header id="header">
    <div class="nav-wrapper">
      <div class="header-brand-group">
        <a href="index.php">
          <img src="images/SKLogo.png" alt="Logo" class="header-logo-img">
        </a>
        <span class="logo-text">FOOD ORDERING SYSTEM</span>
      </div>
      <div>
        <span class="balance-box">Wallet Balance: $<?php echo number_format($balance, 2); ?></span>
      </div>
    </div>
  </header>

  <div class="dashboard-grid" id="main">

    <aside id="left-sidebar-nav">
      <div class="user-details">
        <span class="profile-name"><?php echo htmlspecialchars($name); ?></span>
        <p class="user-role"><?php echo htmlspecialchars($role); ?></p>
        <a href="routers/logout.php" class="logout-link">Logout</a>
      </div>

      <ul class="side-nav-links">
        <li>
          <a href="index.php">Order Food</a>
        </li>
        <li>
          <a href="orders.php">Orders</a>
          <ul>
            <li><a href="orders.php">All Orders</a></li>
            <?php
              /* BLOCK 9: Customer Personal Orders Context Filtering Sub-menu */
              // Scans transaction records to render user-specific sub-navigation targets isolating custom delivery paths.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders WHERE customer_id = $user_id;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="orders.php?status=' . urlencode($row['status']) . '">' . htmlspecialchars($row['status']) . '</a></li>';
              }
            ?>
          </ul>
        </li>
        <li>
          <a href="tickets.php">Tickets</a>
          <ul>
            <li><a href="tickets.php">All Tickets</a></li>
            <?php
              /* BLOCK 10: Customer Support Tickets Navigation Link Generator */
              // Dynamically builds ticket tracking lists tied specifically to issues raised under this consumer record.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets WHERE poster_id = $user_id AND NOT deleted;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="tickets.php?status=' . urlencode($row['status']) . '">' . htmlspecialchars($row['status']) . '</a></li>';
              }
            ?>
          </ul>
        </li>     
        <li>
          <a href="details.php">Edit Details</a>
        </li>       
      </ul>
    </aside>

    <main id="content">
      <div class="content-card">
        <h5 class="breadcrumbs-title">Verify Order Details</h5>
        <p class="caption">Please review your item checklist and configuration details below before checking out.</p>
        <div class="divider"></div>

        <div class="receipt-wrapper">
          <h4 class="workspace-section-heading">Order Verification Invoice</h4>
          
          <div class="receipt-meta-box">
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Contact Info:</strong> <?php echo htmlspecialchars($contact); ?></p>
            <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($_POST['address']); ?></p>
            <p><strong>Payment Option Selected:</strong> <?php echo htmlspecialchars($_POST['payment_type']); ?></p>
          </div>

          <div class="receipt-items-table">
            <div class="receipt-row row-header">
              <div>Item Description</div>
              <div>Quantity</div>
              <div style="text-align: right;">Subtotal</div>
            </div>

            <?php
              /* BLOCK 13: Order Quantities Parser & Database Pricing Aggregator Loop */
              // Scans numeric keys within the global global input container to extract menu records, evaluate mathematical totals, and dynamically compose invoice rows.
              foreach ($_POST as $key => $value) {
                if(is_numeric($key)){       
                  $result = mysqli_query($con, "SELECT * FROM items WHERE id = $key");
                  while($row = mysqli_fetch_array($result)) {
                    $price = $row['price'];
                    $item_name = $row['name'];
                    $item_id = $row['id'];
                  }
                  $calculated_subtotal = $value * $price;
                  
                  echo '<div class="receipt-row">';
                  echo '  <div><span style="color: #708585; font-weight: 600; margin-right: 4px;">#' . htmlspecialchars($item_id) . '</span> ' . htmlspecialchars($item_name) . '</div>';
                  echo '  <div style="color: #5c7373; font-weight: 500;">' . htmlspecialchars($value) . ' Pieces</div>';
                  echo '  <div style="text-align: right; font-weight: 600; color: #004d4d;">$' . number_format($calculated_subtotal, 2) . '</div>';
                  echo '</div>';
                  
                  $total = $total + $calculated_subtotal;
                }
              }
            ?>

            <div class="receipt-row row-total">
              <div>Grand Total Due</div>
              <div>&nbsp;</div>
              <div style="text-align: right;">$<?php echo number_format($total, 2); ?></div>
            </div>
          </div>

          <?php 
            /* BLOCK 14: Special Ordering Instructions Box */
            // Conditionally captures operational descriptions/notes submitted by clients and appends them to invoice layouts.
            if(!empty($_POST['description'])): 
          ?>
            <div class="receipt-meta-box" style="border-left-color: #b3cccc; margin-top: 1.5rem; background-color: #fafcfc;">
              <p><strong>Notes with this order:</strong></p>
              <p style="color: #5c7373; margin-top: 0.25rem;"><?php echo htmlspecialchars($_POST['description']); ?></p>
            </div>
          <?php endif; ?>

          <?php 
            /* BLOCK 15: Digital Wallet Ledger Verification Component */
            // Runs local evaluations on remaining funding levels, triggering warning classes if accounts enter critical deficit parameters.
            if($_POST['payment_type'] == 'Wallet'): 
              $remaining_balance = $balance - $total;
              $is_insufficient = ($remaining_balance < 0);
            ?>
            <div class="wallet-ledger-box">
              <div class="wallet-ledger-row">
                <div>Current Wallet Balance</div>
                <div>$<?php echo number_format($balance, 2); ?></div>
              </div>
              <div class="wallet-ledger-row <?php echo $is_insufficient ? 'insufficient-funds' : 'highlight-delta'; ?>">
                <div>
                  <?php echo $is_insufficient ? 'Warning: Balance Overdraft Amount' : 'Est. Wallet Balance After Checkout'; ?>
                </div>
                <div>$<?php echo number_format($remaining_balance, 2); ?></div>
              </div>
            </div>
          <?php endif; ?>

          <form action="routers/order-router.php" method="post" style="margin-top: 1.5rem;">
            <?php
              foreach ($_POST as $key => $value) {
                if(is_numeric($key)){
                  echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                }
              }
            ?>
            <input type="hidden" name="payment_type" value="<?php echo htmlspecialchars($_POST['payment_type']);?>">
            <input type="hidden" name="address" value="<?php echo htmlspecialchars($_POST['address']);?>">
            
            <?php if (isset($_POST['description'])): ?>
              <input type="hidden" name="description" value="<?php echo htmlspecialchars($_POST['description']);?>">
            <?php endif; ?>
            
            <?php if($_POST['payment_type'] == 'Wallet'): ?>
              <input type="hidden" name="balance" value="<?php echo $remaining_balance;?>">
            <?php endif; ?>
            
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            
            <div style="text-align: right; margin-top: 1.5rem;">
              <button class="btn" type="submit" name="action" <?php if($_POST['payment_type'] == 'Wallet' && $is_insufficient) { echo 'disabled'; } ?>>
                <?php echo ($_POST['payment_type'] == 'Wallet' && $is_insufficient) ? 'Insufficient Funds Available' : 'Confirm &amp; Place Order'; ?>
              </button>
            </div>
          </form>

        </div>
      </div>
    </main>
  </div>

  <footer>
    <div class="footer-content">
      <span>Copyright © 2026</span>
      <span>Design and Developed by <a href="https://www.linkedin.com/in/bacinie" target="_blank">bacinie</a></span>
    </div>
  </footer>
</body>
</html>
<?php
}
/* BLOCK 19: Security Interceptor Authentication Router Fallbacks */
// Catches unverified sessions and handles redirects (Admins travel to all-orders views, while unauthorized traffic drops to login endpoints).
else
{
    if($_SESSION['admin_sid']==session_id()) { 
        header("location:all-orders.php"); 
    } else { 
        header("location:login.php"); 
    }
}
?>