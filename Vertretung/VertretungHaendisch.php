<?php
/*
 * VertretungHaendisch.php
 * Erlaubt die manuelle Eingabe einer Vertretung.
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
 *                 Parameter VonStunde übergeben werden
 * NeuLehrer/
 * NeuKlasse/
 * NeuFach/
 * NeuRaum       - Die Daten für die neuen Vertretungsinformationen 
 * VonStunde     - die Stunde, für die eine Vertretung eingerichtet werden soll. 
 *                 Lehrer und Datum wird aus der Session entnommen.

 * AndererLehrer - Schaltet die Anzeige auf einen anderen Lehrer um 
 *                  
 *  
 * Letzte Änderung:
 * 25.02.06 C. Griep: neu erstellt
 * 
 */
$Ueberschrift = "Vertretung manuell bearbeiten";
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';

include("include/header.inc.php");
include("include/helper.inc.php");
include("include/Vertretungen.inc.php");
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

$Felder = array('Klasse','Fach','Lehrer','Raum');

if ( isset($_REQUEST['AndererLehrer']))
{
	$_SESSION['DerEintrag'] = $_REQUEST['AndererLehrer'];
	$_SESSION['DieArt'] = 'Lehrer';
	if ( !isset($_REQUEST['Vertretung_id']))
	  $_REQUEST['Vertretung_id'] = -1;
}
elseif ( !session_is_registered('DerEintrag'))
{
  $_SESSION['DerEintrag'] = $_SESSION['Wofuer'];
  $_SESSION['DieArt'] = $_SESSION['Art'];
  if ( $_SESSION['Art'] == 'Lehrer')
    $_SESSION['Abhaengig'] = 'Klasse';
  elseif ( $_SESSION['Art'] == 'Klasse')
    $_SESSION['Abhaengig'] = 'Lehrer';
}  
if ( isset($_REQUEST['Bemerkung']) && is_array($_REQUEST['Bemerkung']) )
{
  // speichern
  // Mal sehen wo wir was eintragen müssen
  
  foreach ( $_REQUEST['Bemerkung'] as $key => $bemerkung )
  {
    $bemerkung = mysql_real_escape_string($bemerkung);    
    $neu = array();
    foreach ( $Felder as $Feld)
      if ( isset($_REQUEST["Neu$Feld"][$key]))
        $neu[$Feld] = $_REQUEST["Neu$Feld"][$key];
    if ( (!is_numeric($key) || $key < 0 ) && isset($_REQUEST['VonStunde']))
    {
    	// Feststellen, was eingetragen werden muss
    	if ( is_numeric($key) )
    	{
    	  $was[0] = $_SESSION['Abhaengig'];
    	  $was[1] = $neu[$was[0]];
    	  // ausgeblendeten Wert sichern
    	  $neu[$_SESSION['DieArt']] = $_SESSION['DerEintrag'];
    	}
    	else
    	{ 
    	  $was = explode(':', $key);
    	}    	
    	// Änderung zu einer unbetroffenen Stunde eingeben
        // wenn Lehrer unterschiedlich, muss die Klasse beibehalten werden
    	if ($_SESSION['DieArt'] == 'Lehrer' )
    	{
    		// Wenn Lehrer unterschiedlich => Klasse muss gleich sein
    		// wenn neuer Eintrag, ignorieren!
    		if ( $neu['Lehrer'] <> $_SESSION['DerEintrag'])
    		  unset($neu['Klasse']);
    	}        
    	unset($neu[$_SESSION['Abhaengig']]);    
    	$neu[$was[0]] = $was[1];
    	$nids = trageVertretungEin($was[0], $was[1], 
    	                   $_SESSION['Datum'], $_REQUEST['VonStunde'],
                           $neu, 0, $bemerkung, $_SESSION['Verhinderung_id'],
                           VERTRETUNG_SONDER);
        if ( Count($nids) != 0 )
          $_REQUEST['Vertretung_id'] .= ','.implode(',',$nids);
    }       
    elseif ( is_numeric($key) && $key > 0 )
    {     
      $sql = "UPDATE T_Vertretungen SET Bemerkung='$bemerkung'";
      foreach ( $neu as $Feld => $wert)
        $sql .= ", {$Feld}_Neu='$wert'";
      $sql .= ",Bearbeiter='".$_SERVER['REMOTE_USER']."'";
      $sql .= "WHERE Vertretung_id=$key";
      if ( ! mysql_query($sql))
        echo '<div class="Fehler">Fehler: '.$sql.'/'.mysql_error().'</div>';
      $_REQUEST['Vertretung_id'] .= ','.$key;
    }
    else
      dieMsg("Key $key ist nicht numerisch!");
    
    echo '<div class="Hinweis">Die Daten wurden gespeichert.
    <a href="javascript:Uebertragen();">Zurück zum Plan</a></div>';
  }
  if ( substr($_REQUEST['Vertretung_id'],0,1) == ',' )
      $_REQUEST['Vertretung_id'] = substr($_REQUEST['Vertretung_id'],1);
}
elseif ( ! isset($_REQUEST['Vertretung_id']) )
{
  dieMsg('Keine Vertretung-Id übergeben!');
}
  $ids = explode(',',$_REQUEST['Vertretung_id']);
  $zusatz = false;
  foreach ( $ids as $key => $id )
  {
    if ( !is_numeric($id) )
    {
        dieMsg('Keine gültige Vertretung-Id übergeben!');
    }
    if ( $id  < 0 && isset($_REQUEST['VonStunde']))
    {
    	$zusatz = true;
    }    
  }
  $eintraege = array();
  if ( $zusatz )
  {
  	// Die passenden Einträge heraussuchen
  	// Bemerkung zu einer unbetroffenen Stunde eingeben
  	$eintraege = liesStundenplanEintrag($_SESSION['DieArt'], 
  	    $_SESSION['DerEintrag'],    	
   	    $_SESSION['Datum'], $_REQUEST['VonStunde']);
   	foreach ( $eintraege as $key => $value )
   	{
   	  $eintraege[$key]['Vertretung_id'] = -2;
      $eintraege[$key]['Datum'] = $_SESSION['Datum'];
      $eintraege[$key]['Bemerkung'] = '';
      foreach ( $Felder as $Feld )
        $eintraege[$key][$Feld.'_Neu'] = $eintraege[$key][$Feld];         	  
   	}    	  	
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
  // Immer einen neuen Eintrag anbieten
  $eintrag = array();
  $eintrag['Vertretung_id'] = -1;
  $eintrag['Datum'] = $_SESSION['Datum'];
  $eintrag['Bemerkung'] = '';
  $eintrag['Stunde'] = $_REQUEST['VonStunde'];
  foreach ( $Felder as $Feld )
  {
          $eintrag[$Feld.'_Neu'] = '(frei)';
          $eintrag[$Feld] = '(frei)';
  }
  $eintrag[$_SESSION['DieArt'].'_Neu'] = $_SESSION['DerEintrag'];
  $eintraege[] = $eintrag;
  // Mehrere Einträge vorhanden, vorsicht damit nicht alle verändert werden
  if ( $_SESSION['DieArt'] == 'Lehrer' )
  {
      $NewKey = 'Klasse';
  }
  elseif ( $_SESSION['DieArt'] == 'Klasse' )
  {
      $NewKey ='Lehrer';
  }   
   
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
  foreach ( $eintraege as $vertretung)
  {
    echo '<h2>Vertretung am ';
    echo date('d.m.Y',$vertretung['Datum']).' im '.$vertretung['Stunde'].'. Block</h2>'."\n";
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
        {
          echo $vertretung[$Feld.'_Neu'];         
        }
       echo '</td><td width="10px">&nbsp;</td>';
       // Vermeiden, dass eine Auswahl bei feststehenden Feldern erfolgt
       if ( !($Feld == 'Klasse' && $_SESSION['DieArt'] == 'Klasse') && 
            ($Feld != $_SESSION['DieArt'] || 
            ($vertretung[$_SESSION['Abhaengig']] != '(frei)' 
            && $vertretung[$_SESSION['Abhaengig']] != '')) )       
       {
       	 echo '<td>Änderung in</td><td>'; 	   
         echo '<select name="Neu'.$Feld.'[';
         if ( $vertretung['Vertretung_id'] >= -1 )
           echo $vertretung['Vertretung_id'];
         else
           echo $NewKey.':'.$vertretung[$NewKey];
         echo ']">';
         $Version = getAktuelleVersion($vertretung['Datum']);
         switch ( $Feld ) 
         {
           case 'Klasse':
       	     $sql = "SELECT DISTINCT Klasse FROM T_StuPla WHERE Version=$Version ORDER BY Klasse";
       	     break;
       	   case 'Lehrer':
         	   $sql = "SELECT DISTINCT Lehrer, Name, Vorname FROM T_StuPla " .
         	   		"WHERE Version=$Version ORDER BY Name,Vorname";
       	     break; 
       	   case 'Fach':
         	   $sql = "SELECT DISTINCT Fach FROM T_StuPla WHERE Version=$Version ORDER BY Fach";
       	     break;
       	   case 'Raum':
         	   $sql = "SELECT DISTINCT Raum FROM T_StuPla WHERE Version=$Version ORDER BY Raum";
       	    break;
         }
         $query = mysql_query($sql);
         echo '<option value="" ';
             if ( $vertretung[$Feld.'_Neu'] == '' )
               echo 'selected="selected"';
             echo '>--entfällt--</option>';         	 
         while ( $eintrag = mysql_fetch_array($query))
         {
         	 echo '<option value="'.$eintrag[0].'" ';
             if ( $vertretung[$Feld.'_Neu'] == $eintrag[0])
               echo 'selected="selected"';
             echo '>';
         	 if ( isset($eintrag[1]))
         	   echo $eintrag[1].', '.$eintrag[2];
         	 else
         	   echo $eintrag[0];         	
             echo '</option>'."\n";
         }
         mysql_free_result($query);
         echo '</select></td>';
         echo '<td>';
         // Warnung, wenn Probleme eingestellt sind
         if ( $Feld != 'Fach' && $vertretung[$Feld.'_Neu'] != '')
         {
         	// Prüfe, ob das Feld doppelt belegt ist
         	$seintraege = liesStundenplanEintragMitVertretung($Feld, 
         	  $vertretung[$Feld.'_Neu'],$vertretung['Datum'], 
         	  $vertretung['Stunde']);
         	if ( Count($seintraege) > 1 )
         	{
         		echo '<span class="Fehler">Doppeleintrag:';
         		foreach ( $seintraege as $eintrag)
         		{
         		  if ( $eintrag['Lehrer'] != $vertretung['Lehrer_Neu'])
         		  {
         		  	echo '<a href="'.$_SERVER['PHP_SELF'].'?AndererLehrer=';
                    echo ohneStern($eintrag['Lehrer']).'&VonStunde='.$_REQUEST['VonStunde'];
                    if ( isset($eintrag['Vertretung_id']))
                      echo '&Vertretung_id='.$eintrag['Vertretung_id'];
                    echo '">';
         		    switch ($Feld)
         		    {
         		  	case 'Klasse': 
         		  	  echo ohneStern($eintrag['Lehrer']).
                           ' ('.ohneStern($eintrag['Fach']).') ';
         		  	  break;
         		  	case 'Lehrer':
         		  	  echo ohneStern($eintrag['Klasse']).
                           ' ('.ohneStern($eintrag['Fach']).') ';
         		  	  break;
         		  	case 'Raum':
         		  	  echo ohneStern($eintrag['Lehrer']).
                           ' ('.ohneStern($eintrag['Klasse']).') ';
         		  	  break;
         		    }
         		    echo '</a>'."\n";
         		  }
         		}          		  
         		echo '</span>';
         	}
         }
         echo '</td>';
       } // wenn nicht Lehrer oder Lehrer hat Unterricht
       echo "</tr>\n";
      }
    }
    echo "</table>\n";
    echo "Bemerkung<br />\n";
    echo '<textarea name="Bemerkung[';
    if ( $vertretung['Vertretung_id'] >= -1 )
      echo $vertretung['Vertretung_id'];
    else
      echo $NewKey.':'.$vertretung[$NewKey];
    echo ']" cols="60" rows="5">';
    echo $vertretung['Bemerkung'];
    echo "</textarea>\n";
    if ( isset($_REQUEST['VonStunde']))
      echo '<input type="hidden" name="VonStunde" value="'.
        $_REQUEST['VonStunde'].'"/>';
    echo '<input type="Submit" value="Speichern" />';
    echo "<hr />\n";
  }
  echo "</form>\n"; 
  echo '<a href="javascript:Uebertragen();">Fenster ohne Speichern schließen</a>';


echo '</td></tr>';
include("include/footer.inc.php");

?>