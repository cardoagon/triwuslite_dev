/*************************************************

    Tilia Framework v.0

    basico.js

    Author: Carlos Domingo Arias González

    Copyright (C) 2011

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation version 3.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/ at http://www.gnu.org/licenses/>.

*************************************************/

//* evita submit al pulsar enter en un input[type=text]
/*
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[type=text]').forEach( node => node.addEventListener('keypress', e => {
    if(e.keyCode == 13) {
      e.preventDefault();
    }
  }))
});
*/

// Adding trim function to String object

if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, '');
  }
}



var vm_IE    = (navigator.userAgent.indexOf("MSvm_IE") >= 0);

var cnome    = ";";
var csubnome = "*";

//~ var tilia_onsubmit = false;

function validacion_envia(confirma) {
  if (confirma == undefined) return true;

  if (confirma != "") return window.confirm(confirma);

  return true;
}

function enviaEventoSubmit_str(idev, tipo_evento, confirma, ancla) {
//~ console.log(idev, tipo_evento, confirma, ancla);

  if (idev == null) return false;


  if (tipo_evento == null) tipo_evento = "nulo";
  if (confirma    == null) confirma    = "";
  if (ancla       == null) ancla       = "";

  var fn = nome_formulario();

  if (!validacion_envia(confirma)) return false;

  if (ancla != "") document.forms[fn].action = document.forms[fn].action + "#" + ancla;

  document.getElementById(fn + cnome + "evento").value = idev + csubnome + "tipo=" + tipo_evento + csubnome + "ajax=0";


  document.forms[fn].method = "post";

  document.forms[fn].submit();

  
  document.getElementById(idev).disabled = true; //* deshabilitamos obxeto para bloquear chamadas repetitivas.
}

function enviaEventoSubmit(obxeto, tipo_evento, confirma, ancla) {
  if (obxeto == null) return false;

  return enviaEventoSubmit_str(obxeto.id, tipo_evento, confirma, ancla);
}

function enviaEventoMime(obxeto, tipo_evento, confirma) {
  if (obxeto == null) return false;

  if (tipo_evento == null) tipo_evento = "nulo";
  if (confirma    == null) confirma = "";

  if (!validacion_envia(confirma)) return false;

  var fn   = nome_formulario();
  var idev = obxeto.id;

  document.getElementById(fn + cnome + "evento").value = idev + csubnome + "tipo=" + tipo_evento + csubnome + "ajax=0";



  var action_aux = document.forms[fn].action;

  document.forms[fn].action = action_aux + "?mime=1";


  document.forms[fn].method = "post";

  document.forms[fn].submit();

  document.forms[fn].action = action_aux;

  //~ console.log(document.forms[fn]);
}

function nome_formulario() {
  return document.forms[0].name;
}

//---------------------------

var tilia_rlx_scroll; //* reloxio

function tilia_scroll_top(s, v, t) {
  if (s == null) s = -75; //* salto, (px)
  if (v == null) v = 10;  //* velocidade, (mseg)
  if (t == null) t = 0;   //* top, (px)

  if (document.body.scrollTop > t || document.documentElement.scrollTop > t) {
    window.scrollBy(0, s);

    var f = function() { tilia_scroll_top(s, v, t); };

    tilia_rlx_scroll = setTimeout(f, v);
  }
  else
    clearTimeout(tilia_rlx_scroll);
}

function tilia_cursor(cursor_css, o) {
  if (cursor_css == null) cursor_css = "initial";

  if (o == null) {
    var o2  = document.getElementsByTagName("body");

    o = o2[0];
  }
//~ console.log([cursor_css, o]);

  o.style.cursor = cursor_css;
}

function tilia_copytxt(txt) {
  if (!document.queryCommandSupported || !document.queryCommandSupported("copy")) return;

  // Ref 1
  var ta = document.createElement("textarea");

  // Ref 2
  ta.textContent = txt;

  // Evita scroll al elemento en Edge
  ta.style.position = "fixed";
  document.body.appendChild(ta);

  // Ref 3
  ta.select();

  // Algunos navegadores pueden lanzar una excepción de seguridad
  try {
    // Ref 4
    return document.execCommand("copy");
  }
  catch (ex) {
    console.warn("Copy to clipboard failed.", ex);
    return false;
  }
  finally {
    document.body.removeChild(ta);
  }
}

