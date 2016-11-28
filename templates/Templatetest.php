<?php
/*
 * Created on 30.01.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

include("OSZIMTSmarty.class.php");

$smarty = new OSZIMTSmarty();
$smarty->compile_check = true;
$smarty->debugging = true;

/* ---- Ab hier spezifischer Quellcode */

$smarty->display('Templatetest.tpl');

// Datenbank schließen
$smarty->schliesseDatenbank(); 
 
?>
