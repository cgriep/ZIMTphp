function ersetzeUmlaute($suchstring)
{
	$suchmuster = array ("/Ä/","/Ö/","/Ü/","/ä/","/ö/","/ü/","/ß/");
	$ersetzen   = array ("Ae","Oe","Ue","ae","oe","ue","ss");
	return preg_replace($suchmuster, $ersetzen, $suchstring);
}