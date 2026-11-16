var a_prgf=null;prgf_reset();function prgf_reset(){a_prgf=new Array();a_prgf[0]=new Array();a_prgf[1]=new Array();a_prgf[2]=new Array();a_prgf[3]=new Array()}
function prgf_info(id_prgf){var i=prgf_i(id_prgf);var prgfinfo=new Array();prgfinfo[0]=i;prgfinfo[1]=id_prgf;prgfinfo[2]=a_prgf[1][i];prgfinfo[3]=a_prgf[2][i];prgfinfo[4]=a_prgf[3][i];return prgfinfo}
function prgf_i(id_prgf){for(var i=0;i<a_prgf[0].length;i++){if(id_prgf!=a_prgf[0][i])continue;return i}
return prgf_post(id_prgf)}
function prgf_post(id_prgf){var p=a_prgf[0].length;a_prgf[0][p]=id_prgf;a_prgf[1][p]=prgf_calcula_numcols(id_prgf);a_prgf[2][p]=prgf_calcula_desfase_w(id_prgf);a_prgf[3][p]=prgf_calcula_excluir(id_prgf);return p}
function prgf_calcula_numcols(id_prgf){return document.getElementById(id_prgf+"__nc").value}
function prgf_calcula_excluir(id_prgf){return document.getElementById(id_prgf+"__excluir").value}
function prgf_calcula_desfase_w(id_prgf){var dw=0;var prgf=document.getElementById(id_prgf);if(!prgf)return dw;if(!prgf.style)return dw;if(prgf.style.margin)
dw=dw+parseInt(prgf.style.marginRight)+parseInt(prgf.style.marginLeft);if(prgf.style.padding)
dw=dw+parseInt(prgf.style.paddingRight)+parseInt(prgf.style.paddingLeft);return dw}
function prgf_bgcolor(id_prgf){var prgf=document.getElementById(id_prgf);if(!prgf)return null;if(!prgf.style)return null;return prgf.style.backgroundColor}
async function prgf_crear(id_prgf,posicion){var det=document.getElementById("findex;detalle;illa");if(det==null)return;if(posicion==undefined)posicion=det.childNodes.length;var id_aux=id_prgf+";illa";var prgf=document.getElementById(id_aux);if(prgf==null){prgf=document.createElement("div");prgf.id=id_aux;prgf.name=id_aux;det.insertBefore(prgf,det.childNodes[posicion-1])}
let promise=new Promise((resolve,reject)=>{resolve(enviaEventoAJAX(prgf,"solic-html","",null,null))});let result=await promise;if(!result)alert("Sucedeu un erro de comunicación co servidor.");}
function prgf_ocultar(id_prgf){var prgf=document.getElementById(id_prgf);if(prgf==null)return;prgf.parentNode.removeChild(prgf)}
function prgf_onmouseover(p,css){p.className=css;if(p.style.backgroundColor!=null)p.style.borderColor=null;let l=document.getElementById(p.id+"_legend");if(l==null)return;l.style.display="flex"}
function prgf_onmouseout(p,css){p.className=css;if(p.style.backgroundColor!=null)p.style.borderColor=p.style.backgroundColor;let l=document.getElementById(p.id+"_legend");if(l==null)return;l.style.display="none"}
function prgf_iventas_0000(){const panel=document.getElementById("id_ccatalogo_filtros");const backdrop=document.getElementById("trw_iventas_00");if(panel&&backdrop)panel.classList.toggle("open")?(backdrop.style.display="block"):(backdrop.style.display="none");}
function prgf_iventas_0001(pechar){if(pechar==1)prgf_iventas_0000();var d0=document.getElementById("trw_iventas_00");var hevento=d0.querySelector("input[type='hidden']");hevento.value="f";enviaEventoAJAX(hevento,"onchange","",null,null)}
function prgf_iventas_0002(){var d0=document.getElementById("trw_iventas_00");var hevento=d0.querySelector("input[type='hidden']");hevento.value="l";enviaEventoSubmit(hevento,"onchange","")}
function prgf_iventas_url_f(action){if(action==undefined)action=document.forms[0].action+"?f=-1";var f=action;var f_aux=null;if((f_aux=prgf_iventas_url_f_aux_a())!="")f+="&f0="+f_aux;if((f_aux=prgf_iventas_url_f_aux_b("trw_ventas_busca_subcats"))!="")f+="&f1="+f_aux;if((f_aux=prgf_iventas_url_f_aux_b("trw_ventas_busca_marcas"))!="")f+="&f2="+f_aux;document.location.href=f}
function prgf_iventas_url_f_aux_a(){var t=document.querySelector("input[trw_novas2_busca_etq_0]");if(t==null)return"";return encodeURIComponent(t.value)}
function prgf_iventas_url_f_aux_b(tipo){var d=document.getElementById(tipo);var _h=d.getElementsByTagName("input");var f_aux="";var _v=null;for(var i=0;i<_h.length;i++){if(_h[i].value!=1)continue;if(f_aux!="")f_aux+=",";_v=_h[i].id.split(csubnome);f_aux+=_v[_v.length-1]}
return f_aux}
function prgf_iventas_0000_colorea(){const urlParams=new URLSearchParams(window.location.search);const iconoFiltros=document.getElementById("cxcli3-buscador_2");const filtros=["f0","f1","f2","f3"];filtros.forEach((filtro)=>{if(urlParams.has(filtro)&&urlParams.get(filtro)!==""){iconoFiltros.classList.add("cxcli3_filter");return}})}
function prgf_imarcas_0000(json_marcas,vista,m){var illa=document.getElementById("trw_prgf_marcas_illa");var _path=location.pathname.replaceAll("/","").split(".");var clase=(vista==2)?"imarcas-collage":"";illa.innerHTML="";for(let i=0;i<json_marcas.length;i++){var href="";if(trw_editor())
href=`onclick="alert('Función no disponible en el editor')"`;else href=`href="${_path[0]}/${json_marcas[i].nome.replaceAll(" ", "")}/${m}=${json_marcas[i].id_marca}/"`;let marca_html=`
    <a ${href} title="${json_marcas[i].nome}" class="${clase} marcas-caja-borde">
      <img src="${json_marcas[i].id_logo}" />
    </a> `;illa.innerHTML+=marca_html}}
function prgf_producto_solicita_1(selector){console.log(selector);prgf_producto_solicita_2(selector.value)}
function prgf_producto_solicita_2(p){var o=document.querySelector('[trw_prgfProducto_p]');o.value=p;enviaEventoSubmit(o,"onchange","","")}