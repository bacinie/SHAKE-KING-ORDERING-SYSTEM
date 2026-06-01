<?php
/* BLOCK 1: Initialization & Database Connection */
// Establishes a link with the underlying database by calling the connection file where $con is initialized.
include 'includes/connect.php';

  /* BLOCK 2: Administrative Security Gatekeeper */
  // Checks if the current session token matches an active administrator session ID before rendering sensitive content.
  if($_SESSION['admin_sid']==session_id())
  {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>All Orders</title>

  <link rel="icon" href="images/SKLogo.png">

  <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection">
</head>

<body class="admin-theme">

  <header>
    <div class="nav-wrapper">
      <div class="header-brand-group">
        <a href="index.php">
          <img src="images/SKLogo.png" alt="Logo" class="header-logo-img">
        </a>
        <span class="logo-text">System Orders Management</span>
      </div>
      <div class="balance-box">
        Account: <?php echo htmlspecialchars($role); ?>
      </div>
    </div>
  </header>
  <div class="dashboard-grid">

    <aside>
      <div class="user-details">
        <span class="profile-name"><?php echo htmlspecialchars($name); ?></span>
        <p class="user-role"><?php echo htmlspecialchars($role); ?></p>
        <a href="routers/logout.php" class="logout-link">Logout</a>
      </div>

      <ul class="side-nav-links">
        <li>
          <a href="index.php">Food Menu</a>
        </li>
        <li class="active">
          <a>Orders</a>
          <ul>
            <li class="<?php echo !isset($_GET['status']) ? 'active' : ''; ?>">
              <a href="all-orders.php">All Orders</a>
            </li>
            <?php
              /* BLOCK 9: Order Category Dynamic Sub-Navigation Link Generator */
              // Scans distinct workflow states present inside orders and drops filter items into the list structure.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders;");
              while($row = mysqli_fetch_array($sql)){
                $is_active = (isset($_GET['status']) && $_GET['status'] == $row['status']) ? 'class="active"' : '';
                echo '<li ' . $is_active . '><a href="all-orders.php?status=' . urlencode($row['status']) . '">' . htmlspecialchars($row['status']) . '</a></li>';
              }
            ?>
          </ul>
        </li>
        <li>
          <a>Tickets</a>
          <ul>
            <li><a href="all-tickets.php">All Tickets</a></li>
            <?php
              /* BLOCK 10: Dynamic Support Tickets Filtering Node Builder */
              // Pulls individual customer support statuses from tickets tables to generate localized URL nodes.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="all-tickets.php?status=' . urlencode($row['status']) . '">' . htmlspecialchars($row['status']) . '</a></li>';
              }
            ?>
          </ul>
        </li>     
        <li>
          <a href="users.php">Users</a>
        </li>       
      </ul>
    </aside>

    <main>
      <h5 class="breadcrumbs-title">All Customer Orders</h5>
      <p class="caption">List of orders placed by customers sorted with full address verification parameters.</p>
      <div class="divider"></div>

      <div class="content-card">
        <h4 class="workspace-section-heading" style="color: #004d4d; font-size: 1.2rem; font-weight: 600; margin-bottom: 1.5rem;">Active Order Ledger</h4>
        
        <?php
          /* BLOCK 12: Order Filtration & Initial SQL Lookup Engine */
          // Collects and processes string filters via URL params, assigning a SQL wild card matching character ('%') if none exist.
          if(isset($_GET['status'])){
            $status_filter = $_GET['status'];
          } else {
            $status_filter = '%';
          }
          
          $sql = mysqli_query($con, "SELECT * FROM orders WHERE status LIKE '$status_filter';");
          
          /* BLOCK 13: Main Multi-Order Data Fetch Loop Engine */
          // Iterates across records extracted via query lookups and provisions targeted system interface values.
          while($row = mysqli_fetch_array($sql))
          {
            $status = $row['status'];
            $deleted = $row['deleted'];
            $order_id = $row['id'];
            $customer_id = $row['customer_id'];
            
            // Core wrapper container init
            echo '<div class="order-card-wrapper">';
            
            /* BLOCK 14: Contextual Administrative Action Form Opener */
            // Generates an interactive form post element targeted at updating records if the current invoice line has not been soft-deleted.
            if(!$deleted) {
              echo '  <form method="post" action="routers/edit-orders.php">';
            }
            
            echo '  <ul class="orders-collection">';
            
            /* BLOCK 15: Primary Invoice ID & Placement Meta Records Bar */
            // Outputs essential tracking information including purchase identification keys, execution dates, and banking metrics.
            echo '    <li class="order-group-item order-header-row">';
            echo '      <div class="order-title-id">Order ID: #' . htmlspecialchars($order_id) . '</div>';
            echo '      <div class="order-meta-info">';
            echo '        <p><strong>Date Placed:</strong> ' . htmlspecialchars($row['date']) . '</p>';
            echo '        <p><strong>Payment Variant:</strong> ' . htmlspecialchars($row['payment_type']) . '</p>';
            echo '      </div>';
            echo '    </li >';
            
            /* BLOCK 16: Dynamic Order Status Dropdown Form Selection Controls */
            // Renders static text badges for completed/removed elements or opens selectable tracking forms for editing active states.
            echo '    <li class="order-group-item">';
            echo '      <div class="status-alignment-row">';
            echo '        <div class="status-label"><strong>Current Order Status:</strong></div>';
            if($deleted) {
              echo '      <div><span class="badge-ui bg-dark">' . htmlspecialchars($status) . '</span></div>';
            } else {
              echo '      <div class="status-input-group">';
              echo '        <input type="hidden" value="' . htmlspecialchars($order_id) . '" name="id">';
              echo '        <div class="input-field" style="margin: 0;">';
              echo '          <select name="status">';
              echo '            <option value="Yet to be delivered" ' . ($status == 'Yet to be delivered' ? 'selected' : '') . '>Yet to be delivered</option>';
              echo '            <option value="Delivered" ' . ($status == 'Delivered' ? 'selected' : '') . '>Delivered</option>';
              echo '            <option value="Cancelled by Admin" ' . ($status == 'Cancelled by Admin' ? 'selected' : '') . '>Cancelled by Admin</option>';
              echo '            <option value="Paused" ' . ($status == 'Paused' ? 'selected' : '') . '>Paused</option>';
              echo '          </select>';
              echo '        </div>';
              echo '      </div>';
            }
            echo '      </div>';
            echo '    </li>';

            /* BLOCK 17: User Profile Data Correlator Sub-Query Loop */
            // References customer_id elements back to the general users profile schema to retrieve names, telephone digits, and emails.
            $sql3 = mysqli_query($con, "SELECT * FROM users WHERE id = $customer_id;");
            while($row3 = mysqli_fetch_array($sql3))
            {
              echo '    <li class="order-group-item">';
              echo '      <div class="order-customer-grid">';
              echo '        <div><strong>Customer Name:</strong><p>' . htmlspecialchars($row3['name']) . '</p></div>';
              echo '        <div><strong>Delivery Destination:</strong><p>' . htmlspecialchars($row['address']) . '</p></div>';
              if(!empty($row3['contact'])) echo '<div><strong>Contact Tel:</strong><p>' . htmlspecialchars($row3['contact']) . '</p></div>';
              if(!empty($row3['email'])) echo '<div><strong>Email Address:</strong><p>' . htmlspecialchars($row3['email']) . '</p></div>';
              if(!empty($row['description'])) echo '<div class="grid-span-full"><strong>Special Instructions:</strong><p>' . htmlspecialchars($row['description']) . '</p></div>';
              echo '      </div>';
              echo '    </li>';
            }

            /* BLOCK 18: Line Item Purchases Lookup & UI Table Matrix Generation */
            // Gathers nested order rows, resolves product IDs back into food labels, and generates granular breakdown displays.
            $sql1 = mysqli_query($con, "SELECT * FROM order_details WHERE order_id = $order_id;");
            echo '    <li class="order-group-item items-summary-section">';
            echo '      <p class="summary-header"><strong>Items Summary Breakdown</strong></p>';
            
            while($row1 = mysqli_fetch_array($sql1))
            {
              $item_id = $row1['item_id'];
              $item_name = "Unknown Item";
              $sql2 = mysqli_query($con, "SELECT name FROM items WHERE id = $item_id;");
              if($row2 = mysqli_fetch_array($sql2)) {
                $item_name = $row2['name'];
              }
              
              echo '      <div class="order-item-line">';
              echo '        <div class="item-name-tag"><strong>#' . htmlspecialchars($row1['item_id']) . '</strong> ' . htmlspecialchars($item_name) . '</div>';
              echo '        <div class="item-qty-tag">' . htmlspecialchars($row1['quantity']) . ' Pcs.</div>';
              echo '        <div class="item-price-tag">USD ' . htmlspecialchars($row1['price']) . '</div>';
              echo '      </div>';
            }
            echo '    </li>';

            /* BLOCK 19: Order Card Summary Footer & Form Processing Elements */
            // Prints the calculated financial aggregate total and creates execution controls allowing system status overrides.
            echo '    <li class="order-group-item closing-summary-row">';
            echo '      <div class="order-total-row">';
            echo '        <div><span class="badge-ui bg-info">Gross Total Ledger</span></div>';
            echo '        <div class="order-total-price">USD ' . htmlspecialchars($row['total']) . '</div>';
            echo '      </div>';
            
            if(!$deleted) {
              echo '      <div class="action-btn-container">';
              echo '        <button class="btn" type="submit" name="action">Update Order Parameters</button>';
              echo '      </div>';
            }
            
            echo '    </li>';
            echo '  </ul>';

            /* BLOCK 20: Contextual Action Form Closer & Card Layout Safety Guard */
            // Encapsulates the open input forms and securely isolates the generated order container elements.
            if(!$deleted) {
              echo '  </form>';
            }

            echo '</div>'; 
          }
        ?>
      </div>
    </main>
  </div>
  
  <footer>
    <div class="footer-content">
      <span>Copyright © 2026</span>
      <span>Design and Developed by <a href="https://www.linkedin.com/in/bacinie">bacinie</a></span>
    </div>
  </footer>
</body>
</html>
<?php
  }
  /* BLOCK 22: Authentication Failure & Role Router Interceptor Routing Fallbacks */
  // Redirects authorized standard customers to normal endpoints while safely casting guests toward authentication pages.
  else
  {
    if($_SESSION['customer_id']==session_id())
    {
      header("location:orders.php");    
    }
    else{
      header("location:login.php");
    }
  }
?>