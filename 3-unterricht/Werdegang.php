<?php
/*
 * Created on 26.04.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 * (c) 2007 Christoph Griep
 */
 
$Ueberschrift = '';
define('USE_KALENDER', 1);
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
echo '<tr><td>';
mysql_close($db);
// Rechte f�r den Benutzer
// Einf�gen in T_Werdegang_EMails
// Select auf T_Schueler (Name, Vorname, Klasse, Geburtsdatum)
$dbUser = "werdegang";
$dbPassword = 'g&7rtG4$';

// Datenbank �ffnen
$db = mysql_connect('localhost', $dbUser, $dbPassword);
mysql_select_db($dbName, $db);

if ( isset($_REQUEST['EMail']) && $_SESSION['Tag'] == $_REQUEST['Tag'])
{
	if ( strpos($_REQUEST['EMail'],'@') === false)
	{
		echo '<div class="Fehler">Das ist keine g�ltige E-Mail.</div>';
	}
	elseif (isset($_REQUEST['Klasse']) && isset($_REQUEST['Name']) )
	{
		if ( isset($_REQUEST['Einverstaendnis']) && $_REQUEST['Einverstaendnis'] == 'ok')
		{
		$Name = explode(',',$_REQUEST['Name']);
		if ( mysql_query('INSERT INTO T_Werdegang_EMails (EMail,Name,Vorname,Klasse) ' .
				'VALUES ("'.mysql_real_escape_string($_REQUEST['EMail']).'","'.
                 mysql_real_escape_string(trim($Name[0])).'","'.
                 mysql_real_escape_string(trim($Name[1])).'","'.
                 mysql_real_escape_string($_REQUEST['Klasse']).'")'))
        {
          echo '<div class="Hinweis">Ihre E-Mail wurde registriert.</div>';
          unset($_REQUEST['Tag']); 
        }
        else
          echo '<div class="Fehler">Ihre E-Mail konnte nicht gespeichert werden. Ist sie vielleicht bereits registriert?</div>';
		}
		else
		{
			echo '<div class="Fehler">Sie m�ssen Ihr Einverst�ndnis zur Datenspeicherung erkl�ren!</div>';
		}
	}
}

$TagFehler = '';
$KlasseFehler = '';

