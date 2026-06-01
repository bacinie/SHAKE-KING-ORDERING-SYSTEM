<?php
/* BLOCK 1: Database Initialization Context Linkage */
// Connects the page execution layer to the database controller ($con) to query backend tables.
include 'includes/connect.php';

/* BLOCK 2: Administrative Security Access Control Gate */
// Verifies if the current visitor holds a valid admin token, blocking non-admin users from accessing management options.
if($_SESSION['admin_sid']==session_id())
{
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>User List</title>

  <link rel="icon" href="images/SKLogo.png">

  <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body class="admin-theme">

  <header id="header">
    <div class="nav-wrapper">
      <div class="header-brand-group">
        <a href="index.php">
          <img src="images/SKLogo.png" alt="Logo" class="header-logo-img">
        </a>
        <span class="logo-text">Management Terminal</span>
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
          <a href="index.php">Food Menu</a>
        </li>
        <li>
          <a>Orders</a>
          <ul>
            <li><a href="all-orders.php">All Orders</a></li>
            <?php
              /* BLOCK 8: Dynamic Order Status Category Sub-links */
              // Scans distinct processing states across all customer orders to build individual sorting filters.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="all-orders.php?status='.urlencode($row['status']).'">'.htmlspecialchars($row['status']).'</a></li>';
              }
            ?>
          </ul>
        </li>
        <li>
          <a>Tickets</a>
          <ul>
            <li><a href="all-tickets.php">All Tickets</a></li>
            <?php
              /* BLOCK 9: Dynamic Help Ticket Category Sub-links */
              // Isolates current ticket status indicators directly from the tracking tables to generate helpdesk filters.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="all-tickets.php?status='.urlencode($row['status']).'">'.htmlspecialchars($row['status']).'</a></li>';
              }
            ?>
          </ul>
        </li>     
        <li class="active">
          <a href="users.php">Users</a>
        </li>     
      </ul>
    </aside>

    <main id="content" class="admin-container">
      
      <h5 class="breadcrumbs-title">User Registry Records</h5>
      <p class="caption">Enable, Disable, or Verify user access controls across the localized ecosystem.</p>
      <div class="divider"></div>

      <div class="content-card" style="margin-bottom: 2rem;">
        <div style="margin-bottom: 1.5rem;">
          <h4 style="color: #004d4d; font-size: 1.2rem; font-weight: 600; margin-bottom: 0.25rem;">List of Registered Accounts</h4>
          <p style="color: #5c7373; font-size: 0.9rem;">Alter permissions, global roles, verification status parameters, and system wallet limits.</p>
        </div>

        <form id="formValidate1" method="post" action="routers/user-router.php">
          <div class="table-responsive-wrapper">
            <table class="admin-table">
              <thead>
                <tr>
                  <th style="min-width: 140px;">Name</th>
                  <th style="min-width: 160px;">Email</th>
                  <th style="min-width: 120px;">Contact</th>
                  <th style="min-width: 200px;">Address</th>  
                  <th style="min-width: 140px;">Role</th>
                  <th style="min-width: 130px;">Verified</th>
                  <th style="min-width: 120px;">Account State</th>
                  <th style="min-width: 150px; text-align: right;">Wallet Balance</th>            
                </tr>
              </thead>

              <tbody>
                <?php
                /* BLOCK 12: Account Directory Query & Relational Wallet Resolver Loop */
                // Pulls all registered profile data rows, executing matching internal queries to find linked wallet assets before outputting table field options.
                $result = mysqli_query($con, "SELECT * FROM users");
                while($row = mysqli_fetch_array($result))
                {
                  echo '<tr>';
                  echo '<td><span class="cell-primary-text">'.htmlspecialchars($row["name"]).'</span></td>';
                  echo '<td><span class="cell-secondary-text">'.htmlspecialchars($row["email"]).'</span></td>';
                  echo '<td><span class="cell-secondary-text">'.htmlspecialchars($row["contact"]).'</span></td>';   
                  echo '<td><span class="cell-address-text">'.htmlspecialchars($row["address"]).'</span></td>';                      
                  echo '<td>';
                  echo '  <select name="'.htmlspecialchars($row['id']).'_role" class="table-select">';
                  echo '    <option value="Administrator" '.($row['role']=='Administrator' ? 'selected' : '').'>Administrator</option>';
                  echo '    <option value="Customer" '.($row['role']=='Customer' ? 'selected' : '').'>Customer</option>';
                  echo '  </select>';
                  echo '</td>';
                  echo '<td>';
                  echo '  <select name="'.htmlspecialchars($row['id']).'_verified" class="table-select">';
                  echo '    <option value="1" '.($row['verified'] ? 'selected' : '').'>Verified</option>';
                  echo '    <option value="0" '.(!$row['verified'] ? 'selected' : '').'>Not Verified</option>';
                  echo '  </select>';
                  echo '</td>'; 
                  echo '<td>';
                  echo '  <select name="'.htmlspecialchars($row['id']).'_deleted" class="table-select">';
                  echo '    <option value="1" '.($row['deleted'] ? 'selected' : '').'>Disable</option>';
                  echo '    <option value="0" '.(!$row['deleted'] ? 'selected' : '').'>Enable</option>';
                  echo '  </select>';
                  echo '</td>';
                  
                  $key = $row['id'];
                  $balance = 0; 
                  $sql = mysqli_query($con,"SELECT * from wallet WHERE customer_id = $key;");
                  if($row1 = mysqli_fetch_array($sql)){
                    $wallet_id = $row1['id'];
                    $sql1 = mysqli_query($con,"SELECT * from wallet_details WHERE wallet_id = $wallet_id;");
                    if($row2 = mysqli_fetch_array($sql1)){
                      $balance = $row2['balance'];
                    }
                  }
                  echo '<td>';
                  echo '  <div class="table-wallet-input">';
                  echo '    <span class="currency-prefix">USD</span>';
                  echo '    <input aria-label="Balance for '.htmlspecialchars($row['name']).'" name="'.htmlspecialchars($row['id']).'_balance" value="'.htmlspecialchars($balance).'" type="number" step="any" required>';
                  echo '  </div>';
                  echo '</td>';
                  echo '</tr>';           
                }
                ?>
              </tbody>
            </table>
          </div>
          
          <div style="text-align: right; margin-top: 1.5rem;">
            <button class="btn" type="submit" name="action" style="padding: 0.75rem 2rem;">Modify Records</button>
          </div>
        </form>
      </div>

      <div class="content-card">
        <div style="margin-bottom: 1.5rem;">
          <h4 style="color: #004d4d; font-size: 1.2rem; font-weight: 600; margin-bottom: 0.25rem;">Add New User Profile</h4>
          <p style="color: #5c7373; font-size: 0.9rem;">Populate identity credentials down below into the local database registry core system.</p>
        </div>

        <form id="formValidate" method="post" action="routers/add-users.php">
          <div class="form-grid-layout">
            
            <div class="input-field">
              <label for="username">Username</label>
              <input id="username" name="username" type="text" required minlength="5" placeholder="Min 5 characters">
            </div>               
            
            <div class="input-field">
              <label for="password">Password</label>
              <input id="password" name="password" type="password" required minlength="5" placeholder="Min 5 characters">
            </div>               
            
            <div class="input-field">
              <label for="name">Full Name</label>
              <input id="name" name="name" type="text" required minlength="5" placeholder="Enter full name">
            </div>

            <div class="input-field">
              <label for="email">Email Address</label>
              <input id="email" name="email" type="email" required placeholder="name@domain.com">
            </div>

            <div class="input-field">
              <label for="contact">Phone Number</label>
              <input id="contact" name="contact" type="number" required minlength="4" placeholder="Min 4 digits">
            </div>   

            <div class="input-field">
              <label for="role">Global Role</label>
              <select id="role" name="role" class="table-select" style="padding: 0.75rem 1rem;">
                <option value="Administrator">Administrator</option>
                <option value="Customer" selected>Customer</option>
              </select>
            </div>

            <div class="input-field">
              <label for="verified">Verification Status</label>
              <select id="verified" name="verified" class="table-select" style="padding: 0.75rem 1rem;">
                <option value="1">Verified</option>
                <option value="0" selected>Not Verified</option>
              </select>
            </div> 

            <div class="input-field">
              <label for="deleted">Access Pipeline</label>
              <select id="deleted" name="deleted" class="table-select" style="padding: 0.75rem 1rem;">
                <option value="1">Disable</option>
                <option value="0" selected>Enable</option>
              </select>
            </div>

            <div class="input-field span-grid-full">
              <label for="address">Postal Address</label>
              <input id="address" name="address" type="text" required minlength="10" placeholder="Enter complete home or drop-off location address profile parameters...">
            </div>   

          </div>

          <div style="text-align: right; margin-top: 1.5rem; border-top: 1px solid #f0f5f5; padding-top: 1.5rem;">
            <button class="btn" type="submit" name="action" style="padding: 0.75rem 2rem;">Add Account Entry</button>
          </div>
        </form>     
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
/* BLOCK 15: Session Violation Routing Exception Interceptor */
// catches unauthorized access attempts, routing authenticated customers back to front menus or sending missing/guest sessions directly to login tools.
else
{
  if($_SESSION['customer_sid']==session_id())
  {
    header("location:index.php");   
  }
  else{
    header("location:login.php");
  }
}
?>