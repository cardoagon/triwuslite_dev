//* sobreescribe método fetch, para controlar los rastreos
window.fetch = new Proxy(window.fetch, {
      apply: function (target, that, args) {
        
        //* control cookie_config;
        if ( !cookies_validar_url(args[0])) {
          console.error("Dominio non válido", args[0]);
            
          return;
         }
        

        
        // args holds argument of fetch function
        // Do whatever you want with fetch request
        let temp = target.apply(that, args);
        temp.then((res) => {
          // After completion of request
          if (res.status === 401) {
            console.error("Session expired, please reload the page!");
          }
        });
        return temp;
      },
    });



//* sobreescribe método fetch, para controlar los rastreos
/*
XMLHttpRequest = new Proxy(XMLHttpRequest, {
      construct: function (target, args) {
        
        console.log("cookies_conf:", ccookies_config());
        
        
        //~ console.log(target);
        
        //~ cookies_controlDominio(target);

        const xhr = new target(...args);
        
        console.log(xhr);
        // Do whatever you want with XHR request
        xhr.onreadystatechange = function () {
          if (xhr.readyState === 1) {
            // Before sent request to server
            //~ xhr.setRequestHeader("Authorization", "XXXX-XXXX-XXXX-XXXX");
          }
          if (xhr.readyState === 4) {
            // After complition of XHR request 
            if (xhr.status === 401) {
              alert("Session expired, please reload the page!");
            }
          }
        };
        return xhr;
      },
    });
*/

function trw_body_onload(id_paxina) {
  ccookies_inicia();
  panoramix2_inicia(id_paxina);

  tilia_body_onload();
}

//------------------------- saída de erros.

function trw_post_msx(m, t) {
  var d0 = document.getElementById("findex;msx");

//~ console.log(d0);

  if (d0 == null) return; //* throw
  
  //* ¿ pecha msx ?
  if (m == undefined) {
    d0.className = "txt_apagado";
    
    return;
  }
  
  //* mostra msx
  if (t == undefined) t = "e";
  
  var c = "txt_err";
  
       if (t == "a") c = "txt_adv";
  else if (t == "m") c = "txt_msx";
  
  d0.innerHTML     = `<div onclick="trw_post_msx()">✕</div><div>${m}</div>`;
  d0.className     = `trw_msx ${c}`;
  d0.style.display = `flex`;
}

//------------------------- redireccións a login

function trw_loginRedirectURL() {
  window.location.href = `login.php?redirect=${window.location.pathname}`;
}

//------------------------- CLogo

function clogo_imx_click(b) {
  var f = document.getElementById(b.id.substring(0, b.id.length - 4) + "flogo");

  f.click();
}

//--------------------------- CMenuSite

var trw_menu_aberto   = {id:null, tempo: 7777, limpar: null};

function trw_menu_abrir(id_menu) {
//~ console.log(trw_menu_aberto.id, id_menu);
  if (trw_menu_aberto.id != id_menu) trw_menu_pechar();

  trw_menu_aberto.id = id_menu;

  var m = document.getElementById(trw_menu_aberto.id);

  if (m != null) {
    m.style.display = 'block';

    trw_menu_actualiza_mais(m);
  }

  trw_menu_aberto.limpar = setTimeout( function() { trw_menu_pechar() } , trw_menu_aberto.tempo );
}

function trw_menu_pechar() {
//~ console.log(111111111111);
  clearInterval( trw_menu_aberto.limpar );

  var m = document.getElementById(trw_menu_aberto.id);

  if (m == null) return;


  m.style.display = 'none';

  trw_menu_actualiza_mais(m);
}

function trw_menu_actualiza_mais(menu) {
  var aux_1 = menu.parentNode.parentNode;

  var aux_2 = aux_1.firstElementChild.firstElementChild;

//~ console.log(aux_1, aux_2);


  aux_2.innerHTML = (menu.style.display == 'none')?"▶":"▼";
}

//~ function trw_menu_ajax_resposta(json) {
//~ console.log(json);
  
  //~ var ms = document.getElementById("trw_ms");
  
//~ console.log(ms);
//~ }

