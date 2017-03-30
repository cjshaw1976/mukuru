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

// quote
if (isset($_POST['action']) && $_POST['action'] == "quote") {
  //get rate
}

/* Surcharge
○ ZAR: 7.5%
○ GBP: 5%
○ EUR: 5%
○ KES: 2.5%
*/



 ?>
