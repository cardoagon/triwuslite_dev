function fechaHora() {
  const dt = new Date();

  const ds = dt.toLocaleDateString() + " " + dt.toLocaleTimeString();

  let dmomento = document.getElementById("cab_momento");

  if (dmomento != null) dmomento.innerHTML = ds;
}

setInterval(fechaHora, 1000);


function openMenu() {
  const menu    = document.querySelector(".adm_menu_0");
  const burguer = document.querySelector(".menu-btn__burguer");

  menu    ? menu   .classList.toggle("show") : "";
  burguer ? burguer.classList.toggle("open") : "";
}


function cadxunto_banexar_click(badd) {
  let id_f = badd.id.substr(0, badd.id.length -7) + "file";

  document.getElementById(id_f).click();
}

function clag_ch_click(o, k) {
  var s = document.getElementById(`clag_ico_${k}`);

  s.style.color = ( s.style.color == 'rgb(187, 187, 187)' )?'#00ba00':'rgb(187, 187, 187)';

  //~ chekea(o, "submit", "", "");
  chekea(o, "ajax", "", "");
}


function mostraFiltros(o) {
  const content = o.nextElementSibling;
 // content.classList.toggle("show");
  let altura = 0;
  if (content.clientHeight == 0) altura = content.scrollHeight;

  content.style.height = `${altura}px`;
}


function showFiltros() {
  const filtros = document.getElementsByClassName("filtro");
  Array.from(filtros).forEach(filtro => filtro.classList.toggle("sm-hide"));
}

function copyURL(elemento) {
  let url = document.getElementById(elemento);
  navigator.clipboard.writeText(url.innerText).then(() => {
    let range = document.createRange();
    range.selectNode(url);
    window.getSelection().addRange(range);
    alert(`URL dos materias,  copiada ao portapapeis`);
  },() => {
    console.error('error copiando ao portapapeles')
  });
}