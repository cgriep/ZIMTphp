var gotoString = "aktueller Monat";
var todayString = "heute ist";
var weekString = "KW";
monthName = new Array("Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");
startAt = 1;    // since we always start at 1, we only change the else part :-) lazy
if (startAt==0)
{
  dayName = new Array	("Mo","Di","Mi","Do","Fr","Sa","So");
}
else
{
  dayName = new Array	("Mo","Di","Mi","Do","Fr","Sa","So");
}
var	fixedX = -1; // x position (-1 if to appear below control)
var	fixedY = -1; // y position (-1 if to appear below control)
var showWeekNumber = 1; // 0 - don't show; 1 - show
var showToday = 1;  // 0 - don't show; 1 - show
var imgDir = "img/Kalender/";   // directory for images ... e.g. var imgDir="/img/"
var scrollLeftMessage = "Vorangegangener Monat. Gedrückt halten zum automatischen Blättern.";
var scrollRightMessage = "Nächster Monat. Gedrückt halten zum automatischen Blättern.";
var selectMonthMessage = "Monat auswählen.";
var selectYearMessage = "Jahr auswählen.";
var selectDateMessage = "Datum [date] auswählen."; // do not replace [date], it will be replaced by date.
var	crossobj, crossMonthObj, crossYearObj, monthSelected, yearSelected, dateSelected,
    omonthSelected, oyearSelected, odateSelected, monthConstructed, yearConstructed,
    intervalID1, intervalID2, timeoutID1, timeoutID2, ctlToPlaceValue, ctlNow,
    dateFormat, nStartingYear;
var	bPageLoaded=false;
var	ie=document.all;
var	dom=document.getElementById;
var	ns4=document.layers;
var	today =	new	Date();
var	dateNow	 = today.getDate();
var	monthNow = today.getMonth();
var	yearNow	 = today.getYear();
var	imgsrc = new Array("drop1.gif","drop2.gif","left1.gif","left2.gif","right1.gif","right2.gif");
var	img	= new Array();
var bShow = false;
var cal_onClickId = null;
var cal_onKeyId = null;

