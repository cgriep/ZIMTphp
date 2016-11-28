<?php
/**
 * Liest Lizenzen in die Datenbank ein
 * (c) 2006 Christoph Griep
 * neues XML-Format, 19.08.2009 C. Griep 
 */
$Ueberschrift = "Lizenzen einlesen";
include ("include/header.inc.php");
include ("msdnaaconfig.inc.php");
?>
<form action="<?=$SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
<tr><td>
Key-Datei <input type="file" name="Datei" ><br />
<select name="Produkt">
<?php


$qu = mysql_query("SELECT * FROM T_Produkte ORDER BY Produkt");
while ($row = mysql_fetch_array($qu))
{
	echo '<option value="' . $row[id] . '">' . $row[Produkt] . '</option>';
}
mysql_free_result($qu);
?>
</select><br />

Bestelldatum der Lizenzen (YYYY/MM/DD) <input type="text" name="Datum" /><br />
Keys in Datenbank schreiben <input type="Checkbox" name="Schreiben" value="v"><br />
Keys aus Datenbank löschen <input type="Checkbox" name="Loeschen" value="v"><br />
<input type="Submit" name="" value="Key's einlesen">
</td></tr>
</form>
<tr><td>
<?php


if (isset ($_FILES["Datei"]["name"]) && file_exists($_FILES['Datei']['tmp_name']))
{
	$anz = 0;
	if ( strpos($_FILES['Datei']['name'], '.xml') > 0 )
	{
		echo 'XML-Format erkannt.<br />';
		$xml = simplexml_load_file($_FILES['Datei']['tmp_name']);
		$key = $xml->xpath("/YourKey/Product_Key/Key");
		for ( $i=0; $i< count($key); $i++) 
		{
			$buffer = trim($key[$i]);
			if (isset ($_REQUEST["Produkt"]) && $key[$i]['ClaimedDate'] == $_REQUEST['Datum'])
				{
					echo $buffer.' - '.$key[$i]['ClaimedDate'].'<br />';
					if ($_REQUEST["Schreiben"] == "v" || $_REQUEST["Loeschen"] == "v")
					{
						if ($_REQUEST["Schreiben"] == "v")
						{
							$sql = "INSERT INTO T_Lizenznummern (ProduktID, Serialkey, Art) VALUES (";
							$sql .= $_REQUEST["Produkt"] . ",'" . ($buffer) . "','Student')";
						} else
						{
							$sql = "DELETE FROM T_Lizenznummern WHERE ProduktID = " . $_REQUEST["Produkt"];
							$sql .= " AND Serialkey = '" . ($buffer) . "' AND Art = 'Student'";
						}
						if (!mysql_query($sql))
							echo "Fehler: " . mysql_error();
						else
							$anz++;

					}
				}
				else
				{
					echo $buffer.' - '.$key[$i]['ClaimedDate'].' FALSCHES DATUM<br />';
				}
		}
	}
	else 
	{	
	$dat = fopen($_FILES['Datei']['tmp_name'], "r");
	while (!feof($dat))
	{
		$buffer = fgets($dat, 4096);
		$bufferarray = explode(' ', $buffer);
		foreach ($bufferarray as $buffer)
		{
			$buffer = trim($buffer);
			if ($buffer != '')
			{
				echo $buffer . '<br />';
				if (isset ($_REQUEST["Produkt"]))
				{
					if ($_REQUEST["Schreiben"] == "v" || $_REQUEST["Loeschen"] == "v")
					{
						if ($_REQUEST["Schreiben"] == "v")
						{
							$sql = "INSERT INTO T_Lizenznummern (ProduktID, Serialkey, Art) VALUES (";
							$sql .= $_REQUEST["Produkt"] . ",'" . trim($buffer) . "','Student')";
						} else
						{
							$sql = "DELETE FROM T_Lizenznummern WHERE ProduktID = " . $_REQUEST["Produkt"];
							$sql .= " AND Serialkey = '" . trim($buffer) . "' AND Art = 'Student'";
						}
						if (!mysql_query($sql))
							echo "Fehler: " . mysql_error();
						else
							$anz++;

					}
				}
			}
		}
	} // while
	fclose($dat);
	}
	echo "<em>$anz Keys eingelesen</em><br />";

} // if  
?>
</td></tr>
<?php


include ("include/footer.inc.php");
?>
