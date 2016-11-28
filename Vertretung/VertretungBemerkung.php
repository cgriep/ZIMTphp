<?php
/*
 * VertretungsBemerkung.php
 * Erlaubt die Eingabe einer Bemerkung zu einer Vertretung.
 * Das Skript wird als Popup vom Stundenplanfenster aufgerufen. Nach dem Speichern 
 * aktualisiert es das parent-Fenster und schließt sich per JavaScript.
 *
 * Parameter:
 * Vertretung_id - kommaseparierte Liste mit den IDs der Vertretung, zu der eine 
 *                 Bemerkung eingegeben werden soll.
 *                 Wenn vorhanden, wird pro Vertretung ein Eingabefeld angezeigt.
 *                 ist die id -1, so soll eine neue Bemerkung zu einer 
 *                 unbetroffenen Stunde hinzugefügt werden. In diesem Fall
 *                 wird eine neue Vertretung erzeugt. Es muss zusätzlich der 
 *                 Parameter VertretungInfo übergeben werden
 * Bemerkung[] - ein Feld, dessen Indizes die IDs der zugehörigen Vertretung 
 *               sind. Wenn vorhanden, werden die Bemerkungen der entsprechenden
 *               Vertretungen mit den neuen Bemerkungen überschrieben.
 *               Danach wird die Speicherung bestätigt und die Möglichkeit zum 
 *               Schließen des Fensters gegeben.
 * VertretungInfo - nur notwendig, wenn Vertretung_id = -1 ist. Enthält eine 
 *                  kommaseparierte Liste mit folgenden Informationen über die 
 *                  Stunde, die eine Bemerkung bekommen soll:
 *                  Datum (timestamp), Stunde, Lehrer 
 *  
 * Letzte Änderung:
 * 06.01.06 C. Griep
 * 25.02.06 C. Griep: Bemerkung für nicht vorhandene Vertretungen eingeben
 * 
 */
$Ueberschrift = 'Bemerkung zu Vertretung hinzufügen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';

include('include/header.inc.php');
include('include/helper.inc.php');
include('include/Vertretungen.inc.php');
// Skript zum Schließen und Aktualisieren des Parent-Fensters
echo '<script language="javascript">
    function Uebertragen()
    {
      opener.location.search = ""; 
      this.close();
    }
    </script><noscript>Sie haben JavaScript deaktiviert. Bitte schließen Sie das
    Fenster.</noscript>';

echo "<tr><td>\n";

