function taboa_filaActualizada(taboa,fila){var x=document.getElementById(taboa+cnome+"estado"+csubnome+fila).value;if(x=="N")
x="NA";else{if(x=="")
x="A"}
document.getElementById(taboa+cnome+"estado"+csubnome+fila).value=x}
function taboa_check(taboa,fila){var checker=document.getElementById(taboa+cnome+"check"+csubnome+fila+csubnome+"checker");var check=document.getElementById(taboa+cnome+"check"+csubnome+fila);check.value=(checker.checked)?"1":"0";var pos_uch=document.getElementById(taboa+cnome+"ultimoCheck");if(pos_uch==null)return;if(pos_uch.value==fila){if(check.value==0)pos_uch.value="-1";return}
if((pos_uch.value!="-1")){var uch_checker=document.getElementById(taboa+cnome+"check"+csubnome+pos_uch.value+csubnome+"checker");if(uch_checker!=null){var uch_check=document.getElementById(taboa+cnome+"check"+csubnome+pos_uch.value);uch_check.value="0";uch_checker.checked=!uch_checker.checked}}
pos_uch.value=fila}