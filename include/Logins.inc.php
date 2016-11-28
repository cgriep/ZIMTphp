<?php

function SaveLogin()
{
  $args = array();
  foreach ( $_REQUEST as $key => $value )
    $args[] = $key.'='.$value;
  if ( ! session_is_registered('Login') )
    $_SESSION['Login'] = time();
  if ( ! isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = '';
  if ( ! isset($_SERVER['REMOTE_USER'])) $_SERVER['REMOTE_USER'] = '';
  
  mysql_query("INSERT INTO T_Logins (User, Login, IP, Referer,Args,Agent,Method,Seite) VALUES ('".
    $_SERVER['REMOTE_USER']."',".time().",'".$_SERVER['REMOTE_ADDR']."','".
    $_SERVER['HTTP_REFERER']."','".
    mysql_real_escape_string(implode(';',$args))."','".$_SERVER['HTTP_USER_AGENT']."','".
    $_SERVER['REQUEST_METHOD']."','".$_SERVER['PHP_SELF']."')");
}

function getLastLogin()
{
  $s = '';
  $last = '';
  if ( $_SERVER['REMOTE_USER'] != '' )
  {
    if ( session_is_registered('Login') ) $last = 'AND Login < '.$_SESSION['Login'];
    // 2010-01-12 (eifler) SQL-Statement erstmal als String zusammenbauen, 
    // wegen Lesbarkeit und Möglichkeit, zu debuggen
    // deshalb auch "$query" in "$q_result" umbenannt, es ist keine Frage,
    // sondern das Abfrage-Ergebnis - also die Antwort. 
    $statement =  "SELECT Max(Login) FROM T_Logins ";
    $statement .= "WHERE User='".$_SERVER['REMOTE_USER']."' $last ";
    $q_result = mysql_query($statement);

    // Original auskommentiert
    // $q_result = mysql_query("SELECT Max(Login) FROM T_Logins WHERE User='".
    //   $_SERVER['REMOTE_USER']."' $last ");

    // Debug-Anweisungen (eifler), Begin
    // $s .= "XXX" . $statement . "XXX" . "<br/>";
    // $s .= "XXX" . $q_result . "XXX" . "<br/>";
    // if ($q_result == NULL) {
    //     $s .= "XXX query-result is NULL" . "<br/>";
    // }
    // Debug-Anweisungen (eifler), END

    if ( $l = mysql_fetch_row($q_result) )
    {
      $s .= '<i>'.$_SERVER['REMOTE_USER'].'</i>, Sie haben<br />den internen Bereich<br />zuletzt am '.
        date('d.m.Y H:i',$l[0])."<br />betreten.\n";
    }
    mysql_free_result($q_result);
  }
  return $s;
}

?>
