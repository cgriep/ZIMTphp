<?php

function Schuljahr($kurz = true, $Datumzahl = 0)
{
  if ( $Datumzahl == 0 ) $Datumzahl = time();
  if ( $kurz )
  {
    // Kurzform: yy/YY
    $Schuljahr = date("y");
    if ( date("m", $Datumzahl) < 8 )
      $Schuljahr = sprintf("%02d",$Schuljahr-1)."/".$Schuljahr;
    else
      $Schuljahr = $Schuljahr."/".sprintf("%02d",$Schuljahr+1);
  }
  else
  {
    // Langform: yyyy/YY II
    if ( date("m",$Datumzahl) < 8 )
      if ( date("m",$Datumzahl) == 1 )
        $Schuljahr = "I ".(date("Y",$Datumzahl)-1)."/".sprintf("%02d",date("y",$Datumzahl));
      else
        $Schuljahr = "II ".date("Y",$Datumzahl);
    else
      $Schuljahr = "I ".date("Y",$Datumzahl)."/".sprintf("%02d",date("y",$Datumzahl)+1);
  }
  return $Schuljahr;
}

function sindFerien($Datum, $StuPlaDB)
{
  // Ferien prüfen
  $sql = "SELECT * FROM T_FreiTage WHERE ersterTag <= $Datum AND letzterTag >= $Datum";
  if ( ! $freiquery = mysql_query($sql, $StuPlaDB) ) echo "Fehler SF $sql: ".mysql_error();
  $erg = false;
  if ( $ferientag = mysql_fetch_array($freiquery) )
  {
    // es sind Ferien!
    $erg = true;
  }
  //echo "Ferien ".date("d.m.Y",$Datum).": ".$erg."<br />";
  mysql_free_result($freiquery);
  return $erg;
}

function TurnusAktuell($Turnus, $Datum, $StuPlaDB)
{
  if ( $Turnus != "a" && $Turnus != "b" ) return true;
  $Schuljahr = Schuljahr();
  $query =  mysql_query("SELECT ID_Turnus FROM T_Turnus WHERE Turnus='$Turnus' AND SJahr='$Schuljahr'",$StuPlaDB);
  $erg = mysql_fetch_array($query);
  $TurnusNr = $erg[0];
  if ( ! is_numeric($TurnusNr) ) $TurnusNr = 0;
  $sql = "SELECT ID_Woche FROM T_Woche WHERE Montag <= $Datum ORDER BY Montag DESC";
  if ( ! $query = mysql_query($sql, $StuPlaDB)) echo mysql_error($StuPlaDB);
  if ( mysql_num_rows($query) > 0 )
    $Woche = mysql_fetch_row($query);
  else
    $Woche[0] = 0;
  mysql_free_result($query);
  if ( ! $query = mysql_query("SELECT F_ID_Turnus FROM T_WocheTurnus WHERE F_ID_Woche = $Woche[0] AND F_ID_Turnus = ".
    $TurnusNr, $StuPlaDB))
  {
    $erg = false;
    echo mysql_error($StuPlaDB);
  }
  else
  {
    if ( mysql_num_rows($query) == 1 )
      $erg = true;
    else
      $erg = false;
    mysql_free_result($query);
  }
  return $erg;
}

?>