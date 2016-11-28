<html>
<head><title>SUPI</title></head>
<body>

<?php

//function readSchuljahre(&$SJ_list)
{
  $SJ_file = "files/SJ_termine.txt";
  if (!file_exists($SJ_file))
    return false;

  $fp = fopen($SJ_file,"r");
  if (!$fp)
    return false;
  $countSJ = 0;
  while (!feof($fp))
  {
    $line = fgets($fp,4096);
    if(trim($line) != "")
    {
      $tmpline = explode(";",$line);
      $SJ_list[$countSJ]['SJahr']  = trim($tmpline[0]);//String Schuljahr
      $SJ_list[$countSJ]['Beginn'] = trim($tmpline[1]);//Erster Schultag
      $SJ_list[$countSJ]['Ende']   = trim($tmpline[2]);//Letzter Ferientag  
      $countSJ++;
    }
  }
  fclose($fp);
  foreach($SJ_list as $SJ)
  {
    echo "Schuljahr " . $SJ['SJahr'] . "<br>";
    //echo $SJ['Beginn'] . "<br>";    
    //echo $SJ['Ende'] . "<br><br>";    
    echo "&nbsp;Beginn: " . date("d.m.Y H:i:s", strtotime($SJ['Beginn'])) . "<br>";    
    echo "&nbsp;Ende: " . date("d.m.Y H:i:s", strtotime($SJ['Ende'])) . "<br><br>";    
  }
  return true;
}
//$schnitt = 3;
//$breite = 100;
//$fuellgrad = round(($schnitt-1) / 4 * $breite, 0);
//echo $fuellgrad;

//$img = erzeugeImageSchnitt(1);
//erzeugeImageSchnitt(1);

/*
echo "<img src = \"erzeugeImageSchnitt.php?schnitt=1\"><br><br>";
echo "<img src = \"erzeugeImageSchnitt.php?schnitt=1.5\"><br><br>";
echo "<img src = \"erzeugeImageSchnitt.php?schnitt=2\"><br><br>";
echo "<img src = \"erzeugeImageSchnitt.php?schnitt=2.5\"><br><br>";
echo "<img src = \"erzeugeImageSchnitt.php?schnitt=3\"><br><br>";
echo "<img src = \"erzeugeImageSchnitt.php?schnitt=3.5\"><br><br>";
echo "<img src = \"erzeugeImageSchnitt.php?schnitt=4\"><br><br>";
echo "<img src = \"erzeugeImageSchnitt.php?schnitt=4.5\"><br><br>";
echo "<img src = \"erzeugeImageSchnitt.php?schnitt=5\">";
*/
?>

</body>
</html>