var trw_ms5_aberto           = {id:null, tempo: 7777, limpar: null};
var trw_ms5_submenu_aberto   = {o :null, tempo: 7777, limpar: null};

function trw_ms5_abrir(id_menu) {
//~ console.log(id_menu, trw_cab_actual);
  if (trw_ms5_aberto.id != null) trw_ms5_pechar();

  trw_ms5_aberto.id = id_menu;

//~ console.log(trw_ms5_aberto);
  var m_illa = document.getElementById( "trw_ms5_illa");

  if (m_illa == null) {
    console.error("ms5. Non está definido o elemento 'trw_ms5_illa' na plantilla.");

    return;
  }


  var m_abre = document.getElementById( trw_ms5_aberto.id   );

  m_illa.innerHTML = m_abre.innerHTML;
  m_abre.innerHTML = "";

  m_illa.style.display = "flex";

//~ console.log(m_illa, m_abre);

  if (trw_cab_actual == 2) { //* se estamos no menú móbil.
//~ console.log(m_abre.parentNode.parentNode);

    //* movemos m_illa a unha posición relativa á opción que invoca a esta función.
    m_abre.parentNode.parentNode.insertBefore(m_illa, m_abre.parentNode);

    //* intercambiamos o caracter marcador de grupo.
    trw_ms5_mobil_intercambiar_marcador_grupo(m_abre, true);
  }
  else {
    //* configuracion para cabeceiras de tipo 0 e 1.

    trw_ms5_aberto.limpar = setTimeout( function() { trw_ms5_pechar() } , trw_ms5_aberto.tempo );
  }

  trw_ms5_ordenar_submenus(m_illa);
}

function trw_ms5_pechar() {
  if (trw_ms5_aberto.id == null) return;

  clearInterval( trw_ms5_aberto.limpar );

  var m_illa = document.getElementById( "trw_ms5_illa");

  if (m_illa == null) {
    console.error("MS5. Non está definido o elemento 'trw_ms5_illa' na plantilla.");

    return;
  }


  var m_pech = document.getElementById( trw_ms5_aberto.id );

  m_pech.innerHTML = m_illa.innerHTML;
  m_illa.innerHTML     = "";

  m_illa.style.display = "none";

  trw_ms5_aberto.id = null;


  if (trw_cab_actual == 2) { //* se estamos no menú móbil.
    //* intercambiamos o caracter marcador de grupo.
    trw_ms5_mobil_intercambiar_marcador_grupo(m_pech, false);
  }
}

function trw_ms5_ordenar_submenus(m_illa) {
  if (trw_ms5_submenu_aberto.o != null) return; //* so primeira vez.

  var _o = m_illa.getElementsByClassName("ms5_subgrupo");

  for (var i = 0; i < _o.length; i++) {
    if (trw_cab_actual == 2) {
      _o[i].children[1].style.display = "none";

//~ console.log(_o[i]);
//~ console.log(_o[i].children[0].firstElementChild.firstElementChild.firstElementChild);

      trw_ms5_mobil_intercambiar_marcador_grupo( _o[i].children[0].firstElementChild.firstElementChild.firstElementChild, false );

      continue;
    }

    _o[i].children[1].style.display = "initial";
  }
}

function trw_ms5_submenu_abrir(o) {
//~ console.log(o, trw_cab_actual);
  if (trw_ms5_submenu_aberto.o != null) trw_ms5_submenu_pechar();

  trw_ms5_submenu_aberto.o = o;


  //* abrimos submenu.
  trw_ms5_mobil_intercambiar_marcador_grupo(o, true);

  var o2 = o.parentNode.parentNode.parentNode.parentNode;

//~ console.log(o2);

  o2.children[1].style.display = "initial";

  //~ trw_ms5_submenu_aberto.limpar = setTimeout( function() { trw_ms5_submenu_pechar() } , trw_ms5_submenu_aberto.tempo );
}

