<?php
/***
 * Startseite für die Lizenzverwaltung
 * Mit Suchfunktionen und automatischer Abwicklung der Lizenzbestellungen
 * (c) 2006 Christoph Griep
 * 
 */
$Ueberschrift = 'MSDNAA-Verwaltung';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
include('./msdnaaconfig.inc.php');
include('./Lizenz.inc.php');
?>
<tr><td>
<form action="Bestelluebersicht.php" method="post" target="_blank" class="Formular">
<label>Vertragsnummer</label>
<input type="Text" name="Wer" size="5" maxlength="5" />
<input type="Submit" name="" value="Bestellungen ansehen" />
</form>
<form action="Lizenznummern.php" method="post" target="_blank" class="Formular">
<label>Vertragsnummer oder Klasse</label>
<input type="Text" name="VN" size="5" maxlength="10" />
<input type="Submit" name="" value="Lizenznummern erneut ausgeben" />
</form>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post" class="Formular">
<label><em>Anträge</em> für Vertragsnummer</label>
<input type="Text" name="DelAntrag" size="5" maxlength="5" />
<input type="Submit" name="delete" value="unwiderruflich Löschen"/>
</form>

<?php

function bearbeiteAntrag($Antragsnummer, $Produkte, $Gesperrt)
{
  if ( is_numeric($Antragsnummer) )
  {
    $qu = mysql_query('SELECT * FROM T_Antraege WHERE Vertragsnummer = '.
      $Antragsnummer.' ORDER BY Eingang DESC');
    if ( mysql_num_rows($qu) == 0 )
    {
      echo '<div class="content-bold">Vertragsnummer nicht gefunden!</div>';
      mysql_free_result($qu);
      unset($_REQUEST['AntragNr']);
      unset($_REQUEST['Antrag']);
      unset($_REQUEST['Wer']);
      return -1;
    }
    else
    {
      $allesok = true;
      while ( ($row = mysql_fetch_object($qu)) && $allesok ) {
        // Seriennummer feststellen
        if ( in_array($row->Produkt, $Gesperrt) )
        {
          // Produkt gesperrt !
          echo 'Produkt '.$Produkte[$row->Produkt].' ist momentan gesperrt! Anfrage wird übersprungen.<br />';
        }
        elseif ( strpos($row->Beschreibung,'gelöscht') !== false )
        {
          echo 'Datensatz ist als gelöscht gekennzeichnet! Anfrage wird übersprungen!<br />';
        }
        else {
          if ( ! $query = mysql_query('SELECT ProduktID, MAX(Serialkey) AS snkey, Art ' .
          		'FROM T_Lizenznummern WHERE ProduktID = '.
             $row->Produkt.' GROUP BY ProduktID, Art') ) die ('Fehler:'.mysql_error());;
          $Seriennr = '';
          while ( $rw = mysql_fetch_object($query)) {
            $Art = $rw->Art;
            if ( $rw->Art == 'Student' )
              $Seriennr = $rw->snkey;
            elseif ( $Seriennr == '' && $rw->Art == 'Volume' )
              $Seriennr = $rw->snkey;
          }
          mysql_free_result($query);
          if ( $Seriennr != '' ) {
            // CD suchen
            //$ersProdukt = $CDs[$row->Produkt][aufProdukt];
            //if ( ! is_numeric($ersProdukt) || $ersProdukt == 0 )
              $ersProdukt = $row->Produkt;
            if ( true ) { // isset($CDs[$ersProdukt]) || isset($_REQUEST['OhneCD']) ) {
              $sql = 'INSERT INTO T_Lizenznehmer (Name, Vorname, Vertragsnummer, Art, Datum,';
              $sql .= 'ProduktID, Serialkey, Ansprechpartner,Schueler_Nr) VALUES (';
              $sql .= "'".$row->Name."','".$row->Vorname."',".$Antragsnummer.",'";
              $sql .= $row->Art."','".date("Y-m-d")."',";
              $sql .= $row->Produkt.",'";
              $sql .= $Seriennr."','".$row->Ansprechpartner."',".$row->Schueler_Nr.")";
              if ( mysql_query($sql) ) {
                if ( $Art == "Student" ) {
                  if (!mysql_query("DELETE FROM T_Lizenznummern WHERE ProduktID = " .
                    $row->Produkt." AND Serialkey = '".
                    $Seriennr."' AND Art = 'Student'")) echo "Löschen der Seriennummer $Seriennr für Produkt ".$row->Produkt." misslungen.";
                  if (mysql_affected_rows() != 1) echo "Löschen der Seriennummer $Seriennr für Produkt ".$row->Produkt.
                    " misslungen, betroffene Datensätze ".mysql_affected_rows();
                }
              }
              else
                die ("Fehler: ($sql) ".mysql_error());
              echo '<b>Die Seriennummer '.$Seriennr.' wurde eingetragen.</b><br />';
              // Bearbeiteten Antrag in die Tabelle schreiben
              if ( ! mysql_query("INSERT INTO T_AktuelleAntraege (Vertragsnummer, Lehrer, Klasse,Produkte) VALUES (".
                $Antragsnummer.",'".$row->Ansprechpartner."','".
                $row->Art."',',".$row->Produkt.",')"))
              {
                mysql_query("UPDATE T_AktuelleAntraege SET Produkte=CONCAT(Produkte,'".
                  $row->Produkt.",') WHERE Vertragsnummer=".$Antragsnummer);
              }
              if ( ! mysql_query("DELETE FROM T_Antraege WHERE id = ".$row->id ))
                die ("Fehler: ".mysql_error());
              if ( $row->Bemerkung != "" )
                echo "Bemerkung: " . $row->Bemerkung."<br />";
            }// if CD vorhanden
            else {
              echo "Keine CD für ".$Produkte[$row->Produkt]." vorhanden!<br />";
              $allesok = false;
            }
          }// if Seriennummer vorhanden
          else {
            echo "Keine Seriennummer für ".$Produkte[$row->Produkt]." vorhanden !<br />";
            $allesok = false;
          }
        } // gesperrt
      } // while
      if ( $allesok ) {
        echo 'Für direkte Ausgabe drucken Sie die <a href="Lizenznummern.php?VN='.$Antragsnummer;
        echo '" target="_blank">Liste mit Seriennummern</a> für den Lizenznehmer aus. '.
         'Hinweis: Die Lizenz wird in jedem Falle per E-Mail versendet.<br /><hr />';
      }
      mysql_free_result($qu);
      unset ( $_REQUEST["Antrag"] );
      unset ( $_REQUEST["Wer"] );
      return -1;
    }
  }
}


  $Produkte = holeProdukte();
  if ( isset($_REQUEST["Verteilen"]) )
  {
    if ( $_REQUEST["Verteilen"] == 1 )
    {
      $query = mysql_query("SELECT * FROM T_AktuelleAntraege ORDER BY Lehrer, Klasse");
      $Lehrerantraege = array();
      $Klasse = "";
      $Lehrer = "";
      $isVisualStudio = false;
      while ($row = mysql_fetch_array($query) )
      {
        if ( ($Lehrer != $row["Lehrer"] || $Klasse != $row["Klasse"]) &&
           Count($Lehrerantraege) > 0 )
        {
           VerschickeMail($Lehrer, $Klasse, $Lehrerantraege, $isVisualStudio);
           $isVisualStudio = false;
           $Lehrerantraege = array();
        }
        $Lehrerantraege[] = $row["Vertragsnummer"];
        // Produkt 5: Visual Studio
        $isVisualStudio = $isVisualStudio || ( strpos($row["Produkte"],",5,") !== false );
        $Lehrer = $row["Lehrer"];
        $Klasse = $row["Klasse"];
      }
      if ( Count($Lehrerantraege) > 0 )
        VerschickeMail($Lehrer, $Klasse, $Lehrerantraege, $isVisualStudio);
      mysql_free_result($query);
    }
    mysql_query("DELETE FROM T_AktuelleAntraege");
  }
  if ( isset($_REQUEST["DelAntrag"]) && is_numeric($_REQUEST["DelAntrag"])) {
    mysql_query("DELETE FROM T_Antraege WHERE Vertragsnummer = ".$_REQUEST["DelAntrag"]);
    echo mysql_affected_rows()." Produktanträge gelöscht.<br />";
  }
  if ( isset($_REQUEST["MakeAntrag"])) {
    $qu = mysql_query("SELECT * FROM T_Lizenznehmer WHERE Vertragsnummer = ".$_REQUEST["Vertragsnummer"]);
    if ( $r = mysql_fetch_object($qu) ) {
      mysql_query("INSERT INTO T_Antraege (Name, Vorname, Art, Ansprechpartner, Produkt, Vertragsnummer) VALUES ('".$r->Name."','".$r->Vorname."','".$r->Art."','',".$_REQUEST["Produkt"][0].",".$_REQUEST["Vertragsnummer"].')');
      $_REQUEST["Antrag"] = mysql_insert_id();
    }
    mysql_free_result($qu);
  }

