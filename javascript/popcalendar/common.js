function removeConfirm( url , message )
{
  rowId = url.replace(/^.*\?/,"").replace(/=/,"");
  tds = document.getElementById(name).getElementsByTagName("td");
  for( i=0 ; i< tds.length ; i++ )
  {
    Colors[i] = tds[i].style.backgroundColor;
  }
  markRow( rowId );
  if( !message )
    message = "Sind Sie sicher, daß Sie diesen Eintrag löschen wollen?";
  if( confirm( message ) )
  {
    markRow( rowId , "red" );
    window.location = url;
  }
  else
  {
    markRow( rowId , "white" );
  }
}
function markRow( name , color, colors )
{
  tds = document.getElementById(name).getElementsByTagName("td");
  if( !color )
    color = "yellow";
  for( i=0 ; i< tds.length ; i++ )
  {
    if ( colors )
      tds[i].style.backgroundColor = colors[i];
    else
      tds[i].style.backgroundColor = color;
  }
}
function autoCorrectTime( formName , inputField )
{
ref = document.forms[formName][inputField];
time = ref.value;
newTime = time;
if( time.split(":").length < 2 )
{
var tmpTime = time;
var time = new Array();
for( i=0 ; i<tmpTime.length ; i++ )
time[i] = tmpTime.substring(i,i+1);
newTime = 0;
if( time.length == 1 )
{
newTime = "0"+time[0]+":00";
}
if( time.length == 2 )
{
if( parseInt(time[0]+time[1]) > 23 )
newTime = "0"+time[0]+":0"+time[1];
else
newTime = time[0]+time[1]+":00";
}
if( time.length == 3 )
{
if( parseInt(time[0]+time[1]) > 23 )
{
if( parseInt(time[1]+time[2]) <60 )
newTime = "0"+time[0]+":"+time[1]+time[2];
else
newTime = 0;
}
else
{
newTime = time[0]+time[1]+":0"+time[2];
}
}
if( time.length == 4 )
{
if( parseInt(time[0]+time[1]) < 23 && parseInt(time[2]+time[3]) < 60 )
{
newTime = time[0]+time[1]+":"+time[2]+time[3];
}
else
{
newTime = 0;
}
}
}
if( !newTime )
newTime = "00:00";
ref.value = newTime;
}

function autoCorrectDate( formName , inputField , setTodayIfEmpty )
{
  ref = document.forms[formName][inputField];
  date = ref.value;
  if( setTodayIfEmpty == true && date=="" )
  {
    month = new Date().getMonth()+1;
    year = new Date().getFullYear();
    day = new Date().getDate();
    ref.value = day+"."+month+"."+year;
    return;
  }
  newDate = date;
  month = new Date().getMonth()+1;
  year = new Date().getFullYear();
  if( date.split(".").length == 1 )
  {
    var tmpDate = date;
    var date = new Array();
    for( i=0 ; i<tmpDate.length ; i++ )
      date[i] = tmpDate.substring(i,i+1);
    if( date.length == 1 ) {
      newDate = "0"+date[0]+"."+month+"."+year;
    }
    if (date.length == 2) {
      if (date[0] > 3) {
        newDate = "0"+date[0]+".0"+date[1]+"."+year;
      } else {
        if (parseInt(date[0]+date[1]) < 32) {
          newDate = date[0]+date[1]+"."+month+"."+year;
        } else {
          date[2] = date[1];
          date[1] = date[0];
          date[0] = 0;
        }
      }
    }
    if( date.length == 3 ) {
      if (date[2] != 0) {
        newDate = date[0]+date[1]+".0"+date[2]+"."+year;
      } else {
        newDate = 0+date[0]+"."+date[1]+date[2]+"."+year;
      }
    }
    if (date.length == 4) {
      newDate = date[0]+date[1]+"."+date[2]+date[3]+"."+year;
    }
    if (date.length == 5) {
      newDate = date[0]+date[1]+".0"+date[2]+".20"+date[3]+date[4];
    }
    if (date.length == 6) {
      newDate = date[0]+date[1]+"."+date[2]+date[3]+".20"+date[4]+date[5];
    }
  } else {
    tmpDate = date.split(".");
    if (tmpDate.length==2) {
      if (parseInt(tmpDate[0]) < 32 && parseInt(tmpDate[1]) < 13) {
        newDate = tmpDate[0]+"."+tmpDate[1]+"."+year;
      }
    } else {
      if (tmpDate[2].length == 2) {
        newDate = tmpDate[0]+"."+tmpDate[1]+".20"+tmpDate[2];
      }
      if (tmpDate[2].length == 1) {
        newDate = tmpDate[0]+"."+tmpDate[1]+".200"+tmpDate[2];
      }
    }
  }
  ref.value = newDate;
}
