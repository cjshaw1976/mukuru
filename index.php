<?php
/*      Mukuru Benchmark Test
 *
 *      C. J. Shaw
 *      chris@shawcando.com
 *      28 March 2017
 *
 *      Build a money money trading page, utlizing PHP, MySQL & ajax
 *
 *      index.php
 *      ---------
 *      User interface
 *
 */

// Session to hold csrf_token
session_start();
if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === null)
  $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));

// Get common settings
include 'settings.php';

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

// Create tables and insert inital data to database
$stmt = $conn->prepare("SHOW TABLES LIKE 'trader_rates';");
$result = $stmt->fetch();
if(!$result) {
  $sql = file_get_contents('data.sql');
  $conn->exec($sql);
}

// For demo purposes, get all purchases

$stmt = $conn->prepare("SELECT `id`, `currency`, `exchange_rate`, `surcharge_percent`, `currency_amount`, `usd_amount`, `surcharge_amount`, `timestamp`, `discount_amount` FROM `trader_orders` ORDER BY `timestamp` DESC;");
$stmt->execute();
$purchases = $stmt->fetchall();

 ?>
 <!doctype html>
 <html class="no-js" lang="">
     <head>
         <meta charset="utf-8">
         <meta http-equiv="x-ua-compatible" content="ie=edge">
         <title>Mukuru Benchmark Test</title>
         <meta name="description" content="">
         <meta name="viewport" content="width=device-width, initial-scale=1">

         <!-- Normalize to make all browsers start equal -->
         <link rel="stylesheet" href="css/normalize.css">

         <!-- Latest compiled and minified CSS -->
         <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

          <!-- Our tweeks -->
          <link rel="stylesheet" href="css/main.css">
     </head>
     <body>
         <!--[if lt IE 8]>
             <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
         <![endif]-->
         <div class="container">
           <div class="row demo">
             <h4>Instructions:</h4>
             <ul>
               <li>Select the currency you want to work in</li>
               <li>Type an amount to change</li>
               <li>Swop the source and target currencies by pressing the middle button</li>
               <li>Change the source and target currencies and amount.</li>
               <li>When you are happy, click purchase to complete a transaction</li>
               <li>Perform another transaction</li>
             </ul>
           </div>
           <div class="row">
             <div  class="form-inline">

               <div class="form-group">
                 <div class="input-group">
                   <div class="input-group-btn">
                     <button id="source_currency" type="button" class="btn btn-default dropdown-toggle" data-value="USD" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">USD <span class="caret"></span></button>
                     <ul class="dropdown-menu">
                       <li><a href="#">USD</a></li>
                       <li role="separator" class="divider"></li>
                       <li><a href="#">ZAR</a></li>
                       <li><a href="#">GBP</a></li>
                       <li><a href="#">EUR</a></li>
                       <li><a href="#">KES</a></li>
                     </ul>
                   </div><!-- /btn-group -->
                   <input type="text" class="form-control" aria-label="..." placeholder="Amount" id="amount" name="amount">
                 </div><!-- /input-group -->
               </div>
               <button type="button" class="btn btn-default" id="swop" name="swop">
                 <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                 <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
               </button>
               <div class="form-group">
                 <div class="input-group">
                   <div class="input-group-btn">
                     <button id="target_currency" type="button" class="btn btn-default dropdown-toggle" data-value="ZAR" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">ZAR <span class="caret"></span></button>
                     <ul class="dropdown-menu">
                       <li><a href="#">USD</a></li>
                       <li role="separator" class="divider"></li>
                       <li><a href="#">ZAR</a></li>
                       <li><a href="#">GBP</a></li>
                       <li><a href="#">EUR</a></li>
                       <li><a href="#">KES</a></li>
                     </ul>
                   </div><!-- /btn-group -->
                   <input type="text" class="form-control" aria-label="..." placeholder="Total" id="total" name="total" readonly>
                 </div><!-- /input-group -->
               </div>
               <button type="submit" class="btn btn-primary" id="purchase" name="purchase">Purchase</button>
             </div>
           </div><!-- /.row -->
           <div id="message">&nbsp</div>
           <div class="row demo">
             <h4>For demonstration purposes:</h4>
             <h5>Last Ajax results:</h5>
             <p id="ajax">&nbsp</p>
             <h5>Purchases:</h5>
             <table class="table table-striped">
               <thead>
                 <tr>
                   <th>Reference</th>
                   <th>Foreign currency purchased</th>
                   <th>Exchange rate for foreign currency</th>
                   <th>Surcharge percentage</th>
                   <th>Amount of foreign currency purchased</th>
                   <th>Amount to be paid in USD</th>
                   <th>Amount of surcharge</th>
                   <th>Date created</th>
                   <th>Discount</th>
                 </tr>
               </thead>
               <tbody>
                 <? if ($purchases) {
                   foreach ($purchases as $purchase){ ?>
                 <tr>
                   <td><? echo $purchase['id']; ?></td>
                   <td><? echo $purchase['currency']; ?></td>
                   <td><? echo $purchase['exchange_rate']; ?></td>
                   <td><? echo $purchase['surcharge_percent']; ?></td>
                   <td><? echo $purchase['currency_amount']; ?></td>
                   <td><? echo $purchase['usd_amount']; ?></td>
                   <td><? echo $purchase['surcharge_amount']; ?></td>
                   <td><? echo $purchase['timestamp']; ?></td>
                   <td><? echo $purchase['discount_amount']; ?></td>
                 </tr>
                 <? }} ?>
               </tbody>
             </table>
           </div>

         </div><!-- /.container -->

         <!-- Jquery -->
         <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>

         <!-- Latest compiled and minified JavaScript -->
         <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

         <!-- Page scripts -->
         <script>
            var csrf = '<? echo $_SESSION['csrf_token']; ?>';
         </script>
         <script src="js/main.js"></script>

     </body>
 </html>
