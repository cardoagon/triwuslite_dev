/*************************************************

    Tilia Framework v.0

    ajax-3.js

    Author: Carlos Domingo Arias González

    Copyright (C) 2020

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


function __xml() {
  var xml = null;

  try {
    xml = new ActiveXObject('Msxml2.XMLHTTP');
  }
  catch(e) {
    try {
      xml = new ActiveXObject('Microsoft.XMLHTTP');
    }
    catch(e2) {
      xml = false;
    }
  }

  if (!xml && typeof XMLHttpRequest != 'undefined') xml = new XMLHttpRequest();

  return xml;
}


//-------------------------------------

function tilia_postAJAX(post_callback) {
  //* envía datos ao servidor.

  var tipo_evento = "nulo";

  var obxetoAJAX = __xml();

  obxetoAJAX.open('POST', document.forms[0].action + "?ajax=1", true);

  obxetoAJAX.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  obxetoAJAX.setRequestHeader("Cache-Control", "no-cache");

  obxetoAJAX.send(valores_enviar_AJAX(null, tipo_evento));

  obxetoAJAX.onreadystatechange = function() {
                                  if (obxetoAJAX.readyState != 4) return;

                                  if (obxetoAJAX.status == 0) {}

                                  if (obxetoAJAX.status == 200) {
                                    if (post_callback != null) post_callback();
                                  }
                                }
}


//-------------------------------------

async function enviaEventoAJAX(obxeto, tipo_evento, confirma, post_callback, pre_callback) {
  if (obxeto == null) return false;

  if (tipo_evento == null) tipo_evento = 'nulo';

  if (!validacion_envia(confirma)) return false;

  if (pre_callback != null) pre_callback();

  obxeto.disabled = true;

  const o_fetch = {method: 'POST',
                   body  : valores_enviar_AJAX(obxeto, tipo_evento)
                  };


  const response = await fetch(`${document.forms[0].action}?ajax=1`, o_fetch);

  obxeto.disabled = false;

//~ console.log(response);

  if ( !response.ok ) {
    throw "ERRO. tilia.enviaEventoAJAX(). ( !response.ok ).";

    return false;
  }

  if ( response.redirected ) {
    window.location.replace( response.url );

    return false;
  }

  const rjson = await response.json();

  if (rjson == null) {
    window.location.replace( response.url );

    return false;
  }

  resposta_AJAX(rjson);

  if (typeof post_callback === "function") post_callback();

  return true;
}


function enviaEventoAJAX_src(src, tipo_evento, confirma, post_callback, pre_callback) {
  var obxeto = document.getElementById(src);

  if (obxeto == null) return false;

  return enviaEventoAJAX(obxeto, tipo_evento, confirma, post_callback, pre_callback);
}


function radio_click_ajax(rb, i_opcion, confirma) {
  var r = document.getElementById(rb.name.substring(0, rb.name.length - 3));

  if (r.value == i_opcion) return false;

  if (!validacion_envia(confirma)) return false;

  r.value = i_opcion;

  enviaEventoAJAX(r, "onclick");
}

//-------------------------------------

function valores_enviar_AJAX(obxeto, tipo_evento, componente, formData) {
  if (obxeto != null) {
    var codigo = obxeto.id;
    var nf = nome_formulario();

    document.getElementById(nf + cnome + "evento").value = codigo + csubnome + "tipo=" + tipo_evento + csubnome + "ajax=1";
  }

  var d = (formData == undefined)?new FormData():formData;
  var f = (componente != null)?componente:document;

  var ref = f.getElementsByTagName("INPUT");
  for (var i = 0; i < ref.length; i++) {
    if (ref[i].id.length == 0) ref[i].id = ref[i].name;

    d.append(ref[i].id, ref[i].value);
  }

  var ref = f.getElementsByTagName("SELECT");
  for (var i = 0; i < ref.length; i++) d.append(ref[i].id, ref[i].value);

  var ref = f.getElementsByTagName("TEXTAREA");
  for (var i = 0; i < ref.length; i++) d.append(ref[i].id, ref[i].value);

  /*
   *   Unha vez construida a cadea de valores post a enviar ao servidor (var s), eliminamos o valor
   * do hidden 'evento', para evitar re-solicitudes ajax ao pulsar a tecla F5.
   */

  if (obxeto != null) document.getElementById(nf + cnome + "evento").value = null;


  return d;
}