function HolidayRec (d, m, y, desc)
{
  this.d = d
  this.m = m
  this.y = y
  this.desc = desc
}
var HolidaysCounter = 0
var Holidays = new Array()
function addHoliday (d, m, y, desc)
{
  Holidays[HolidaysCounter++] = new HolidayRec ( d, m, y, desc )
}
if (dom)
{
  s = "";
  for (i=0;i<imgsrc.length;i++) {
    img[i] = new Image;
    img[i].src= imgDir + imgsrc[i];
    s += imgDir + imgsrc[i]+"\r\n";
  }
  document.write ("<div onclick='bShow=true' id='calendar' class='div-style'><table width="+
    ((showWeekNumber==1)?260:230)+
    " class='table-style' cellspacing='0' cellpadding='0'><tr class='title-background-style' >"+
    "<td><table width='"+((showWeekNumber==1)?268:238)+
    "' cellspacing='0' cellpadding='0'><tr><td class='title-style'><b><span id='caption'>"+
    "</span></b></td><td align=right class='title-style'><a href='javascript:hideCalendar()'>"+
    "<img src='"+imgDir+"close.gif' width='15' height='13' border='0' "+
    "alt='Schliessen'></a></td></tr></table></td></tr><tr><td "+
    "class='body-style'><span id='content'></span></td></tr>")
  if (showToday==1) {
    document.write ("<tr class='today-style'><td class='today-style'><span id='lblToday'></span></td></tr>")
  }
  document.write ("</table></div><div id='selectMonth' class='div-style'></div>"+
  "<div id='selectYear' class='div-style'></div>");
}
function swapImage(srcImg, destImg){
  if (ie){
    document.getElementById(srcImg).setAttribute("src",imgDir + destImg)
  }
}
function init()
{
  if (!ns4)
  {
    if (!ie)
    {
      yearNow += 1900;
    }
    crossobj=(dom)?document.getElementById("calendar").style : ie? document.all.calendar : document.calendar;
    hideCalendar(true);
    crossMonthObj=(dom)?document.getElementById("selectMonth").style : ie? document.all.selectMonth	: document.selectMonth;
    crossYearObj=(dom)?document.getElementById("selectYear").style : ie? document.all.selectYear : document.selectYear;
    monthConstructed=false;
    yearConstructed=false;
    if (showToday==1)
    {
      document.getElementById("lblToday").innerHTML =	todayString +
        " <a class='today-style' onmousemove='window.status=\""+gotoString+
        "\"' onmouseout='window.status=\"\"' title='"+gotoString+
        "' href='javascript:monthSelected=monthNow;yearSelected=yearNow;constructCalendar();'>"+
        dayName[(today.getDay()-startAt==-1)?6:(today.getDay()-startAt)]+", " + dateNow + " " +
        monthName[monthNow].substring(0,3)	+ "	" +	yearNow	+ "</a>";
    }
    sHTML1= "<span id='spanLeft'  class='title-control-normal-style' onmouseover='swapImage(\"changeLeft\",\"left2.gif\");this.className=\"title-control-select-style\";window.status=\""+scrollLeftMessage+"\"' onclick='javascript:decMonth()' onmouseout='clearInterval(intervalID1);swapImage(\"changeLeft\",\"left1.gif\");this.className=\"title-control-normal-style\";window.status=\"\"' onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout(\"StartDecMonth()\",500)'	onmouseup='clearTimeout(timeoutID1);clearInterval(intervalID1)'>&nbsp<IMG id='changeLeft' SRC='"+imgDir+"left1.gif' width=10 height=11 BORDER=0>&nbsp</span>&nbsp;"; sHTML1+="<span id='spanRight' class='title-control-normal-style' onmouseover='swapImage(\"changeRight\",\"right2.gif\");this.className=\"title-control-select-style\";window.status=\""+scrollRightMessage+"\"' onmouseout='clearInterval(intervalID1);swapImage(\"changeRight\",\"right1.gif\");this.className=\"title-control-normal-style\";window.status=\"\"' onclick='incMonth()' onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout(\"StartIncMonth()\",500)'	onmouseup='clearTimeout(timeoutID1);clearInterval(intervalID1)'>&nbsp<IMG id='changeRight' SRC='"+imgDir+"right1.gif'	width=10 height=11 BORDER=0>&nbsp</span>&nbsp"; sHTML1+="<span id='spanMonth' class='title-control-normal-style' onmouseover='swapImage(\"changeMonth\",\"drop2.gif\");this.className=\"title-control-select-style\";window.status=\""+selectMonthMessage+"\"' onmouseout='swapImage(\"changeMonth\",\"drop1.gif\");this.className=\"title-control-normal-style\";window.status=\"\"' onclick='popUpMonth()'></span>&nbsp;"; sHTML1+="<span id='spanYear'  class='title-control-normal-style' onmouseover='swapImage(\"changeYear\",\"drop2.gif\");this.className=\"title-control-select-style\";window.status=\""+selectYearMessage+"\"'	onmouseout='swapImage(\"changeYear\",\"drop1.gif\");this.className=\"title-control-normal-style\";window.status=\"\"'	onclick='popUpYear()'></span>&nbsp;";
    document.getElementById("caption").innerHTML  = sHTML1;
    bPageLoaded=true;
  }
}
var calendarShown = false;
function hideCalendar(comesFromInit)	{
  crossobj.visibility="hidden";
  if (crossMonthObj != null){crossMonthObj.visibility="hidden"}
  if (crossYearObj !=	null){crossYearObj.visibility="hidden"}
  if (comesFromInit!==true && calendarShown==true) {
    showMagic();
  }
  calendarShown = false;
}
function padZero(num) {
  return (num	< 10)? '0' + num : num ;
}
function constructDate(d,m,y)
{
sTmp = dateFormat;
sTmp = sTmp.replace	("dd","<e>"); sTmp = sTmp.replace	("d","<d>");
sTmp = sTmp.replace	("<e>",padZero(d)); sTmp = sTmp.replace	("<d>",d);
sTmp = sTmp.replace	("mmm","<o>"); sTmp = sTmp.replace	("mm","<n>");
sTmp = sTmp.replace	("m","<m>"); sTmp = sTmp.replace	("<m>",m+1);
sTmp = sTmp.replace	("<n>",padZero(m+1)); sTmp = sTmp.replace	("<o>",monthName[m]);
return sTmp.replace ("yyyy",y);
}
function closeCalendar() {
var	sTmp;
hideCalendar();
ctlToPlaceValue.value =	constructDate(dateSelected,monthSelected,yearSelected);
}
function StartDecMonth()
{
intervalID1=setInterval("decMonth()",80)
}
function StartIncMonth()
{
intervalID1=setInterval("incMonth()",80)
}
function incMonth () {
monthSelected++
if (monthSelected>11) {
monthSelected=0;
yearSelected++;
}
constructCalendar();
}
function decMonth () {
monthSelected--
if (monthSelected<0) {
monthSelected=11;
yearSelected--;
}
constructCalendar();
}
function constructMonth() {
popDownYear()
if (!monthConstructed) {
sHTML =	""
for	(i=0; i<12;	i++) {
sName =	monthName[i];
if (i==monthSelected){
sName =	"<B>" +	sName +	"</B>"
}
sHTML += "<tr><td class='dropdown-normal-style' id='m" + i +
  "' onmouseover='this.className=\"dropdown-select-style\"' "+
  "onmouseout='this.className=\"dropdown-normal-style\"' onclick='monthConstructed=false;"+
  "monthSelected=" + i + ";constructCalendar();popDownMonth();event.cancelBubble=true'>"+
  "&nbsp;" + sName + "&nbsp;</td></tr>";
}
document.getElementById("selectMonth").innerHTML = "<table width=70	class='dropdown-style' cellspacing=0 onmouseover='clearTimeout(timeoutID1)'	onmouseout='clearTimeout(timeoutID1);timeoutID1=setTimeout(\"popDownMonth()\",100);event.cancelBubble=true'>" +	sHTML +	"</table>";
monthConstructed=true;
}
}
function popUpMonth() {
  constructMonth();
  crossMonthObj.visibility = (dom||ie)? "visible"	: "show";
  crossMonthObj.left = parseInt(crossobj.left) + 50;
  crossMonthObj.top = parseInt(crossobj.top) + 26;
  hideMagic("selectMonth");
}
function popDownMonth()	{
crossMonthObj.visibility= "hidden";
}
function incYear() {
for	(i=0; i<7; i++){
newYear	= (i+nStartingYear)+1;
if (newYear==yearSelected)
{ txtYear =	"&nbsp;<B>"	+ newYear +	"</B>&nbsp;" }
else
{ txtYear =	"&nbsp;" + newYear + "&nbsp;" }
document.getElementById("y"+i).innerHTML = txtYear;
}
nStartingYear ++;
bShow=true;
}
function decYear() {
for	(i=0; i<7; i++){
newYear	= (i+nStartingYear)-1;
if (newYear==yearSelected)
{ txtYear =	"&nbsp;<B>"	+ newYear +	"</B>&nbsp;" }
else
{ txtYear =	"&nbsp;" + newYear + "&nbsp;" }
document.getElementById("y"+i).innerHTML = txtYear;
}
nStartingYear --;
bShow=true;
}
function selectYear(nYear) {
yearSelected=parseInt(nYear+nStartingYear);
yearConstructed=false;
constructCalendar();
popDownYear();
}
function constructYear() {
popDownMonth();
sHTML =	"";
if (!yearConstructed) {
sHTML =	"<tr><td class='dropdown-normal-style' align='center'	onmouseover='this.className=\"dropdown-select-style\"' onmouseout='clearInterval(intervalID1);this.className=\"dropdown-normal-style\"' onmousedown='clearInterval(intervalID1);intervalID1=setInterval(\"decYear()\",30)' onmouseup='clearInterval(intervalID1)'>-</td></tr>";
j =	0;
nStartingYear =	yearSelected-3;
for	(i=(yearSelected-3); i<=(yearSelected+3); i++) {
sName =	i;
if (i==yearSelected){
sName =	"<B>" +	sName +	"</B>";
}
sHTML += "<tr><td class='dropdown-normal-style' id='y" + j + "' onmouseover='this.className=\"dropdown-select-style\"' onmouseout='this.className=\"dropdown-normal-style\"' onclick='selectYear("+j+");event.cancelBubble=true'>&nbsp;" + sName + "&nbsp;</td></tr>";
j ++;
}
sHTML += "<tr><td class='dropdown-normal-style' align='center' onmouseover='this.className=\"dropdown-select-style\"' onmouseout='clearInterval(intervalID2);this.className=\"dropdown-normal-style\"' onmousedown='clearInterval(intervalID2);intervalID2=setInterval(\"incYear()\",30)'	onmouseup='clearInterval(intervalID2)'>+</td></tr>"; document.getElementById("selectYear").innerHTML	= "<table width=44 class='dropdown-style' onmouseover='clearTimeout(timeoutID2)' onmouseout='clearTimeout(timeoutID2);timeoutID2=setTimeout(\"popDownYear()\",100)' cellspacing=0>"	+ sHTML	+ "</table>";
yearConstructed	= true;
}
}
function popDownYear() {
  clearInterval(intervalID1);
  clearTimeout(timeoutID1);
  clearInterval(intervalID2);
  clearTimeout(timeoutID2);
  crossYearObj.visibility= "hidden";
}
function popUpYear() {
  var	leftOffset;
  constructYear();
  crossYearObj.visibility = (dom||ie)? "visible" : "show";
  leftOffset = parseInt(crossobj.left) + document.getElementById("spanYear").offsetLeft;
  if (ie)
  {
    leftOffset += 6;
  }
  crossYearObj.left = leftOffset;
  crossYearObj.top = parseInt(crossobj.top) + 26;
}
function WeekNbr(today)
{
  Year = takeYear(today);
  Month = today.getMonth();
  Day = today.getDate();
  now = Date.UTC(Year,Month,Day+1,0,0,0);
  var Firstday = new Date();
  Firstday.setYear(Year);
  Firstday.setMonth(0);
  Firstday.setDate(1);
  then = Date.UTC(Year,0,1,0,0,0);
  var Compensation = Firstday.getDay();
  if (Compensation > 3) Compensation -= 4;
  else Compensation += 3;
  NumberOfWeek =  Math.round((((now-then)/86400000)+Compensation)/7);
  return NumberOfWeek;
}
function takeYear(theDate)
{
x = theDate.getYear();
var y = x % 100;
y += (y < 38) ? 2000 : 1900;
return y;
}

