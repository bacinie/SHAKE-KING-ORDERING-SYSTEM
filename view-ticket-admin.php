<?php
/* BLOCK 1: Dependancy Imports & View Gate Variables */
// Pulls core database handles ($con) and tracking constants, while initializing the control boolean ($continue).
include 'includes/connect.php';
include 'includes/wallet.php';
$continue=0;

/* BLOCK 2: Multi-Role Authorization & Parametric Ticket Verification */
// Evaluates active session strings against systemic user permissions. It parses incoming ticket IDs, runs relational data validations, and extracts metadata to structure individual ticket profiles.
if($_SESSION['admin_sid']==session_id())
{
    $ticket_id = $_GET['id'];
    $sql1 = "SELECT * FROM tickets WHERE id = $ticket_id;";
    if(mysqli_num_rows(mysqli_query($con,$sql1))>0){
        $row = $con->query($sql1)->fetch_assoc();
        $type = $row['type'];
        $subject = $row['subject'];
        $description = $row['description'];
        $date = $row['date'];
        $status = $row['status'];
        $continue=1;
    }
    else {
        $continue = 0; 
    }
}
elseif($_SESSION['customer_sid']==session_id()) {
    $ticket_id = $_GET['id'];
    $sql1 = "SELECT * FROM tickets WHERE id = $ticket_id AND user_id = ".$_SESSION['user_id'].";";
    if(mysqli_num_rows(mysqli_query($con,$sql1))>0){
        $row = $con->query($sql1)->fetch_assoc();
        $type = $row['type'];
        $subject = $row['subject'];
        $description = $row['description'];
        $date = $row['date'];
        $status = $row['status'];
        $continue=1;
    }
    else {
        $continue = 0;
    }
}

if($continue){
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Ticket No. <?php echo $ticket_id.' - '. $type;?></title>
  <link rel="icon" href="images/SKLogo.png">
  
  <link href="css/style.css" type="text/css" rel="stylesheet">
</head>

<body class="admin-theme">

  <header>
    <div class="nav-wrapper">
      <div class="header-brand-group">
        <span class="logo-text">Logo</span>
      </div>
      <div>
        <span class="balance-box">Wallet Balance: $<?php echo $balance;?></span>
      </div>
    </div>
  </header>
  <div class="dashboard-grid">

    <aside>
      <div class="user-details">
        <span class="profile-name"><?php echo $name;?></span>
        <p class="user-role"><?php echo $role;?></p>
        <a href="routers/logout.php" class="logout-link">Logout</a>
      </div>
      
      <ul class="side-nav-links">
        <li><a href="index.php">Order Food</a></li>
        <li>
          <a href="all-orders.php">Orders</a>
          <ul>
            <?php
              /* BLOCK 6: System-wide Order Status Filter Aggregator */
              // Dynamically identifies all active status types in the global system table to provide clean navigation sort points.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="all-orders.php?status='.$row['status'].'">'.$row['status'].'</a></li>';
              }
            ?>
          </ul>
        </li>
        <li class="active">
          <a href="all-tickets.php">Tickets</a>
          <ul>
            <?php
              /* BLOCK 7: System-wide Help Desk Ticket Status Link Aggregator */
              // Scans helpdesk logs across the platform to build direct, categorized url filters for ticket records.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="all-tickets.php?status='.$row['status'].'">'.$row['status'].'</a></li>';
              }
            ?>
          </ul>
        </li>       
        <li><a href="details.php">Edit Details</a></li>        
      </ul>
    </aside>
    <main>
      <h2 class="breadcrumbs-title">Ticket Operations Workspace</h2>
      <p class="caption">Review support history thread status updates or add replies below.</p>
      <div class="divider"></div>

      <div class="ticket-info-banner">
        <h3>Ticket No. #<?php echo $ticket_id; ?></h3>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($subject); ?></p>
        <p><strong>Status:</strong> 
          <span class="badge-ui <?php echo ($status == 'Closed') ? 'bg-dark' : 'bg-info'; ?>">
            <?php echo $status; ?>
          </span>
        </p>  
        <p><strong>Type:</strong> <?php echo htmlspecialchars($type); ?></p>                      
        
        <div style="margin-top: 1.25rem;">
          <form method="post" action="routers/ticket-status.php">
            <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">                    
            <input type="hidden" name="status" value="<?php echo ($status != 'Closed' ? 'Closed' : 'Open'); ?>">
            <button class="btn <?php echo ($status != 'Closed') ? 'btn-danger' : ''; ?>" type="submit">
              <?php echo ($status != 'Closed' ? 'Close Ticket' : 'Reopen Ticket'); ?>
            </button>
          </form>
        </div>
      </div>                    

      <div class="workspace-section-heading">Discussion History Thread</div>
      <ul class="thread-container">
        <?php
        $sql1 = mysqli_query($con, "SELECT * from ticket_details WHERE ticket_id = $ticket_id;");
        while($row1 = mysqli_fetch_array($sql1)){
          $sql2 = "SELECT * FROM users WHERE id = ".$row1['user_id'].";";
          $name = "Unknown User";
          $role1 = "Customer";
          if(mysqli_num_rows(mysqli_query($con,$sql2))>0){
            $row2 = $con->query($sql2)->fetch_assoc();
            $name = $row2['name'];
            $role1 = $row2['role'];                    
          }
          ?>
          <li class="thread-message-block">
            <div class="thread-meta-header">
              <div><strong><?php echo htmlspecialchars($name); ?></strong> (<?php echo $role1; ?>)</div>
              <div>Date Stamp: <?php echo $row1['date']; ?></div>
            </div>
            <p class="thread-body-text"><?php echo nl2br(htmlspecialchars($row1['description'])); ?></p>
          </li>
          <?php
        }
        ?>
      </ul>

      <?php 
        /* BLOCK 11: Message Response Intake Gateway Conditional */
        // Keeps user interaction options available only while the ticket's state is set to "Open", providing a data transmission gateway to message processors.
        if($status != 'Closed'): 
      ?>
        <div class="content-card">
          <div class="workspace-section-heading">Add a Message Response</div>
          <form method="post" action="routers/ticket-message.php">           
            <input type="hidden" name="role" value="<?php echo $role; ?>">
            <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
            
            <div class="input-field">
              <label for="message">Your Reply Text Message</label>
              <textarea name="message" id="message" required placeholder="Type reply context info here (min 5 characters)..."></textarea>
            </div>
            
            <button class="btn" type="submit">Submit Reply Response</button>
          </form>
        </div>
      <?php endif; ?>

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
/* BLOCK 13: Integrity Interceptor Violation Routers */
// Intercepts structural validation breakdowns (e.g. invalid target item IDs), rerouting active client sessions back to user dashboards or guests back to standard login setups.
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