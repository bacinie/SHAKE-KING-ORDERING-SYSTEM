<?php
/* BLOCK 1: Core System Initialization */
// Loads the mandatory script responsible for connecting with the database engine via the persistent link variable ($con).
include 'includes/connect.php';

/* BLOCK 2: Target Profile Information Fetch Engine */
// Captures the active user session ID value and queries the database users table to extract matching registry details.
$user_id = $_SESSION['user_id'];

$result = mysqli_query($con, "SELECT * FROM users WHERE id = $user_id");
while($row = mysqli_fetch_array($result)){
    $name = $row['name'];
    $address = $row['address'];
    $contact = $row['contact'];
    $email = $row['email'];
    $username = $row['username'];
}

// Fetch wallet details for the current user
$wallet_result = mysqli_query($con, "SELECT w.id as wallet_id, wd.number, wd.cvv, wd.balance 
                                      FROM wallet w 
                                      LEFT JOIN wallet_details wd ON w.id = wd.wallet_id 
                                      WHERE w.customer_id = $user_id");
$card_number = '';
$cvv = '';
$wallet_balance = 0;
while($wallet_row = mysqli_fetch_array($wallet_result)){
    $card_number = $wallet_row['number'];
    $cvv = $wallet_row['cvv'];
    $wallet_balance = $wallet_row['balance'];
}

/* BLOCK 3: Client Identity Security Gatekeeper */
// Evaluates if the visitor has a valid customer session handshake before proceeding; otherwise skips to administrative/guest routing logic.
if($_SESSION['customer_sid'] == session_id())
{
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Edit Details</title>

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
              /* BLOCK 8: Contextual Orders Category Link Loop Generator */
              // Isolates unique workflow states matching this client inside transaction records to compile custom target filter URLs.
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
              /* BLOCK 9: Contextual Support Ticket Status Filter Link Loop */
              // Scans helpdesk records belonging to this author to inject custom URL links alongside status indicators.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets WHERE poster_id = $user_id AND NOT deleted;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="tickets.php?status=' . urlencode($row['status']) . '">' . htmlspecialchars($row['status']) . '</a></li>';
              }
            ?>
          </ul>
        </li>     
        <li class="active">
          <a href="details.php">Edit Details</a>
        </li>       
      </ul>
    </aside>

    <main id="content">
      <div class="content-card">
        <h5 class="breadcrumbs-title">User Profile Details</h5>
        <p class="caption">Update your credential information here to ensure precise delivery tracking routes and contact points.</p>
        <div class="divider"></div>

        <div class="profile-content-layout">
          <div class="profile-v-meta">
            <h4>Account Meta</h4>
          </div>

          <form class="formValidate" id="formValidate" method="post" action="routers/details-router.php">
            <div class="row-flex-grid">
              
              <div class="input-field">
                <label for="username">Username Registry</label>
                <input name="username" id="username" type="text" value="<?php echo htmlspecialchars($username); ?>" required>
                <div class="errorTxt1"></div>
              </div>

              <div class="input-field">
                <label for="name">Full Name</label>
                <input name="name" id="name" type="text" value="<?php echo htmlspecialchars($name); ?>" required>
                <div class="errorTxt2"></div>
              </div>

              <div class="input-field">
                <label for="email">Email Address</label>
                <input name="email" id="email" type="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <div class="errorTxt3"></div>
              </div>

              <div class="input-field">
                <label for="password">Account Security Password (Leave blank to keep unchanged)</label>
                <input name="password" id="password" type="password" placeholder="••••••••">
                <div class="errorTxt4"></div>
              </div>

              <div class="input-field span-grid-full">
                <label for="phone">Active Contact Number</label>
                <input name="phone" id="phone" type="number" value="<?php echo htmlspecialchars($contact); ?>" required>
                <div class="errorTxt5"></div>
              </div>

              <div class="input-field span-grid-full">
                <label for="address">Primary Destination Address</label>
                <textarea name="address" id="address" required><?php echo htmlspecialchars($address); ?></textarea>
                <div class="errorTxt6"></div>
              </div>

            </div>

            <div class="submit-btn-row">
              <button class="btn" type="submit" name="action">
                Update Settings Configuration
              </button>
            </div>
          </form>
        </div>

        <div class="profile-content-layout" style="margin-top: 2rem;">
          <div class="profile-v-meta">
            <h4>Wallet Payment Details</h4>
            <p style="color: #5c7373; font-size: 0.9rem; margin-top: 0.25rem;">These details were automatically generated when you registered and cannot be modified.</p>
          </div>

          <div class="row-flex-grid">
            <div class="input-field">
              <label for="card_number">Card Number</label>
              <input name="card_number" id="card_number" type="text" value="<?php echo htmlspecialchars($card_number); ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
            </div>

            <div class="input-field">
              <label for="cvv">CVV Code</label>
              <input name="cvv" id="cvv" type="text" value="<?php echo htmlspecialchars($cvv); ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
            </div>

            <div class="input-field">
              <label for="wallet_balance">Wallet Balance</label>
              <input name="wallet_balance" id="wallet_balance" type="text" value="$<?php echo number_format($wallet_balance, 2); ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
            </div>
          </div>
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
/* BLOCK 14: Unauthorized Traffic & Role Router Fallbacks Interceptor */
// Handles unauthenticated clients (Routes system administrators to the global admin console panel, while routing anonymous traffic back to authorization screens).
else
{
    if($_SESSION['admin_sid'] == session_id())
    {
        header("location:admin-page.php");    
    }
    else {
        header("location:login.php");
    }
}
?>