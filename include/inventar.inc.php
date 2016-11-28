<?php

// Berechtigung für Inventar
function user_berechtigt($bearbeiter)
{
  if ( $bearbeiter == "" ) return true;
  if ( $_SERVER["REMOTE_USER"] != "techniker" && 
       $_SERVER["REMOTE_USER"] != "Eiben" &&
       $_SERVER["REMOTE_USER"] != "Griep" && 
       $_SERVER["REMOTE_USER"] != "Ehlert" &&
       $_SERVER["REMOTE_USER"] != "kaemmler" &&
       $_SERVER["REMOTE_USER"] != "Seidel" &&
       $_SERVER["REMOTE_USER"] != $bearbeiter )
    return false;
  else
    return true;
}

?>