if ( isset($_REQUEST['Bemerkung']) && is_array($_REQUEST['Bemerkung']) )
{
  // speichern
  foreach ( $_REQUEST['Bemerkung'] as $key => $bemerkung )
  {
    if ( is_numeric($key) && $key < 0 && isset($_REQUEST['VertretungInfo']))
    {
    	// Bemerkung zu einer unbetroffenen Stunde eingeben
    	$eintraege = explode(',',$_REQUEST['VertretungInfo']);
    	if ( Count($eintraege) != 3 )
    	  dieMsg('Falsche Vertretungsinformationen übergeben!');
    	$nids = trageVertretungEin('Lehrer', $eintraege[2], 
    	                   $eintraege[0], $eintraege[1],
                           $eintraege[2], 0, '', $_SESSION['Verhinderung_id'],
                           VERTRETUNG_NURBEMERKUNG);
        $key = reset($nids);
    }       
    if ( is_numeric($key) && $key > 0 )
    {
      $bemerkung = mysql_real_escape_string($bemerkung);
      if ( ! mysql_query("UPDATE T_Vertretungen SET Bemerkung='$bemerkung' " .
      		"WHERE Vertretung_id=$key"))
        echo mysql_error();
    }
    else
      dieMsg("Key $key ist nicht numerisch!");
    echo '<div class="Hinweis">Die Bemerkungen wurden gespeichert.
    <a href="javascript:Uebertragen();">Zurück zum Plan</a></div>';
  }
}
elseif ( ! isset($_REQUEST['Vertretung_id']) )
{
  dieMsg('Keine Vertretung-Id übergeben!');
}
else
{
  $ids = explode(',',$_REQUEST['Vertretung_id']);
  $zusatz = false;
  foreach ( $ids as $key => $id )
  {
    if ( !is_numeric($id) )
    {
        dieMsg('Keine gültige Vertretung-Id übergeben!');
    }
    if ( $id  < 0 && isset($_REQUEST['VertretungInfo']))
    {
    	$zusatz = true;
    }
  }
  $Felder = array('Klasse','Fach','Lehrer','Raum');
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
  $eintraege = array();
  if ( isset($_REQUEST['VertretungInfo']))
  {
  	// Die passenden Einträge heraussuchen
  	$dereintrag = explode(',',$_REQUEST['VertretungInfo']);
 	// Bemerkung zu einer unbetroffenen Stunde eingeben
  	if ( Count($dereintrag) != 3 )
   	  dieMsg('Falsche Vertretungsinformationen übergeben!');
   	$eintraege = liesStundenplanEintrag('Lehrer', $dereintrag[2], 
   	    $dereintrag[0], $dereintrag[1]);
   	foreach ( $eintraege as $key => $value )
   	{
   	  $eintraege[$key]['Vertretung_id'] = -1;
      $eintraege[$key]['Datum'] = $dereintrag[0];
      $vertretung[$key]['Bemerkung'] = '';
      foreach ( $Felder as $Feld )
        $eintraege[$key][$Feld.'_Neu'] = $eintraege[$key][$Feld];         	  
   	} 
   	/*
   	$nids = trageVertretungEin('Lehrer', $eintraege[2], 
    	                   $eintraege[0], $eintraege[1],
                           $eintraege[2], 0, '', $_SESSION['Verhinderung_id'],
                           VERTRETUNG_NURBEMERKUNG);
    */
  }
  else
  {
    $_REQUEST['Vertretung_id'] = implode(',',$ids);   
    $query = mysql_query('SELECT * FROM T_Vertretungen WHERE Vertretung_id IN ('.
      $_REQUEST['Vertretung_id'].')');
    while ( $vertretung = mysql_fetch_array($query) )
      $eintraege[] = $vertretung;
    mysql_free_result($query);
  }
  foreach ( $eintraege as $vertretung)
  {
    echo '<h2>Vertretung am ';
    echo date('d.m.Y',$vertretung['Datum']).' im '.$vertretung['Stunde'].". Block</h2>\n";
    echo '<table>';
    foreach ($Felder as $Feld )
    {
      if ( $vertretung[$Feld] != '' || $vertretung[$Feld.'_Neu'] != '' )
      {
        echo "<tr><td>$Feld: </td><td>";
        if ( $vertretung[$Feld] != $vertretung[$Feld.'_Neu'] )
        {
          echo $vertretung[$Feld].' &rarr ';
          if ( $vertretung[$Feld.'_Neu'] != '' ) 
            echo $vertretung[$Feld.'_Neu'];
          else
            echo '(entfällt)';
        }
        else
          echo $vertretung[$Feld.'_Neu'];
       echo "</td></tr>\n";
      }
    }
    echo "</table>\n";
    echo "Bemerkung<br />\n";
    echo '<textarea name="Bemerkung['.$vertretung['Vertretung_id'].
      ']" cols="60" rows="5">';
    echo $vertretung['Bemerkung'];
    echo "</textarea>\n";
    if ( isset($_REQUEST['VertretungInfo']))
      echo '<input type="hidden" name="VertretungInfo" value="'.
        $_REQUEST['VertretungInfo'].'"/>';
    echo '<input type="Submit" value="Speichern" />';
    echo "<hr />\n";
  }
  echo "</form>\n";
  
  echo '<a href="javascript:window.close();">Fenster ohne Speichern schließen</a>';
}

echo '</td></tr>';
include('include/footer.inc.php');

?>