function tilia_getLanguage() {
  var l = navigator.language || navigator.userLanguage;

  var _l = l.split("-");

  return _l[0];
}

//---------------------------

function nome_formulario(obxeto) {
    $a_form = document.getElementsByTagName("form");

    nf = $a_form[0].name;

  return nf;
}


function checker_chekeado(checker) {
  return checker.childNodes[0].checked;
}

function chekea(checker, tipo_envio, confirma, ancla) {
//~ console.log(checker);

  var id_hidden = checker.id.substring(0, checker.id.length - 8);
  var checkbox  = document.getElementById( id_hidden + csubnome + "input" );
  var hidden    = document.getElementById( id_hidden );


  if (!validacion_envia(confirma)) {
    checkbox.checked = (hidden.value == "1");

    return false;
  }



  //* almacena valor no hidden.
  var hidden = document.getElementById( id_hidden );

  hidden.value = (hidden.value == "1")?"0":"1";

//~ console.log(hidden);

  //* actualiza o control visual.
//~ console.log(checkbox);

  checkbox.checked = hidden.value == "1";

  //* comunica co servidor, se fora preciso.
       if (tipo_envio == "submit") enviaEventoSubmit(hidden, "onclick", "", ancla);
  else if (tipo_envio == "ajax"  ) enviaEventoAJAX  (hidden, "onclick", ""       );

  return hidden.value == "1";
}

function chekea_submit(checker, confirma, ancla) {
  chekea(checker, "submit", confirma, ancla);
}

function chekea_ajax(checker, confirma) {
  chekea(checker, "ajax", confirma, "");
}

function chequea_readonly(checkbox) {
//~ console.log(checkbox);

  checkbox.checked = !checkbox.checked;
}


function radio_click(rb, tipo_envio, i_opcion, confirma, ancla) {
  if (rb.readOnly) return radio_readonly(rb);

  if (!validacion_envia(confirma)) return false;

  var r = document.getElementById(rb.name.substring(0, rb.name.length - 3));

  if (r.value == i_opcion) return false;

  r.value = i_opcion;


  if (tipo_envio == "submit") return enviaEventoSubmit(r, "onclick", "", ancla);

  if (tipo_envio == "ajax")  return enviaEventoAJAX(r, "onclick", "");
}

function radio_readonly(rb){
  var r = document.getElementById(rb.name.substring(0, rb.name.length - 3));
  var rb0 = document.getElementById(r.id + csubnome + r.value);

  rb0.checked = true;
}



//* input type=range, etq

function tilia_range_etq(input) {
  var etq = document.getElementById( input.id + csubnome + "etq" );


  etq.innerHTML = input.value;

  input.title = input.value;
}

function tilia_multirange_etq(input, uds) {
  var etq_0 = document.getElementById( input.id + csubnome + "etq_0" );
  var etq_1 = document.getElementById( input.id + csubnome + "etq_1" );

  var _value = input.value.split(",");

  etq_0.innerHTML = _value[0] + uds;
  etq_1.innerHTML = _value[1] + uds;

  input.title = input.value;
}


function id_pai(obxeto) { //* deshuso
  if (obxeto    == null) return null;
  if (obxeto.id == null) return null;

  var id_pai = null;

  var a_id = obxeto.id.split(cnome);

  for (i = 0; i < (a_id.length - 1); i++)
    if (id_pai == null)
      id_pai = a_id[i];
    else
      id_pai += (cnome + a_id[i]);

  return id_pai;
}

function tilia_irman(obxeto, id_irman) { //* deshuso
  var p = id_pai(obxeto);

  if (p == null) return null;

  return p + cnome + id_irman;
}



function tilia_illa(obxeto) {
  return tilia_irman(obxeto, "illa");
}

function tilia_file_click(id_file) {
  var f = document.getElementById(id_file);

  if (f != null) f.click();
}

