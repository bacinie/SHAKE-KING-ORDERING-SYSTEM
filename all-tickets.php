<?php
/* BLOCK 1: Database Initialization Connection */
// Establishes a link with the underlying database by calling the file where the connection variable ($con) is defined.
include 'includes/connect.php';

  /* BLOCK 2: Administrative Authentication Guard */
  // Checks if the current session token matches an authorized administrator session ID before serving private layout data.
  if($_SESSION['admin_sid']==session_id())
  {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Tickets Management</title>

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
        <span class="logo-text">Support Tickets Grid</span>
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
        <li>
          <a>Orders</a>
          <ul>
            <li><a href="all-orders.php">All Orders</a></li>
            <?php
              /* BLOCK 9: Order Status Sub-Navigation Generation Query */
              // Scans distinct processing states within the orders database table and targets them into parameterized filter strings.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="all-orders.php?status=' . urlencode($row['status']) . '">' . htmlspecialchars($row['status']) . '</a></li>';
              }
            ?>
          </ul>
        </li>
        <li class="active">
          <a>Tickets</a>
          <ul>
            <li class="<?php echo !isset($_GET['status']) ? 'active' : ''; ?>">
              <a href="all-tickets.php">All Tickets</a>
            </li>
            <?php
              /* BLOCK 10: Dynamic Support Ticket Categories Sub-Navigation Generator */
              // Fetches individual existing workflow states inside helpdesk files to output filter states with contextual CSS markers.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets;");
              while($row = mysqli_fetch_array($sql)){
                $is_active = (isset($_GET['status']) && $_GET['status'] == $row['status']) ? 'class="active"' : '';
                echo '<li ' . $is_active . '><a href="all-tickets.php?status=' . urlencode($row['status']) . '">' . htmlspecialchars($row['status']) . '</a></li>';
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
      <h5 class="breadcrumbs-title">Support Tickets Management</h5>
      <p class="caption">Review, filter, and respond to platform issue tickets logged by system client accounts.</p>
      <div class="divider"></div>

      <div class="content-card" style="padding: 1.5rem;">
        <h4 class="workspace-section-heading" style="color: #004d4d; font-size: 1.2rem; font-weight: 600; margin-bottom: 1.5rem;">Customer Communications Ledger</h4>
        
        <div class="tickets-list-wrapper">
          <div class="ticket-list-header">
            <div>Subject Topic</div>
            <div>Status</div>
            <div>Category Type</div>
            <div style="text-align: right;">Date Logged</div>
          </div>

          <?php
            /* BLOCK 13: Ticket Sorting Filtration Engine */
            // Captures sorting instructions passed over URL queries, executing a SQL wildcard search matching ('%') if empty.
            if(isset($_GET['status'])){
              $status_filter = $_GET['status'];
            } else {
              $status_filter = '%';
            }     
            
            /* BLOCK 14: Support Ticket Record Dynamic Fetch & Loop Array Generator */
            // Extracts matched records from data targets and builds interactive link cards styled dynamically via conditional badge status indicators.
            $sql = mysqli_query($con, "SELECT * FROM tickets WHERE status LIKE '$status_filter';");
            while($row = mysqli_fetch_array($sql)){ 
              $status = htmlspecialchars($row['status']);
              $badge_class = ($status == 'Open' || $status == 'Active') ? 'bg-info' : 'bg-dark';

              echo '<a href="view-ticket-admin.php?id=' . urlencode($row['id']) . '" class="ticket-list-row">';
              echo '  <div class="ticket-subject-text"><strong>#' . htmlspecialchars($row['id']) . '</strong> ' . htmlspecialchars($row['subject']) . '</div>';
              echo '  <div><span class="badge-ui ' . $badge_class . '">' . $status . '</span></div>';                     
              echo '  <div class="ticket-type-text">' . htmlspecialchars($row['type']) . '</div>';
              echo '  <div class="ticket-date-stamp">' . htmlspecialchars($row['date']) . '</div>';
              echo '</a>';
            }
          ?>
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
  /* BLOCK 16: Authentication Interception Router Fallbacks */
  // Redirects validated regular consumers back toward client support profiles, or filters guests to login screens.
  else
  {
    if($_SESSION['customer_sid']==session_id())
    {
      header("location:tickets.php");    
    }
    else{
      header("location:login.php");
    }
  }
?>