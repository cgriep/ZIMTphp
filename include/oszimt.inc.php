function ersetzeUmlaute($suchstring)
{
	$suchmuster = array ("/�/","/�/","/�/","/�/","/�/","/�/","/�/");
	$ersetzen   = array ("Ae","Oe","Ue","ae","oe","ue","ss");
	return preg_replace($suchmuster, $ersetzen, $suchstring);
}