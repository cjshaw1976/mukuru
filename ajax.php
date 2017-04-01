<?php
// Get common settings
include 'settings.php';

session_start();

// If no token or token does not match, return 404
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
  header("HTTP/1.0 404 Not Found");
  exit;
}

// Connect to database
try {
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "<strong>Error: Unable to connect to database.</strong></br>";
  echo "Have you set your database connection details in settings.php?</br></br>";
  echo "Error message: " . $e->getMessage();
  exit();
}

function get_rate( $target ) {
  $conn = $GLOBALS['conn'];
  $stmt = $conn->prepare("SELECT `rate` FROM `trader_rates` WHERE `code` = :code;");
  $stmt->bindParam(':code', $target);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update rates in db
if (isset($_POST['action']) && $_POST['action'] == "update") {
  $stmt = $conn->prepare("SELECT `code` FROM `trader_rates` WHERE DATE_ADD(NOW(), INTERVAL -1 DAY) > `timestamp`;");
  $stmt->execute();
  $result = $stmt->fetchall(PDO::FETCH_ASSOC);
  if ($result){
    $currencies = '';
    foreach ($result as $currency){
      $currencies .= $currency['code'].",";
    }
    $json = file_get_contents('http://www.apilayer.net/api/live?access_key=cedba1f7e1e924185afb10443eb2b06b&currencies='.$currencies);
    $obj = json_decode($json, true);
    if ($obj['success'] == 1) {
      foreach ($result as $currency){
        $stmt = $conn->prepare("UPDATE `trader_rates` SET `rate` = :rate, `timestamp` = :timestamp  WHERE `code` = :code;");
        $stmt->bindParam(':rate', $obj['quotes']['USD'.$currency['code']]);
        $stmt->bindParam(':code', $currency['code']);
        $stmt->bindParam(':timestamp', $currency['timestamp']);
        $stmt->execute();
      }
    }
  }
}

// quote or purchase
if (isset($_POST['action']) && ($_POST['action'] == "quote" || $_POST['action'] == "purchase")) {
    $result = array();

    //Get Rate
    $stmt = $conn->prepare("SELECT `rate` FROM `trader_rates` WHERE `code` = :code;");
    if($_POST['source'] == "USD"){
        $stmt->bindParam(':code', $_POST['target']);
        $result['currency'] = $_POST['target'];
        $result['usd'] = $_POST['amount'];
    } else {
        $stmt->bindParam(':code', $_POST['source']);
        $result['currency'] = $_POST['source'];
        $result['foreign'] = $_POST['amount'];
    }
    $stmt->execute();
    $result['exchange_rate'] = $stmt->fetchColumn();

    // Get Surcharge
    switch ($result['currency']) {
    case 'ZAR':
        $result['surcharge_percentage'] = 7.5;
        break;
    case 'GBP':
    case 'EUR':
        $result['surcharge_percentage'] = 5;
        break;
    case 'KES':
        $result['surcharge_percentage'] = 2.5;
        break;
    }

    if($_POST['source'] == "USD"){
      // (amount / (1 + ( surcharge_percentage / 100 ))) * exchange_rate = total
      $result['total'] = number_format((($_POST['amount'] / (1 + ($result['surcharge_percentage'] / 100))) * $result['exchange_rate']), 2, '.', '');
      $result['surcharge_amount'] = number_format($_POST['amount'] - ($_POST['amount'] / (1 + ($result['surcharge_percentage'] / 100))), 2, '.', '');
      $result['foreign'] = $result['total'];
    } else {
      // (amount / exchange_rate) * (1 + ( surcharge_percentage / 100 )) = total
      $result['total'] = number_format(($_POST['amount']  / $result['exchange_rate']) * (1 + ($result['surcharge_percentage'] / 100)), 2, '.', '');
      $result['surcharge_amount'] = number_format($result['total'] - ($_POST['amount']  / $result['exchange_rate']), 2, '.', '');
      $result['usd'] = $result['total'];
    }

    if ($_POST['action'] == "purchase") {
      $stmt = $conn->prepare("INSERT INTO `trader_orders` (`currency`, `exchange_rate`, `surcharge_percent`, `surcharge_amount`, `currency_amount`, `usd_amount`)
        VALUES (:currency, :exchange_rate, :surcharge_percent, :surcharge_amount, :currency_amount, :usd_amount);");

      $stmt->bindParam(':currency', $result['currency']);
      $stmt->bindParam(':exchange_rate', $result['exchange_rate']);
      $stmt->bindParam(':surcharge_percent', $result['surcharge_percentage']);
      $stmt->bindParam(':surcharge_amount', $result['surcharge_amount']);
      $stmt->bindParam(':currency_amount', $result['foreign']);
      $stmt->bindParam(':usd_amount', $result['usd']);
      $stmt->execute();

      // Get id & timestamp
      $result['purchace_id'] = $conn->lastInsertId();
      $stmt = $conn->prepare("SELECT `timestamp` FROM `trader_orders` WHERE `id` = :id;");
      $stmt->bindParam(':id', $result['purchace_id']);
      $stmt->execute();
      $result['timestamp'] = $stmt->fetchColumn();

      // Extra Actions
      if ($result['currency'] == 'GBP'){
        $to      = $email_recipent;
        $subject = 'GBP Currency purchace';
        $message = 'Good day,' . "\r\n" .
            'There has been '. $result['foreign'] .' GBP purchased, reference: '. $result['purchace_id'];
        $headers = 'From: webmaster@example.com' . "\r\n" .
            'Reply-To: webmaster@example.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);
      }

      if ($result['currency'] == 'EUR'){
        $result['discount_amount'] = number_format($result['usd'] * 0.02, 2, '.', '');
        $stmt = $conn->prepare("UPDATE `trader_orders` SET discount_amount = :discount WHERE id = :id;");
        $stmt->bindParam(':id', $result['purchace_id']);
        $stmt->bindParam(':discount', $result['discount_amount']);
        $stmt->execute();
      } else{
        $result['discount_amount'] = 0;
      }
    }

    // Return result
    echo json_encode($result);
}

?>
