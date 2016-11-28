<?php
$DBname        = "oszimt";
$DBhost        = "localhost";

//-------------USER----------
$DBuser         = "oszintern";
$DBpasswd       = "qBEj8h";

//RECHTE:
//DB oszimt:    T_FreiTage, T_RaumSperre, T_StuPla, T_StuPlaDaten, T_Turnus, T_Woche, T_WocheTurnus->SELECT
//DB oszimt:    T_RaumReservierung->SELECT,INSERT,UPDATE,DELETE
//DB confixx:   email(prefix,domain)->SELECT
$DBuserIntern   = "oszStuPlaIntern";
$DBpasswdIntern = "TG56hjvF3G29";

//RECHTE:
//DB oszimt:   T_StuPla, T_StuPlaDaten   ->SELECT
//DB oszimt:   T_Schueler (Tutor,Klasse) ->SELECT
$DBuserOffen   = "oszStuPlaExtern";
$DBpasswdOffen = "U4Txy56GhjwL";
//-----------ENDE USER-------
?>