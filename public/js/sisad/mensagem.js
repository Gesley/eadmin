/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de para geração de qualquer mensagem
 */
$(function() {


});
//* FUNCTIONS ----------------------------------------------------------------*/
/**
 * Responsavel por adicionar mais uma mensagem flashmessage na tela
 * @param {mixed} data
 * @param {string} tipo
 * @returns {none}
 */
function mostraFlashMessage(data, tipo) {
    divMensagem = getMensagemDiv(data, tipo);
    $("#flashMessages").append(divMensagem);
    $('html').animate({scrollTop: 0},'fast');
}

/**
 * Responsavel remover os flashMessages da tela
 * @returns {none}
 */
function removeFlashMessage() {
    $("#flashMessages").html("");
}

/**
 * Responsavel por retornar o html da div do flashmessage
 * @param {mixed} data
 * @param {string} tipo
 * @returns {string}
 */
function getMensagemDiv(data, tipo) {
    conteudo = "";
    if (tipo === "form") {
        /*basear-se na function getMensagemDiv da classe Sisad_DocumentoController*/
    } else {
        if ($.isArray(data)) {
            conteudo = "<ul>";
            $.each(data, function(indice, valor) {
                conteudo += "<li>" + valor["mensagem"] + "</li>";
            });
            conteudo += "</ul>";
        } else {
            conteudo = data;
        }
        if (tipo === "notice") {
            classe = tipo;
            label = "Aviso";
        } else if (tipo === "error") {
            classe = tipo;
            label = 'Erro';
        } else if (tipo === "success") {
            classe = tipo;
            label = "Sucesso";
        } else if (tipo === "info") {
            classe = tipo;
            label = "Informação";
        }
    }
    return "<div class=\"" + classe + "\"><strong>" + label + ": </strong>" + conteudo + "</div>";
}