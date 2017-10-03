/**
 * Valida a quantidades de votos nas cédulas eleitorais
 * 
 */

function contaCheckbox(selecionados) {
  var inputs, x, selecionados=0;
  inputs = document.getElementsByTagName('input');
  for(x=0;x<inputs.length;x++) {
    if(inputs[x].type=='checkbox') {
      if(inputs[x].checked==true) {
        selecionados++;
      }
    }
  }
  return selecionados;
}

function desabilitaCheckbox() {
  var inputs, x;
  inputs = document.getElementsByTagName('input');
  for(x=0;x<inputs.length;x++) {
    if(inputs[x].type=='checkbox') {
      if(inputs[x].checked==false) {
        document.getElementById(inputs[x].id).disabled = true;
      }
    }
  }
  return 0;
}

function desabilitaSimNao(y) {
  var inputs, x, z;
  inputs = document.getElementsByTagName('input');
  if (y%2 == 0)
  	z = y-1;
  else
    z = y+1;
  for(x=0;x<inputs.length;x++){
    if(inputs[x].type=='checkbox' && inputs[x].id == z){
      document.getElementById(inputs[x].id).checked = false;
    }
  }
  return 0;
}

function habilitaCheckbox() {
  var inputs, x;
  inputs = document.getElementsByTagName('input');
  for(x=0;x<inputs.length;x++){
    if(inputs[x].type=='checkbox' && inputs[x].id != '2' ){
      document.getElementById(inputs[x].id).disabled = false;
    }
  }
  return 0;
}

function pegaQuantidade(quant) {
  var total, desabilita, habilita;
  total = contaCheckbox();
  if (total == quant){
	desabilita = desabilitaCheckbox();
	return true;
  }
  else {
  	habilita = habilitaCheckbox();
  	return false;
  }
}

function show_dialog(){
   dijit.byId("dialog").show();
   return;
}

function validaQuantidade(quant) {
    d = document.colar;
	var tot;
	tot = contaCheckbox();
	if (tot != quant) {
            show_dialog();
            return false;
	}
	else {
            return true;
	}
}

function validaQuant(quant) {
    d = document.colar;
	var tot;
	tot = contaCheckbox();
	if (tot < 1 || tot > quant) {
		if (quant > 1)
                    show_dialog();
			//alert("Selecione entre 1 e " + quant + " opções!");
		else
                    show_dialog();
			//alert("Selecione uma opção!");
		return false;
	}
	else {
		return true;
	}
}