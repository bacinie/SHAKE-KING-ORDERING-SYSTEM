<?php
/* BLOCK 1: Database Connection */
// Includes the external configuration file that initializes the MySQL database connection ($con).
include 'includes/connect.php';

  /* BLOCK 2: Security & Admin Session Validation */
  // Checks if the user logged in is authenticated as an Admin. If true, it loads the page.
  if($_SESSION['admin_sid']==session_id())
  {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Food Menu</title>

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
        <span class="logo-text">Food Menu Panel</span>
      </div>
      <div class="balance-box">
        Welcome, <?php echo htmlspecialchars($name); ?>
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
        <li class="active">
          <a href="index.php">Food Menu</a>
        </li>
        <li>
          <a>Orders</a>
          <ul>
            <li><a href="all-orders.php">All Orders</a></li>
            <?php
              /* BLOCK 9: Dynamic Order Status Query Sub-navigation */
              /* Queries the database to retrieve every distinct order status and builds dynamic URL filter nodes. */
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
              /* BLOCK 10: Dynamic Support Ticket Status Query Sub-navigation */
              /* Queries database to group active customer help desk ticket types and dynamically formats them as links. */
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="all-tickets.php?status='.urlencode($row['status']).'">'.htmlspecialchars($row['status']).'</a></li>';
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
      <h5 class="breadcrumbs-title">Food Menu Management</h5>
      <p class="caption">Add, Edit or Remove Menu Items from the system inventory database safely.</p>
      <div class="divider"></div>

      <div class="content-card" style="margin-bottom: 2rem;">
        <h4 class="workspace-section-heading">Modify Order Food Menu Items</h4>
        
        <form method="post" action="routers/menu-router.php">
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
              <tr style="text-align: left; border-bottom: 2px solid #d9e6e6;">
                <th style="padding: 0.75rem;">Name</th>
                <th style="padding: 0.75rem;">Item Price/Piece</th>
                <th style="padding: 0.75rem;">Available Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              /* BLOCK 13: Database Food Inventory Fetch & Loop UI Generator */
              /* Selects all catalog items from the database and loops through them to build dynamic form input rows. */
              $result = mysqli_query($con, "SELECT * FROM items");
              while($row = mysqli_fetch_array($result))
              {
                $itemId = $row["id"];
                $checkedAvailable = ($row['deleted'] == 0) ? 'selected' : '';
                $checkedNotAvailable = ($row['deleted'] != 0) ? 'selected' : '';

                echo '<tr style="border-bottom: 1px solid #dee2e6;">';
                echo '  <td style="padding: 0.75rem;">';
                echo '    <div class="input-field">';
                echo '      <input value="'.htmlspecialchars($row["name"]).'" id="'.$itemId.'_name" name="'.$itemId.'_name" type="text" required>';
                echo '    </div>';
                echo '  </td>';
                echo '  <td style="padding: 0.75rem;">';
                echo '    <div class="input-field">';
                echo '      <input value="'.htmlspecialchars($row["price"]).'" id="'.$itemId.'_price" name="'.$itemId.'_price" type="number" step="0.01" required>';
                echo '    </div>';
                echo '  </td>';
                echo '  <td style="padding: 0.75rem;">';
                echo '    <div class="input-field">';
                echo '      <select name="'.$itemId.'_hide">';
                echo '        <option value="1" '.$checkedAvailable.'>Available</option>';
                echo '        <option value="2" '.$checkedNotAvailable.'>Not Available</option>';
                echo '      </select>';
                echo '    </div>';
                echo '  </td>';
                echo '</tr>';
              }
              ?>
            </tbody>
          </table>
          
          <div style="text-align: right;">
            <button class="btn" type="submit" name="action">Modify Menu Configuration</button>
          </div>
        </form>
      </div>

      <div class="content-card">
        <h4 class="workspace-section-heading">Add New Custom Menu Item</h4>
        
        <form method="post" action="routers/add-item.php">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="input-field">
              <label for="name">New Item Name</label>
              <input id="name" name="name" type="text" placeholder="e.g. Grilled Chicken Burgers" required>
            </div>
            <div class="input-field">
              <label for="price">Price Per Piece</label>
              <input id="price" name="price" type="number" step="0.01" placeholder="e.g. 250.00" required>
            </div>
          </div>
          
          <div style="text-align: right;">
            <button class="btn" type="submit" name="action">Add To Database</button>
          </div>
        </form>
      </div>

    </main>
    </div>
    
  <footer>
    <div class="footer-content">
      <span>Copyright © 2026</span>
      <span>Design and Developed by <a href="www.linkedin.com/in/bacinie">bacinie</a></span>
    </div>
  </footer>
  </body>
</html>
<?php
  }
  /* BLOCK 16: Authentication Fallbacks Router Redirection */
  /* Fallback route handles edge errors: sends authenticated active customers to index.php, and unauthenticated guests to login.php. */
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