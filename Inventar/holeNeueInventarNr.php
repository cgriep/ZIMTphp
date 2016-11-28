<?php
/*
 * Created on 02.09.2006
 *
 * (c) 2006 Christoph Griep
 * 
 */
include('include/config.php');

/*
 * SELECT DISTINCT REPLACE(REPLACE(REPLACE(
REPLACE(REPLACE(REPLACE(REPLACE(
REPLACE(REPLACE(REPLACE(Inventar_Nr,'9',''),
'8',''),'7',''),'6',''),'5',''),'4',''),
'3',''),'2',''),'1',''),'0','') FROM `T_Inventar` WHERE Inventar_Nr <> ''
 */
$nr = 1;
$Prefix = '';
if ( isset($_REQUEST['Art']) && is_numeric($_REQUEST['Art'])) 
{
		$query = mysql_query('SELECT Inventarnummerprefix ' .
				'FROM T_Inventararten WHERE Art_id='.$_REQUEST['Art']);
	    if ( $art = mysql_fetch_array($query))
	      $Prefix = $art['Inventarnummerprefix'];
        mysql_free_result($query);
}
elseif ( isset($_REQUEST['INr']))
{ 
      $Prefix = mysql_real_escape_string($_REQUEST['INr']);
      if ( is_numeric($Prefix))
        $Prefix = '';
}
$sql = 'SELECT Inventar_Nr FROM T_Inventar WHERE Inventar_Nr="'.$Prefix;
$gefunden = true;
while ( $nr > 0 && $gefunden )
{
    $query = mysql_query($sql.$nr.'"');
    if (mysql_num_rows($query) > 0)
    {
    	$nr++;
    } 
    else
      $gefunden = false;
    mysql_free_result($query);
}
echo $Prefix.$nr;
mysql_close();
?>