/*
  $query = mysql_query("SELECT * FROM Antrag ORDER BY Name, Vorname, id");
  $Namen = array();
  $name = "";
  $WerID = -1;
  $Lizenznehmer = array();
  while ( $row = mysql_fetch_object($query))
  {
    if ( $name != $row->Name." ".$row->Vorname ) {
      $Namen[][name] = $row->Name.", ".$row->Vorname." (".$row->Art.",".$row->Ansprechpartner.",".
        $row->Eingang.")";
      $Namen[count($Namen)-1][id] = $row->id;
      $name = $row->Name." ".$row->Vorname;
    }
    if ( $row->Vertragsnummer == $_REQUEST["Wer"] ) $WerID = $row->id;
    $Lizenznehmer[$row->id][Art] = $row->Art;
    $Lizenznehmer[$row->id][Name] = $row->Name;
    $Lizenznehmer[$row->id][Vorname] = $row->Vorname;
    $Lizenznehmer[$row->id][Ansprechpartner] = $row->Ansprechpartner;
    $Lizenznehmer[$row->id][Produkt] = $row->Produkt;
    $Lizenznehmer[$row->id][Bemerkungen] = $row->Bemerkungen;
    $Lizenznehmer[$row->id][Vertragsnummer] = $row->Vertragsnummer;
    $Lizenznehmer[$row->id][Schueler_Nr] = $row->Schueler_Nr;
  }
  mysql_free_result($query);
  */
  $query = mysql_query("SELECT id FROM T_Produkte WHERE Gesperrt=1");
  $Gesperrt = array();
  while ( $row = mysql_fetch_object($query))
  {
    $Gesperrt[] = $row->id;
  }
  mysql_free_result($query);
  // CD ausleihen
  /*
  if ( isset($_REQUEST["VN"]) && isset($_REQUEST["CDNr"]))
  {
    // CD ausleihen
    $qu = mysql_query("SELECT * FROM Lizenznehmer WHERE Vertragsnummer = ".$_REQUEST["VN"]);
    if ( $row = mysql_fetch_object($qu) ) {
        $sql = "UPDATE CD SET VertragID = ".$_REQUEST["VN"];
        $sql .= ", Datum = '".date("Y-m-d")."', Ansprechpartner = '".
        $_SERVER["REMOTE_USER"]."' WHERE id = ";
        $sql .= $_REQUEST["CDNr"];
        if ( mysql_query($sql) )
          echo "CD ".$_REQUEST["CDNr"]." wurde ausgeliehen.<br />";
        else
          echo "Fehler: ".mysql_error();
    }
    mysql_free_result($qu);
  }
  $query = mysql_query("SELECT Max(id) as nr, ProduktID, Bezeichnung, aufProdukt FROM CD WHERE Datum IS NULL AND VertragID IS NULL GROUP BY ProduktID");
  $CDs = array();
  while ( $row = mysql_fetch_object($query))
  {
    $CDs[$row->ProduktID][ID] = $row->nr;
    $CDs[$row->ProduktID][Bezeichnung] = $row->Bezeichnung;
    if ( is_numeric($row->aufProdukt) ) {
      if ( $row->aufProdukt > 0 ) $CDs[$row->ProduktID][aufProdukt] = $row->aufProdukt;
    }
    else
      $CDs[$row->ProduktID][aufProdukt] = 0;
  }
  mysql_free_result($query);
  */
  // Antragsnummer bearbeiten
  if ( isset($_REQUEST["AntragNr"]) && is_numeric($_REQUEST["AntragNr"]) )
  {
    bearbeiteAntrag($_REQUEST["AntragNr"], $Produkte, $Gesperrt);
  }
  else
  {
    if ( isset($_REQUEST["EinfacheAntraege"]) && 
         Count($_REQUEST["EinfacheAntraege"]) > 0 )
    {
      foreach ( $_REQUEST["EinfacheAntraege"] as $vn )
        bearbeiteAntrag($vn, $Produkte, $Gesperrt);
    }
  }
  // Einzelnen Antrag bearbeiten
  if ( isset($_REQUEST["Antrag"]) && isset($_REQUEST["SN"]) &&
       is_numeric($_REQUEST["Vertragsnummer"]) ) {
    $Lizenznehmer = holeLizenznehmer($_REQUEST["Vertragsnummer"]);
    if ( $_REQUEST["Delete"] != "v" && $Lizenznehmer["id"] != -1 )
    {
      $Name = $Lizenznehmer["Name"];
      $Vorname = $Lizenznehmer["Vorname"];
      $Klasse = $Lizenznehmer["Art"];
      $sql = "INSERT INTO T_Lizenznehmer (Name, Vorname, Vertragsnummer, Art, Datum,";
      $sql .= "ProduktID, Serialkey, Ansprechpartner,Schueler_Nr) VALUES (";
      $sql .= "'$Name','$Vorname',".$_REQUEST["Vertragsnummer"].",'$Klasse','".
        date("Y-m-d")."',";
      $sql .= $Lizenznehmer['Produkt'].",'";
      $sql .= $_REQUEST["SN"]."','".$Lizenznehmer['Ansprechpartner'].
          "',".$Lizenznehmer['Schueler_Nr'].")";
      //echo "SQL: $sql";
      if ( mysql_query($sql) ) {
        mysql_query("DELETE FROM T_Lizenznummern WHERE ProduktID = " .
          $Lizenznehmer['Produkt']." AND Serialkey = '".
          $_REQUEST["SN"]."' AND Art = 'Student'");
      }
      else
        die ("Fehler: ".mysql_error());
      echo '<b>Die Seriennummer '.$_REQUEST["SN"].' wurde eingetragen.</b><br />';
      /*
      if ( $_REQUEST["OhneCD"] != "v" ) {
        $sql = "UPDATE CD SET VertragID = ".$_REQUEST["Vertragsnummer"];
        $sql .= ", Datum = '".date("Y-m-d")."', Ansprechpartner = '".
        $Lizenznehmer[$_REQUEST["Antrag"]]["Ansprechpartner"]."' WHERE id = ";
        $sql .= $_REQUEST["CD"];
        if ( ! mysql_query($sql) ) die (mysql_error());
        echo "CD ".$CDs[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['ID'];
        echo "(".$CDs[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['Bezeichnung'].")";
        echo " wurde als ausgeliehen markiert.<br />";
      }
      */
      echo 'Drucken Sie die <a href="Lizenznummern.php?VN='.$_REQUEST["Vertragsnummer"];
      echo '&L='.$Lizenznehmer['Ansprechpartner'].
        '" target="_blank">Liste mit Seriennummern</a> für den Lizenznehmer aus.<br /><hr />';
    }
    if ( ! mysql_query("DELETE FROM T_Antraege WHERE id = ".$_REQUEST["Antrag"]) )
      echo "Fehler: ".mysql_error();
    //unset( $CDs[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]);
    //unset ( $Lizenznehmer[$_REQUEST["Antrag"]]);
    unset($_REQUEST["Antrag"]);
  }
  $query = mysql_query("SELECT ProduktID, MAX(Serialkey) AS snkey, Art " .
  		"FROM T_Lizenznummern GROUP BY Art, ProduktID");
  $Seriennummer = array();
  while ( $row = mysql_fetch_object($query))
  {
    $Seriennummer[$row->ProduktID][$row->Art] = $row->snkey;
  }
  mysql_free_result($query);
  if ( count($Gesperrt) > 0 ) {
    echo "<big>Gesperrte Produkte</big> (werden nicht ausgegeben)<br />";
    while ( list($key, $was) = each($Gesperrt) ) {
      echo $Produkte[$was].",";
    }
    echo "<br />";
    reset($Gesperrt);
  }
  $query = mysql_query("SELECT Count(*) FROM T_AktuelleAntraege");
  $row = mysql_fetch_row($query);
  mysql_free_result($query);
  if ( $row[0] > 0 )
  {
    echo '<div class="content-bold">'.$row[0].
    ' bearbeitete Verträge warten auf die Verteilung an die Kollegen.</div>';
    echo '<a href="'.$_SERVER["PHP_SELF"].'?Verteilen=1">Jetzt per Mail verteilen.</a><br >';
    echo '<a href="'.$_SERVER["PHP_SELF"].'?Verteilen=-1">Gemerkte Nummern ohne Mail löschen.</a><br />';
  }
  ?>