function trw_ms5_submenu_pechar() {
  if (trw_ms5_submenu_aberto.o == null) return;

  clearInterval( trw_ms5_submenu_aberto.limpar );

  trw_ms5_mobil_intercambiar_marcador_grupo(trw_ms5_submenu_aberto.o, false);

  var o2 = trw_ms5_submenu_aberto.o.parentNode.parentNode.parentNode.parentNode;

  o2.children[1].style.display = "none";

  trw_ms5_submenu_aberto.o = null;
}

function trw_ms5_mobil_intercambiar_marcador_grupo(m, abrir) {
  var m2 = m.parentNode.parentNode.firstElementChild.firstElementChild;
//~ console.log(m2);

  m2.innerHTML = (abrir)?"━":"✚";
  //~ m2.innerHTML = (abrir)?"▼":"▶";
}





/*  MenuSite ms6  */

var trw_ms6_aberto           = {id:null, tempo: 7777, limpar: null};
var trw_ms6_submenu_aberto   = {o :null, tempo: 7777, limpar: null};

function trw_ms6_abrir(id_menu) {
//~ console.log(id_menu, trw_cab_actual);
  if (trw_ms6_aberto.id != null) trw_ms6_pechar();

  trw_ms6_aberto.id = id_menu;

//~ console.log(trw_ms6_aberto);
  var m_illa = document.getElementById( "trw_ms6_illa");

  if (m_illa == null) {
    console.error("ms6. Non está definido o elemento 'trw_ms6_illa' na plantilla.");
    return;
  }

  m_illa.classList.add("ms6-abrir-submenu");
  m_illa.addEventListener("mouseleave", () => m_illa.classList.remove("ms6-abrir-submenu"));

  var m_abre = document.getElementById(trw_ms6_aberto.id);

  m_illa.innerHTML = m_abre.innerHTML;
  m_abre.innerHTML = "";

  m_illa.style.display = "flex";

//~ console.log(m_illa, m_abre);

  if (trw_cab_actual == 2) { //* se estamos no menú móbil.
//~ console.log(m_abre.parentNode.parentNode);

    //* movemos m_illa a unha posición relativa á opción que invoca a esta función.
    m_abre.parentNode.parentNode.insertBefore(m_illa, m_abre.parentNode);

    //* intercambiamos o caracter marcador de grupo.
    trw_ms6_mobil_intercambiar_marcador_grupo(m_abre, true);
  }
  else {
    //* configuracion para cabeceiras de tipo 0 e 1.

    trw_ms6_aberto.limpar = setTimeout( function() { trw_ms6_pechar() } , trw_ms6_aberto.tempo );
  }

  trw_ms6_ordenar_submenus(m_illa);
}

function trw_ms6_pechar() {
  if (trw_ms6_aberto.id == null) return;

  clearInterval( trw_ms6_aberto.limpar );

  var m_illa = document.getElementById( "trw_ms6_illa");

 // console.log("m_illa", m_illa);

  if (m_illa == null) {
    console.error("MS5. Non está definido o elemento 'trw_ms6_illa' na plantilla.");
    return;
  }


  var m_pech = document.getElementById( trw_ms6_aberto.id );

  m_pech.innerHTML = m_illa.innerHTML;
  
  if (screen.widht < 888) {
     m_illa.innerHTML     = "";
     m_illa.style.display = "none";
  }

 // m_illa.innerHTML     = "";
 // m_illa.style.display = "none";

  trw_ms6_aberto.id = null;


  if (trw_cab_actual == 2) { //* se estamos no menú móbil.
    //* intercambiamos o caracter marcador de grupo.
    trw_ms6_mobil_intercambiar_marcador_grupo(m_pech, false);
  }
}

function trw_ms6_ordenar_submenus(m_illa) {
  if (trw_ms6_submenu_aberto.o != null) return; //* so primeira vez.

  var _o = m_illa.getElementsByClassName("ms6_subgrupo");

  for (var i = 0; i < _o.length; i++) {
    if (trw_cab_actual == 2) {
      _o[i].children[1].style.display = "none";

//~ console.log(_o[i]);
//~ console.log(_o[i].children[0].firstElementChild.firstElementChild.firstElementChild);

      trw_ms6_mobil_intercambiar_marcador_grupo( _o[i].children[0].firstElementChild.firstElementChild.firstElementChild, false );

      continue;
    }

    _o[i].children[1].style.display = "initial";
  }
}



