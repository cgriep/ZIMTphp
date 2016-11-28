<?php
/*
 * Created on 24.06.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once('include/config.php');

DEFINE('DOMAIN','@oszimt.de');

$allowed_domains = array('http://www.oszimt.de', 'http://oszimt.de', 
   'http://bscw-oszimt.de', 'http://osz-imt.de', 'http://www.osz-imt.de');

//# Hintergrundfarbe
$bgcolor = 'eeeeee';

// Ab hier nichts ändern, wenn Sie nicht genau wissen, was Sie tun
// Testet, ob die aufrufende Seite die Erlaubnis hat das Script zu starten (wegen
$DOMAIN_OK=false;

if ( isset($_REQUEST['sender']))
{ 
  $sendername = $_REQUEST['sender'];
  $senderemail = $_REQUEST['sender'].'@oszimt.de';
}

if ( isset($_SERVER['HTTP_REFERER']))
  $RF=$_SERVER['HTTP_REFERER'];
else
  $RF='-!-';
foreach ($allowed_domains as $ts)
{
  if (strpos($RF, $ts)== 0 && strpos($RF, $ts) !== false) {
    $DOMAIN_OK=true;
  }
}
if ( ! $DOMAIN_OK && ! isset($_REQUEST['sender'])){
  echo "Fehler, die aufrufende Domain $RF ist nicht in der Liste erlaubter Domains.";
  exit;
}

if (! isset($_REQUEST['betreff']) || $_REQUEST['betreff'] == '' || 
      $_REQUEST['betreff'] == 'undefined') 
{ 
  	 $betreffexist = false; 
  	 $betreff='';
  	 if ( isset($_REQUEST['title']) && $_REQUEST['title'] != '')
  	 {
  	 	$betreffexist = true;
  	 	$betreff = $_REQUEST['title'];
  	 }
}
else 
{ 
  	$betreffexist = true;
  	$betreff=$_REQUEST['betreff'];
}

// ID auswerten, Mail schreiben lassen
if (isset($_REQUEST['urlx']) && isset($_REQUEST['title']))
{
	$urlx = $_REQUEST['urlx'];
	$title = $_REQUEST['title'];
	$betreff = 'Empfehlung der Seite '.$title;
	$betreffexist = true;
	$email ='';
	if (isset($_REQUEST['email'])) $email = $_REQUEST['email']; 
	echo "<html>\n
<head>\n
<title>Die Seite $title empfehlen</title>\n
</head>\n
<body bgcolor=\"#$bgcolor\">\n
". get_formular() ."
</body>\n
</html>\n";
}
elseif (isset($_REQUEST['id'])) 
{
  $id = $_REQUEST['id']; 
  get_data($id);
   
  echo "<html>\n
<head>\n
<title> Mail an $name senden</title>\n
</head>\n
<body bgcolor=\"#$bgcolor\">\n
". get_formular() ."
</body>\n
</html>\n";
}
// Dankesmeldung und Absenden der Mail
elseif (isset($_REQUEST['senden'])) 
{
  if (isset($_REQUEST['name'])) 
    $name = $_REQUEST['name'];
  if (isset($_REQUEST['urlx']))
    $urlx = $_REQUEST['urlx'];
  if ( isset($_REQUEST['nachricht']))
    $nachricht = $_REQUEST['nachricht'];
  else
    $nachricht = '';
  if ( isset($_REQUEST['sendername']))  
    $sendername = $_REQUEST['sendername'];
  else
    $sendername = '';
  if ( isset($_REQUEST['senderemail']))
    $senderemail = $_REQUEST['senderemail'];
  else
    $senderemail = '';
  if ( isset($_REQUEST['betreffexist']))
    $betreffexist = $_REQUEST['betreffexist'];
  else
    $betreffexist = true;
  if ( isset($_REQUEST['betreff']))
    $betreff = $_REQUEST['betreff'];
  else
    $betreff = '';
  if ( isset($_REQUEST['Daten']) && is_array($_REQUEST['Daten']))
  {
  	foreach ($_REQUEST['Daten'] as $key => $value ) 
  	  $nachricht .= "\n".$key.': '.$value; 
  	$nachricht .= "\n";
  }
  if (!isset($_REQUEST['ccme']) ) 
  {
  	$ccme=false;
  }
  else 
  { 
  	$ccstatus ='checked';
    $ccme = true;
  }
  if (isset($_REQUEST['sendtoid']))
  {
    $id = $_REQUEST['sendtoid'];    
    get_data($id);
    $email .= DOMAIN;    
  }
  elseif (isset($_REQUEST['email']))
    $email = $_REQUEST['email'];
  else
    $email = '';
  $error = '';
  //Fehlerprüfung
  if (($sendername == '') || ($senderemail == '') || $nachricht == '' || 
     checkemail($senderemail) || checkemail($email)) { 
        if ($sendername == '') 
        { $error .="Bitte geben Sie Ihren Namen ein!<br/>\n";}
        if ($senderemail == '') 
        { $error .="Bitte geben Sie Ihre E-Mail-Adresse ein!<br/>\n";}
        elseif (checkemail($senderemail) ) 
        { $error .="Bitte geben Sie eine g&uuml;ltige E-Mail Adresse ein!<br/>\n";}
        if ($nachricht =='') 
        { $error .="Bitte geben Sie eine Nachricht ein!<br/>\n";}
        if (checkemail($email)) 
        { $error .="Bitte geben Sie eine gültige Empfänger-E-Mail-Adresse ein!<br/>\n";}

    echo "<html>\n<head>\n";
    if (isset($name))
      echo "<title> Mail an $name senden</title>\n";
    else
      echo "<title>$betreff</title>\n";
echo "</head>\n
<body bgcolor=\"#$bgcolor\">\n
<font color=\"red\">$error</font>
<br>\n".get_formular() ."
</body>\n
</html>\n";
  exit;
  }
  // sonst email senden
  else
  {
    if ($betreff == '') 
    { 
  	   $betreff='kein Betreff angegeben';
    }
    if (isset($_REQUEST['email']))
      $nachricht = "$sendername möchte Ihnen diese Seite empfehlen.\n".
       "Wollen Sie die Seite besuchen, so klicken Sie auf den Link:\n$urlx\n---\n".
       "$sendername hat für Sie folgende Nachricht hinzugefügt:\n\n$nachricht";
    
  // zuerst an den Empfänger...
  //$body =~ s/<p>/\n\n/g;
  //$body =~ s/<br>/\n/g;
  if (!isset($name)) $name = $email;
  $nachricht = "Von: $sendername ($senderemail)\n".
               "An: $name\n".
               "Gesendet am ".make_date()." (IP: {$_SERVER['REMOTE_ADDR']})\n\n".
               $nachricht;
  $absender = 'From: '.$senderemail."\n";

  mail($email, $betreff, $nachricht, $absender /*, '-f'.$senderemail*/ ); // wegen Safemode 5. Parameter entfernt
  /*
  * Loggin in Datenbank
  */
  include('include/config.php');
  $args = array();
  foreach ( $_REQUEST as $key => $value )
    $args[] = $key.'='.$value;
  if ( ! isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = '';
   
  mysql_query('INSERT INTO T_Logins (Login, IP, Referer,Args,Agent,Method,Seite) VALUES ('.
    time().",'".$_SERVER['REMOTE_ADDR']."','".$_SERVER['HTTP_REFERER']."','".
    mysql_real_escape_string(implode(';',$args))."','".$_SERVER['HTTP_USER_AGENT']."','".
    $_SERVER['REQUEST_METHOD']."','".$_SERVER['PHP_SELF']."')");
  mysql_close();  
  
  // dann vielleicht an den Versender senden...
  if ($ccme ) {
  	$nachricht .= "\n\n---\nEin Service von http://www.oszimt.de";
    mail($senderemail,'Kopie von '.$betreff, $nachricht, $absender);   
  }        
// Dankesbotschaft, mgl. Variablen: $name, $ccme, $sendername, 
// $senderemail, $nachricht, $betreff, $date
  echo "<html>\n
<head>\n
<title>Vielen Dank</title>\n
</head>\n
<body bgcolor=\"#$bgcolor\">\n
<center>\n
<br>\n
<br>\n
Vielen Dank, die E-Mail an <b>$name</b> wurde erfolgreich versendet.<br>\n
<br>\n
<a href=\"javascript:self.close()\">Fenster schliessen</a>
</body>\n
</html>
";
  }
}
else { 
	die('Fehler, das Script kann nicht direkt ausgeführt werden.');    
}

