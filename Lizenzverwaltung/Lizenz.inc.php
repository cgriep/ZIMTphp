<?php
/*
 * Created on 12.12.2005
 * (c) 2006 Christoph Griep
 * 
 */
 
function VerschickeMail($Lehrer, $Klasse, $Antraege, $isVisualStudio = false)
{
  require_once("LizenznummernPDF.php");
  require_once("phpmail.php");
  $mail = new mime_mail();
  $mail->to = $Lehrer."@oszimt.de";
  //$mail->to = "griep@oszimt.de";
  $mail->from = $_SERVER["REMOTE_USER"].'@oszimt.de';
  $mail->headers = "Errors-To: ".$_SERVER["REMOTE_USER"]."@oszimt.de";
  $mail->subject = "[OSZIMT-MSDNAA] Lizenzen $Klasse";
  $body = "Liebe/r Kollegin/Kollege $Lehrer,\n".
    "anbei finden Sie die im Rahmen der MSDNAA bestellten Lizenzen für die Gruppe ".$Klasse.
    ".\nSofern Installations-CDs benötigt werden, müssen diese gebrannt ".
    "werden.\n";
  $body .= "Beachten Sie dazu die beiliegenden Informationen zur lizenzkostenfreien Software.\n\n";
  if ( $isVisualStudio )
    $body .= "Anbei finden Sie eine Installationsanleitung für Visual Studio, ".
      "die Sie bitte den Lizenznehmern aushändigen. Dadurch wird gewährleistet, dass ".
      "diese die gleichen Bedingungen vorfinden wie in der Schule.\n";
  $body .= "Bitte sorgen Sie dafür, dass die Lizenzdatei direkt nach dem Ausdrucken ";
  $body .= "gelöscht wird, damit sie nicht in falsche Hände gerät. Eine Weitergabe der ";
  $body .= "PDF-Datei an Schüler ist unerwünscht!\n";
  $body .=  "Bitte sorgen Sie ebenfalls dafür, dass nur die Software kopiert wird, für die ".
    "Lizenzschlüssel ausgegeben wurden.\n\n".
    "mit freundlichen Grüßen\n".
    "die MSDNAA-Verwaltung am OSZ IMT\n\n".
    "bearbeitet durch: ".$_SERVER["REMOTE_USER"]."\n".
    "(automatisch generiert am ".date("d.m.Y H:i").". Aus technischen Gründen ist es ".
    "möglich, dass Sie mehrere E-Mails an einem Tag erhalten. Wir bitten um Verständnis.)";
  $attachment = file_get_contents("Mitteilung Lizenzkostenfreie Software.pdf");
  $mail->add_attachment($attachment, "Hinweise lizenzkostenfreie Software.pdf", "application/pdf");
  $attachment = LizenznummernPDF(implode(",",$Antraege));
  $mail->add_attachment($attachment, "Lizenz".$Klasse.".pdf", "application/pdf");
  if ( $isVisualStudio )
  {
    $attachment = file_get_contents("Installationsanleitung Visual Studio.pdf");
    $mail->add_attachment($attachment, "Installation Visual Studio.pdf", "application/pdf");
  }
  $mail->body = $body;
  if ( ! $mail->send() )
    echo "Fehler beim Mailen: UserId ".$key."<br />";
  else
    echo '<span class="unterlegt">Mail an '.$mail->to." für Gruppe $Klasse gesendet.</span><br />";
  //echo "Mail an $Lehrer für Klasse $Klasse<br />";
  //mail($Lehrer."@oszimt.de", "[MSDNAA] Lizenzen $Klasse",
  //    "", "From: ".$_SERVER["REMOTE_USER"]."@oszimt.de");
} 

function holeLizenznehmer($Vertragsnummer = -1)
{
  $query = mysql_query("SELECT * FROM T_Antraege WHERE Vertragsnummer=$Vertragsnummer");
  $row["Name"] = "";
  $row["Vertragsnummer"] = -1;
  $row["id"] = -1;
  $row = mysql_fetch_array($query);
  mysql_free_result($query);
  return $row;
}
 
?>
