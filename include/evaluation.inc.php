<?php
/*===========================================================================*/
function ermittleID($md5_ID, $mod = 1) //Ermittelt die unverschluesselte ID der Umfrage
{
  switch ( $mod )
  {
    case 1://evaluation
      $sql = "SELECT ID_Umfrage FROM T_eval_Umfragen;";
      break;
    case 2://umfrage
      $sql = "SELECT ID_Umfrage FROM T_umfrage_Umfragen;";
  }
  $rs = mysql_query($sql);
  while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
    if(md5($row['ID_Umfrage']) == $md5_ID)
    {
      mysql_free_result($rs);
      return $row['ID_Umfrage'];
    }
  mysql_free_result($rs);
  return -1;
}
/*===========================================================================*/
function printNotSpi($listNoten)
{
  //Notenschnitt berechnen
  $schnitt = getAritMittel($listNoten);
    
  echo "\n<!---------------------------------------------------------->";
  echo "\n<!------------------ Beginn Notenspiegel ------------------->";

  echo "\n<table border = \"0\" cellpadding = \"0\" cellspacing = \"0\">";
  
  echo "\n\t<tr>";
  echo "\n\t\t<td align = \"center\" class = \"ra_sw x0110\">1</td>";
  echo "\n\t\t<td align = \"center\" class = \"ra_sw x0110\">2</td>";
  echo "\n\t\t<td align = \"center\" class = \"ra_sw x0110\">3</td>";
  echo "\n\t\t<td align = \"center\" class = \"ra_sw x0110\">4</td>";
  echo "\n\t\t<td align = \"center\" class = \"ra_sw x0110\">5</td>";
  echo "\n\t\t<td align = \"center\" class = \"ra_sw x0010\">6</td>";
  echo "\n\t\t<td rowspan = \"2\" align = \"center\">&nbsp;&#216;:&nbsp;" . number_format($schnitt, 2, ",", ".") . "</td>";  
  echo "\n\t</tr>";
  
  echo "\n\t<tr>";
  echo "\n\t\t<td class = \"ra_sw x0100\"><div id = \"NotSpiNoten\">" . (int)$listNoten[0] . "</div></td>";
  echo "\n\t\t<td class = \"ra_sw x0100\"><div id = \"NotSpiNoten\">" . (int)$listNoten[1] . "</div></td>";
  echo "\n\t\t<td class = \"ra_sw x0100\"><div id = \"NotSpiNoten\">" . (int)$listNoten[2] . "</div></td>";
  echo "\n\t\t<td class = \"ra_sw x0100\"><div id = \"NotSpiNoten\">" . (int)$listNoten[3] . "</div></td>";
  echo "\n\t\t<td class = \"ra_sw x0100\"><div id = \"NotSpiNoten\">" . (int)$listNoten[4] . "</div></td>";
  echo "\n\t\t<td><div id = \"NotSpiNoten\">" . (int)$listNoten[5] . "</div></td>";
  echo "\n\t</tr>";
  
  echo "\n</table>";
  
  echo "\n<!-------------------- Ende Notenspiegel ------------------->";
  echo "\n<!---------------------------------------------------------->";
}
/*===========================================================================*/
function readSchuljahre(&$SJ_list)
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
  return true;
}
/*===========================================================================*/
?>