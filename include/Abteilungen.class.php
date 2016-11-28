<?php

class Abteilungen
{
  var $Abteilungen;
  function Abteilungen($db)
  {
    $this->Abteilungen = array();
    if (! $query = mysql_query('SELECT * FROM T_Abteilungen', $db)) 
      echo '<div class="Fehler">'.mysql_error().'</div>';
    while ( $abt = mysql_fetch_array($query) )
      $this->Abteilungen[$abt['Abteilung_id']] = $abt;
    mysql_free_result($query);
//    $this->Abteilungen[0] = array('Abteilung_id'=>0,'Abteilung'=>'',
//      'Ansprechpartner'=>'Griep');
  }
  function getAbteilung($nr)
  {
    return $this->Abteilungen[$nr]['Abteilung'];
  }
  function getEmpfaenger($nr)
  {
    $e = explode(',',$this->Abteilungen[$nr]['Ansprechpartner']);
    $empf = array();
    foreach ( $e as $name )
      $empf[] = $name.'@oszimt.de';
    return $empf;
  }
  /*
   * Gibt die Abteilung zurück, zu der ein Benutzer gehört.
   * @param der Benutzer. Wenn weggelassen, wird der eingeloggte Benutzer verwendet
   * @return die Nummer der Abteilung, zu der der eingeloggte gehört
   */
  function abteilungsZugehoerigkeit($User = '')
  {
  	if ( $User == '' )
  	  $User = strtoupper($_SERVER['REMOTE_USER']);
  	foreach ( $this->Abteilungen as $key => $abteilung)
  	  if ( in_array($User, explode(',',str_replace(' ','',
           strtoupper($abteilung['Ansprechpartner'])))) )
             return $key;
    return -1;
  }
  /*
   * Gibt die ID der Abteilung zu einer Klasse zurück. Wenn keine Klasse angegeben ist 
   * oder die Klasse nicht existiert, wird eine leere Zeichenkette zurückgegebem.
   * @param $Klasse die Klasse deren Abteilung gesucht wird
   * @return die Abteilung oder -1, wenn keine Abteilung gefunden werden konnte 
   */
  function getKlassenAbteilung($Klasse)
  {
  	$Abt = -1;
  	if ( $Klasse != '' )
  	{      		  
  	  $query = mysql_query('SELECT DISTINCT Abteilung FROM T_Schueler WHERE Klasse="'.
    	  mysql_real_escape_string($Klasse).'"');          	 
  	  if ( $abteilung = mysql_fetch_array($query))
  	  {
  	  	foreach ( $this->Abteilungen as $Nr => $dieAbteilung)
  	  	{
  	  		if ( $dieAbteilung['Abteilung'] == 'Abteilung '.$abteilung['Abteilung'])
  	  		{
  	  			 $Abt = $Nr;
  	  		}
  	  	}
  	  }
  	  mysql_free_result($query);
  	}
    return $Abt;  	   
  }
  function getAlleEmpfaenger($nr)
  {
    // Sonderfall Abteilung II
    $e = explode(',',$this->Abteilungen[$nr]['Ansprechpartner']);
    $em = array();
    foreach ( $e as $empfaenger )
    {
      $em[] = $empfaenger.'@oszimt.de';
    }
    return $em;
  }

  function isAbteilungsleitung($Abteilung = '', $user = '')
  {
     if ( $user == '' ) $user = $_SERVER['REMOTE_USER'];
     $user = strtoupper($user);
     if ( $Abteilung != '' )
     {
       if ( in_array($user, explode(',',str_replace(' ','',
         strtoupper($this->Abteilungen[$Abteilung]['Ansprechpartner'])))) )
         return true;
       else
         return false;
     }
     else
     {
       foreach ( $this->Abteilungen as $Abteilung )
       {
         // print_r($Abteilung);
         if ( in_array($user, explode(',',str_replace(' ','',
           strtoupper($Abteilung['Ansprechpartner'])))) )
           return true;
       }
       return false;
     }
  }
}

?>