/****************************** */
/* Terceiro nivel do submenú   */
/**************************** */

function trw_ms6_submenu_abrir(o) {
  // console.log("param 'o':  ",o ,"Cabecera : ", trw_cab_actual);
  const txtTitulo = o.nextElementSibling.innerText;
  const enlace = o.parentNode.parentNode["href"]; //enlace que envolve a opcion do submenu
  const panelMenu_1 = document.getElementById("id_panel_menu_1");
  const panelMenu_2 = document.getElementById("id_panel_menu_2");
  const menu_2_titulo = panelMenu_2.querySelector(".panel-menu__titulo");
  const menu_2_lista = panelMenu_2.querySelector(".panel-menu__lista");
  const footer = document.getElementById("id_menu_footer");

  //Titulo encabezado menu-2  <a> creado para engadir no título do panel
  menu_2_titulo.innerHTML = "";
  let link = document.createElement("a");
  link.setAttribute("href", enlace);
  link.textContent = txtTitulo;
  menu_2_titulo.appendChild(link);

  //o2 é o ms6_subgrupo
  const o2 = o.parentNode.parentNode.parentNode.parentNode;
  const subgrupo = o2.querySelector(".subms_capa_2").children[0];

  const subgrupoCopy = subgrupo.cloneNode(true);
  menu_2_lista.appendChild(subgrupoCopy);

  //Añadir borrar clases para transición menu
  if (panelMenu_2.classList.contains("hide")) {
    panelMenu_2.classList.remove("hide");
    panelMenu_2.classList.add("show");
    panelMenu_1.classList.add("hide");
  }

  tilia_mover("id_menu_footer", "id_panel_menu_2", 2);
}

function trw_ms6_atras(o) {
  const panelMenu_1 = document.getElementById("id_panel_menu_1");
  const panelMenu_2 = document.getElementById("id_panel_menu_2");
  const menu_2_lista = panelMenu_2.querySelector(".panel-menu__lista");

  //transicions
  if (panelMenu_2.classList.contains("show")) {
    panelMenu_2.classList.remove("show");
    panelMenu_2.classList.add("hide");
    panelMenu_1.classList.remove("hide");
  }

  //limpar lista
  menu_2_lista.innerHTML = "";

  tilia_mover("id_menu_footer", "id_panel_menu_1", 2);
}


function trw_ms6_submenu_pechar() {
  if (trw_ms6_submenu_aberto.o == null) return;

  clearInterval( trw_ms6_submenu_aberto.limpar );

  trw_ms6_mobil_intercambiar_marcador_grupo(trw_ms6_submenu_aberto.o, false);

  var o2 = trw_ms6_submenu_aberto.o.parentNode.parentNode.parentNode.parentNode;

  o2.children[1].style.display = "none";

  trw_ms6_submenu_aberto.o = null;
}

function trw_ms6_mobil_intercambiar_marcador_grupo(m, abrir) {
  var m2 = m.parentNode.parentNode.firstElementChild.firstElementChild;

 // m2.innerHTML = (abrir) ? " ━ " : " ✚ ";
    m2.innerHTML = (abrir)?"▼":"▶";
}


var trw_menu_aberto_h2 = [];

function trw_submenu_h2_toggle(id_menu, prof) {
  //* Inicia menú.
  const m = document.getElementById(id_menu);

//~ console.log(m);

  if (m == null) return;

  //* Control da pila de capas.
  trw_submenu_h2_toggle_3(id_menu, prof);

  //* abre o menú.
  if (m.classList.contains("abrir")) return;

  m.classList.toggle("abrir");
  trw_submenu_h2_actualiza_mais(m);

  //* programa peche do menú.
  setTimeout( function(){ trw_submenu_h2_toggle_2(prof) } , trw_menu_aberto_h2[prof].tempo );
}