//-------------------------------------

function resposta_AJAX(msx) {
//~ console.log("resposta_AJAX()", msx);

  if (msx == null) return;

  for (var j = 0; j < msx.length; j++) {
         if ( msx[j].ti == 'TIPO'  ) resposta_AJAX_TIPO  ( msx[j] );
    else if ( msx[j].ti == 'OK'    ) resposta_AJAX_OK    ( msx[j] );
    else if ( msx[j].ti == 'INPUT' ) resposta_AJAX_INPUT ( msx[j] );
    else if ( msx[j].ti == 'CHECK' ) resposta_AJAX_CHECK ( msx[j] );
    else if ( msx[j].ti == 'SRC'   ) resposta_AJAX_SRC   ( msx[j] );
    else if ( msx[j].ti == 'LINK'  ) resposta_AJAX_LINK  ( msx[j] );
    else if ( msx[j].ti == 'SELECT') resposta_AJAX_SELECT( msx[j] );

    if ( msx[j].cb != null) {
      try {
        let fcb = eval(msx[j].cb);
      }
      catch( ex ) {
        //~ throw ex;
      }
    }
  }
}

//-------------------------------------

function resposta_AJAX_INPUT( msx ) {
  var o = document.getElementById( msx.at.nome );

  if (o == null) return;

  o.value = msx.fi;

  if (msx.at.clase != null) o.className = msx.at.clase;

  o.style.display =  msx.at.visible;
}

function resposta_AJAX_CHECK( msx ) {
  var h = document.getElementById( msx.at.nome                      ); if (h == null) return;
  var i = document.getElementById( `${msx.at.nome}${csubnome}input` ); if (i == null) return;


  h.value   = (msx.fi)?"1":"0";
  i.checked = msx.fi;

  if (msx.at.clase != null) i.className = msx.at.clase;

  i.style.display =  msx.at.visible;
}

function resposta_AJAX_SELECT( msx ) {
  var s = document.getElementById( msx.at.nome );

  if (s == null) return;


  //* 0.- actualiza atributos de estilo
  if (msx.at.clase != null) s.className = msx.at.clase;

  s.style.display = msx.at.visible;

  //* 1.- actualiza opcións
  //~ if (msx.fi.length > 1)  {
  if (msx.fi.length > 0)  {
    //* msx.fi[0] == valor

    while (s.options.length > 0) s.remove(0);

    for (var i = 0; i < msx.fi.length; i++) { //* msx.fi[0] == valor
      var o = document.createElement("OPTION");

      o.value = msx.fi[i].k;
      o.text  = msx.fi[i].v;

      s.options.add( o );
    }
  }

  //* 2.- actualiza valor
  //* 2.1.- asigna valor a selector común (no múltiple).
  if ( !Array.isArray(msx.fi[0]) ) {
    s.value = msx.fi[0];

    return;
  }

  //* 2.2.- asigna valor a selector múltiple.
  for (var i = 0; i < s.options.length; i++) {
    s.options[i].selected = (msx.fi[0].indexOf( s.options[i].value ) >= 0);
  }
}

function resposta_AJAX_TIPO( msx ) {
  var o = document.getElementById( msx.at.nome );


  if (o == null) return;

  o.innerHTML = msx.fi;

  if (msx.at.clase != null) o.className = msx.at.clase;

  o.style.display =  msx.at.visible;
}

function resposta_AJAX_SRC( msx ) {
  var o = document.getElementById( msx.at.nome );

  if (o == null) return;

  o.src = msx.fi;

  if (msx.at.clase != null) o.className = msx.at.clase;

  o.style.display = msx.at.visible;
}

function resposta_AJAX_LINK( msx ) {
  var o = document.getElementById( msx.at.nome );

  if (o == null) return;

  o.innerHTML = msx.fi;

  if (msx.at.clase != null) o.className = msx.at.clase;

  o.style.display = msx.at.visible;
}

function resposta_AJAX_OK( msx ) {}

//***********************

function tilia_ajax2post() {
  document.forms[0].method = "post";

  document.forms[0].submit();
}


