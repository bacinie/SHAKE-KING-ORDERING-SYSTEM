<?php
/* BLOCK 1: Initialization & Baseline State Context Dependencies */
// Imports baseline infrastructure assets for database connection handles ($con) and functional financial wallets ($balance).
include 'includes/connect.php';
include 'includes/wallet.php';
$total = 0;

/* BLOCK 2: Customer Session Guard & Profile Sync Registry */
// Verifies active customer tokens, then synchronously fetches up-to-date registry traits (address, status, metadata) for checkout calculations.
if($_SESSION['customer_sid']==session_id())
{
  $result = mysqli_query($con, "SELECT * FROM users where id = $user_id");
  while($row = mysqli_fetch_array($result)){
    $name = $row['name']; 
    $address = $row['address'];
    $contact = $row['contact'];
    $verified = $row['verified'];
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Provide Order Details</title>

  <link rel="icon" href="images/SKLogo.png">

  <link rel="stylesheet" type="text/css" href="css/style.css">
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
        <span class="balance-box">Wallet Balance: $<?php echo number_format($balance, 2);?></span>
      </div>
    </div>
  </header>

  <div class="dashboard-grid" id="main">

    <aside id="left-sidebar-nav">
      <div class="user-details">
        <span class="profile-name"><?php echo htmlspecialchars($name);?></span>
        <p class="user-role"><?php echo htmlspecialchars($role);?></p>
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
              /* BLOCK 7: Parametric Invoices Link Generator Loop */
              // Collects transaction states specific to this user to generate segmented sorting paths.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders WHERE customer_id = $user_id;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="orders.php?status='.urlencode($row['status']).'">'.htmlspecialchars($row['status']).'</a></li>';
              }
            ?>
          </ul>
        </li>
        <li>
          <a href="tickets.php">Tickets</a>
          <ul>
            <li><a href="tickets.php">All Tickets</a></li>
            <?php
              /* BLOCK 8: Customer Ticket Status Filtering Loop */
              // Scans helpdesk records belonging to this author to inject custom URL links alongside status indicators.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets WHERE poster_id = $user_id AND NOT deleted;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="tickets.php?status='.urlencode($row['status']).'">'.htmlspecialchars($row['status']).'</a></li>';
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
        <h5 class="breadcrumbs-title">Checkout Details</h5>
        <p class="caption">Provide required delivery and payment details below to complete your order purchase.</p>
        <div class="divider"></div>

        <div class="checkout-form-container">
          <h4 class="workspace-section-heading">Delivery &amp; Account Verification</h4>
          
          <?php if(isset($_GET['error'])): ?>
            <div class="error-message" style="background-color: #ffe6e6; border: 1px solid #ff9999; color: #cc0000; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
              <?php
                if($_GET['error'] == 'invalid_card') {
                  echo 'Invalid card number or CVV. Please check your wallet details and try again.';
                } elseif($_GET['error'] == 'no_wallet_details') {
                  echo 'No wallet details found. Please set up your wallet payment information first.';
                }
              ?>
            </div>
          <?php endif; ?>
          
          <form id="formValidate" method="post" action="confirm-order.php">
            
            <div class="input-field">
              <label for="payment_type">Payment Method</label>
              <select id="payment_type" name="payment_type" required>
                <option value="Wallet" selected>Wallet Balance</option>
                <option value="Cash On Delivery" <?php if(!$verified) echo 'disabled';?>>Cash on Delivery (Verified Accounts Only)</option>             
              </select>
            </div>          
            
            <div class="input-field">
              <label for="address">Delivery Address</label>
              <textarea name="address" id="address" required minlength="5" placeholder="Enter complete location details..."><?php echo htmlspecialchars($address);?></textarea>
            </div>
            
            <div class="input-field">
              <label for="cc_number">Card Number (Exactly 16 Digits)</label>
              <input name="cc_number" id="cc_number" type="text" required 
                     pattern="\d{16}" minlength="16" maxlength="16" inputmode="numeric"
                     placeholder="e.g. 1234567890123456" 
                     title="Please enter an exact 16-digit card number containing no dashes or spaces.">
            </div>
            
            <div class="input-field">
              <label for="cvv_number">CVV Code (Exactly 3 Digits)</label>
              <input name="cvv_number" id="cvv_number" type="password" required 
                     pattern="\d{3}" minlength="3" maxlength="3" inputmode="numeric"
                     placeholder="e.g. 123" 
                     title="Please enter an exact 3-digit CVV identification block.">
            </div>                    
            
            <div style="text-align: right; margin-top: 1.5rem;">
              <button class="btn" type="submit" name="action">Continue to Confirmation</button>
            </div>

            <?php
              /* BLOCK 11: Menu Item Quantity Preservation Relay */
              // Scans incoming menu quantities and re-encodes them into hidden input tags to carry item selection details safely onto the confirmation stage.
              foreach ($_POST as $key => $value) {
                if($key == 'action' || $value == ''){
                  continue; 
                }
                echo '<input name="'.htmlspecialchars($key).'" type="hidden" value="' . htmlspecialchars($value) . '">';
              }
            ?>
          </form>
        </div>

        <div class="receipt-wrapper">
          <h4 class="workspace-section-heading">Itemized Order Summary</h4>
          
          <div class="receipt-meta-box">
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($name);?></p>
            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($contact);?></p>
          </div>

          <div class="receipt-items-table">
            <div class="receipt-row row-header">
              <div>Item Description</div>
              <div>Quantity</div>
              <div style="text-align: right;">Subtotal</div>
            </div>

            <?php
              /* BLOCK 13: Financial Aggregation & Menu Database Intersection Engine */
              // Matches incoming numeric IDs against the active menu list, dynamically outputting line-item names, calculations, and compiling the grand total.
              foreach ($_POST as $key => $value) {
                if($value == ''){
                  continue;
                }
                if(is_numeric($key)){
                  $result = mysqli_query($con, "SELECT * FROM items WHERE id = $key");
                  while($row = mysqli_fetch_array($result)) {
                    $price = $row['price'];
                    $item_name = $row['name'];
                    $item_id = $row['id'];
                  }
                  $calculated_price = $value * $price;
                  
                  echo '<div class="receipt-row">';
                  echo '  <div><span style="color: #708585; font-weight: 600; margin-right: 4px;">#' . htmlspecialchars($item_id) . '</span> ' . htmlspecialchars($item_name) . '</div>';
                  echo '  <div style="color: #5c7373; font-weight: 500;">' . htmlspecialchars($value) . ' Pieces</div>';
                  echo '  <div style="text-align: right; font-weight: 600; color: #004d4d;">$' . number_format($calculated_price, 2) . '</div>';
                  echo '</div>';
                  
                  $total = $total + $calculated_price;
                }
              }
            ?>

            <div class="receipt-row row-total">
              <div>Grand Total</div>
              <div>&nbsp;</div>
              <div style="text-align: right;">$<?php echo number_format($total, 2); ?></div>
            </div>
          </div>

          <?php 
            /* BLOCK 15: Optional Customer Instructions Interceptor */
            // Captures optional customer comments or delivery preferences from earlier arrays and renders them inside a localized layout box.
            if(!empty($_POST['description'])): 
          ?>
            <div class="receipt-meta-box" style="border-left-color: #b3cccc; margin-top: 1.5rem; background-color: #fafcfc;">
              <p><strong>Notes submitted with this order:</strong></p>
              <p style="color: #5c7373; margin-top: 0.25rem;"><?php echo htmlspecialchars($_POST['description']);?></p>
            </div>
          <?php endif; ?>

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
/* BLOCK 17: Security Access Violation Interceptor & Router Fallbacks */
// Catches unauthorized visitors or routing exceptions, redirecting management accounts to admin tools and unauthenticated guests back to authentication panels.
else
{
  if($_SESSION['admin_sid']==session_id()) { 
    header("location:all-orders.php"); 
  } else { 
    header("location:login.php"); 
  }
}
?>