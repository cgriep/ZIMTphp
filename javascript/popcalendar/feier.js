<!--

 function div(dividend,divisor)
 //liefert ganzzahliges Ergebnis der Division
 {
  return((dividend-(dividend%divisor))/divisor);
 }

 function calc(datum)
 // Berechnung der Feiertage
 {
  einzeldatum = datum.value.split(".");
  century=eval(einzeldatum[2].substr(0,2)); //eval(document.easter.century[document.easter.century.selectedIndex].value);
  year=eval(einzeldatum[2].substr(2,2)); //eval(document.easter.year[document.easter.year.selectedIndex].value);
  alert(century+" "+year);
  if (century==16)
   {d=10; m=202;}
  else if (century==17)
   {d=11; m=203;}
  else if (century==18)
   {d=12; m=203;}
  else if (century==19)
   {d=13; m=204;}
  else if (century==20)
   {d=13; m=204;}
  else if (century==21)
   {d=14; m=204;}
  else if (century==22)
   {d=15; m=205;}
  else if (century==23)
   {d=16; m=206;}
   j=century*100+year;
  q=div(j,4);
  a=j%19;
  b=(m-11*a)%30;
  if (((century==16)||(century==19)||(century==20)||(century==21)||(century==22))&&(b>=28))
   {b--;}
  c=(j+q+b-d)%7;

  em=0;
  am=2;
  wm=2;
  ed=28+b-c;
  ad=ed-22;
  wd=ed-12;
  if (ed>31)
   {em=1; ed=ed-31;}
  if (ad>31)
   {am=3; ad=ad-31;}
  if (wd>31)
   {wm=3; wd=wd-31;}

  months=new Array(". März",". April",". Mai",". Juni");
  easter_str=ed+months[em];
  ascens_str=ad+months[am];
  with_str=wd+months[wm];

  document.easter.eastersunday.value=easter_str;
  document.easter.ascension.value=ascens_str;
  document.easter.withsunday.value=with_str;
 }

//-->