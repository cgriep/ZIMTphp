<?php
/*
 * Created on 04.07.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

if ( isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '')
{
  include('include/config.php');
  $url = $_SERVER['QUERY_STRING'];  
  mysql_query('INSERT INTO T_URLClicks (URL,Clicks,LastClick,Referer) VALUES ("'.
  mysql_real_escape_string($url).'",1,'.time().',"'.$_SERVER['HTTP_REFERER'].'")');     
  mysql_close();
  
  //$url =~ tr/+/ /;
  //$url =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
  //$url =~ s/<!--(.|\n)*-->//g;
  //if ($url=~/http/gi) {
    header("Location: $url\n\n");
}
else 
  die('Kein Parameter angegeben!');

?>
