<?php

  $dbName = "oszimt";
  $dbUser = "oszintern";
  $dbPassword = "qBEj8h";

  // Datenbank �ffnen
  $db = mysql_connect("localhost", $dbUser, $dbPassword);
  mysql_select_db($dbName, $db);

?>