function iframe_poncontido(id_iframe) {
  var iframe = document.getElementById(id_iframe);
  var iframe_aux = document.getElementById(id_iframe + "_aux");

  if (iframe == null) return false;


  iframe.contentWindow.document.write(iframe_aux.innerHTML);
  iframe.contentWindow.document.close();

  iframe_aux.innerHTML = "";
}

function __swdisplay(obxeto) {
  obxeto.style.display = (obxeto.style.display == "none")?"inline":"none";
}

function __swreadonly(obxeto) {
  obxeto.readOnly = !obxeto.readOnly;
}

function __menu_onmouseoverout(td, css) {
  td.className = css;
  document.getElementById(id_pai(td)).className = css;
}

function __menu_onclick(td, confirma) {
  var obxeto = document.getElementById(id_pai(td));

  if (obxeto == null) return;

  enviaEventoSubmit(obxeto, "onclick", confirma, "");
}

function __ptnh_onclick(td, confirma) {
//~ console.log(td, confirma);
//~ console.log(id_pai(td));

  var obxeto = document.getElementById(id_pai(td));

  if (obxeto == null) return;
//~ console.log(obxeto);
  enviaEventoSubmit(obxeto, "onclick", confirma, "");
}

function __ptnh_onmouseoverout(td, css) {
  td.className = css;
}

// ----------

function tilia_mover(id_obxeto, id_destino, pos_destino) {
  //* permite cambiar id_obxeto de posición. Moi útil en plantillas responsive.

  var m = document.getElementById(id_obxeto);

  if (m == null) return;

  m.parentNode.removeChild(m);


  var d = document.getElementById(id_destino);

  if (d == null) return;

  if (pos_destino == null) {
    d.appendChild(m);

    return;
  }

  var o_aux = d.children[pos_destino];

  d.insertBefore(m, o_aux);
}


// ----------

function tilia_jquery_selector(id) {
  var id2 = "";

  for (var i = 0; i < id.length; i++) {
    if (id[i] == cnome   ) continue;
    if (id[i] == csubnome) continue;

    id2 += id[i];
  }


  return id2;
}

// ----------

var _bodyOnload_callback = [];

function tilia_body_onload() {
  //~ tilia_fin_cargado();

  for (var i = 0; i < _bodyOnload_callback.length; i++) {
    _bodyOnload_callback[i]();
  }

  _bodyOnload_callback = [];
}

function tilia_body_onload_addCallback(f) {
  _bodyOnload_callback.push(f);
}

function tilia_fin_cargado(b, t) {
  if (b == undefined) b = true;
  if (t == undefined) t = 5000;

  if (!b) if (t > 0) setTimeout(function() {tilia_fin_cargado(true)}, t);

  //~ var d = document.getElementById('tilia_div_cargando');

  //~ d.style.display = (b)?"none":"initial";
}


// ----------


function tilia_haiScript(s) {
  for(var i=0,scs; scs=document.getElementsByTagName('script')[i];i++){
//~ console.log([s, scs.src]);

    if(scs.src.indexOf(s) > -1) return true;
  }

  return false;
}

function tilia_loadScript(url, callback, body_onload) {
  if (callback    == undefined) callback    = null;
  if (body_onload == undefined) body_onload = true;

  if (body_onload) {
    tilia_body_onload_addCallback( function() { tilia_loadScript(url, callback, false); } );

    return;
  }

  try {
    var script = document.createElement("script");

    script.type = "text/javascript";

    if (callback != null) {
      if (script.readyState) { //IE
          script.onreadystatechange = function () {
              if (script.readyState == "loaded" || script.readyState == "complete") {
                  script.onreadystatechange = null;
                  callback();
              }
          };
      }
      else { //Others
          script.onload = function () {
              callback();
          };
      }
    }

    script.src = url;

    document.getElementsByTagName("head")[0].appendChild(script);
  }
  catch (ex) {
    console.warn("Error de carga del JS, ", ex);

    return false;
  }
}



function tilia_loadCSS(url, id) {
  if (id != undefined) if (document.getElementById(id) != null) return; //* tal vez sexa mellor eliminar o nodo e crear un novo.
  
  var link = document.createElement('link');
  
  link.rel  = 'stylesheet';
  link.type = 'text/css';
  link.href = url;
  
  if (id != undefined) link.id = id;

  document.getElementsByTagName('HEAD')[0].appendChild(link);
}

