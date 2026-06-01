<?php
/* BLOCK 1: Initialization & Global Context Dependencies */
// Imports necessary configurations to hook into the database server ($con) and retrieve real-time account wallet values ($balance).
include 'includes/connect.php';
include 'includes/wallet.php';

    /* BLOCK 2: Client Access Security Boundary Control */
    // Determines if the operational session signature matches a legitimate customer; blocks anomalies from displaying core transaction history.
    if($_SESSION['customer_sid']==session_id())
    {
        ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Past Orders</title>

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
        <li class="active">
          <a href="orders.php">Orders</a>
          <ul>
            <li class="<?php if(!isset($_GET['status'])){ echo 'active'; } ?>">
              <a href="orders.php">All Orders</a>
            </li>
            <?php
              /* BLOCK 7: Personalized Order State Segmented Links Loop */
              // Scans distinct processing states tied exclusively to the active customer ID to establish parameterized status-sorting links.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders WHERE customer_id = $user_id;");
              while($row = mysqli_fetch_array($sql)){
                $is_active = (isset($_GET['status']) && $_GET['status'] == $row['status']) ? 'active' : '';
                echo '<li class="'.$is_active.'"><a href="orders.php?status='.urlencode($row['status']).'">'.htmlspecialchars($row['status']).'</a></li>';
              }
            ?>
          </ul>
        </li>
        <li>
          <a href="tickets.php">Tickets</a>
          <ul>
            <li><a href="tickets.php">All Tickets</a></li>
            <?php
              /* BLOCK 8: Customer Helpdesk Ticket Submenu Filtering Loop */
              // Dynamically builds issue tracking links tied directly to validation records assigned to this user profile.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets WHERE poster_id = $user_id AND not deleted;");
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
        <h5 class="breadcrumbs-title">Past Orders</h5>
        <p class="caption">List of your past orders with details.</p>
        <div class="divider"></div>

        <?php
          /* BLOCK 10: URL Filter Input Sanitizer Engine */
          // Captures sorting definitions passed by client links, utilizing SQL escape sanitization layers or defaulting to wildcard strings ('%').
          if(isset($_GET['status'])){
            $status_filter = mysqli_real_escape_string($con, $_GET['status']);
          } else {
            $status_filter = '%';
          }
          
          /* BLOCK 11: Main Order Group Context Query Loop */
          // Collects transaction records matching both the client ID and the active search category, rendering modular order invoice wrappers.
          $sql = mysqli_query($con, "SELECT * FROM orders WHERE customer_id = $user_id AND status LIKE '$status_filter';");
          while($row = mysqli_fetch_array($sql))
          {
            $status = $row['status'];
            $order_id = $row['id'];
        ?>
            <div class="order-group-card">
              
              <div class="order-group-header">
                <h4>Order No. #<?php echo $order_id; ?></h4>
                <div class="order-meta-grid">
                  <div><strong>Date:</strong> <?php echo htmlspecialchars($row['date']); ?></div>
                  <div><strong>Payment Type:</strong> <?php echo htmlspecialchars($row['payment_type']); ?></div>
                  <div><strong>Address:</strong> <?php echo htmlspecialchars($row['address']); ?></div>
                  <div>
                    <strong>Status:</strong> 
                    <?php 
                      if($status == 'Paused') {
                        echo 'Paused <span class="status-info-help" title="Please contact administrator for further details.">?</span>';
                      } else {
                        echo htmlspecialchars($status);
                      }
                    ?>
                  </div>
                  <?php if(!empty($row['description'])): ?>
                    <div style="grid-column: 1 / -1;" class="grid-span-full">
                      <strong>Note:</strong> <?php echo htmlspecialchars($row['description']); ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="order-items-list">
                <?php
                  $sql1 = mysqli_query($con, "SELECT * FROM order_details WHERE order_id = $order_id;");
                  while($row1 = mysqli_fetch_array($sql1))
                  {
                    $item_id = $row1['item_id'];
                    $sql2 = mysqli_query($con, "SELECT * FROM items WHERE id = $item_id;");
                    $item_name = "Unknown Item";
                    while($row2 = mysqli_fetch_array($sql2)){
                      $item_name = $row2['name'];
                    }
                ?>
                    <div class="order-item-row">
                      <div><span style="color: #708585; font-weight: 600; margin-right: 4px;">#<?php echo $row1['item_id']; ?></span> <?php echo htmlspecialchars($item_name); ?></div>
                      <div style="color: #5c7373; font-weight: 500;"><?php echo $row1['quantity']; ?> Pieces</div>
                      <div style="text-align: right; color: #004d4d; font-weight: 600;">$<?php echo number_format($row1['price'], 2); ?></div>
                    </div>
                <?php
                  }
                ?>
              </div>

              <div class="order-summary-row">
                <div class="order-total-label">
                  Total Paid: <span style="color: #008080;">$<?php echo number_format($row['total'], 2); ?></span>
                </div>
                
                <?php if(!preg_match('/^Cancelled/', $status) && $status != 'Delivered'): ?>
                  <form action="routers/cancel-order.php" method="post" style="margin: 0;">
                    <input type="hidden" value="<?php echo $order_id; ?>" name="id">
                    <input type="hidden" value="Cancelled by Customer" name="status"> 
                    <input type="hidden" value="<?php echo htmlspecialchars($row['payment_type']); ?>" name="payment_type">                                         
                    <button class="btn btn-danger" type="submit" name="action">Cancel Order</button>
                  </form>
                <?php endif; ?>
              </div>

            </div>
        <?php
          }
        ?>

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
    /* BLOCK 16: Authentication Exception Interception Engine */
    // Traps unverified client tokens, safely routing administrators forward to management screens or dropping guests out to login portals.
    else
    {
        if($_SESSION['admin_sid']==session_id())
        {
            header("location:all-orders.php");      
        }
        else{
            header("location:login.php");
        }
    }
?>