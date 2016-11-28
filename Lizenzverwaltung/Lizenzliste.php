<?php
/**
 * Liste der ausgegebenen Lizenzen
 * (c) 2006 Christoph Griep
 * 
 */
  $Ueberschrift = 'Ausgebebene Lizenzen';
  $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
  include('include/header.inc.php');
  include('msdnaaconfig.inc.php');
  $Produkte = holeProdukte();
  if ( ! isset($_REQUEST['Sort'] )) 
    $Sort = 'Vertragsnummer, Produkt';
  else
    $Sort = $_REQUEST['Sort'];
  if ( isset($_REQUEST['Search']))
    $Search = '%'.$_REQUEST['Search'].'%';
  else
    $Search = '';

?>
<tr><td align="center">Stand: <?=date('d-m-Y H:i')?></td></tr>
<tr><td>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
Name oder Klasse <input type="text" name="Search" value="<?=str_replace('%','',$Search)?>" />
<input type="submit" value="Suchen" />
</form>
<h2>Einzellizenzen</h2>
<?php
  
  $query = mysql_query('SELECT * FROM T_Lizenznehmer INNER JOIN T_Produkte ON ' .
     "T_Lizenznehmer.ProduktID = T_Produkte.id WHERE Vertragsnummer >= 0 AND " .
     "(Name LIKE '$Search' OR Art LIKE '$Search') ORDER BY $Sort");
  echo '<table class="Liste">';
  echo '<tr><th><a href="?Sort=Name">Name</a></th><th><a href="?Sort=Art">Klasse</a>'.
    '</th><th><a href="?Sort=Datum">Datum</a></th><th><a href="?Sort=Produkt">Produkt</a>'.
    '</th><th>Key</th><th><a href="?Sort=Vertragsnummer">Vertrag</a></th></tr>';
  while ( $row = mysql_fetch_object($query))
  {
    echo '<tr><td>';
    echo $row->Name.', '.$row->Vorname;
    echo '</td><td>'.$row->Art.'</td>';
    echo '<td>'.$row->Datum.'</td>';
    echo '<td>'.$row->Produkt.'</td>';
    echo '<td>'.$row->Serialkey.'</td>';
    echo '<td align="right">'.$row->Vertragsnummer.'</td>';
    echo '</tr>';
  }
  mysql_free_result($query);
  echo '</table><br /><br />';
?>

<h2>Volumelizenzen (Labore)</h2>
<?php
  $Sort = 'Vorname, Produkt';
  if ( ! $query = mysql_query('SELECT Datum,Vertragsnummer,Count(Datum) AS Anzahl,' .
  		'Vorname,Produkt,Serialkey FROM T_Lizenznehmer INNER JOIN T_Produkte ON ' .
     'T_Lizenznehmer.ProduktID = T_Produkte.id GROUP BY Vorname, Name, ' .
     'Produkt, Serialkey HAVING Vertragsnummer < 0'))
  echo mysql_error();
  echo '<table>';
  echo '<tr><th>Labor</th><th>Datum</th><th>Produkt</th><th>Key</th><th>Anzahl</th></tr>';
  while ( $row = mysql_fetch_object($query))
  {
    echo '<tr><td>';
    echo $row->Vorname;
    echo '</td>';
    echo '<td>'.$row->Datum.'</td>';
    echo '<td>'.$row->Produkt.'</td>';
    echo '<td>'.$row->Serialkey.'</td>';
    echo '<td>'.$row->Anzahl.'</td>';
    echo '</tr>';
  }
  mysql_free_result($query);
  echo '</table><br /><br />';
  echo '<h2>Gesamtanzahlen</h2>';
  $query = mysql_query('SELECT Produkt, Count(*) AS Anz ' .
  		'FROM T_Lizenznehmer INNER JOIN T_Produkte ON ' .
     'T_Lizenznehmer.ProduktID = T_Produkte.id ' .
     'WHERE Vertragsnummer>=0 GROUP BY Produkt');
  echo '<table>';
  while ( $row = mysql_fetch_object($query))
  {
    echo '<tr><td>';
    echo $row->Produkt;
    echo '</td>';
    echo '<td>'.$row->Anz.'</td>';
    echo '<td>';
    echo 'Einzel';
    echo '</td>';
    echo '</tr>';
  }
  mysql_free_result($query);
  $query = mysql_query('SELECT Produkt, Count(*) AS Anz ' .
  		'FROM T_Lizenznehmer INNER JOIN T_Produkte ON ' .
     'T_Lizenznehmer.ProduktID = T_Produkte.id ' .
     'WHERE Vertragsnummer<0 GROUP BY ProduktID ');
    while ( $row = mysql_fetch_object($query))
  {
    echo '<tr><td>';
    echo $row->Produkt;
    echo '</td>';
    echo '<td>'.$row->Anz.'</td>';
    echo '<td>';
    echo 'Volume/LabUse';
    echo '</td>';
    echo '</tr>';
  }
  mysql_free_result($query);
  echo '</table><br /><br />';
  include('include/footer.inc.php');
?>