// ----------

/* Validacions
 */

function tilia_validar_url(url) {
  try {
    var http = new XMLHttpRequest();

    http.open('GET', url, false);

    http.send();

    return http.status != 404;
  }
  catch (e) {
    return false;
  }

  return false;
}

function tilia_leer_json_remoto(url, f_ok) {
  var f_ok_aux = null;

  if (f_ok == undefined) {
    f_ok_aux = function() { console.log(http.response);};
  }
  else {
    f_ok_aux = function() { f_ok(http.response);};
  }

  try {
    var http = new XMLHttpRequest();

    http.open('GET', url);

    http.responseType = 'json';

    http.onload = f_ok_aux;

    http.send();

    return http.status != 404;
  }
  catch (e) {
    return false;
  }

  return false;
}

function tilia_validar_email( t ) {
   expr = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

  return expr.test( t );
}

function tilia_validar_data(t)  {
  t.focus();

  if (tilia_validar_data_2(t)) return true;
  t.value = "";

  return false;
}

function tilia_validar_hora(t)  {
  t.focus();

  if (tilia_validar_hora_2(t)) return true;

  t.value = "";

  return false;
}

function tilia_validar_data_2(t) {
  var valor = t.value;

  if (valor.length == 0) return true;
  if (valor.length > 10) {
    alert("Lonxitude da data incorrecta");
    return false;
  }

  var delimitador = tilia_busca_delimitador(t.value, 2);

  if (!delimitador) return false;

  var partes_fecha = valor.split(delimitador);
  dia = partes_fecha[0];
  mes = partes_fecha[1];
  ano = partes_fecha[2];

  if (dia == null || dia.length > 2) {
    alert("Día incorrecto");
    return false;
  }

  if (dia.length == 1) dia = "0" + dia;

  if (mes == null || mes.length > 2) {
    alert("Mes incorrecto");
    return false;
  }

  if (mes.length == 1) mes = "0" + mes;

  if (ano == null) {
    ano = (new Date()).getFullYear();
  }
  else if (ano.length > 4) {
    alert("Ano incorrecto");
    return false;
  }

  if (ano.length < 4) ano = Math.abs(ano) + 2000;

  if (dia < 1 || dia > 31) {
    alert("Dia incorrecto");
    return false;
  }

  if (mes < 1 || mes > 12) {
    alert("Mes incorrecto");
    return false;
  }

  if (ano < 1) {
    alert("Ano incorrecto");
    return false;
  }

  if ((mes == 4 || mes == 6 || mes == 9 || mes == 11) && dia > 30) {
    alert("O mes ten 30 dias");
    return false;
  }

  if (mes == 2) {
    if (!tilia_bisiesto(ano)) {
      if (dia > 28) {
        alert("O mes ten 28 dias");
        return false;
      }
    } else {
      if (dia > 29) {
        alert("O mes ten 29 dias");
        return false;
      }
    }
  }

  t.value = dia + "-" + mes + "-" + ano;
  return true;
}

function tilia_numero_addCommas(n, c = ",") {
  if (c == undefined) c = ",";

//~ console.log(n, c);

  n += '';

  _x = n.split(".");

  x0 = "";
  for(var i = 1; i <=_x[0].length; i++) {
    if (i > 1) if ( ((i - 1) % 3) == 0 ) x0 = " " + x0;

    x0 = _x[0][ _x[0].length - i ] + x0;
  }

  x1 = (_x.length > 1) ? `.${_x[1]}` : ``;


  return x0 + x1;
}

function tilia_numero(car) {
  var numeros = "0123456789";

  return numeros.indexOf(car) >= 0;
}

function tilia_bisiesto(ano) {
  if (((ano % 4 == 0) && (ano % 100 != 0)) || (ano % 400 == 0)) return true;
  return false;
}

