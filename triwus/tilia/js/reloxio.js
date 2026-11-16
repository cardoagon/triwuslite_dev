var timerID=new Array()
var timerRunning=new Array()
function startclock(f_intervalo,intervalo,id_clock){if(id_clock==null)id_clock=timerID.lenght+1;stopclock(id_clock)
timerID[id_clock]=setInterval(f_intervalo,intervalo)
timerRunning[id_clock]=!0}
function stopclock(id_clock){if(timerRunning[id_clock])clearInterval(timerID[id_clock]);timerRunning[id_clock]=!1}