if ( isset($_REQUEST['Tag']))
{
  $dieSchueler = array();	
  $Tag = explode('.',$_REQUEST['Tag']);
  if ( Count ($Tag) != 3 )
    $Tag = explode('-', $_REQUEST['Tag']);
  if ( Count($Tag) != 3 )
  {
    $TagFehler = ' class="markiert" value="'.htmlentities($_REQUEST['Tag']).'"';
    $Fehler[] = 'Das Datum ist falsch.';
    unset($_REQUEST['Tag']);
  }
  else
  {
  	if ( !checkdate($Tag[1], $Tag[0], $Tag[2]) )
  	{
  		$TagFehler = ' class="markiert" value="'.htmlentities($_REQUEST['Tag']).'"';
  		$Fehler[] = 'Das Datum ist ung�ltig.';
        unset($_REQUEST['Tag']);
  	}
  	else
  	{
  		$Datum = mktime(0,0,0,$Tag[1],$Tag[0],$Tag[2]);
  		$Klasse = strtoupper($_REQUEST['Klasse']);
  		$Klasse = mysql_real_escape_string($Klasse);
  		$Klasse = str_replace(' ','', $Klasse);
  		$Klasse1 = '';
  		while ( strlen($Klasse) > 0 && !is_numeric(substr($Klasse,0,1)) )
  		{
  		  $Klasse1 = $Klasse1 . substr($Klasse,0,1);
  		  $Klasse = substr($Klasse,1);
  		}
  		$Klasse = $Klasse1.' '.$Klasse;
  		$query = mysql_query('SELECT Name, Vorname FROM T_Schueler ' .
  				'WHERE Klasse="'.$Klasse.'" AND Geburtsdatum='.$Datum);
  		if ( mysql_num_rows($query) == 0 )
 		{
 			$Fehler[] = 'In dieser Klasse gibt es niemanden mit diesem Geburtsdatum.';
 			$KlasseFehler = ' class="markiert" values="'.$Klasse.'"';
 			unset($_REQUEST['Tag']);
 		}
 		else
 		{
 			while ($schueler = mysql_fetch_array($query))
 			{
 				$dieSchueler[] = $schueler;
 			}
 		}
 		mysql_free_result($query); 		
  	}
  }
}
if ( ! isset($_REQUEST['Tag']) )
{
?>	
<p>Liebe Sch�lerinnen, liebe Sch�ler,<br />
Sie verlassen demn�chst mit einem Abschluss das OSZ IMT.  
<br />
<br />
F�r unser Qualit�tsmanagement w�re es sehr wichtig, wenn wir in zwei Jahren von Ihnen 
Ausk�nfte bekommen w�rden �ber 
<ul>
  <li>Ihren beruflichen Werdegang&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;und</li>
  <li>den Stellenwert des hier erworbenen Wissens.</li>
</ul>
Hierzu bekommen Sie in zwei Jahre eine E-Mail.
Wir bitte Sie uns zu diesem Zwecke eine E-Mail-Anschrift anzugeben, unter der Sie auch 
in zwei Jahren noch zu erreichen sind. 
</p>
<?php  
	if ( isset($Fehler) && is_array($Fehler) && Count($Fehler) > 0 )
	  echo '<div class="Fehler">'.implode('<br />',$Fehler).'</div>';
	echo '<form class="Formular" action="'.$_SERVER['PHP_SELF'].'" name="Form" method="post">';
	echo 'Bitte geben Sie Ihr Geburtsdatum (tt.mm.jjjj) ein:<br />';
	echo '<input type="text" name="Tag" maxlength="10" size="10" '.$TagFehler.' ';
	echo 'onClick="popUpCalendar(this,Form[\'Tag\'],\'dd.mm.yyyy\')" ';
    echo 'onBlur="autoCorrectDate(\'Form\',\'Tag\' , false )"/>';
	echo '<br />';
	echo 'Bitte geben Sie Ihre Klasse ein:<br />';
	echo '<input type="text" name="Klasse" maxlength="10" size="10" '.$KlasseFehler.' /><br />';
	echo '<input type="submit" value="abschicken" />';
	echo '</form>';
}
else
{
   echo '<form class="Formular" method="post" action="'.$_SERVER['PHP_SELF'].'">';
   echo 'Klasse: <input type="text" name="Klasse" value="'.$Klasse.
     '" readonly="readonly" /><br />';   
   if ( Count($dieSchueler) > 1 )
   {
     echo 'Bitte w�hlen Sie Ihren Namen:';
     echo '<select name="Name">';
     $Ok = 'selected="selected"';
     foreach ($dieSchueler as $schueler)
     {
       echo '<option '.$Ok.'>'.$schueler['Name'].', '.$schueler['Vorname'].'</option>'."\n";
       $Ok = '';
     }
     echo '</select><br />';
   }
   else
   {
   	 echo 'Name: <input type="text" name="Name" value="'.$dieSchueler[0]['Name'].', '.
   	   $dieSchueler[0]['Vorname'].'" readonly="readonly" /><br />';   	 
   }
   echo '<input type="hidden" name="Tag" value="'.$_REQUEST['Tag'].'" />';
   // Absicherung, damit keine automatisierte Eintragung erfolgen kann
   $_SESSION['Tag'] = $_REQUEST['Tag'];
   echo '<br />Vielen Dank, dass Sie bereit sind, uns bei unserer Arbeit zu unterst�tzen.<br />';
   if ( isset($_REQUEST['Tag']))
   echo '<input type="checkbox" name="Einverstaendnis" value="ok" /> Ich erkl�re mich einverstanden, dass meine Ausk�nfte gespeichert ';
   echo 'und f�r Schulzwecke verwendet werden. Weiterhin bin ich einverstanden, dass mir das OSZ IMT ';
   echo 'per E-Mail Einladungen zu Befragungen �ber meinen ';
   echo 'beruflichen Werdegang schickt.<br />';
   echo 'Sie k�nnen diese Einwilligung jederzeit widerrufen.<br /><br />';
   echo 'Sollte Sich Ihre E-Mail-Adresse �ndern, w�re es sehr freundlich uns dieses mitzuteilen.<br />';
   echo 'Bitte geben Sie Ihre E-Mail-Adresse ein: ';
   echo '<input type="text" name="EMail" maxlength="50" /><br />';   
   echo '<input type="submit" value="Abschicken" />';
   echo '</form>';
   echo 'Datenschutzinformation:<br />';
   echo '<ul>';
   echo '<li>Das OSZ IMT verpflichtet sich zum Schutz und Sicherheit der pers�nlichen Daten.</li>';
   echo '<li>Das OSZ IMT wird die Sicherheit personenbezogener Daten wahren und deren Vollst�ndigkeit sch�tzen,';
   echo 'soweit dies mit einem angemessenen Aufwand m�glich ist.</li>';
   echo '<li>Das OSZ IMT wird personenbezogene Daten nur f�r schulische Zwecke oder in besonderen F�llen';
   echo 'als anonymisierte Daten f�r schulische, historische, wissenschaftliche oder andere Zwecke ';
	echo 'entsprechend dem geltenden Recht verwenden.</li></ul>';
}

echo '</td></tr>';
include('include/footer.inc.php'); 
?>