function trw_submenu_h2_toggle_2(prof) {
//~ console.log(`trw_submenu_h2_toggle_2: ${prof}`);

  if (!trw_menu_aberto_h2[prof].pechar) {
    setTimeout( function(){ trw_submenu_h2_toggle_2(prof) } , (trw_menu_aberto_h2[prof].tempo / 2) );

    return;
  }

  var m = document.getElementById(trw_menu_aberto_h2[prof].id);

  if (m == null) return;

  if (!m.classList.contains("abrir")) return;

  m.classList.toggle("abrir");
  trw_submenu_h2_actualiza_mais(m)
}

function trw_submenu_h2_toggle_3(id_menu, prof) {
//~ console.log(trw_menu_aberto_h2);

  //* pecha capas.
  for (var p = trw_menu_aberto_h2.length; p >= prof; p--) {
    if ( trw_menu_aberto_h2[p] == null ) continue;

    trw_submenu_h2_toggle_2( p );
  }

  //* abre capa.
  trw_menu_aberto_h2[prof]    = {id:null, pechar:true, tempo: 9999};
  trw_menu_aberto_h2[prof].id = id_menu;
}

function trw_submenu_h2_actualiza_mais(menu) {
//~ console.log(menu);
  var aux_1 = menu.parentNode.parentNode;

//~ console.log(aux_1);

  var aux_2 = aux_1.firstElementChild;

  aux_2.innerHTML = (menu.classList.contains('abrir'))?"▼":"▶";
}



var trw_subms3_swap  = null;
var trw_subms3_clock = 5;

function trw_ms3_over(o) {
  if (o.parentNode.className == 'ms_opcion_selec') return;

  var _oid = o.id.split(cnome);

  var trw_subms3_activar = document.getElementById("trw-subms3-" + _oid[2]);

  var _subms3 = document.getElementsByName("trw-subms3");

  for (var i = 0; i < _subms3.length; i++) _subms3[i].style.display = "none";

  if (trw_subms3_swap != null) trw_subms3_swap.style.display = "none";

  trw_subms3_activar.style.display = "flex";


  startclock("trw_ms3_out('trw-subms3-" + _oid[2] + "')", 3333, trw_subms3_clock);
}

function trw_ms3_out(id) {
  if (trw_subms3_swap != null) trw_subms3_swap.style.display = "inline";

  document.getElementById(id).style.display = "none";

  stopclock(trw_subms3_clock);
}

function trw_ms3_swap() {
  var _subms3 = document.getElementsByName("trw-subms3");

  for (var i = 0; i < _subms3.length; i++) {
    if (_subms3[i].style.display != "inline") continue;

    trw_subms3_swap = _subms3[i];

    break;
  }
}



//--------------------------------

function trw_logosite() {
  var _l = document.getElementsByClassName("celda_logo");

  return _l[0];
}


//--------------------------------

function trw_subir() {
  tilia_scroll_top(-80, 10);
}

//--------------------------------

function trw_noimx(src) {
  var _src = src.split("/");

  return _src.pop() == "noimx.png";
}

//--------------------------------

function trw_dominio() {
  return document.getElementById("findex;dominio").value;
}

//--------------------------------

function trw_editor() {
  return document.getElementById("findex;editor").value == "1";
}

//--------------------------------

function trw_url(url, site) {
//~ console.log(window.location);

  var p = window.location.protocol;
  var d = trw_dominio();

//~ console.log(p, d, url, site);


  return p + "//" + d + "/" + url;
}

//--------------------------------

function trw_vmobil() {
  //~ return document.getElementById("findex;w").value < 400;
  var dbody = document.getElementById("dbody");

  if (dbody == null) return false;

  return dbody.scrollWidth <= 400;
}

//--------------------------------

function trw_vm_bpechar(o) {
  var src = o.getAttribute("bpecha_id");

  enviaEventoAJAX_src(src, "onclick", "", null, null)
}

//--------------------------------

function cxcli2_bAceptar(baceptar, ajax) {
  var a = baceptar.id.split(";");

  var o = document.getElementById("findex;detalle;cmsxdet;c;" + a[4] + ";btnr;sGardando");

  if (o == null) o = document.getElementById("findex;detalle;prgf*1;cxcli2c;" + a[4] + ";btnr;sGardando");


  if (o != null) o.style.display = "inline";


  //~ enviaEventoAJAX  (baceptar, "onclick", "", null, null);
  enviaEventoSubmit(baceptar, "onclick", "", null, null);
}

