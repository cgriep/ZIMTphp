<?php
  
include_once("include/helper.inc.php");

function ladeOszKopf_o_neu($titel, $ueberschrift, $druck = false, $bodyTagZusatz = "")
{
  $Kopf_o = 	
"<html>
\t<head>
\t\t<title>$titel</title>
\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=iso-8859-1\">
\t\t<meta name=\"robots\" content=\"noindex,nofollow\">
\t\t<meta http-equiv=\"reply-to\" content=\"webmaster@oszimt.de\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/stupla_neu.css\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/evaluation.css\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/huevaluation.css\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/style.css\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/oszimt.css\">";
  if($druck)
    $Kopf_o .= "\n\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/stupla_druckversion.css\">";
  else
  {
    $Kopf_o .= "\n\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/stupla_normalversion.css\">";
    $Kopf_o .= "\n\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/oszframe.css\">";
  }
$Kopf_o .= 
"\n\t\t<script src=\"http://js.oszimt.de/overlib.js\" type=\"text/javascript\"></script>
\t\t<script src=\"http://js.oszimt.de/prototype.js\" type=\"text/javascript\"></script>
\t</head>";
$Kopf_o .= "\n\t<body $bodyTagZusatz>";
  if(!$druck)
  {
    $Kopf_o .=
"\t\t<table class = \"oszRahmen_bg\" height = \"100%\" width = \"100%\" cellpadding = \"0\" cellspacing = \"0\" border = \"0\">
\t\t\t<tr height = \"1%\">
\t\t\t\t<td style = \"text-align:center\" width = \"1%\"><a href = \"http://www.oszimt.de/\"><img src = \"http://img.oszimt.de/logo/oszimt-logo.gif\" alt = \"OSZ IMT (Logo)\" border = \"0\"></a></td>
\t\t\t\t<td class = \"oszHeader\" width = \"1%\" nowrap>Oberstufenzentrum<br>Informations- und Medizintechnik</td>
\t\t\t\t<td>&nbsp;</td>
\t\t\t\t<td>&nbsp;</td>
\t\t\t</tr>
\t\t\t<tr>
\t\t\t\t<td  class = \"oszInhalt_bg\" colspan = \"3\" style = \"vertical-align:top;\">";
  
    if($ueberschrift != "keine")
      $Kopf_o .= "\n\t\t\t\t\t<div id = \"oszUeberschrift\"><span class = \"ueberschrift\">$ueberschrift</span></div>";
  }
  
  $Kopf_o .=
"\n\n<!--------------------------------------------------------------------------------->
<!-------------------------Inhalt-------------------------------------------------->
<!--------------------------------------------------------------------------------->\n\n";
  return $Kopf_o;
}

function ladeOszKopf_o($titel, $ueberschrift, $druck = false)
{
  $Kopf_o = 	
"<html>
\t<head>
\t\t<title>$titel</title>
\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=iso-8859-1\">
\t\t<meta name=\"robots\" content=\"noindex,nofollow\">
\t\t<meta http-equiv=\"reply-to\" content=\"webmaster@oszimt.de\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/stupla.css\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/evaluation.css\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/oszframe.css\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/style.css\">
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"http://oszimt.de/css/oszimt.css\">
\t\t<script src=\"http://js.oszimt.de/overlib.js\" type=\"text/javascript\"></script>
\t</head>
\t<body>";
  if(!$druck)
  {
  $Kopf_o .=
"\t\t<table class = \"oszRahmen_bg\" height = \"100%\" width = \"100%\" cellpadding = \"0\" cellspacing = \"0\" border = \"0\">
\t\t\t<tr height = \"1%\">
\t\t\t\t<td style = \"text-align:center\" width = \"1%\"><a href = \"http://www.oszimt.de/\"><img src = \"http://img.oszimt.de/logo/oszimt-logo.gif\" alt = \"OSZ IMT (Logo)\" border = \"0\"></a></td>
\t\t\t\t<td class = \"oszHeader\" width = \"1%\" nowrap>Oberstufenzentrum<br>Informations- und Medizintechnik</td>
\t\t\t\t<td>&nbsp;</td>
\t\t\t\t<td>&nbsp;</td>
\t\t\t</tr>
\t\t\t<tr>
\t\t\t\t<td  class = \"oszInhalt_bg\" colspan = \"3\" style = \"vertical-align:top;\">";
  
    if($ueberschrift != "keine")
      $Kopf_o .= "\n\t\t\t\t\t<div id = \"oszUeberschrift\"><span class = \"ueberschrift\">$ueberschrift</span></div>";
  }
  
  $Kopf_o .=
"\n\n<!--------------------------------------------------------------------------------->
<!-------------------------Inhalt-------------------------------------------------->
<!--------------------------------------------------------------------------------->\n\n";
  return $Kopf_o;
}

function ladeOszKopf_u($druck = false)
{
  $Kopf_u =	
"\n\n<!--------------------------------------------------------------------------------->
<!---------------------------Ende Inhalt------------------------------------------->
<!--------------------------------------------------------------------------------->\n\n";
  if(!$druck)
  {
    $Kopf_u .=
"\t\t\t\t</td>
\t\t\t\t<td id = \"oszLinkleiste\">
\t\t\t\t\t<!--Linkleiste-->
\t\t\t\t\t<table cellpadding = \"0\" cellspacing = \"0\" border = \"0\">";
  }
  return $Kopf_u;
}

function ladeLink($href,$hLink,$format = "")
{
  if(gibBrowser() == "IE")//fuer IE
    return "\n\t\t\t\t\t\t<tr><td><a class = \"Lile\" href = \"$href\" $format><span class = \"link linkeinrueck\">$hLink</span></a></td></tr>";
  else//fuer andere Browser
    return "\n\t\t\t\t\t\t<tr><td class = \"Lile\"><a href = \"$href\" $format><span class = \"link linkeinrueck\">$hLink</span></a></td></tr>";
}	

function ladeOszFuss($druck = false)
{
  $Fuss = "";
  if(!$druck)
  {
    $Fuss =	
"\n\t\t\t\t\t</table>
\t\t\t\t\t<!--Ende Linkleiste-->
\t\t\t\t</td>
\t\t\t</tr>
\t\t\t<tr id = \"oszFussleiste\">
\t\t\t\t<td colspan = \"4\">&nbsp;</td>
\t\t\t</tr>
\t\t</table>";
  }
  $Fuss .=
"\n\t</body>
</html>";
  return $Fuss;
}
?>