function get_data($idx) {
	global $name;
	global $email;
	global $Adressat;
// Holt aus der angegebenen Datei den Namen und die Mailadresse anhand der ID
  $query = mysql_query('SELECT EMail, Taetigkeit, Name, Geschlecht FROM T_Lehrer ' .
  		'WHERE EMail="'.mysql_real_escape_string($idx).'"');
  if ( $lehrer = mysql_fetch_array($query))
  {
          $name = $lehrer['Taetigkeit'];
          if ( $lehrer['Geschlecht'] =='M' )
            $Adressat = 'Herr '.$lehrer['Name'];
          elseif ( $lehrer['Geschlecht']=='W' )
            $Adressat = 'Frau '.$lehrer['Name'];          
          else 
            $Adressat = '';
          if ( $name == '' )
          {
          	$name = $Adressat;
          	$Adressat = '';
          }          
          $email = $lehrer['EMail'];
          mysql_free_result($query);
  }
  else
  {
           mysql_free_result($query);
           mysql_close();       
           echo 'Fehler, Zur angegebenen ID ist keine E-Mailadresse und Name vorhanden.';
           exit;
  }        	
} //sub get_mail

function make_date() {
 $days = array('Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Sonnabend');
 return $days[date('w')].' '.date('d.m.Y H:i');
} // sub make_date