//--------------------------------
/*
function trw_enlace_cat(id_cat) {
//~ console.log(id_cat);

  var action    = document.forms[0].action;
  var id_idioma = document.getElementById("findex" + cnome + "id_idioma").value;

//~ console.log(action);

  document.location.href = action + "?f=" + id_idioma + "&f1=" + id_cat;
}
*/
//--------------------------------

function tbusca_onblur(b) {
  if (b.value != "") return;

  //~ enviaEventoAJAX(b, "onblur", "");
  enviaEventoSubmit(b, "onblur", "", "");
}

//--------------------------------

function valida_number(n, predet) {
  var v   = parseFloat( n.value.replace(",", ".") );

  var max = parseInt(n.max);
  var min = parseInt(n.min);

  if (!v) v = predet;

  if (v > max) v = max;
  if (v < min) v = min;

  n.value = v;
}

//--------------------------------

function cpagar2_bmais(i, s) {
  var u = document.getElementById("findex;cpagar2;ccarro2_lista;uds*" + i);

  u.value = Math.round(u.value) + s;

  if (u.value < 0  ) u.value =  0;
  if (u.value > 999) u.value =  999;

  enviaEventoAJAX(u, "onchange", "", null, null );
}

function cpagar2_chlogin(ch) {
  //~ chekea(ch);

  var b = (ch.checked)?"initial":"none";

  document.getElementById("cpagar_chlogin").style.display = b;
}

function cpagar2_codprom_onchange(o) {
  document.getElementById("cpagar_codprom_agarda").style.display = "inline";

  //~ enviaEventoSubmit(o, "onclick", "", "");
  enviaEventoAJAX(o, "onclick", "");
}

function cpagar2_cregalo_click(ip, ia) {
  var h = document.getElementById("findex;cpagar2;ccarro2_lista;cregalos;h");

  h.value = ip + ";" + ia;

  //~ enviaEventoSubmit(h, "onclick", "", "");
  enviaEventoAJAX(h, "onclick", "");
}


async function cpagar2_finalizar() {
  var bfin = document.getElementById("findex;cpagar2;bAceptar");

  await enviaEventoAJAX(bfin, "onclick");
}

function cpagar2_finalizar_redirect(url) {
  window.location.href = url;
}

//--------------------------------


const trw_cook_men    = "trw_cookies_men";
var   trw_cook_config = -2;

function ccookies_inicia() {
  var config = ccookies_config();
  
  if (config == -2) return;
  
       if (config == 2) ccokkies_menu_A();
  else if (config == 1) ccokkies_menu_A();
  else                  ccokkies_menu_B();
}

function ccookies_config(config) {
  if (config != undefined) trw_cook_config = config;
  //console.log(config);
//~ console.log(config, trw_cook_config);

  return trw_cook_config;
}

function cookies_validar_url(url) {
  let config = ccookies_config();
  
  if (config == -2) return true;
  if (config ==  2) return true;
       

  const URL_0 = new URL(url);
  //~ const ctdval = (_ccookies_dominios_validos == []) ? _ccookies_dominios_validos.length : 0;
  
  //~ for (let i = 0; i < ctdval; i++) {
    //~ if (URL_0.hostname == _ccookies_dominios_validos[i]) return true;
  //~ }
          
  return (URL_0.hostname == document.location.hostname);      
}

function ccokkies_menu_A() {
//~ console.log("AAAAAAAAAAAAAAA");
  var men = document.getElementById(trw_cook_men);
  
  if (men == null) return;
  
  men.classList.add("ccookies_sites_1");
  
  men.innerHTML = "<di onclick='ccokkies_menu_B()'>Cookies</div>";
}

