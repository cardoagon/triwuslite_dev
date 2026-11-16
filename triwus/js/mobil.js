function triwusmobil_axustar_zoom(){var w1=screen.availWidth;var w2=document.getElementById("dbody").scrollWidth;if(w2>500)w2=500;var z=Math.round((w1/w2)*100)/100;if(z>1)z=1;document.body.style.zoom=Math.round(z*100)+"%"}
function triwusmobil_menu_app(){var t=document.getElementById("tbody");var m=document.getElementById("menu_app_div");var td=document.getElementById("menu_app_td");if(m.style.display=="none"){t.width="141%";td.width="22%";m.style.display="block"}else{t.width="100%";td.width="0";m.style.display="none"}
tilia_scroll_top()}
function triwusmobil_menu_app_2(){var tr=document.getElementById("menu_app_tr");if(tr.style.display=="none"){tr.style.display="block"}else{tr.style.display="none"}
tilia_scroll_top()}
function triwusmobil_buscador_app(){var tr=document.getElementById("buscador_app_tr");if(tr.style.display=="none"){tr.style.display="block"}else{tr.style.display="none"}
window.scroll(0,0)}
function triwusmobil_cidioma_app(){var tr=document.getElementById("cidioma_app_tr");if(tr.style.display=="none"){tr.style.display="block"}else{tr.style.display="none"}
window.scroll(0,0)}
function galeria_iniciar_fotos(){}(function(doc,win){'use strict'
if(typeof doc.createEvent!=='function')return!1
var useJquery=typeof jQuery!=='undefined',msEventType=function(type){var lo=type.toLowerCase(),ms='MS'+type
return navigator.msPointerEnabled?ms:lo},debounce=function(fn,delay){var t
return function(){var args=arguments
clearTimeout(t)
t=setTimeout(function(){fn.apply(null,args)},delay)}},touchevents={touchstart:msEventType('PointerDown')+' touchstart',touchend:msEventType('PointerUp')+' touchend',touchmove:msEventType('PointerMove')+' touchmove'},setListener=function(elm,events,callback){var eventsArray=events.split(' '),i=eventsArray.length
while(i--){elm.addEventListener(eventsArray[i],callback,!1)}},getPointerEvent=function(event){return event.targetTouches?event.targetTouches[0]:event},getTimestamp=function(){return new Date().getTime()},sendEvent=function(elm,eventName,originalEvent,data){var customEvent=doc.createEvent('Event')
customEvent.originalEvent=originalEvent
data=data||{}
data.x=currX
data.y=currY
data.distance=data.distance
if(useJquery){customEvent=$.Event(eventName,{originalEvent:originalEvent})
jQuery(elm).trigger(customEvent,data)}
if(customEvent.initEvent){for(var key in data){customEvent[key]=data[key]}
customEvent.initEvent(eventName,!0,!0)
elm.dispatchEvent(customEvent)}
if(elm['on'+eventName])
elm['on'+eventName](customEvent)},onTouchStart=function(e){var pointer=getPointerEvent(e)
cachedX=currX=pointer.pageX
cachedY=currY=pointer.pageY
longtapTimer=setTimeout(function(){sendEvent(e.target,'longtap',e)
target=e.target},longtapThreshold)
timestamp=getTimestamp()
tapNum++},onTouchEnd=function(e){var eventsArr=[],now=getTimestamp(),deltaY=cachedY-currY,deltaX=cachedX-currX
clearTimeout(dblTapTimer)
clearTimeout(longtapTimer)
if(deltaX<=-swipeThreshold)
eventsArr.push('swiperight')
if(deltaX>=swipeThreshold)
eventsArr.push('swipeleft')
if(deltaY<=-swipeThreshold)
eventsArr.push('swipedown')
if(deltaY>=swipeThreshold)
eventsArr.push('swipeup')
if(eventsArr.length){for(var i=0;i<eventsArr.length;i++){var eventName=eventsArr[i]
sendEvent(e.target,eventName,e,{distance:{x:Math.abs(deltaX),y:Math.abs(deltaY)}})}
tapNum=0}else{if(cachedX>=currX-tapPrecision&&cachedX<=currX+tapPrecision&&cachedY>=currY-tapPrecision&&cachedY<=currY+tapPrecision){if(timestamp+tapThreshold-now>=0){sendEvent(e.target,tapNum>=2&&target===e.target?'dbltap':'tap',e)
target=e.target}}
dblTapTimer=setTimeout(function(){tapNum=0},dbltapThreshold)}},onTouchMove=function(e){var pointer=getPointerEvent(e)
currX=pointer.pageX
currY=pointer.pageY},swipeThreshold=win.SWIPE_THRESHOLD||100,tapThreshold=win.TAP_THRESHOLD||150,dbltapThreshold=win.DBL_TAP_THRESHOLD||200,longtapThreshold=win.LONG_TAP_THRESHOLD||1000,tapPrecision=win.TAP_PRECISION/2||60/2,justTouchEvents=win.JUST_ON_TOUCH_DEVICES,tapNum=0,currX,currY,cachedX,cachedY,timestamp,target,dblTapTimer,longtapTimer
setListener(doc,touchevents.touchstart+(justTouchEvents?'':' mousedown'),debounce(onTouchStart,1))
setListener(doc,touchevents.touchend+(justTouchEvents?'':' mouseup'),debounce(onTouchEnd,1))
setListener(doc,touchevents.touchmove+(justTouchEvents?'':' mousemove'),debounce(onTouchMove,1))}(document,window))