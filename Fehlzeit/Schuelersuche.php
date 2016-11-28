<?php
/*
 * Schulersuche.php
 * Sucht nach einem Schüler und zeigt an wo er sein sollte
 * (c) Christoph Griep 13.02.07
 */
 
/* Weiterleitung wenn es sich um normale Pläne handelt */
if ( ! isset($_REQUEST['Schueler']) && isset($_REQUEST['ID_Woche']))
{
	$s = array();
	foreach ( $_REQUEST as $key => $value)
	  $s[] = $key.'='.$value;
	header('Location: https://' . $_SERVER['HTTP_HOST'] . '/StuPla/PlanAnzeigen.php?'.implode('&',$s));	
}
else
{
 $Ueberschrift = 'Suche nach einem Schüler';
 $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="https://lehrer.oszimt.de/css/Vertretung.css">
<link rel="stylesheet" type="text/css" href="https://lehrer.oszimt.de/css/stupla.css">
<link rel="stylesheet" type="text/css" href="https://lehrer.oszimt.de/css/oszframe.css">';
 include('include/header.inc.php');
 include('include/helper.inc.php');
 include('include/turnus.inc.php');
 include('include/stupla.inc.php');
 include('include/Vertretungen.inc.php');
?>
<tr><td>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="Attest" class="Verhinderung">

 <?php
if ( ! $query = mysql_query('SELECT DISTINCT Nr, Name, Vorname, Klasse FROM T_Schueler INNER JOIN T_Kurse '.
     "ON T_Schueler.Nr = T_Kurse.Schueler_id WHERE Klasse LIKE 'OG _' " .
     ' ORDER BY Name, Vorname', $db))
       echo mysql_error($db);
 echo 'Schüler/in <select name="Schueler">';
while ($s = mysql_fetch_array($query) )
{
  echo '<option value="'.$s['Nr'].'" ';
  $name = $s['Vorname'].' '.$s['Name'].' ('.$s['Klasse'].')';
  if ( isset($_REQUEST['Schueler']) && $_REQUEST['Schueler']==$name)
    $_REQUEST['Schueler'] = $s['Nr'];    
  if ( isset($_REQUEST['Schueler']) && $s['Nr'] == $_REQUEST['Schueler']) 
    echo 'selected="selected"';
  echo '>'.$s['Name'].', '.$s['Vorname'].' ('.$s['Klasse'].")</option>\n";
  
}
mysql_free_result($query);
echo "</select> \n";
echo '<span class="small">In der Liste Anfangsbuchstaben eingeben, um hinzuspringen</span>';
echo '<br />';
?>
<input type="Submit" value="anzeigen" /><br />
</form>

</td></tr>
<?php

if ( isset($_REQUEST['Schueler']) && is_numeric($_REQUEST['Schueler']) )
{
  // Feststellen in welchen Kursen der Schüler ist
  $query = mysql_query('SELECT * FROM T_Kurse WHERE Schueler_id='.
    $_REQUEST['Schueler'].' AND Schuljahr="'.Schuljahr(false).'"');	
  $Kurse = array();
  while ( $kurs = mysql_fetch_array($query))
  {
  	$Kurse[] = $kurs['Kurs'];
  }
  mysql_free_result($query);
  // Klasse herausfinden
  $query = mysql_query('SELECT * FROM T_Schueler WHERE Nr='.$_REQUEST['Schueler']);
  $schueler = mysql_fetch_array($query);
  mysql_free_result($query);
  // Nun Stundenplan mit den gewählten Kursen aufbauen
  if ( isset($_REQUEST['ID_Woche']) && is_numeric($_REQUEST['ID_Woche']))
    $ID_Woche = $_REQUEST['ID_Woche'];
  else
    $ID_Woche = getID_Woche();
  $Plan = liesPlanEin($db, 'Klasse', $schueler['Klasse'], $ID_Woche,true,$Kurse);  
  // Achtung, nicht ändern oder oben ebenfalls anpassen!
  $Plan['Anzeige'] = $schueler['Vorname'].' '.$schueler['Name'].' ('.$schueler['Klasse'].')';
  $Plan['Wofuer'] = 'Schueler';
  echo '<tr><td>';
  schreibePlan($Plan, $db);
  echo '</td></tr>'; 
}
?>

<?php
 include('include/footer.inc.php');
}
?>