function calcFeiertage(year)
 // Berechnung der Feiertage
{
  century=eval((year+" ").substr(0,2)); //eval(document.easter.century[document.easter.century.selectedIndex].value);
  year=eval((year+" ").substr(2,2)); //eval(document.easter.year[document.easter.year.selectedIndex].value);
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
  q=(j-(j%4))/4;
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
  if (wd==31 && wm==2)
   {wm=3; wd=wd-31;}
  pf=new Date(year,em+2,ed);
  
  tag=pf.getTime()+39*24*60*60*1000;
  pf.setTime(tag);
  HolidaysCounter = 0;
  addHoliday(ed+1,em+3,0,"Ostermontag");
  addHoliday(ed-2,em+3,0,"Karfreitag");
  addHoliday(pf.getDate(),pf.getMonth()+1,0,"Himmelfahrt");  // wd+1,wm+3
  //addHoliday(ad,am+3,0,"Himmelfahrt");
  tag=pf.getTime()+11*24*60*60*1000;
  pf.setTime(tag);
  addHoliday(pf.getDate(),pf.getMonth()+1,0,"Pfingstmontag");  // wd+1,wm+3
  tag=pf.getTime()+10*24*60*60*1000;
  pf.setTime(tag);
  addHoliday(pf.getDate(),pf.getMonth()+1,0,"Fronleichnam");  // wd+1,wm+3
  addHoliday(25,12,0, "Weihnachten");
  addHoliday(26,12,0, "Weihnachten");
  addHoliday(1,1,0, "Neujahr");
  addHoliday(1,5,0, "1. Mai");
  addHoliday(3,10,0, "Dt. Einheit");

}

