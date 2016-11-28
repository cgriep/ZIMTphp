<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
{popup_init src="http://js.oszimt.de/overlib.js"}
{$HeaderZusatz}
{if $USE_KALENDER}
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/popcalendar.css">
<script type="text/javascript" language="JavaScript" src="http://js.oszimt.de/common.js"></script>
<script type="text/javascript" language="JavaScript" src="http://js.oszimt.de/popcalendar.js"></script>
{/if}
<title>{$Ueberschrift}</title>
<link rel="stylesheet" type="text/css" href="/oszimt.css">
</head>

<!-- Beginn OSZIMT Kopf -->
<body>
<div class="header">
  <a id="top" name="top"></a>
  <div class="logo">
    <a href="http://www.oszimt.de/"><img src="http://img.oszimt.de/logo/oszimt-logo.gif"
      alt="OSZ IMT (Logo)" title="Logo" /></a>
  </div>
  <div id="LetzterLogin">
    <span class="small">{$getLastLogin}</span>
    <br /><img src="http://img.oszimt.de/logo/internLehrer.gif" width="165"
         height="20" alt="interner Lehrerbereich" title="interner Lehrerbereich" />
  </div>
  <div class="Schulname">
  Oberstufenzentrum<br />Informations- und Medizintechnik
  </div><img id="ITSchuleLogo" src="http://img.oszimt.de/logo/GroessteIT-Schule.gif" 
    alt="Logo Größte IT-Schule" title="Größte IT-Schule" />
</div>
<!-- Ende OSZIMT Kopf -->

<!-- Beginn des eigentlichen Inhalts -->
<div class="Seite">
  <div class="content{$ContentArt}">
    <h1>{$Ueberschrift}</h1>