//***********************


/** InputFieAjax *************************
*/

async function tilia_subir_adxunto(f) {
//~ console.log(f.files);

  const maxSize = f.previousElementSibling.value;

  for (var i = 0; i < f.files.length; i++) await tilia_subir_adxunto_b(f, i, maxSize);
}

async function tilia_subir_adxunto_b(f, i, maxSize) { //* private
  //console.log(f.files[i], i);
  //console.log(f);

  if(f.files[i].size > maxSize) {
    alert(`Err. ${f.title}`);
    f.value = "";
    return;
  }

  var evento   = nome_formulario() + cnome + "evento";

  var formData = new FormData();

  formData.append(evento , f.id + csubnome + "tipo=onclick" + csubnome + "ajax=1");
  formData.append("tsa_i", i);
  formData.append(f.id   , f.files[i]);


  const o_fetch = {method: 'POST',
                   body  : formData
                  };


  const resposta = await fetch(`${document.forms[0].action}?ajax=1`, o_fetch);

//~ console.log(resposta);
  if ( !resposta.ok ) {
    throw "ERRO. tilia.tilia_subir_adxunto_b(). ( !resposta.ok ).";

    return;
  }


  if ( resposta.redirected ) {
    window.location.replace( resposta.url );

    return;
  }

  const rjson = await resposta.json();
//~ console.log(rjson);
  if (rjson == null) {
    console.error("tilia_subir_adxunto_b(). (rjson ==null).");

    return;
  }

  resposta_AJAX(rjson);

  if (typeof post_callback === "function") post_callback();
}



function tilia_uploadFile_0(inputFile, callback) {
  for (var i = 0; i < inputFile.files.length; i++) tilia_uploadFile(inputFile, i, callback);
}

function tilia_uploadFile(inputFile, i, callback) {
//~ console.log([inputFile, i, callback]);

  var nc = inputFile.id;

  var file = inputFile.files[i];


  var max_cantidad = document.getElementById( nc + "_s" ).value;
  var rutaloadphp  = document.getElementById( nc + "_p" ).value;
  var rutadestino  = document.getElementById( nc + "_d" ).value;

  var id_bt = nc + "_b";

  var filename = file.name || file.fileName;
  var extension = filename.split('.').pop();
  var filesize = file.size;
  var pathsave = rutadestino;
  var pathload = window.atob(rutaloadphp); // decode

  filename = encodeURIComponent(filename);

  if( !file ) return;


//~ console.log(pathload);
//~ console.log(file);


  if( filesize > max_cantidad ) {
    alert('ERROR: el tamaño máximo permitido es de: ' + (max_cantidad / 1000000) + " MegaBytes");

    return;
  }

  var xhr = new XMLHttpRequest();

  xhr.upload.addEventListener('loadstart', function(e) {
    document.getElementById( id_bt ).style.display = 'inline';
    document.getElementById( id_bt ).setAttribute('value', 0);
    return true;
  });
  xhr.upload.addEventListener('progress', function(e) {
    document.getElementById( id_bt ).setAttribute('value', e.loaded / e.total*100);
  });

  xhr.upload.addEventListener('load'   , function(e) {});
  xhr.upload.addEventListener('loadend', function(e) {});
  xhr.upload.addEventListener('error'  , function(e) {});
  xhr.upload.addEventListener('abort'  , function(e) {});
  xhr.upload.addEventListener('timeout', function(e) {});

  xhr.addEventListener('readystatechange', function(e) {
//~ console.log(this.readyState);
    if( this.readyState === 4 ) {
      document.getElementById( id_bt ).setAttribute('value', 0);
      document.getElementById( id_bt ).style.display = 'none';

      if ((i + 1) < inputFile.files.length) return;

      if (callback != null) callback();
    }
  });

  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ \\ ajax
  xhr.open('POST', pathload + '?path=' + pathsave + "&i=" + tilia_lpad(i) + "." + extension);

  xhr.setRequestHeader("Cache-Control", "no-cache");
  xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
  xhr.setRequestHeader("X-File-Name", filename);
  xhr.setRequestHeader("Content-Type", "application/octet-stream");

  xhr.send(file);
}


//***********************