function ccokkies_menu_B() {
 // console.log("BBBBBBBBBBBBBBB");
  var men = document.getElementById(trw_cook_men);
 // console.log(trw_cook_men, men);

  if (men == null) return;
  
  men.classList.remove("ccookies_sites_1");

  men.innerHTML = `
<div>
  <div>${trw_cook_aviso}</div>
  <div style='display: flex;justify-content: center;gap: 1.3em;'>${trw_cook_mais}${trw_cook_rexei}${trw_cook_acept}</div>
</div>`;

//console.log("config:", trw_cook_config);

if(trw_cook_config == 1){

  document.getElementById('findex;ccookies;lNecs').classList.add('ccookies_boton_activo');
  document.getElementById('findex;ccookies;bAceptar').classList.remove('ccookies_boton_activo');

} else if(trw_cook_config == 2){

  document.getElementById('findex;ccookies;bAceptar').classList.add('ccookies_boton_activo');
  document.getElementById('findex;ccookies;lNecs').classList.remove('ccookies_boton_activo');
  }
}

async function ccookies_aceptar_click(o) {
  ccokkies_menu_A();
  
  ccookies_config(2);

   //console.log(o);
   

  let promise = new Promise((resolve, reject) => {
    resolve(enviaEventoAJAX(o, "onclick", "", null, null));
  });
  
  let result = await promise;

  
  if (!result) alert("ccookies_aceptar_click(). Sucedeu un erro de comunicación co servidor.");


}

async function ccookies_necesarias_click(o) {
  ccokkies_menu_A();
    
  ccookies_config(1);
  
  let promise = new Promise((resolve, reject) => {
    resolve(enviaEventoAJAX(o, "onclick", "", null, null));
  });
  
  let result = await promise;
  
  if (!result) alert("ccookies_necesarias_click(). Sucedeu un erro de comunicación co servidor.");
}


//--------------------------------

function trw_RS_url(rs) {
  switch (rs) {
    case "facebook": return "https://www.facebook.com/sharer/sharer.php?u=";
    case "twitter" : return "https://twitter.com/intent/tweet?&url=";
    case "linkedin": return "https://www.linkedin.com/shareArticle?url=";
    case "whatsapp": return "whatsapp://send?text=";
  }

  return null;
}

function trw2facebook(a) {
  var s = trw_RS_url("facebook");

  if (a == undefined) {
    window.open(s + encodeURI(document.location.href));

    return;
  }

  trw2RS_aux(s, a);
}

function trw2twitter(a) {
  var s = trw_RS_url("twitter");

  if (a == undefined) {
    window.open(s + encodeURI(document.location.href));

    return;
  }

  trw2RS_aux(s, a);
}

function trw2linkedin(a) {
  var s = trw_RS_url("linkedin");

  if (a == undefined) {
    window.open(s + encodeURI(document.location.href));

    return;
  }

  trw2RS_aux(s, a);
}

function trw2whatsapp(a) {
  var s = trw_RS_url("whatsapp");

  if (a == undefined) {
    window.open(s + encodeURI(document.location.href));

    return;
  }

  trw2RS_aux(s, a);
}

function trw2RS_aux(s, a) {
//~ console.log([s,a]);
  //* función auxiliar para componente CRS dun elmt_novas

  var _id = a.id.split(cnome + "crs" + cnome);

  var a2 = document.getElementById( _id[0] + cnome + "ldetalle");

//~ console.log(a2.href);


  s += encodeURI(a2.href);


  window.open(s);
}

//--------------------------------

function crs_disparador_onmouseover(src) {
  document.getElementById(src).style.display = "block";
}

function crs_disparador_onmouseout(src) {
  document.getElementById(src).style.display = "none";
}

function crs_onmouseover(o) {
  o.style.display = "block";
}

function crs_onmouseout(o) {
  o.style.display = "none";
}

//--------------------------------

function trw_cloca_coords(o) {
  var coords = tilia_coords();

//~ console.log(coords);

  if (coords == null) {
    tilia_localizar_inicia(null, true);

    coords = tilia_coords();
  }
  else if (coords[0] == "erro") {
    tilia_localizar_inicia(null, true);

    coords = tilia_coords();
  }

  if (coords[0] == "erro") return;

  document.getElementById(o.id.substring(0, o.id.length - 7) + "lat").value = coords[0];
  document.getElementById(o.id.substring(0, o.id.length - 7) + "lnx").value = coords[1];

  enviaEventoAJAX(o, "onclick", "", null, null);
}

