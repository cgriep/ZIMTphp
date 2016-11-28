<?php
/**
 * Zeigt die E-Mail-Adressen des OSZIMT an. Verwendet die Plesk-Datenbank
 * (c) 2006 Christoph Griep 
 */
$Ueberschrift = 'E-Mail-Liste OSZIMT';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
include('include/header.inc.php');
include('include/Lehrer.class.php');
?>
<tr>
 <td align = "center">(nur für internen Gebrauch)<br />
 Stand: <?=date('d.m.Y H:i')?>
 </td>
</tr>

<tr><td align="center">
<?php
if ( !isset($_REQUEST['Print']))
{
	echo '<a href="Kollegenliste.php?Excel=1&Alle=1">Alle E-Mail-Adressen exportieren</a>';
}
?>
<table class="Liste">
<tr><th>Name</th><th>E-Mail</th><th>Besonderes</th>
</tr>
<?php
  // Sortierkriterium angegeben?
  /* Confixx:
  $sql = "select kommentar, pop3, prefix, domain from (email left join email_forward ".
    "on ident=email_ident) left join pop3 on email_forward.pop3 = " .
      "pop3.account where email.kunde = 'web2' ORDER BY kommentar, pop3, prefix, domain DESC";
  */
  mysql_select_db('psa'); // psa-Datenbank
  $sql = "SELECT mail_name, redirect FROM mail INNER JOIN domains " .
  		"ON dom_id=domains.id " .
  		"WHERE name='oszimt.de'";
  // mail_aliases -> mn_id, alias
  if ( ! $query = mysql_query($sql))
  {
    die('<div class="Fehler">Fehler bei Datenbankabfrage: '.mysql_error().'</div>');
  }
  mysql_select_db($dbName);
  $farbe = '#dddddd';
  $Letztes = '';
  $da = false;
  $Sonstige = array();
  $Ohne = array();
  while ( $data = mysql_fetch_array($query))
  {
    /*if ( $data['mail_name'] != $Letztes ) // confixx: pop3
    {
      if ( $Letztes != "" ) {
        echo '<td align="center"><img src="http://skripte.oszimt.de/mailsize.pl?wer='.
        $Letztes.'"></td>';
        echo "</tr>";
        $da = false;
      }
      if ( $farbe == "#dddddd" ) 
        $farbe = "#afafaf";
      else 
        $farbe = "#dddddd";
      $Letztes = $data['mail_name'];// confixx: $data["pop3"];
      */
      // Name heraussuchen
      $lehrer = new Lehrer($data['mail_name'], LEHRERID_EMAIL);
      if ( $lehrer->Kuerzel != '' )
      {
        echo '<tr>';
        echo '<td>';
        echo '<a href="mailto:'.$data['mail_name'].'@oszimt.de">';     
      	echo $lehrer->Name;
      	if ( $lehrer->Vorname != '' ) echo ', '.$lehrer->Vorname;     
        if ( $data['redirect'] == 'true')
          echo ' (Weiterleitung)'; // .$data['prefix'].'-';
        echo '</a>';
        echo '</td>';
        echo '<td><a href="http://skripte.oszimt.de/MailIt.php?id='.$data['mail_name'];
        echo '&sender='.$_SERVER['REMOTE_USER'].'" title="E-Mail schreiben">'.
        $data['mail_name'].'@oszimt.de</td>';
        /*
        echo '<td align="center"><img src="http://skripte.oszimt.de/mailsize.pl?wer='.$Letztes.'"></td>';
        */
        echo '<td>'.$lehrer->Taetigkeit.'</td>';
        echo "</tr>\n";
      }
      elseif ( $lehrer->Username != '')
      {
      	$Sonstige[] = $lehrer;
      }
      else
      {
      	$Ohne[] = $data;
      }      
  } 
  mysql_free_result($query);
  if ( Count($Sonstige)>0)
  {
    echo '<tr><th colspan="3">Sonstige Adressen</th></tr>'."\n";
    foreach ( $Sonstige as $lehrer)
    {
    	echo '<tr><td>';
  	  echo '<a href="mailto:'.$lehrer->Username.'@oszimt.de">';     
  	        
      echo $lehrer->Name;
      if ( $lehrer->Vorname != '' ) echo ', '.$lehrer->Vorname;     
      echo '</a>';
      echo '</td>';
      echo '<td><a href="http://skripte.oszimt.de/MailIt.php?id='.$lehrer->Username;
      echo '&sender='.$_SERVER['REMOTE_USER'];            
      echo '" target="_blank">'.$lehrer->Username.'@oszimt.de</td>';
      //echo '<td align="center"><img src="http://skripte.oszimt.de/mailsize.pl?wer='.$Letztes.'"></td>';
      echo '<td>'.$lehrer->Taetigkeit.'</td>';
  	  echo '</td></tr>'."\n";
    }
  }
  if ( Count($Ohne) > 0 )
  {
    echo '<tr><th colspan="3">Adressen ohne Zuordnung</th></tr>'."\n";
    foreach ( $Ohne as $data)
    {
    	echo '<tr><td>';
  	  echo '<a href="mailto:'.$data['mail_name'].'@oszimt.de">';     
      echo $data['mail_name'];     
      echo '</a>';
      echo '</td>';
      echo '<td><a href="http://skripte.oszimt.de/MailIt.php?id='.$data['mail_name'];
      echo '&sender='.$_SERVER['REMOTE_USER'];
      echo '" target="_blank">'.$data['mail_name'].'@oszimt.de</a></td>';
      //echo '<td align="center"><img src="http://skripte.oszimt.de/mailsize.pl?wer='.$Letztes.'"></td>';
      echo '<td></td>';
  	  echo '</td></tr>'."\n";
    }
  }
  echo '</table>';
  if ( !isset($_REQUEST['Print']))
  {
	echo '<a href="Kollegenliste.php?Excel=1&Alle=1">Alle E-Mail-Adressen exportieren</a>';
  }
?>
</td></tr>

<tr><td><div class="Hinweis">Seite automatisch erstellt für 
<?=$_SERVER['REMOTE_USER']?> am <?php echo date("d.m.y") ?>
</div>

</td></tr>

<?php
include('include/footer.inc.php');

?>