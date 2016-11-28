<?php
/**
 * Sperrung von Produkten
 * zusätzlich Verwaltung der Produkte und deren Sichtbarkeit nach außen 
 * (c) 2006 Christoph Griep
 */
  $Ueberschrift = "Produkte sperren";
  include("include/header.inc.php");
  include("msdnaaconfig.inc.php");
  $Produkte = holeProdukte();
  if ( isset($_REQUEST['ProduktName']))
  {
  	$query = mysql_query('INSERT INTO T_Produkte (Produkt, sichtbar, Gesperrt) ' .
  			'VALUES ("'.mysql_real_escape_string($_REQUEST['ProduktName']).'",0,0)');
  }
  if ( isset($_REQUEST["was"]) && isset($_REQUEST["Produkt"]) &&
    $_REQUEST["was"] == "+" && is_numeric($_REQUEST["Produkt"]) )
  {
    if ( $_REQUEST["Produkt"] >= 0 )
      if ( ! mysql_query("UPDATE T_Produkte SET Gesperrt=1 WHERE id=".
        $_REQUEST["Produkt"]) )
        echo "Fehler: ".mysql_error();
  }
  if ( isset($_REQUEST["was"]) && isset($_REQUEST["Gesperrt"]) &&
       $_REQUEST["was"] == "-" && is_numeric($_REQUEST["Gesperrt"]) )
  {
    if ( $_REQUEST["Gesperrt"] >= 0 )
      if ( ! mysql_query("UPDATE T_Produkte SET Gesperrt=0 WHERE id= ".
        $_REQUEST["Gesperrt"]) )
      echo "Fehler: ".mysql_error();
  }
  $query = mysql_query("SELECT id FROM T_Produkte WHERE Gesperrt=1");
  $Gesperrt = array();
  while ( $row = mysql_fetch_object($query))
  {
    $Gesperrt[] = $row->id;
  }
  mysql_free_result($query);
  if ( isset($_REQUEST["sicht"]) && isset($_REQUEST["Produkt"]) &&
    $_REQUEST["sicht"] == "+" && is_numeric($_REQUEST["Produkt"]) )
  {
    if ( $_REQUEST["Produkt"] >= 0 )
      if ( ! mysql_query("UPDATE T_Produkte SET sichtbar=1 WHERE id=".
        $_REQUEST["Produkt"]) )
        echo "Fehler: ".mysql_error();
  }
  if ( isset($_REQUEST["sicht"]) && isset($_REQUEST["sichtbar"]) &&
       $_REQUEST["sicht"] == "-" && is_numeric($_REQUEST["sichtbar"]) )
  {
    if ( $_REQUEST["sichtbar"] >= 0 )
      if ( ! mysql_query("UPDATE T_Produkte SET sichtbar=0 WHERE id= ".
        $_REQUEST["sichtbar"]) )
      echo "Fehler: ".mysql_error();
  }
  $query = mysql_query("SELECT id FROM T_Produkte WHERE sichtbar=1");
  $sichtbar = array();
  while ( $row = mysql_fetch_object($query))
  {
    $sichtbar[] = $row->id;
  }
  mysql_free_result($query);
  
?>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post" name="S">
<tr><td>Folgende Produkte werden nicht angezeigt.</td></tr>
<tr><td>
   <table border="0">
<tr>
 <td>Produkte</td>
 <td> </td>
 <td>Nicht sichtbare Produkte</td>
</tr>
<tr>
 <td><select name="Produkt" size="5">
 <?php
 foreach ($Produkte as $key => $value) 
 {
   if ( ! in_array($key, $sichtbar) )
     echo '<option value="'.$key.'">'.$value.'</option>';
 }
 ?>
 </select> </td>
 <td>
<input type="Hidden" name="sicht" />
 <input type="Submit" name="Sperren" value="&gt;&gt;"
 onClick="javascript:document.forms[0].sicht.value='+';">
 <br /><br />
 <input type="Submit" name="Entsperren" value="&lt;&lt;"
  onClick="javascript:document.forms[0].sicht.value='-';">
 </td>
 <td><select name="sichtbar" size="5">
 <?php
 foreach ( $sichtbar as $value) 
 {
   echo '<option value="'.$value.'">';
   echo $Produkte[$value];
   echo '</option>';
 }
 ?>
 </select> </td>
</tr>
</table>
</td></tr>
</form>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post" name="G">
<tr><td>Folgende gesperrte Produkte werden bei Ausgaben nicht berücksichtigt.</td></tr>
<tr><td>
<table border="0">
<tr>
 <td>Produkte</td>
 <td> </td>
 <td>Gesperrte Produkte</td>
</tr>
<tr>
 <td><select name="Produkt" size="5">
 <?php
 foreach ($Produkte as $key => $value) 
 {
   if ( ! in_array($key, $Gesperrt) )
     echo '<option value="'.$key.'">'.$value.'</option>';
 }
 ?>
 </select> </td>
 <td>
<input type="Hidden" name="was" />
 <input type="Submit" name="Sperren" value="&gt;&gt;"
 onClick="javascript:document.forms[1].was.value='+';">
 <br /><br />
 <input type="Submit" name="Entsperren" value="&lt;&lt;"
  onClick="javascript:document.forms[1].was.value='-';">
 </td>
 <td><select name="Gesperrt" size="5">
 <?php
 foreach ( $Gesperrt as $value) 
 {
   echo '<option value="'.$value.'">';
   echo $Produkte[$value];
   echo '</option>';
 }
 ?>
 </select> </td>
</tr>
</table>
</td></tr>
</form>

<tr><td>
<h1>Neue Produkte hinzufügen</h1>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
Neues Produkt: <input type="text" name="ProduktName"/>
<input type="submit" value="Hinzufügen" />
</form>
</td></tr>

<?php
include("include/footer.inc.php");
?>