function constructCalendar () {
calcFeiertage(yearSelected);
var dateMessage
var	startDate =	new	Date (yearSelected,monthSelected,1)
var	endDate	= new Date (yearSelected,monthSelected+1,1);
endDate	= new Date (endDate	- (24*60*60*1000));
numDaysInMonth = endDate.getDate()
datePointer	= 0
dayPointer = startDate.getDay() - startAt
if (dayPointer<0)
{
dayPointer = 6
}
sHTML =	"<table	border=0 class='body-style'><tr>";
if (showWeekNumber==1)
{
sHTML += "<td width='27' class='bgw'><b>" + weekString +
  "</b></td><td width='1' rowspan='7' class='weeknumber-div-style'><img src='"+
  imgDir+"divider.gif' width='1'></td>";
}
for	(i=0; i<7; i++)	{ sHTML += "<td width='27' align='right' class='bgw'><B>"+ dayName[i]+"</B></td>";
}
sHTML +="</tr><tr>"
if (showWeekNumber==1)
{
sHTML += "<td align='right' class='bgw'>" + WeekNbr(startDate) + "&nbsp;</td>";
}
for	( var i=1; i<=dayPointer;i++ )
{
sHTML += "<td class='bgw'>&nbsp;</td>"
}
for	( datePointer=1; datePointer<=numDaysInMonth; datePointer++ )
{
dayPointer++;
sHTML += "<td align='right' class='bgw'>"
var sStyle="normal-day-style"; //regular day
if ((datePointer==dateNow)&&(monthSelected==monthNow)&&(yearSelected==yearNow)) //today
{ sStyle = "current-day-style"; }
else
if	(dayPointer % 7 == (startAt * -1) +1 ||     //end-of-the-week day
dayPointer % 7 == (startAt * -1) +7 )   // for us saturday is weekend too :-)
{ sStyle = "end-of-weekday-style"; }
if ((datePointer==odateSelected) &&	(monthSelected==omonthSelected)	&& (yearSelected==oyearSelected))
{ sStyle += " selected-day-style"; }
sHint = ""
for (k=0;k<HolidaysCounter;k++)
{
if ((parseInt(Holidays[k].d)==datePointer)&&(parseInt(Holidays[k].m)==(monthSelected+1)))
{
if ((parseInt(Holidays[k].y)==0)||((parseInt(Holidays[k].y)==yearSelected)&&(parseInt(Holidays[k].y)!=0)))
{
sStyle += " holiday-style";
sHint+=sHint==""?Holidays[k].desc:"\n"+Holidays[k].desc
}
}
}
var regexp= /\"/g
sHint=sHint.replace(regexp,"&quot;")
dateMessage = "onmousemove='window.status=\""+selectDateMessage.replace("[date]",constructDate(datePointer,monthSelected,yearSelected))+"\"' onmouseout='window.status=\"\"' "
sHTML += "<a class='"+sStyle+"' "+dateMessage+" title=\"" + sHint + "\" href='javascript:dateSelected="+datePointer+";closeCalendar();'>&nbsp;" + datePointer + "&nbsp;</a>";
sHTML += ""
if ((dayPointer+startAt) % 7 == startAt) {
sHTML += "</tr><tr>"
if ((showWeekNumber==1)&&(datePointer<numDaysInMonth))
{
sHTML += "<td align='right' class='bgw'>" + (WeekNbr(new Date(yearSelected,monthSelected,datePointer+1))) + "&nbsp;</td>";
}
}
}
document.getElementById("content").innerHTML   = sHTML;
document.getElementById("spanMonth").innerHTML = "&nbsp;" +	monthName[monthSelected] + "&nbsp;<IMG id='changeMonth' SRC='"+imgDir+"drop1.gif' WIDTH='12' HEIGHT='10' BORDER=0>"; document.getElementById("spanYear").innerHTML =	"&nbsp;" + yearSelected	+ "&nbsp;<IMG id='changeYear' SRC='"+imgDir+"drop1.gif' WIDTH='12' HEIGHT='10' BORDER=0>";
}
function popUpCalendar(ctl, ctl2, format) {
  var leftpos=0;
  var toppos=0;
  if (bPageLoaded)
  {
    if ( crossobj.visibility ==	"hidden" ) {
      ctlToPlaceValue	= ctl2
      dateFormat=format;
      formatChar = " "
      aFormat	= dateFormat.split(formatChar)
      if (aFormat.length<3)
      {
        formatChar = "/"
        aFormat	= dateFormat.split(formatChar)
        if (aFormat.length<3)
        {
          formatChar = "."
          aFormat	= dateFormat.split(formatChar)
          if (aFormat.length<3)
          {
            formatChar = "-"
            aFormat	= dateFormat.split(formatChar)
            if (aFormat.length<3)
            {
              formatChar=""
            }
          }
        }
      }
      tokensChanged =	0
      if ( formatChar	!= "" )
      {
        aData =	ctl2.value.split(formatChar)
        for	(i=0;i<3;i++)
        {
          if ((aFormat[i]=="d") || (aFormat[i]=="dd"))
          {
            dateSelected = parseInt(aData[i], 10)
            tokensChanged ++
          }
          else if ((aFormat[i]=="m") || (aFormat[i]=="mm"))
          {
            monthSelected = parseInt(aData[i], 10) - 1
            tokensChanged ++
          }
          else if (aFormat[i]=="yyyy")
          {
            yearSelected = parseInt(aData[i], 10);
            tokensChanged ++
          }
          else if (aFormat[i]=="mmm")
          {
            for (j=0; j<12; j++)
            {
              if (aData[i]==monthName[j])
              {
                monthSelected=j;
                tokensChanged ++
              }
            }
          }
        }
      }
      if ((tokensChanged!=3)||isNaN(dateSelected)||isNaN(monthSelected)||isNaN(yearSelected))
      {
        dateSelected = dateNow;
        monthSelected = monthNow;
        yearSelected = yearNow;
      }
      odateSelected=dateSelected ;
      omonthSelected=monthSelected;
      oyearSelected=yearSelected;
      aTag = ctl
      do {
        aTag = aTag.offsetParent;
        leftpos += aTag.offsetLeft;
        toppos += aTag.offsetTop;
      } while(aTag.tagName!="BODY");
      crossobj.left = fixedX==-1 ? ctl.offsetLeft + leftpos : fixedX;
      crossobj.top = fixedY==-1 ? ctl.offsetTop + toppos + ctl.offsetHeight + 2 : fixedY;
      constructCalendar (1, monthSelected, yearSelected);
      crossobj.visibility=(dom||ie)? "visible" : "show"
      calendarShown = true;
      hideMagic("calendar");
      bShow = true;
    }
  }
  else
  {
    init();
    popUpCalendar(ctl, ctl2, format)
  }
  if (cal_onClickId!=null) {
    events.unregister(cal_onClickId);
    cal_onClickId = null;
  }
  if (cal_onKeyId!=null) {
    events.unregister(cal_onKeyId);
    cal_onKeyId = null;
  }
  cal_onClickId = events.register("onclick","hidecal2();");
  //cal_onKeyId = events.register("onkeypress", "hidecal2();");
}
function hidecal1 ()
{
  if (document.all && event.keyCode==27)
  {
    hideCalendar()
  }
}
function hidecal2 ()
{
  if (!bShow) {
    hideCalendar();
  }
  bShow = false;
}

window.document.onload=init()

if (typeof(events)=='undefined') {
  document.write(
    '<script src="javascript/func.js"></script>' +"\r\n"+
    '<script src="javascript/env.js"></script>' +"\r\n"+
    '<script src="javascript/object.js"></script>' +"\r\n" +
'<script src="javascript/mouse.js"></script>' +"\r\n"+
'<script src="javascript/events.js"></script>'+
    ""
  );
}