// Prüft die Gültigkeit der aufrufenden Seite (siehe @allowed_domains)
// Mailformular
function get_formular() {
  global $betreffexist;
  global $betreff;
  global $nachricht;
  global $senderemail;
  global $sendername;
  global $ccstatus;
  global $id;
  global $name;
  global $Adressat;
  global $urlx;
  global $email;
  $code = '';
  if ( $name != '')
  {
    $code = "Empf&auml;nger:<br>\n<b>$name";
    if ( $Adressat != '' )
      $code .= ' - '.$Adressat;
    $code .= "</b><br/>\n";
  }
  if ($betreffexist) 
  {
  	$code .= "Betreff:<br>\n<b>$betreff</b><br/>\n";
    $betreffcode="<input type=\"hidden\" name=\"betreff\" value=\"$betreff\"/>\n
<input type=\"hidden\" name=\"betreffexist\" value=\"1\"/>\n"; 
  }
  $code .= "<form method=\"post\" action=\"MailIt.php\">\n
Ihr Name:<br/>\n
<input name=\"sendername\" value=\"$sendername\" size=\"50\"/><br/>\n
Ihre Mailadresse:<br/>\n
<input name=\"senderemail\" value=\"$senderemail\" size=\"50\"/><br/>\n";
  if (!$betreffexist) 
  {
  	$code .= "Betreff:<br/>\n";
    $betreffcode = "<input name=\"betreff\" value=\"$betreff\" size=\"50\"/><br/>\n
<input type=\"hidden\" name=\"betreffexist\" value=\"0\"/>\n"; 
  }
  if ( $urlx != '')
  {
  	$code .= 'E-Mail-Adresse des Empfängers:<br />';
  	$code .= "<input type=\"text\" name=\"email\" value=\"$email\" size=\"50\"/><br/>\n";
  }
  else
    $code .= "<input type=\"hidden\" name=\"sendtoid\" value=\"$id\"/>";
  $code .= $betreffcode."Ihre Nachricht:<br/>\n
<textarea rows=\"6\" name=\"nachricht\" cols=\"50\">$nachricht</textarea><br/>\n";
  if ($urlx != '')
    $code .= "<input type=\"hidden\" name=\"urlx\" value=\"$urlx\"/>\n";
  else
    $code .= "<input type=\"hidden\" name=\"name\" value=\"$name\"/>\n";
  
  $code .= "<input name=\"ccme\" type=\"checkbox\" $ccstatus value=\"ja\"/>Kopie an mich&nbsp;
<input type=\"submit\" value=\"Absenden\" name=\"senden\"/>&nbsp;
<input type=\"reset\" value=\"Zur&uuml;cksetzen\" name=\"senden\"/>\n
</form><br/>\n

<a href=\"javascript:self.close()\">Fenster schliessen</a>
";
return $code;
}// sub get_formular

# Prüft eingegebene Mailadresse auf Plausibilität (nicht auf Gültigkeit!)
function checkemail($adresse) {
    if (ereg('(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)', $adresse) || 
        !ereg('^.+\@(\[?)([a-zA-Z0-9\_\-]+\.)+[a-zA-Z]{2,4}(\]?)$', $adresse))
    {     
      return true;
    }
    else 
    {
    	return false;
    }
}

?>
