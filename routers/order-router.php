<?php
include '../includes/connect.php';
include '../includes/wallet.php';

$total = 0;
$address = mysqli_real_escape_string($con, htmlspecialchars($_POST['address']));
// FIXED: Avoids crashing if the user didn't write an optional description note
$description = isset($_POST['description']) ? mysqli_real_escape_string($con, htmlspecialchars($_POST['description'])) : '';
$payment_type = mysqli_real_escape_string($con, $_POST['payment_type']);
$total = floatval($_POST['total']);

$sql = "INSERT INTO orders (customer_id, payment_type, address, total, description) VALUES ($user_id, '$payment_type', '$address', $total, '$description')";

if ($con->query($sql) === TRUE){
    $order_id = $con->insert_id;
    foreach ($_POST as $key => $value)
    {
        if(is_numeric($key)){
            $key = intval($key);
            $value = intval($value);
            
            $result = mysqli_query($con, "SELECT * FROM items WHERE id = $key");
            $price = 0;
            while($row = mysqli_fetch_array($result))
            {
                $price = $row['price'];
            }
            $calculated_price = $value * $price;
            
            $sql_details = "INSERT INTO order_details (order_id, item_id, quantity, price) VALUES ($order_id, $key, $value, $calculated_price)";
            $con->query($sql_details);     
        }
    }
    
    if($_POST['payment_type'] == 'Wallet'){
        $balance = $balance - $total;
        $sql_wallet = "UPDATE wallet_details SET balance = $balance WHERE wallet_id = $wallet_id;";
        $con->query($sql_wallet);
    }
    header("location: ../orders.php");
    exit();
}
?>