//---------------------------------

var trw_cab_ini    = false;
var trw_cab_actual = -1;

function trw_control_cabs(o) {
//~ console.log(o);

  var t      = window.scrollY;
  //var w = document.getElementById("dbody").scrollWidth; //* dbody, este id sempre estará asociado a o div do body falso (editor)

  let w = window.innerWidth; //ancho respecto ao viewport
  var bsubir = document.getElementById(o.id_bsubir);         //* esta función tamén se ocupa de encender/apagar o botón bsubir.

//~ console.log([t, w]);

  if (w <= o.w_min) {
    if (trw_cab_actual != 2) { //* primeira vez. debemos transitar á cabeceira 3.
      o.f_trans_2();

      trw_cab_actual = 2;

      return;
    }

    return; //* (w <= o.w_min) => (só cab 2)
  }


  if (trw_cab_actual == 2) {
    o.f_trans_0();

    trw_cab_actual = 0;

    return;
  }


  if (t >= o.top_max) {
    if (trw_cab_actual == 1) return;

    if (bsubir != null) bsubir.style.display = "inline";

    o.f_trans_1();

    trw_cab_actual = 1;
  }

  if (t < (o.top_max - o.top_marxe)) {
    if (trw_cab_actual == 0) return;

    if (bsubir != null) bsubir.style.display = "none";

    o.f_trans_0();

    trw_cab_actual = 0;
  }
}

//------------------------------

function trw_bmenu_click() {
  tilia_scroll_top();

  var ms=document.getElementById("trw_ms_desplegable");

  var display=(ms.style.display=="none")?"block":"none";

  ms.style.display=display;
}

//------------------------------

function trw_confirmar_ok( id_baceptar ) {
  var b = document.getElementById(id_baceptar);

  if (b == null) return;

  var d = document.getElementById("trw_confirmar_ok_illa");

  if (d == null)
    b.onclick = function() { window.location = "perfil.php"; };
  else
    b.onclick = function() { window.location = d.value; };

//~ console.log(d.value);
}

//------------------------------

var trw_notifica_permiso = null;
var trw_notifica_coockie = "trw-notifica";

var trw_notifica_aa = {
  id_illa   : null,
  action    : "triwus/php/rpc/notifica/notas.php",
  f_resposta: function( responseText ) { trw_notifica_resposta( responseText ) }
};

function trw_notifica_ini(id_op) {
  if (trw_editor()) return;

  if (id_op == undefined) id_op = 1;

  tilia_aa_envia(trw_notifica_aa, id_op, trw_notifica_param());
}

function trw_notifica_param() {
  var param  = "n1=" + encodeURIComponent(document.domain);

  var cookie = tilia_cookie_leer( trw_notifica_coockie );

  if (cookie == null) return param;

  return param + "&n2=" + encodeURIComponent(cookie);
}

function trw_notifica_resposta(responseText) {
//~ console.log(responseText);

  var nota = JSON.parse( responseText );


  //* Control de permisos do usuario sobre notificacións.

  if (trw_notifica_permiso == null) {
    Notification.requestPermission().then( function(result) { trw_notifica_permiso = result; } );
  }

  if ( trw_notifica_permiso != "granted" ) return; //* non temos permiso.

  //* Crea nota e notifica.
  var options = {
      body: nota.txt,
      icon: nota.ico
  }

  var n = new Notification(document.domain, opcions);

  n.onclick = function() {
    trw_notifica_ini(2); //* anota rexistro stats_a.

    if (nota.url == null) return;

    document.location.href = nota.url;
  };

  n.onclose = function() { trw_notifica_ini(3); }; //* anota rexistro stats_b.


  setTimeout(n.close.bind(n), 5000); //* pecha a notificanción despóis de n mseg.


  //* Anota notificación, (cookie).
  var m = tilia_cookie_leer( trw_notifica_coockie );

  tilia_cookie_crear(trw_notifica_coockie, nota.momento_ini, 60*60*24*10, "/path", document.domain);
}

