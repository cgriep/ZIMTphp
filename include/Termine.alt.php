<?php

function holeKlassifikation($db)
{
  $Klassifikation = array();
  $query = mysql_query("SELECT * FROM T_Klassifikationen ORDER BY Klassifikation", $db);
  while ( $row = mysql_fetch_array($query) )
    $Klassifikation[$row["Klassifikation_id"]] = $row["Klassifikation"];
  mysql_free_result($query);
  return $Klassifikation;
}

function holeBetroffen($db, $Alle)
{
  $Betroffen = array();
  $query = mysql_query("SELECT * FROM T_Betroffene ORDER BY Betroffen", $db);
  $Alle = array();
  while ( $row = mysql_fetch_array($query) )
  {
    if ( trim($row["Berechtigte"]) == "" ||
         in_array($_SERVER["REMOTE_USER"], explode(",",$row["Berechtigte"])) )
    {
      $Betroffen[$row["Betroffen_id"]] = $row["Betroffen"];
      $Alle[] = $row["Betroffen_id"];
    }
  }
  mysql_free_result($query);
  return $Betroffen;
}

function sichereFilter($db, $Alle)
{
 // Filter speichern
 if ( isset($_REQUEST["FilterAlle"]) )
 {
   $_REQUEST["Filter"] = $Alle;
 }
 if ( isset($_REQUEST["Filter"]) )
 {
   $Filter = implode(",",$_REQUEST["Filter"]);
   if ( ! mysql_query("UPDATE T_UserTerminFilter SET Filter='".$Filter."' WHERE User='".
     $_SERVER["REMOTE_USER"]."'",$db)) echo "U:".mysql_error();
   if ( mysql_affected_rows($db) == 0 )
     if ( ! mysql_query("INSERT INTO T_UserTerminFilter (User, Filter) VALUES ('".
       $_SERVER["REMOTE_USER"]."','".$Filter."')",$db)) echo "I:".mysql_error($db);
 }
 // Filter laden
 $Filter = $Alle;
 if ( ! $query = mysql_query("SELECT Filter FROM T_UserTerminFilter WHERE User='".
   $_SERVER["REMOTE_USER"]."'", $db)) echo mysql_error($db);
 if ( $row = mysql_fetch_array($query) )
   $Filter = explode(",",$row[0]);
 mysql_free_result($query);
 foreach ( $Filter as $key=>$value )
   if ( ! isset($Alle[$key]) ) unset($Filter[$key]);
 unset($_REQUEST["Filter"]);
 return $Filter;
}

function InhaltAnfuegen($Inhalt, $Wert, $Uhrzeit = "")
{
  if ( $Uhrzeit != "" )
  {
    $Uhrzeit = date("H:i",$Uhrzeit);
    if ( $Uhrzeit != "00:00" )
      $Wert .= ' <span class="Uhrzeit">('.$Uhrzeit.")</span>";
  }
  if ( trim($Inhalt) == "" )
    return $Wert;
  else
    return $Inhalt."<br />".$Wert;
}

function holeFilternamen($Filter)
{
   global $Betroffen;
   reset($Filter);
   $b = array();
   while (list($key, $value) = each($Filter) )
     $b[] = $Betroffen[$value];
   return implode(",",$b);
}
function BetroffeneAnzeigen($Betroffene)
{
   global $Betroffen;
   $x = "";
   $a = explode(",",$Betroffene);
   while ( list($key, $value) = each($a) )
     $x .= ",".$Betroffen[$value];
   if ( strlen($x) > 0 ) $x = '<br /><span class=content-small-bold>'.substr($x, 1)."</span>";
   return $x;
}

function holeTermineAlsHTML($anzahl = 10)
{
  global $Klassifikation;
  global $Filter;
  global $Alle;
  $query = mysql_query("SELECT DATE_FORMAT(MAX(Stand),'%d.%m.%Y %H:%i') FROM T_Termine WHERE NOT Vorlaeufig");
  $stand = mysql_fetch_row($query);
  mysql_free_result($query);
  $s = '<h1 style="margin:0;padding:0;">nächste Termine</h1><span class="small">(Stand: '.$stand[0];
  if ( $Filter != $Alle ) $s .= " <strong>gefiltert</strong>";
  $s .= ')<br />';
  $s .= '<div class="small">Bewegen Sie die Maus auf einen Termin um näheres zu Erfahren!</div><br />';
  if ( ! $query = mysql_query("SELECT *,DATE_FORMAT(Stand,'%d.%m.%Y %H:%i') AS St ".
         "FROM T_Termine WHERE NOT Vorlaeufig AND Datum >= ".mktime(0,0,0,date("m"),date("d"),
           date("y"))." ORDER BY Datum")) echo mysql_error();
  $anz = 0;
  while ( ($termin = mysql_fetch_array($query)) && $anz < 10)
  {
    $art = explode(",",$termin["Betroffene"]);
    if ( Count(array_intersect($art, $Filter)) > 0 )
    {
      $s .= '<span onMouseOver="return overlib('."'";
      $s .= htmlentities(stripslashes(str_replace("\n","",
             str_replace("\r","",nl2br($termin["Beschreibung"])))));
      $s .= BetroffeneAnzeigen($termin["Betroffene"]);
      $s .= '<br /><span class=Termininfo>'.$termin["Bearbeiter"].' / Stand: ';
      $s .= $termin["St"]."</span>";
      $s .= "',CAPTION,'".$termin["Bezeichnung"]." (".
            $Klassifikation[$termin["F_Klassifikation"]].")');";
      $s .= '" onMouseOut="return nd();">';

      $s .= "<em>".date("d.m.Y", $termin["Datum"]);
      $Uhrzeit = date("H:i",$termin["Datum"]);
      if ( $Uhrzeit != "00:00" )
        $s .= ' '.$Uhrzeit;
      $s .= "</em><br />";
      $s .= stripslashes($termin["Bezeichnung"])." (".
        str_replace("<br />","",BetroffeneAnzeigen($termin["Betroffene"])).")</span><br /><br />";
      $anz++;
    }
  }
  mysql_free_result($query);
  $s .= '<a href="/Termin/TermineWoche.php">';
  $s .= '<img src="http://img.oszimt.de/nav/link.gif" width="25" height="13" border="0">';
  $s .= '</a> ';
  $s .= '<a href="/Termin/TermineWoche.php">Wochenübersicht</a></span><br />';

  $s .= '<a href="/Termin/Termine.php">';
  $s .= '<img src="http://img.oszimt.de/nav/link.gif" width="25" height="13" border="0">';
  $s .= '</a> ';
  $s .= '<a href="/Termin/Termine.php">konfigurieren</a></span><br />';


  return $s;
}

function CheckScriptEinfuegen()
{
?>
<script type="text/javascript">
<!--
function CheckAll(wert)
{
  for (var i=0;i<document.Formular.elements.length;i++)
  {
    var e = document.Formular.elements[i];
    if (e.name == "Filter[]" )
      if ( wert == -1 )
        e.checked = ! e.checked;
      else
        e.checked = wert;
  }
}
//-->
</script>
<?php
}

function vorlaeufigPerson()
{
  switch ( strtoupper($_SERVER["REMOTE_USER"]) )
  {
         case 'ANSORGE':
         case 'HOPP':
         case 'SCHOLL':
         case 'WOFFLEBEN':
         case 'KRUEGER':
         case 'KOEPF':
         case 'PUNKE':
         case 'GUENTHER':
         case 'BROESEMANN':
         case 'SEIDEL':
         case 'GRIEP':
           return true;
           break;
         default:
           return false;
  }
}

?>