function tilia_validar_hora_2(t) {
  valor = t.value;
  if (valor.length == 0) return true;
  if (valor.length > 5) {
    alert("Lonxitude da hora incorrecta");
    return false;
  }

  var delimitador = tilia_busca_delimitador(t.value);

  if (!delimitador) return false;

  var partes_hora = valor.split(delimitador);
  var horas       = partes_hora[0];
  var minutos     = partes_hora[1];

  if (horas == null || horas.length > 2) {
    alert("Lonxitude da hora incorrecta");
    return false;
  }

  if (horas < 0 || horas > 23) {
    alert("Horas incorrectas");
    return false;
  }

  if (horas.length == 1) horas = "0" + horas;

  if (minutos == null || minutos.length > 2) {
    alert("Lonxitude dos minutos incorrecta");
    return false;
  }

  if (minutos < 0 || minutos > 59) {
    alert("Minutos incorrectos");
    return false;
  }

  if (minutos.length == 1) minutos = "0" + minutos;

  t.value = horas + ":" + minutos;
  return true;
}

function tilia_busca_delimitador(valor, ctd) {
  if (ctd == null) ctd = 1;

  var delimitador = new Array();
  for (i = 0; i < valor.length; i++) {
    if (!tilia_numero(valor.charAt(i))) delimitador[delimitador.length] = valor.charAt(i);
  }

  if (delimitador.length == 1) return delimitador[0];

  if (delimitador.length != ctd) {
      alert ("Formato de dato incorrecto");

    return false;
  }

  for (i = 1; i < ctd; i++)
    if (delimitador[0] != delimitador[i]) {
      alert ("Formato de dato incorrecto");
      return false;
    }

  return delimitador[0];
}

function tilia_lpad(s, c, l) {
  s = s.toString();

  if (c == null) c = "0";
  if (l == null) l = 4;

  while (s.length < l) s = c + s;


  return s;
}

function tilia_url_getParamByName(name) {
  name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

  var regex = new RegExp("[\\?&amp;amp;]" + name + "=([^&amp;amp;#]*)"),
    results = regex.exec(location.search);

  return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}


//* input multirange

function tilia_ini_multiRange() {
  "use strict";

  var supportsMultiple = self.HTMLInputElement && "valueLow" in HTMLInputElement.prototype;

  var descriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, "value");

  self.multirange = function(input) {
    if (supportsMultiple || input.classList.contains("multirange")) {
      return;
    }

    var value = input.getAttribute("value");
    var values = value === null ? [] : value.split(",");
    var min = +(input.min || 0);
    var max = +(input.max || 100);
    var ghost = input.cloneNode();

    input.classList.add("multirange", "original");
    ghost.classList.add("multirange", "ghost");

ghost.name = ""; //* 20180610 compativilidade Tilia

//~ console.log(ghost);

    input.value = values[0] || min + (max - min) / 2;
    ghost.value = values[1] || min + (max - min) / 2;

    input.parentNode.insertBefore(ghost, input.nextSibling);

    Object.defineProperty(input, "originalValue", descriptor.get ? descriptor : {
      // Fuck you Safari >:(
      get: function() { return this.value; },
      set: function(v) { this.value = v; }
    });

    Object.defineProperties(input, {
      valueLow: {
        get: function() { return Math.min(this.originalValue, ghost.value); },
        set: function(v) { this.originalValue = v; },
        enumerable: true
      },
      valueHigh: {
        get: function() { return Math.max(this.originalValue, ghost.value); },
        set: function(v) { ghost.value = v; },
        enumerable: true
      }
    });

    if (descriptor.get) {
      // Again, fuck you Safari
      Object.defineProperty(input, "value", {
        get: function() { return this.valueLow + "," + this.valueHigh; },
        set: function(v) {
          var values = v.split(",");
          this.valueLow = values[0];
          this.valueHigh = values[1];
          update();
        },
        enumerable: true
      });
    }

    if (typeof input.oninput === "function") {
      ghost.oninput = input.oninput.bind(input);
    }

    function update() {
      ghost.style.setProperty("--low", 100 * ((input.valueLow - min) / (max - min)) + 1 + "%");
      ghost.style.setProperty("--high", 100 * ((input.valueHigh - min) / (max - min)) - 1 + "%");
    }

    input.addEventListener("input", update);
    ghost.addEventListener("input", update);

    update();
  }

  multirange.init = function() {
    [].slice.call(document.querySelectorAll("input[type=range][multiple]:not(.multirange)")).forEach(multirange);
  }

  if (document.readyState == "loading") {
    document.addEventListener("DOMContentLoaded", multirange.init);
  }
  else {
    multirange.init();
  }
}

