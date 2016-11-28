<?php
/**
 * Verwaltet die Lizenzen zu Produkten 
 * (c) 2006 Christoph Griep
 * 
 */
  $Ueberschrift = "Lizenzen eingeben";
  include("include/header.inc.php");
  include("msdnaaconfig.inc.php");
  echo '<tr><td>';
  if ( isset($_REQUEST["Produkt"]) && (isset($_REQUEST["Bezeichnung"]) ||
    isset($_REQUEST['Art']))) {
    if ( $_REQUEST['Serial'] == "" ) {
      $sql = "INSERT INTO CD (ProduktID, Datum, VertragID, Bezeichnung) VALUES (";
      $sql .= $_REQUEST["Produkt"].",NULL, NULL, '";
      $sql .= $_REQUEST["Bezeichnung"]."')";
    }
    else
    {
      $sql = "INSERT INTO T_Lizenznummern (ProduktID, Serialkey, Art) VALUES (";
      $sql .= $_REQUEST["Produkt"].",'".$_REQUEST['Serial']."','".$_REQUEST["Art"]."')";
    }
    if ( ! mysql_query($sql)) echo "Fehler: ".mysql_error();
    $Nr = mysql_insert_id();
    if ( $_REQUEST['Serial'] == "" )
      echo "<b>Die CD <i>".$_REQUEST["Bezeichnung"]."</i> hat Nummer $Nr. Bitte auf die CD schreiben.</b><br />";
  }
  $Produkte = holeProdukte();
 ?>
 <table >
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
<tr>
 <td>Programm</td>
 <td><select name="Produkt">
 <?php
 while ( list($key, $value) = each($Produkte) )
 {
   echo '<option value="'.$key.'"';
   if ( isset($_REQUEST['Produkt']) && $key == $_REQUEST["Produkt"] ) 
     echo ' selected="selected"';
   echo '>'.$value.'</option>';
 }
 ?>
 </select>
 <b>Für Ausleih-CD:</b>
 CD-Bezeichnung <input type="Text" name="Bezeichnung" size="42" maxlength="60">
 <input type="Submit" value="Ausleih-CD registrieren"></td>
</tr>
<tr>
 <td>Art</td>
 <td><select name="Art">
 <option value="Student">Student (Weitergabe)</option>
 <option value="Volume">Volume (Labor+Weitergabe)</option>
 <option value="LabUse">Laborlizenz (Nicht Weitergeben!)</option>
 </select></td>
</tr>
<tr>
 <td>Lizenznummer </td>
 <td><input type="Text" name="Serial" maxlength="40" size="43"> </td>
</tr>
<tr>
 <td colspan="2">
 <input type="Submit" value="Speichern">
 </td>
</tr>
<tr>
 <td> </td>
 <td> </td>
</tr>
<tr>
 <td> </td>
 <td> </td>
</tr>
</form>
</table>
<hr />
<h1>Verfügbare Lizenzen zur Ausgabe</h1>
<?php
  $query = mysql_query("SELECT * FROM T_Lizenznummern INNER JOIN T_Produkte ON " .
     "T_Lizenznummern.ProduktID = T_Produkte.id ORDER BY Produkt, Art");
  echo '<table border="1" style="border-size:1;border-color:black" width="100%">';
  $Prod = "";
  $Art = "";
  $nr = 0;
  while ( $row = mysql_fetch_object($query))
  {
    if ( $Prod != $row->Produkt )
    {
      if ( $nr % 3 != 0 ) echo "</tr>";
      echo '<tr bgcolor="lightblue"><th colspan="3">'.$row->Produkt."</th></tr>";
      $qu = mysql_query("SELECT Count(*) AS Anz FROM T_Lizenznummern WHERE ProduktID = ".$row->ProduktID);
      $r = mysql_fetch_object($qu);
      mysql_free_result($qu);
      echo '<tr bgcolor="gray"><td colspan="3" align="center">'.$r->Anz." freie Lizenzen</td></tr>";
      $Prod = $row->Produkt;
      $Art = "";
      $nr = 0;
    }
    if ( $Art != $row->Art )
    {
      if ( $nr % 3 != 0 ) echo "</tr>";
      echo '<tr bgcolor="yellow"><th colspan="3">'.$row->Art."</th></tr>";
      $Art = $row->Art;
      $nr = 0;
    }
    if ( $nr % 3 == 0 ) echo "<tr>";
    echo '<td align="center"><font face="Courier New" size="-1">';
    echo $row->Serialkey;
    echo "</font></td>";
    $nr++;
    if ( $nr % 3 == 0 ) echo "</tr>";
  }
  mysql_free_result($query);
  echo "</table><br /><br />";
  include("include/footer.inc.php");
?>