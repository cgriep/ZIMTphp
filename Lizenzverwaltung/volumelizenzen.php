<?php
/**
 * Volume-Lizenzen eintragen
 * (c) 2006 Christoph Griep
 * 
*/
$Ueberschrift = 'Volume-Lizenzen bearbeiten';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
include('msdnaaconfig.inc.php');
echo '<tr><td>';
  $Produkte = holeProdukte();

  // Laborbelegung
  if ( isset($_REQUEST['Labor']) && is_numeric($_REQUEST['Anzahl'])) {
    // Laborbelegung
    $sql = 'INSERT INTO T_Lizenznehmer (Name, Vorname, Vertragsnummer, Art, Datum, ProduktID, Serialkey) VALUES (';
    $sql.= "'Labor','".mysql_real_escape_string(strtoupper($_REQUEST["Labor"])).
      "',-1,'Schule','".
      date("Y-m-d")."', ";
    while ( list($key, $produkt) = each($_REQUEST["Produkt"]) ) {
      $query = mysql_query("SELECT Serialkey FROM T_Lizenznummern WHERE Art <> 'Student' AND ProduktID = $produkt");
      if ( $row = mysql_fetch_row($query) ) {
        $Serialkey = $row[0];
        $sql2 = "SELECT id FROM T_Lizenznehmer WHERE Name ='Labor' AND Vorname = '".
          mysql_real_escape_string(strtoupper($_REQUEST['Labor'])).
          "' AND ProduktID = ".$produkt.
          ' ORDER BY Datum DESC';
        $q = mysql_query($sql2);
        if ( mysql_num_rows($q) > $_REQUEST['Anzahl'] ) {
          // Löschen
          for ( $i = 0; $i < mysql_num_rows($q) - $_REQUEST['Anzahl']; $i++){
            $row = mysql_fetch_row($q);
            if ( ! mysql_query('DELETE FROM T_Lizenznehmer WHERE id = '.$row[0]))
              echo '<div class="Fehler">Fehler: '.mysql_error().'</div>';
          }
          echo $_REQUEST['Anzahl'].' Lizenz(en) '.$Produkte[$produkt].' belegt.<br />';
          echo '(Es wurden Lizenzen freigegeben)';
        }
        elseif ( mysql_num_rows($q) < $_REQUEST['Anzahl'] )
        {
          // Einfügen
          for ( $i = 0; $i < $_REQUEST['Anzahl'] - mysql_num_rows($q); $i++ )
            if ( ! mysql_query($sql.$produkt.",'$Serialkey')"))
              echo "Fehler: ".mysql_error();
          echo $_REQUEST['Anzahl'].' Lizenz(en) '.$Produkte[$produkt].' belegt.<br />';
        }
        mysql_free_result($q);
      }
      else
        echo '<div class="Fehler">Keine Volume-Seriennummer gefunden!</div>';
      mysql_free_result($query);
    }
  }
?>
<div class="titel">Ausleihen zur Bestückung der Labore (Volume-License)</div>
<table>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
<tr>
<td class="home-content">Labor (Raumnummer)</td><td>
<input type="Text" name="Labor" size="30" maxlength="30"></td>
</tr>
<tr><td class="home-content">Produkt(e)</td><td>
<select name="Produkt[]" multiple="multiple" size="10">
<?php  
 foreach ($Produkte as $key => $value) 
 {
   echo '<option value="'.$key.'">'.$value.'</option>';
 }

 ?>
</select></td></tr>
<tr><td>Anzahl Computer im Labor</td><td><input type="Text" name="Anzahl" size="5" maxlength="5"> (0 eingeben, um alle Lizenzen aus diesem Labor freizugeben)
</td></tr>
<tr><td colspan="2"><input type="Submit" value="Lizenzen belegen"></td></tr>
</form></table>
</td></tr>

<?php
include('include/footer.inc.php');
?>