tilia_ini_multiRange();


//* datalist

function tilia_dl_change(o) {
//~ console.log(o);

  var dl = document.getElementById( o.getAttribute("list") );

//~ console.log(dl);

  var v = null;
  var _dl = dl.getElementsByTagName("option");

//~ console.log(_dl);
  for (var i = 0; i < _dl.length; i++) {
//~ console.log(o.value.toLowerCase(), _dl[i].value.toLowerCase());
    if (o.value.toLowerCase() != _dl[i].value.toLowerCase()) continue;

    v = _dl[i].getAttribute("tilia_dlo_valor");

    break;
  }

  var h = document.getElementById( o.id.substring(0, o.id.length - 6) );

  h.value = (v != null)?v:o.value;

//~ console.log(h);

  //* executa evento dl_onchange (se hai).
  var f_str = h.getAttribute("dl_onchange");

//~ console.log(f_str);

  if (f_str == null) return;

  if (f_str == "ajax") {
    enviaEventoAJAX(h, "onchange", "", null, null);

    return;
  }

  if (f_str == "submit") {
    enviaEventoSubmit(h, "onchange", "", "");

    return;
  }

  let f = eval(f_str);
}

function tilia_dl_postOption(dl_id, dlo_k, dlo_s) {
  var dl_l = document.getElementById(`${dl_id}*lista`);

  if (dl_l == null) return;

  var dl_o = dl_l.querySelector(`[tilia_dlo_valor="${dlo_k}"]`);

  if (dl_o != null) {
    dl_o.setAttribute("tilia_dlo_valor", dlo_k);

    dl_o.value = `${dlo_k} - ${dlo_s}`;

    return;
  }

  oat = document.createAttribute("tilia_dlo_valor");

  oat.value = dlo_k;

  o = document.createElement("option");

  o.setAttributeNode(oat);

  o.value = `${dlo_k} - ${dlo_s}`;

//~ console.log(o);

  dl_l.appendChild(o);
}


//* datalist multiple
function tilia_dlm_t_click(o) {
  var o2 = o.parentNode.parentNode.children[1];
  
  o2.style.display = (o2.style.display == "none")?"flex":"none";
}

function tilia_dlm_t_keyup(o) {
//~ console.log(o);
  //* Actualiza lista
  var _label = o.parentNode.parentNode.getElementsByTagName("label");

  for (var i = 0; i < _label.length; i++) {
    var r = new RegExp( o.value, "i" );

    var v = r.test(_label[i].innerHTML)?"1":"0";

    _label[i].parentNode.setAttribute("dlm_l_v", v);
  }

  //* Actualiza hidden
  var h = o.parentNode.parentNode.children[2];

//~ console.log(h);

  var _v = JSON.parse( h.value );

  _v.t = o.value;


  h.value = JSON.stringify(_v);
}

function tilia_dlm_l_click(o) {
  var p  = o.parentNode.parentNode.parentNode;
  var h  = p.children[2];

//~ console.log(o, p, h);

  //* control de accion segun parametro dlm_readonly.
  if ( tilia_dlm_readonly(o, p, h) ) return;

  //* garda seleccion no hidden.
  tilia_dlm_h(o, h);

  //* actualizamos lista seleccionados.
  //~ tilia_dlm_l_actualiza(o, p);

  //* executa evento dlm_onchange (se hai).
  tilia_dlm_change(o, p, h);
}

function tilia_dlm_h(o, h) {
  var v = o.getAttribute("dlm_l_i");

  var _v = JSON.parse( h.value );

//~ console.log(_v);

  if (_v == null) _v = {t: null, _s: []};

  var p = _v._s.indexOf(v);

  if (p >= 0)
    _v._s.splice(p, 1);
  else
    _v._s.push(v);

  h.value = JSON.stringify(_v);
}

