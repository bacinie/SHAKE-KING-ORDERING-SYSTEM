<?php
/* BLOCK 1: Database & Financial Dependencies */
// Establishes a connection to the database server ($con) and loads user-specific ecosystem variables such as digital wallet balances ($balance).
include 'includes/connect.php';
include 'includes/wallet.php';

  /* BLOCK 2: Customer Session Guard */
  // Checks if the current visitor possesses a validated customer session handshake, preventing unauthenticated access to the food ordering panel.
  if($_SESSION['customer_sid']==session_id())
  {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Order Food</title>

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
        <li class="active">
          <a href="index.php">Order Food</a>
        </li>
        <li>
          <a>Orders</a>
          <ul>
            <li><a href="orders.php">All Orders</a></li>
            <?php
              /* BLOCK 7: Customer Custom Orders Sub-Navigation Loop */
              // Scans transaction tables to isolate processing categories unique to the active user, generating filtered navigation anchors.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders WHERE customer_id = $user_id;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="orders.php?status='.urlencode($row['status']).'">'.htmlspecialchars($row['status']).'</a></li>';
              }
            ?>
          </ul>
        </li>
        <li>
          <a>Tickets</a>
          <ul>
            <li><a href="tickets.php">All Tickets</a></li>
            <?php
              /* BLOCK 8: Customer Ticket Category Filter Link Generator */
              // Dynamically queries helpdesk logs authored by this specific customer account to display active support sorting routes.
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
        <h5 class="breadcrumbs-title">Order Panel</h5>
        <p class="caption">Select item quantities and proceed to purchase.</p>
        <div class="divider"></div>

        <form id="formValidate" method="post" action="place-order.php">
          
          <div class="table-responsive-wrapper" style="margin-bottom: 1.5rem;">
            <table class="order-panel-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Item Price/Piece</th>
                  <th style="text-align: right; padding-right: 2.5rem;">Quantity</th>
                </tr>
              </thead>

              <tbody>
                <?php
                  /* BLOCK 11: Food Menu Menu Matrix Database Loop */
                  // Extracts active menu records from table arrays, generating interactive tabular items alongside input count targets mapped to item primary keys.
                  $result = mysqli_query($con, "SELECT * FROM items where not deleted;");
                  while($row = mysqli_fetch_array($result))
                  {
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($row["name"]).'</td>';
                    echo '<td>$'.number_format($row["price"], 2).'</td>';              
                    echo '<td style="text-align: right; padding-right: 1.5rem;">';
                    echo '  <div class="input-field">';
                    echo '    <input id="'.htmlspecialchars($row["id"]).'" name="'.htmlspecialchars($row['id']).'" type="number" min="0" max="10" placeholder="0">';
                    echo '  </div>';
                    echo '</td>';
                    echo '</tr>';
                  }
                ?>
              </tbody>
            </table>
          </div>

          <div class="input-field" style="margin-top: 2rem;">
            <label for="description">Any notes (optional)</label>
            <textarea id="description" name="description" placeholder="Type your dynamic drop or delivery instructions here..."></textarea>
          </div>

          <div class="row" style="text-align: right; margin-top: 1.5rem;">
            <button class="btn" type="submit" name="action">Place Order</button>
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
  /* BLOCK 15: Security Interceptor & Multi-Role Redirect Routing */
  // Catches invalid customer handshakes, rerouting managers to administration hubs and anonymous visitors back to authorization layouts.
  else
  {
    if($_SESSION['admin_sid']==session_id())
    {
      header("location:admin-page.php");    
    }
    else{
      header("location:login.php");
    }
  }
?>