<h1>Ausgabe von Lizenzen an Schulangehörige (Einzellizenzen)</h1>
<?
/*
  if ( ! isset($_REQUEST["Produkt"]) && ! isset($_REQUEST["Antrag"]) && ! isset($_REQUEST["Wer"])) {

<div class="home-content-titel">Direkt erfüllbare Bestellungen</div>
<ul>
<?
 $Wer = "";
 $Ok = false;
 $anz = 0;
 while ( (list($key, $value) = each($Lizenznehmer)) && ($anz < 15) )
 {
   if ( $Wer != $value['Vertragsnummer'] ) {
     if ( $Ok )
     {
       echo '<li>';
       echo $Name.' - ('.$value['Vertragsnummer'].') <a href="Bestelluebersicht.php?Wer='.$Wer.'">Bestellung ansehen</a> - ';
       echo '<a href="?AntragNr='.$value['Vertragsnummer'].'">Bestellung bearbeiten</a></li>';
       $anz++;
     }
     $Wer = $value['Vertragsnummer'];
     $Name = $value['Name']." ".$value['Vorname']." (".
       $value["Ansprechpartner"].",".$value["Art"].")";
     $Ok = true;
   }
   if ( ! isset($CDs[$value["Produkt"]])) $Ok = false;
  }
  reset($Lizenznehmer);
?>
</ul>
<?
}
*/
?>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post" class="Formular">
<label>AntragsNr</label>
 <?php
  if ( isset($_REQUEST["Antrag"]) && is_numeric($_REQUEST["Antrag"]) 
     || isset($_REQUEST["Wer"]) && is_numeric($_REQUEST["Wer"]) ) { // || $WerID >= 0 ) {
    echo '<a href=".">Zurück zur Gesamtauswahl</a><br />';
    if ( is_numeric($_REQUEST["Wer"]) )
      $Lizenznehmer = holeLizenznehmer($_REQUEST["Wer"]);
    else
      $Lizenznehmer = holeLizenznehmer($_REQUEST["Antrag"]);
    echo $Lizenznehmer['Vorname']." ";
    echo $Lizenznehmer['Name']." (";
    echo $Lizenznehmer['Ansprechpartner']."/";
    echo $Lizenznehmer['Art'].")";
    if ( $Lizenznehmer['Bemerkungen'] != "" )
      echo "<br />Bemerkung: ".$Lizenznehmer['Bemerkungen'];
    if ( is_numeric($_REQUEST["Wer"]) ) unset($_REQUEST["Antrag"]);
  }
  /*
  if ( is_numeric($_REQUEST["Wer"]) ) {
     echo "<br />Vertragsnummer: ".$_REQUEST["Wer"];
     echo '<br /><select name="Antrag">';
     // Anträge von ID ausgeben
     $qu = mysql_query("SELECT * FROM Antrag WHERE Vertragsnummer = ".$_REQUEST["Wer"]);
     while ($r = mysql_fetch_object($qu) )
     {
       echo '<option value="'.$r->id.'">';
       echo $Produkte[$r->Produkt];
       echo '</option>';
     }
     echo "</select>";
     mysql_free_result($qu);
  }
  */
  if ( !isset($_REQUEST["Antrag"] )) {
  echo '<input type="Text" name="AntragNr" size="5" maxlength="5" />';
 }