function tilia_dlm_l_actualiza(o, p) {
//~ console.log(o);
  var l_1 = p.children[1];
  var l_2 = p.children[2];

  var b  = o.getAttribute("dlm_l_b");

//~ console.log(l_1, l_2, b);

  if (b == "1") {
    l_1.removeChild(o);

    var _o3 = l_2.querySelector(`div[dlm_l_i='${o.getAttribute("dlm_l_i")}']`);

    _o3.setAttribute("dlm_l_b", "0");
    _o3.children[0].checked = false;
  }
  else { //* b == "0".
    o.setAttribute("dlm_l_b", "1");

    var o2 = o.cloneNode(true);

    o2.children[0].checked = true;

    l_1.appendChild(o2);
  }
}

function tilia_dlm_change(o, p, h) {
  var f_str = p.getAttribute("dlm_onchange");

  if (f_str == null) return;

  if (f_str == "ajax") {
    enviaEventoAJAX(h, "onchange", "", null, null);

    return;
  }

  if (f_str == "submit") {
    enviaEventoSubmit(h, "onchange", "", "");

    return;
  }

  let f = eval(f_str);
}

function tilia_dlm_readonly(o, p, h) {
  var r = p.getAttribute("dlm_readonly"); //* PRECOND: r IN [... -3, -2, -1, 0, 1]

  if (r == "1") return true ; //* readonly total, (non se permite ningunha operacion).


  var b = o.getAttribute("dlm_l_b");

  if (b != "1") return false; //* SE (r < 1), podemos engadir selecionados.


  //* ((r < 1) && (b == "0")), control min(selecion).
  if ( p.children[1].children.length <= Math.abs(r) ) {
    o.children[0].checked = true;

    return true;
  }


  return false;
}


//* cookies.

function tilia_getCookie(name) {
  let matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  
  if (matches    == null     ) return null;
  
  if (matches[1] == undefined) return null;
  if (matches[1] == null     ) return null;

  return matches ? JSON.parse( matches[1] ) : undefined;
}

function tilia_setCookie(nome, valor, maxAge, path) {
  valor = JSON.stringify( valor );
  
  if (maxAge == undefined) maxAge = "86400";
  if (path   == undefined) path   = "/";

  document.cookie = `${nome}=${valor}; max-age=${maxAge}; path=${path}; secure; samesite=strict`;
}

function tilia_deleteCookie(name) {
  tilia_setCookie(name, "", {
    'max-age': -1
  })
}

/*
function tilia_cookie_crear(nome, valor, maxAge, path, dominio) {
  if (maxAge == undefined) maxAge = 3600;
  if (path   == undefined) path   = "/";

  var cookie = nome + "=" + encodeURIComponent( valor ) + "; max-age=" + maxAge + "; path=" + path;

  if (dominio != undefined) cookie += "; domain=" + dominio;


  document.cookie = cookie;
}

function tilia_cookie_modificar(nome, valor, path, dominio) {
  if (path == undefined) path   = "/";

  var cookie = nome + "=" + encodeURIComponent( valor ) + "; path=" + path;

  if (dominio != undefined) cookie += "; domain=" + dominio;


  document.cookie = cookie;
}

function tilia_cookie_leer(nome) {
  var nameEQ = nome + "=";
  var ca = document.cookie.split(';');

  for(var i=0;i < ca.length;i++) {

    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);

    if (c.indexOf(nameEQ) == 0) {
      return decodeURIComponent( c.substring(nameEQ.length,c.length) );
    }

  }

  return null;
}

function tilia_cookie_borrar(nome, valor, path, dominio) {
  if (path   == undefined) path   = "/";

  var cookie = nome + "=" + encodeURIComponent( valor ) + "; max-age=0; path=" + path;

  if (dominio != undefined) cookie += "; domain=" + dominio;


  document.cookie = cookie;
}
*/


// * ver-ocultar password (icono ollo)

function tilia_mostra_contrasinal(o) {
  const passwordInput = o.previousElementSibling; 

  o.classList.toggle("mostrar");

  const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
  passwordInput.setAttribute("type", type);
}

