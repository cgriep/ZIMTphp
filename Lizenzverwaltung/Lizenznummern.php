<?php
/**
 * Zeigt die Lizenznummern an.
 * Wrapper für die PDF-Version der Anzeige
 * (c) 2006 Christoph Griep
 */
  include("LizenznummernPDF.php");
  if ( ! isset($_REQUEST["VN"])) {
    die("Es muss eine Vertragsnummer angegeben werden!");
  }
  $buf = LizenznummernPDF($_REQUEST["VN"]);
  $len = strlen($buf);
  header("Content-type: application/pdf");
  header("Content-Length: $len");
  header("Content-Disposition: inline; filename=Lizenz".$_REQUEST["VN"].".pdf");
  print $buf;

?>