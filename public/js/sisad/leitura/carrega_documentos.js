/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas ao carregamento dos documentos da leitura
 * 
 *  Depende dos scripts:
 *  + ordenacao.js
 *  + botoes.js
 */
//** DECLARAÇÃO DE VARIAVEIS -------------------------------------------------*/
$(function() {

//* USO DINÂMICO -------------------------------------------------------------*/
});
//* FUNCTIONS ----------------------------------------------------------------*/

/**
 * 
 * @param {DOMElement} local
 * @param {int} id_documento_principal
 * @param {JSON} filtro
 * @returns {none}
 */
function carrega_anexados(local, id_documento_principal, filtro) {
    var tabela_atual;
    $.ajax({
        url: base_url + "/sisad/leitura/documentosanexadosajax/DOCM_ID_DOCUMENTO_PRINCIPAL/" + id_documento_principal,
        data: filtro,
        dataType: 'html',
        type: 'post',
        beforeSend: function() {
            tabela_atual = $(local).find(".grid");
            $(local).html("").append(gif_load);
            $(local).closest(".tabs_documentos_juntados").scrollTop(100000);
        },
        success: function(data) {
            $(local).html("").append(data);

            //id principal
            tabs_principal = $(local).closest(".tabs_principal");
            //id tabs interna
            tabs_interna = $(local).closest(".tabs_documentos_juntados");
            //function criada no javascript ordenacao.js
            carrega_array_leitura_tabs_interna(tabs_principal, tabs_interna);
            //function criada no javascript botoes.js
            carregaBotoes();
        },
        error: function(data) {
            $(local).html(data);
            alert("Aconteceu um erro ao carregar os documentos anexados. A tabela de documentos voltará ao seu estado anterior.");
            $(local).html("").append(tabela_atual);
            //function criada no javascript ordenacao.js
            carrega_array_leitura_tabs_interna(tabs_principal, tabs_interna);
            //function criada no javascript botoes.js
            carregaBotoes();
        }
    });
}
function carrega_apensados(local, id_documento_principal, filtro) {
    var tabela_atual;
    $.ajax({
        url: base_url + "/sisad/leitura/documentosapensadosajax/DOCM_ID_DOCUMENTO_PRINCIPAL/" + id_documento_principal,
        data: filtro,
        dataType: 'html',
        type: 'post',
        beforeSend: function() {
            tabela_atual = $(local).find(".grid");
            $(local).html("").append(gif_load);
            $(local).closest(".tabs_documentos_juntados").scrollTop(100000);
        },
        success: function(data) {
            $(local).html("").append(data);

            //id principal
            tabs_principal = $(local).closest(".tabs_principal");
            //id tabs interna
            tabs_interna = $(local).closest(".tabs_documentos_juntados");
            //function criada no javascript ordenacao.js
            carrega_array_leitura_tabs_interna(tabs_principal, tabs_interna);
            //function criada no javascript botoes.js
            carregaBotoes();
            //function criada no javascript filtro.js
            carregaJqueryCampos();
        },
        error: function(data) {
            $(local).html(data);
            alert("Aconteceu um erro ao carregar os documentos anexados. A tabela de documentos voltará ao seu estado anterior.");
            $(local).html("").append(tabela_atual);
            //function criada no javascript ordenacao.js
            carrega_array_leitura_tabs_interna(tabs_principal, tabs_interna);
            //function criada no javascript botoes.js
            carregaBotoes();
            //function criada no javascript filtro.js
            carregaJqueryCampos();
        }
    });
}
function carrega_vinculados(local, id_documento_principal, filtro) {
    var tabela_atual;
    $.ajax({
        url: base_url + "/sisad/leitura/documentosvinculadosajax/DOCM_ID_DOCUMENTO_PRINCIPAL/" + id_documento_principal,
        data: filtro,
        dataType: 'html',
        type: 'post',
        beforeSend: function() {
            tabela_atual = $(local).find(".grid");
            $(local).html("").append(gif_load);
            $(local).closest(".tabs_documentos_juntados").scrollTop(100000);
        },
        success: function(data) {
            $(local).html("").append(data);

            //id principal
            tabs_principal = $(local).closest(".tabs_principal");
            //id tabs interna
            tabs_interna = $(local).closest(".tabs_documentos_juntados");
            //function criada no javascript ordenacao.js
            carrega_array_leitura_tabs_interna(tabs_principal, tabs_interna);
            //function criada no javascript botoes.js
            carregaBotoes();
            //function criada no javascript filtro.js
            carregaJqueryCampos();
        },
        error: function(data) {
            $(local).html(data);
            alert("Aconteceu um erro ao carregar os documentos anexados. A tabela de documentos voltará ao seu estado anterior.");
            $(local).html("").append(tabela_atual);
            //function criada no javascript ordenacao.js
            carrega_array_leitura_tabs_interna(tabs_principal, tabs_interna);
            //function criada no javascript botoes.js
            carregaBotoes();
            //function criada no javascript filtro.js
            carregaJqueryCampos();
        }
    });
}

function carrega_metadados(local, dados_documento) {
    $.ajax({
        url: base_url + "/sisad/leitura/metadados/",
        dataType: 'html',
        data: dados_documento,
        type: 'post',
        beforeSend: function() {
            $(local).html("").append(gif_load);
        },
        success: function(data) {
            $(local).html(data);
        },
        error: function() {
            $(local).html("Erro ao carregar o documento.");
        }
    });
}

function carrega_historico(local, dados_documento) {
    $.ajax({
        url: base_url + "/sisad/leitura/historico/",
        dataType: 'html',
        data: dados_documento,
        type: 'post',
        beforeSend: function() {
            $(local).html("").append(gif_load);
        },
        success: function(data) {
            $(local).html(data);
        },
        error: function() {
            $(local).html("Erro ao carregar o documento.");
        }
    });
}