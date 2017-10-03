/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas a ordenação de dos documentos na leitura
 */


//** DECLARAÇÃO DE VARIAVEIS -------------------------------------------------*/
/**
 * armazena a ordem de leitura dos documentos de toda a tela
 * @type Array
 */
var _ordemLeitura = [];

$(function() {
//* CARREGAMENTO DEFAULT ------------------------------------------------------*/
    //carrega a variavel de leitura com base na primeira aba principal. preenche valor na _ordemLeitura
    carrega_array_leitura_tabs_principal($("#tabs > .tabs_principal")[0]);

//* USO DINÂMICO ------------------------------------------------------*/

    /**
     * Ao clicar no botão subir a tr que o botão está localizado vai trocar de lugar com a tr superior
     * 
     */
    $("#tabs").delegate(".subir", "click", function() {
        tabs_principal = $(this).closest(".tabs_principal");
        tabs_interna = $(this).closest(".tabs_documentos_juntados");
        ordem = $(this).closest("tr").find("td.ordem_leitura").html();
        troca_posicao_array_leitura_tabs(ordem, $(tabs_principal), $(tabs_interna), 'subir');
    });

    /**
     * Ao clicar no botão desde a tr que o botão está localizado vai trocar de lugar com a tr inferior
     * 
     */
    $("#tabs").delegate(".descer", "click", function() {
        tabs_principal = $(this).closest(".tabs_principal");
        tabs_interna = $(this).closest(".tabs_documentos_juntados");
        ordem = $(this).closest("tr").find("td.ordem_leitura").html();
        troca_posicao_array_leitura_tabs(ordem, $(tabs_principal), $(tabs_interna), 'descer');
    });
});

//* FUNCTIONS ------------------------------------------------------------------*/
/**
 * Carrega o array da seguencia de leitura para as abas da aba principal especificada
 * que tenham a tabela de leitura
 * procura pela class="grid" das divs com class="tabs_documentos_juntados"
 * insere a posição do documento na leitura no json da tr do documento
 * 
 * @param {DOM Element} elemento_tabs_principal
 * @returns {array}
 */
function carrega_array_leitura_tabs_principal(elemento_tabs_principal) {
    //id da tabs principal
    id_tabs_principal = $(elemento_tabs_principal).attr("id");
    //id do documento que é o dono da aba(tabs) principal
    array = id_tabs_principal.split("-");
    id_documento_tabs_principal = array[1];
    _ordemLeitura[id_documento_tabs_principal] = [];

    $("#tabs-" + id_documento_tabs_principal + " #tabs_interna-" + id_documento_tabs_principal + " > .tabs_documentos_juntados").each(function(index, tabs_interna) {
        id_tabs_interna = $(tabs_interna).attr("id");
        _ordemLeitura[id_documento_tabs_principal][id_tabs_interna] = [];

        tabela_documentos = $(tabs_interna).find(".grid");
        contador = 0;

        $(tabela_documentos).find("tbody > tr").each(function(index, tr) {
            //converte o value da tr e joga na variavel
            json_tr = $.parseJSON($(tr).attr("value"));
            //inclui a ordem de leitura na tela
            $(tr).find(".ordem_leitura").html(contador + 1);
            _ordemLeitura[id_documento_tabs_principal][id_tabs_interna][contador] = json_tr;
            contador++;
        });
    });

}

/**
 * Carrega o array da seguencia de leitura para a aba especificada da aba principal especificada
 * que tenham a tabela de leitura
 * procura pela class="grid"
 * 
 * @param {DOM Element} elemento_tabs_principal
 * @param {DOM Element} elemento_tabs_interna
 * @returns {array}
 */
function carrega_array_leitura_tabs_interna(elemento_tabs_principal, elemento_tabs_interna) {
    //id da tabs principal
    id_tabs_principal = $(elemento_tabs_principal).attr("id");
    //id do documento que é o dono da aba(tabs) principal
    array = id_tabs_principal.split("-");
    id_documento_tabs_principal = array[1];
    //id da tabs interior
    id_tabs_interna = $(elemento_tabs_interna).attr("id");

    tabela_documentos = $(elemento_tabs_interna).find(".grid");
    contador = 0;

    $(tabela_documentos).find("tbody > tr").each(function(index, tr) {
        //converte o value da tr e joga na variavel
        json_tr = $.parseJSON($(tr).attr("value"));
        //inclui a ordem de leitura na tela
        $(tr).find(".ordem_leitura").html(contador + 1);

        _ordemLeitura[id_documento_tabs_principal][id_tabs_interna][contador] = json_tr;
        contador++;
    });
}

/**
 * Troca a posição de um documento com outro documento no array de leitura.
 * Pode trocar com o elemento de cima ou com o elemento de baixo.
 * 
 * @param {int} ordem
 * @param {DOM Element} tabs_principal
 * @param {DOM Element} tabs_interior
 * @param {string} lado |"subir"|"descer"|
 * @returns {array}
 */
