var capa_aid=new Array();var capa_apechar=new Array();var capa_idclock_base=10000;function mostrar_CAPA(capa,display,confirma,autopechar){if(capa==null)return!1;if(confirma!="")
if(!window.confirm(confirma))return!1;if(display=="disparador_inline"){display="block";if(autopechar==1){for(var i=0;i<capa_apechar.length;i++){capa_disparador_pechar(i)}}}else{if(display=="disparador_none")
display="none";else capa_disparador_control(capa.id)}
capa.style.display=display;return!0}
function capa_disparador_abrir(id_capa,autopechar){var aid=capa_disparador_buscar(id_capa);if(aid>0)return;if(aid==-1)aid=capa_disparador_rexistro(id_capa,autopechar);}
function capa_disparador_onmouseout(id_capa){var aid=capa_disparador_buscar(id_capa);if(aid==-1)return;capa_apechar[aid]=!1}
function capa_disparador_control(id_capa){var aid=capa_disparador_buscar(id_capa);if(aid==-1)return;stopclock(capa_idclock_base+aid);capa_aid[aid]=null;capa_apechar[aid]=!0}
function capa_disparador_buscar(id_capa){if(capa_aid.length==0)return-1;for(var i=0;i<capa_aid.length;i++)if(capa_aid[i]==id_capa)return i;return-1}
function capa_disparador_rexistro(id_capa,autopechar){var aid=capa_aid.length;capa_aid[aid]=id_capa;capa_apechar[aid]=!0;mostrar_CAPA(document.getElementById(id_capa),"disparador_inline","",autopechar);if(autopechar==1)startclock("capa_disparador_pechar('"+id_capa+"')",777,(capa_idclock_base+aid));return aid}
function capa_disparador_pechar(id_capa){var aid=capa_disparador_buscar(id_capa);if(aid==-1)return;if(capa_apechar[aid]){stopclock(capa_idclock_base+aid);mostrar_CAPA(document.getElementById(capa_aid[aid]),"disparador_none","");capa_aid[aid]=null}else{capa_apechar[aid]=!0}}