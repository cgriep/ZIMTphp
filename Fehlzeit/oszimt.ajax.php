<?php
require_once ("xajax/xajax.inc.php");

$xajax = new xajax("oszimt.server.php");
$xajax->registerFunction("saveFehlzeit");
$xajax->registerFunction("saveAusfall");

?>