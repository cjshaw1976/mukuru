<?php

// Enter your MySQL credentials
$server = "localhost";
$username = "root";
$password = "";
$database = "";

$apikey = 'cedba1f7e1e924185afb10443eb2b06b';

// If the server does not support the function hash_equals for passwords
if(!function_exists('hash_equals')) {
  function hash_equals($str1, $str2) {
    if(strlen($str1) != strlen($str2)) {
      return false;
    } else {
      $res = $str1 ^ $str2;
      $ret = 0;
      for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
      return !$ret;
    }
  }
}
?>