?>
<br />
<label>Bestellungen mit vorhandenen Lizenzverträgen</label>
<select name="EinfacheAntraege[]" size="5" multiple="multiple">
<?php
  $query = mysql_query("SELECT DISTINCT T_Antraege.Vertragsnummer FROM T_Antraege " .
  		"INNER JOIN T_Lizenznehmer ON ".
    "T_Antraege.Vertragsnummer=T_Lizenznehmer.Vertragsnummer");
  while ( $row = mysql_fetch_array($query))
  {
    echo "<option>{$row["Vertragsnummer"]}</option>\n";
  }
  mysql_free_result($query);
?>
</select><br />
<?php
  $fehler = false;
  if ( isset($_REQUEST["Antrag"])) {
    echo '<br /><label>Produkt</label>';
    echo $Produkte[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']];
    echo '<input type="hidden" name="Antrag" value="'.$_REQUEST["Antrag"].'">';
    echo '<br />';
    echo '<label>Seriennummer</label>';
    if ( $Seriennummer[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['Student'] != "" ||
         $Seriennummer[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['Volume'] != "")
    {
      echo '<input type="Text" name="SN" size="50" value="';
      if ( $Seriennummer[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['Student'] != "" )
        echo $Seriennummer[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['Student'];
      else
        echo $Seriennummer[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['Volume'];
      echo '" readonly="readonly" />';
      if ( $Seriennummer[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['Student'] != "" )
        echo " (Student)";
      else
        echo " (Volume)";
    ?>
    <br />
    <label>Vertragsnummer</label>
    <input type="Text" name="Vertragsnummer" size="5" maxlength="5" value="<?=$Lizenznehmer[$_REQUEST["Antrag"]]['Vertragsnummer']?>"/>
    <?php
    }
    else
    {
      echo '<b>Keine Seriennummer eingetragen!</b>';
      echo ' Vor der Freigabe müssen Seriennummern <a href="Produkte.php?Produkt=';
      echo $Lizenznehmer[$_REQUEST["Antrag"]]['Produkt'].'">';
      echo 'eingegeben</a> werden.';
      $fehler = true;
    }
    /*
    if ( ! $_REQUEST["OhneCD"] == "v" ) {
      echo '</tr><tr><td>Ausleih-CD</td><td>';
      if ( $CDs[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['ID'] == "" ) {
        echo "<B>Es ist keine CD zur Ausleihe vorhanden!</b><br />";
        echo 'Klicken, um neue CDs <a href="Produkte.php?Produkt=';
        echo $Lizenznehmer[$_REQUEST["Antrag"]]['Produkt'];
        echo '">einzutragen</a>.<br />';
      }
      else
      {
        echo $CDs[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['ID']." - ";
        echo $CDs[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['Bezeichnung'];
        echo '<input type="hidden" name="CD" value="'.$CDs[$Lizenznehmer[$_REQUEST["Antrag"]]['Produkt']]['ID'].'">';
      }
      echo '</td></tr>';
    }
    else
      echo '<input type="hidden" name="OhneCD" value="v">';
    */
    echo '<label>Antrag ohne Bearbeitung löschen</label>' .
    		'<input type="Checkbox" name="Delete"';
    echo ' value="v" /><br />';
  }
if ( ! $fehler ) echo '<input type="Submit" value="Weiter" />'; ?>
</form>
<h1><a name="ohneAntrag" id="ohneAntrag"></a>
Lizenzvergabe ohne Antrag (ohne CD)</h1>
<table>
<form action="<?=$_SERVER["PHP_SELF"]?>#ohneAntrag" method="post">
<tr><td>Produkt(e)</td><td><select name="Produkt[]" size="10">
<?php
 while ( list($key, $value) = each($Produkte) )
 {
   echo '<option value="'.$key.'"';
   if ( isset($_REQUEST["Produkt"]))
     if ( in_array($key,$_REQUEST["Produkt"])) echo ' selected="selected"';
   echo '>'.$value.'</option>';
 }
 ?>
</select></td></tr>
<tr><td>Seriennummer</td><td>
<? if ( isset($_REQUEST["Produkt"])) {
if ( $Seriennummer[$_REQUEST["Produkt"][0]]['Student'] != "" ||
     $Seriennummer[$_REQUEST["Produkt"][0]]['Volume'] != "")
    {
      echo '<input type="Text" name="SN" size="50" value="';
      if ( $Seriennummer[$_REQUEST["Produkt"][0]]['Student'] != "" )
        echo $Seriennummer[$_REQUEST["Produkt"][0]]['Student'];
      else
        echo $Seriennummer[$_REQUEST["Produkt"][0]]['Volume'];
      echo '" readonly="readonly" />';
      if ( $Seriennummer[$_REQUEST["Produkt"][0]]['Student'] != "" )
        echo " (Student)";
      else
        echo " (Volume)";
      echo '<input type="hidden" name="MakeAntrag" value="Ja">';
      echo '<a href=".#ohneAntrag">Anderes Produkt auswählen</a>';
    }
    else
    {
      echo '<b>Keine Seriennummer eingetragen!</b>';
      echo ' Vor der Freigabe müssen Seriennummern <a href="Produkte.php?Produkt=';
      echo $_REQUEST["Produkt"][0].'">';
      echo 'eingegeben</a> werden.';
      $fehler = true;
    }
    ?>
    </td><tr>
<td>Vertragsnummer</td><td><input type="Text" name="Vertragsnummer" size="5" maxlength="5"></td>
</tr>
  <? }
  ?>
  </td>
</tr>
<tr><td colspan="2"><input type="Submit" value="<? if (isset($_REQUEST["Produkt"]))
  echo 'Lizenz vergeben'; else echo 'Weiter'; ?>"></td></tr>
<!--<input type="hidden" name="OhneCD" value="v">-->
</form></table>


<?php
/*
<h2>CD-Ausleihe ohne Lizenznummer</h2>
<table>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
<tr>
<td>Vertragsnummer</td><td><input type="Text" name="VN" size="5" maxlength="5"></td>
</tr>
<tr><td>CD-Nummer</td><td><input type="Text" name="CDNr" size="5" maxlength="5">
<tr><td colspan="2"><input type="Submit" value="CD ausleihen"></td></tr>
</form>
</table>
*/
?>
<script language="javascript">
document.getElementsByName("AntragNr")[0].focus();
</script>
</td></tr>
<?php
include("include/footer.inc.php");
?>