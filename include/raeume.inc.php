<?php

/* Räume */

define("ART_ID_COMPUTER", 1); // Art_id für Computer
define("ART_ID_BEAMER", 9); // Art_id für Computer
/**
  *  Bestimmt die Anzahl der Computer in einem Raum
  *
  */
function ComputerAnzahl($Raumnummer, $db=-1)
{
  if (! $query = mysql_query("SELECT Count(Bezeichnung) FROM T_Inventar, T_Raeume".
    " WHERE F_Art_id=".ART_ID_COMPUTER." AND F_Raum_id=Raum_id AND Raumnummer='".
    mysql_real_escape_string($Raumnummer)."'")) echo mysql_error();
  $row = mysql_fetch_row($query);
  mysql_free_result($query);
  if ( is_numeric($row[0]) )
    return $row[0];
  else
    return 0;
}

/**
  *  Bestimmt die Anzahl der Computer in einem Raum
  *
  */
function BeamerAnzahl($Raumnummer, $db=-1)
{
  if (! $query = mysql_query("SELECT Count(Bezeichnung) FROM T_Inventar, T_Raeume".
    " WHERE F_Art_id=".ART_ID_BEAMER." AND F_Raum_id=Raum_id AND Raumnummer='".
    mysql_real_escape_string($Raumnummer)."'")) echo mysql_error();
  $row = mysql_fetch_row($query);
  mysql_free_result($query);
  if ( is_numeric($row[0]) )
    return $row[0];
  else
    return 0;
}

/**
 * Macht aus der Kurzschreibweise von Winschool die Langschreibweise wo nach dem 
 * Bereich ein Punkt folgt.
 */
function RaumnummerMitPunkt($Raumnummer)
{
  if ( is_numeric(substr($Raumnummer,0,1)) && substr($Raumnummer,1,1) != "." )
    $Raumnummer = substr($Raumnummer,0,1).".".substr($Raumnummer,1);
  return $Raumnummer;
}
/**
 * Macht aus der Langschreibweise mit Punkt die Kurzschreibweise von Winschool (maximal 5 Zeichen).
 * @param string $Raumnummer 
 * @return string Winschool-Raumnummer
 */
function RaumnummerOhnePunkt($Raumnummer)
{
  if (strlen($Raumnummer) > 5)
  {
  return str_replace('.', '', $Raumnummer);
  }
  else
  return $Raumnummer;
}



function RaumBeschreibungLang($Raumnummer, $mitBeschreibung = false)
{
  $Raum = RaumnummerMitPunkt($Raumnummer);
  $sql = "SELECT Beschreibung, Raumbezeichnung, Kapazitaet FROM T_Raeume WHERE Raumnummer='".$Raum."'";
  if (!$query = mysql_query($sql))
    echo mysql_error();
  if ( !$r = mysql_fetch_array($query) )
  {    
    $erg = '';
  }
  else
  {
    $erg = stripslashes($r['Raumbezeichnung']) . ' (' . $r['Kapazitaet'] . ' Schülerplätze';
    $canz = ComputerAnzahl($Raum);
    if ( $canz != 0 )
    {
      $erg .= " / $canz Computer";
      $canz = BeamerAnzahl($Raum);
      if ( $canz != 0 )
        $erg .= ", Beamer";      
    }
    $erg .= ')';	
    if ( $mitBeschreibung )
      $erg .= '<br />'.nl2br($r['Beschreibung']);
  }
  mysql_free_result($query);
  return $erg;	
  
}


function RaumBeschreibungKurz($Raumnummer)
{
  $Raum = RaumnummerMitPunkt($Raumnummer);
  $sql = "SELECT Raumbezeichnung, Kapazitaet FROM T_Raeume WHERE Raumnummer='".$Raum."'";
  if (!$query = mysql_query($sql))
    echo mysql_error();
  if ( !$r = mysql_fetch_array($query) )
    $erg = '';
  else
  {
    $raumbez =  stripslashes($r['Raumbezeichnung']);
    $maxlen = 15;
    
    if(strlen($raumbez) >$maxlen)
      $raumbez = substr($raumbez,0,$maxlen) . '.';
      
    $erg = stripslashes($raumbez) . ' (' . $r['Kapazitaet'] . ' Pl.';
    $canz = ComputerAnzahl($Raum);
    if ( $canz != 0 )
    {
      $erg .= " / $canz PC";
      $canz = BeamerAnzahl($Raum);
      if ( $canz != 0 )
        $erg .= ", Beam.";
    }
    $erg .= ')';
  }
  mysql_free_result($query);
  return $erg;
}

// Ergänzt bei 5-Stelligen Raumbezeichnungen aus WinSchool den Punkt
// Notwendig, da WinSchool nur 5 Ziffern erlaubt und daher manchmal der
// Punkt weggelassen wird.
// Im Inventarsystem sind die Punkte aber vorhanden.
function Raumbezeichnung($Raum)
{
  if ( is_numeric(substr($Raum,0,1)) && substr($Raum,1,1) != "." )
    return substr($Raum,0,1).".".substr($Raum,1);
  else
    return $Raum;
}
?>