function novas2_idjs(){var d=document.getElementById("trw-catalogo-id");if(d==null)return null;return d.value}
function novas2_elmt_btnr_over(id_paxina,mostrar){var c=document.getElementById(`btnr${csubnome}${id_paxina}${cnome}capa`);c.style.display=(mostrar)?"flex":"none"}
async function novas2_editar_1_click(id_paxina,codigo){if(codigo=="suprime"){if(!window.confirm("¿ Deseas borrar la entrada seleccionada ?"))return}
var c=document.getElementById(novas2_idjs()+cnome+"_h10");if(c==null)return;c.value=`["${codigo}", ${id_paxina}]`;let promise=new Promise((resolve,reject)=>{resolve(enviaEventoAJAX(c,"onclick","",null,null))});let result=await promise;if(!result)alert("Sucedeu un erro de comunicación co servidor.");}
function novas2_editar_1_click_resposta(){var id_base="findex;detalle_btnr_oeste;editor";var imx_proporcion=document.getElementById(`${id_base}${cnome}hcrop`).value;elmt_novas_editar(id_base,imx_proporcion)}
function novas2_editar_2_click(id_paxina){var c=document.getElementById(novas2_idjs()+cnome+"_h10");if(c==null)return;c.value=`["edit2", ${id_paxina}]`;enviaEventoSubmit(c,"onclick","","")}
function novas2_editar_3_click(id_paxina){var c=document.getElementById(novas2_idjs()+cnome+"_h10");if(c==null)return;c.value=`["edit1", ${id_paxina}]`;enviaEventoAJAX(c,"onclick","",null,null)}
function novas2_trw2RS(rs,url){var s=trw_RS_url(rs)+encodeURI(url);window.open(s)}
function novas2_resposta_editar(pax){novas2_pechar_editor();trwCat_listar(pax)}
function novas2_resposta_intercambiar(pax1,pax2){var n1=document.querySelector(`[novas2="${pax1}"]`);var n2=document.querySelector(`[novas2="${pax2}"]`);n2.classList.remove('celda_prgf_over_bmais');if(pax1==pax2)return;n1=n1.parentNode.parentNode;n2=n2.parentNode.parentNode;var p=document.getElementById("cxcli3-lanuncios-0-panel");var pos1=Array.prototype.indexOf.call(p.children,n1);var pos2=Array.prototype.indexOf.call(p.children,n2);if(pos1<pos2)
n2.after(n1);else p.insertBefore(n1,n2)}
function novas2_pechar_editor(){document.getElementById("findex;detalle_btnr_oeste;det_bot_elmt").style.display="none"}