/** ActionAJAX *************************
 *
 *  Exemplo iniciar obxeto ActionAJAX
 *

  var aa = {
              action: "url-do-actionAJAX.php",
              id_illa: ["id-illa-componente", ... ],
              f_resposta: function(responseText) { funcion_resposta_actionAJAX(responseText) }
            };

 *
 *
*/


function valores_enviar_AJAX_aa(obxeto, tipo_evento, componente) {
  if (obxeto != null) {
    var codigo = obxeto.id;
    var nf = nome_formulario();

    document.getElementById(nf + cnome + "evento").value = codigo + csubnome + "tipo=" + tipo_evento + csubnome + "ajax=1";
  }

  var s = "";

  var d = (componente != null)?componente:document;

  var ref = d.getElementsByTagName("SELECT");
  for (var i = 0; i < ref.length; i++) s += "&" + ref[i].id + "=" + encodeURIComponent(ref[i].value);


  var ref = d.getElementsByTagName("INPUT");
  for (var i = 0; i < ref.length; i++) {
    //~ if (ref[i].name == "") continue;

    if (s != "") s += "&";

    if (ref[i].id.length == 0) ref[i].id = ref[i].name;

    s += ref[i].id + "=" + encodeURIComponent(ref[i].value);
  }

  var ref = d.getElementsByTagName("TEXTAREA");
  for (var i = 0; i < ref.length; i++) s += "&" + ref[i].id + "=" + encodeURIComponent(ref[i].value);

  /*
   *   Unha vez construida a cadea de valores post a enviar ao servidor (var s), eliminamos o valor
   * do hidden 'evento', para evitar re-solicitudes ajax ao pulsar a tecla F5.
   */

  if (obxeto != null) document.getElementById(nf + cnome + "evento").value = null;


  return s;
}


function tilia_aa_envia(aa, tilia_op, param) {
  var ajax = __xml();

  ajax.open('POST', tilia_aa_src(aa, tilia_op, param), true);
  ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  ajax.send(tilia_aa_cadena(aa));

  ajax.onreadystatechange = function() {
                              if (ajax.readyState != 4  ) return;
                              if (ajax.status     == 0  ) return;
                              if (ajax.status     == 200) aa.f_resposta( ajax.responseText );
                            }
}

function tilia_aa_cadena(aa) {
  var id_illa = aa.id_illa;

  if ( !Array.isArray(id_illa) ) id_illa = [id_illa];

  var illa   = null;
  var cadena = "";

  for (var i = 0; i < id_illa.length; i++) {
    illa = document.getElementById( id_illa[i] );

    if (illa == null) continue;

    if (cadena != "") cadena += "&";

    cadena += valores_enviar_AJAX_aa(null, null, illa);
  }


//~ console.log(cadena);


  return cadena;
}


function tilia_aa_src(aa, tilia_op, param) {
  var action = aa.action + "?tilia_op=";

  action += (tilia_op == undefined)?"1":encodeURIComponent(tilia_op);

  if (param == undefined) param = "";

  if (param != "") action += ("&" + param);


  return action;
}



async function tilia_aa2_envia(aa, tilia_op, param) {
  const o_fetch = {method: 'POST',
                   body  : tilia_aa2_datos(aa)
                  };

console.log(o_fetch);

  const response = await fetch(tilia_aa_src(aa, tilia_op, param), o_fetch);

console.log(response);

  if ( !response.ok ) {
    throw "ERRO. tilia.enviaEventoAJAX(). ( !response.ok ).";

    return false;
  }

  if ( response.redirected ) {
    window.location.replace( response.url );

    return false;
  }

  const rjson = await response.json();

  if (rjson == null) {
    window.location.replace( response.url );

    return false;
  }

  aa.f_resposta( rjson );


  return true;
}


function tilia_aa2_datos(aa) {
  var id_illa = aa.id_illa;

  if ( !Array.isArray(id_illa) ) id_illa = [id_illa];

  var illa = null;
  var fb   = new FormData();

  for (var i = 0; i < id_illa.length; i++) {
    illa = document.getElementById( id_illa[i] );

    if (illa == null) continue;

    fb = valores_enviar_AJAX(null, null, illa, fb);
  }

  return fb;
}
