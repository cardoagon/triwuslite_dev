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



//* instalador.actualizador.


var trwInst_ok = true;

async function trwInst__bInstal() {
  let h = document.getElementById("findex;cactualiza;hInstal");
  
  trwInst__bInstal_resultado_set(true);

//~ console.log(h);

  let promise;
  let result;
  
  for (var i = 1; i < 6; i++) {
    await trwInst__bInstal_i(h, i);
    
    if ( !trwInst_ok ) break;
  }
  
  console.log("i", i);
}

async function trwInst__bInstal_i(h, i) {
  h.value = i;

console.log(h);

  trwInst__bInstal_resultado_set_i(i, true, false);

  let promise = new Promise((resolve, reject) => {
    resolve(enviaEventoAJAX(h, "onclick"));
  });

  let result = await promise;


  if (!result) alert("trwInst__bInstal_i(). Sucedeu un erro de comunicación co servidor.");
}

function trwInst__bInstal_i_resposta(_r) {
  console.log(_r);
  
  trwInst_ok = _r[1];
  
  trwInst__bInstal_resultado_set_i( _r[0], true, trwInst_ok );
}


function trwInst__bInstal_resultado_set(b) {
  var d = document.getElementById("cActu_resultado");
  
  d.style.display = (b) ? "initial": "none";
  
  for (let i = 1; i < 5; i++) trwInst__bInstal_resultado_set_i(i, false, false);
}

function trwInst__bInstal_resultado_set_i(i, ver, ok) {
  var d = document.getElementById(`cActu_resultado_${i}`);
  
  d.style.display = (ver) ? "flex": "none";
  
  if (ok)
   d.classList.add("cActu_resultado_i_ok");
  else
   d.classList.remove("cActu_resultado_i_ok");
}