function troca_posicao_array_leitura_tabs(ordem, tabs_principal, tabs_interior, lado) {
    ordem = parseInt(ordem, 10);
    if (lado == "subir") {
        if (ordem > 1) {
            posicaoPrincipal = ordem - 1;

            posicaoTroca = ordem - 2;
            novaOrdemTroca = ordem;

            elemento_tr = $($(tabs_interior).find("td.ordem_leitura")[posicaoPrincipal]).closest("tr");
            $($(tabs_interior).find("td.ordem_leitura")[posicaoPrincipal]).html(posicaoPrincipal);

            elemento_tr_troca = $($(tabs_interior).find("td.ordem_leitura")[posicaoTroca]).closest("tr");
            $($(tabs_interior).find("td.ordem_leitura")[posicaoTroca]).html(novaOrdemTroca);

            $(elemento_tr_troca).before($(elemento_tr));


            $("button.descer").removeClass("ui-state-focus ui-state-hover");
            $("button.subir").removeClass("ui-state-focus");


            $(elemento_tr).css("background-color", "#87CEFF");
            $(elemento_tr).stop().animate({
                "background-color": "#fff"

            }, 1000);

            troca_ordem(tabs_principal, tabs_interior, posicaoPrincipal, posicaoTroca);
        } else {
            return 0;
        }
    } else if (lado == "descer") {
        qtd_documentos = $(tabs_interior).find(".grid > tbody > tr").size();
        if (ordem < qtd_documentos) {
            posicaoPrincipal = ordem - 1;
            novaOrdemPrincipal = ordem + 1;

            posicaoTroca = ordem;

            elemento_tr = $($(tabs_interior).find("td.ordem_leitura")[posicaoPrincipal]).closest("tr");
            ordem_tr = $($(tabs_interior).find("td.ordem_leitura")[posicaoPrincipal]).html(novaOrdemPrincipal);

            elemento_tr_troca = $($(tabs_interior).find("td.ordem_leitura")[posicaoTroca]).closest("tr");
            ordem_tr_troca = $($(tabs_interior).find("td.ordem_leitura")[posicaoTroca]).html(posicaoTroca);

            //coloca o elemento clicado depois do elemento troca
            $(elemento_tr_troca).after($(elemento_tr));

            $("button.descer").removeClass("ui-state-focus");
            $("button.subir").removeClass("ui-state-focus ui-state-hover");

            $(elemento_tr).css("background-color", "#87CEFF");
            $(elemento_tr).stop().animate({
                "background-color": "#fff"

            }, 1000);

            troca_ordem(tabs_principal, tabs_interior, posicaoPrincipal, posicaoTroca);
        } else {
            return 0;
        }
    }
}

/**
 * Troca a ordem de leitura no array de ordens
 * 
 * @param {DOMElement} tabs_principal
 * @param {DOMElement} tabs_interior
 * @param {int} posicao_principal
 * @param {int} posicao_troca
 * @returns {none}
 */
function troca_ordem(tabs_principal, tabs_interior, posicao_principal, posicao_troca) {
    //arruma o array de ordem de leitura
    id_tabs_principal = $(tabs_principal).attr("id");
    //id do documento que é o dono da aba(tabs) principal
    array = id_tabs_principal.split("-");
    id_documento_tabs_principal = array[1];





    id_tabs_interna = $(tabs_interior).attr("id");
    aux1 = _ordemLeitura[id_documento_tabs_principal][id_tabs_interna][posicao_principal];
    aux2 = _ordemLeitura[id_documento_tabs_principal][id_tabs_interna][posicao_troca];
    _ordemLeitura[id_documento_tabs_principal][id_tabs_interna][posicao_principal] = aux2;
    _ordemLeitura[id_documento_tabs_principal][id_tabs_interna][posicao_troca] = aux1;
}

/**
 * Elimina uma posição principal do array de ordens
 * 
 * @param {DOMElement} tabs_principal
 * @returns {none}
 */
function remove_posicao_ordem_principal(tabs_principal) {
    console.log(id_tabs_principal);
    console.log( $(tabs_principal).attr("id"));
    id_tabs_principal = $(tabs_principal).attr("id");
    array = id_tabs_principal.split("-");
    id_documento_tabs_principal = array[1];
    delete _ordemLeitura[id_documento_tabs_principal]
}

/**
 * Elimina uma documento do array interno da posição principal
 * 
 * @param {DOMElement} tabs_principal
 * @param {DOMElement} tabs_interior
 * @param {int} ordem_de_leitura
 * @returns {none}
 */
function remove_posicao_ordem_interna(tabs_principal, tabs_interior, ordem_de_leitura) {

    ordem_no_array = ordem_de_leitura - 1;
    id_tabs_principal = $(tabs_principal).attr("id");
    array = id_tabs_principal.split("-");
    id_documento_tabs_principal = array[1];

    id_tabs_interior = $(tabs_interior).attr("id");

    _ordemLeitura[id_documento_tabs_principal][id_tabs_interior].splice(ordem_no_array, 1);
}
