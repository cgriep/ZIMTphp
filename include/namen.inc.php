<?php
function LehrerToKuerzel($Name, $Vorname, $db)
{
  $Name = mysql_real_escape_string(trim($Name));
  $Vorname = mysql_real_escape_string(trim($Vorname));
  $sql = "SELECT DISTINCT Lehrer FROM T_StuPla ".
         " WHERE Name LIKE'$Name' AND Vorname LIKE'$Vorname%' LIMIT 1";
  $query = mysql_query($sql);
  if ( $row = mysql_fetch_array($query) )
    $lehrer = $row["Lehrer"];
  else
    $lehrer = "";
  mysql_free_result($query);
  return $lehrer;
}


function userToLehrer($user, $db)
{
  mysql_select_db("confixx", $db);
  // Sortierkriterium angegeben?
  $sql = "select kommentar from (email left join email_forward on ident=email_ident) left join pop3 on email_forward.pop3 = " .
         "pop3.account where email.kunde = 'web2' AND prefix='".$user."' LIMIT 1";
  if (! $query = mysql_query($sql)) echo mysql_error();
  $Lehrer = mysql_fetch_array($query);
  mysql_query($query);
  mysql_select_db("oszimt", $db);
  return $Lehrer["kommentar"];
}

function LehrerToUser($Name, $Vorname, $db)
{
  mysql_select_db("confixx", $db);
  $Name = trim($Name);
  $Vorname = trim($Vorname);
  // Sortierkriterium angegeben?
  $sql = "select prefix from (email left join email_forward on ident=email_ident) left join pop3 on email_forward.pop3 = " .
         "pop3.account where email.kunde = 'web2' AND kommentar LIKE '";
  if ( strpos($Name,",") !== false )
    $Name = substr($Name,0,strpos($Name,","));
  if ( strpos($Name, "-") !== false )
    $Name = substr($Name, 0, strpos($Name,"-"))."%";
  if ( strpos($Vorname, " ") !== false )
    $Vorname = substr($Vorname, 0, strpos($Vorname," "));
  if ( strpos($Vorname, "-") !== false )
    $Vorname = substr($Vorname, 0, strpos($Vorname,"-"));
  $derName = trim($Name).", ".substr(trim($Vorname),0,1)."% LIMIT 1'";
  if ( ! $query = mysql_query($sql.$derName))
    echo mysql_error();
  if ( ! $row = mysql_fetch_array($query) )
  {
    // Weiterleitungen prüfen
     mysql_free_result($query);
     $query = mysql_query("select prefix, domain from email where email.kunde = 'web2' ".
            " AND prefix LIKE '".$Name."' LIMIT 1",$db);
     if ( ! $row = mysql_fetch_array($query) )
       $row["prefix"] = "-unbekannt-";
  }
  mysql_free_result($query);
  mysql_select_db("oszimt",$db);
 return $row["prefix"];
}

function KuerzelToLehrer($Kuerzel, & $Name, & $Vorname)
{
  $Kuerzel = mysql_real_escape_string(trim($Kuerzel));
  $sql = "SELECT DISTINCT Name, Vorname FROM T_StuPla ".
         " WHERE Lehrer LIKE'$Kuerzel' LIMIT 1";
  $query = mysql_query($sql);
  $Name = $Kuerzel;
  $Vorname = "";
  if ( $row = mysql_fetch_array($query) )
  {
    $Name = trim($row["Name"]);
    $Vorname = trim($row["Vorname"]);
  }
  mysql_free_result($query);
}

?>