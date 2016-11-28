<?php
/**
 * (c) 2006 Christoph Griep
 * Hilfsfunktionen f�r Inventarisierungssystem
 */
// Berechtigung f�r Inventar
function user_berechtigt($bearbeiter)
{
  if ( $_SERVER['REMOTE_USER'] != 'techniker' && $_SERVER['REMOTE_USER'] != 'Eiben' &&
       $_SERVER['REMOTE_USER'] != 'Seidel' &&
       $_SERVER['REMOTE_USER'] != 'Griep' && $_SERVER['REMOTE_USER'] != $bearbeiter )
    return false;
  else
    return true;
}

/**
 * Pr�ft ob eine Inventarnummer bereits vergeben ist. Wird eine Inventar_id 
 * �bergeben, so wird dieses Inventar �bergangen (f�r die Pr�fung ob es ein
 * anderes Inventar mit dieser Nummer gibt).
 * @param $inventarnr Die Inventarnummer
 * @param $inventar_id Die id, die nicht ber�cksichtigt werden soll
 * @return true, wenn die Inventarnummer schon vergeben ist, false sonst
 */
function istInventarNrVergeben($inventarnr, $inventar_id = -1)
{
	$ergebnis = false;
	if ( $inventarnr != '' )
	{
	  $sql = 'SELECT Inventar_id FROM T_Inventar WHERE Inventar_Nr="'.
        mysql_real_escape_string($inventarnr).'"';
      if ( is_numeric($inventar_id) && $inventar_id > 0 )
	  $sql .= ' AND Inventar_id != '.$inventar_id;  
	  $query = mysql_query($sql);
	  if ( $inventar = mysql_fetch_array($query))
	    $ergebnis = true;
	  mysql_free_result($query);	  
	}
	return $ergebnis;
}

?>