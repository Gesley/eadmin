/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas a criação, personalização de abas e posicionamento
 */
$(document).ready(function() {
    carregaCssTabs();
    //valida caso o usuário recarregue a página. Logo ela deverá subir a tela
    $(".leitura_tabs").css({
        "display": "none"
    });
    carregaFormParecerDespacho(null, 'tabs');
});
//* FUNCTIONS ------------------------------------------------------------------*/

/**
 * Carrega o css das tabs
 * 
 * @returns {none}
 */
function carregaCssTabs() {
    $("#tabs").tabs();
    $(".tabs_interna").tabs();
    $("#tabs > div").css({
        "padding": 0
                , "margin": 0
                //, "height": "730px"
    });
    $(".tabs_interna > .ui-tabs-panel").css({
        "height": "424px"
                , "overflow": "auto"
    });
    $(".tabs_principal").css({
        "height": "569px"
                //,"overflow": "auto"
                //,"overflow-y": "hidden"
    });
    //IE, FireFox
    $(".tabs_principal").attr("scroll", "no");
}

/**
 * Carrega os dados de 
 * @param {JSON} documento
 * @param {string} prefixo_tabs
 * @returns {undefined}
 */
function carregaFormParecerDespacho(documento, prefixo_tabs) {
    idAux = null;
    if (documento == null) {
        tabs_principal = $(".tabs_principal")[0];
        id_tabs_principal = $(tabs_principal).attr("id");
        //id do documento que é o dono da aba(tabs) principal
        array = id_tabs_principal.split("-");
        id_documento_tabs_principal = array[1];
        documento = {"DOCM_ID_DOCUMENTO": id_documento_tabs_principal};
        idAux = id_documento_tabs_principal;
    } else if (prefixo_tabs == "tabs") {
        //pega os dados do documento anterior da leitura
        documento_post = jQuery.parseJSON($("#documento_post-" + documento.DOCM_ID_DOCUMENTO).val());
        documento.APARTIR_DE_DOCM_ID_DOCUMENTO = documento_post.APARTIR_DE_DOCM_ID_DOCUMENTO;
        idAux = documento.DOCM_ID_DOCUMENTO;
    } else {
        idAux = documento.APARTIR_DE_DOCM_ID_DOCUMENTO;
    }
    $.ajax({
        url: base_url + "/sisad/leitura/formparecerdespachoajax/",
        data: documento,
        dataType: 'html',
        type: 'post',
        beforeSend: function() {
            carregando = '<div id="loading"style="margin-top: 10px;">'
                    + '<div class="span-1 last" style="margin: 1px 0 0 30px;"><img src="' + base_url + '/img/ajax-loader_1.gif"></div>'
                    + '<strong>Aguarde enquanto o formulário é carregado...</strong>'
                    + '</div>';
            $('#' + prefixo_tabs + '_parecer-' + idAux).html(carregando);
            $('#' + prefixo_tabs + '_despacho-' + idAux).html(carregando);
        },
        success: function(data) {
            $('body').append('<div id="div_aux-' + idAux + '" style="display:none">' + data + '</div>');
            div_mensagem = $('#div_aux-' + idAux).find('#mensagem');
            if ($(div_mensagem).size() > 0) {
                $('#' + prefixo_tabs + '_parecer-' + idAux).html($(div_mensagem).html());
                $('#' + prefixo_tabs + '_despacho-' + idAux).html($(div_mensagem).html());
            } else {
                $('#' + prefixo_tabs + '_parecer-' + idAux).html(
                        $('#div_aux-' + idAux).find('#div-parecer_ajax')
                        );
                $('#' + prefixo_tabs + '_despacho-' + idAux).html(
                        $('#div_aux-' + idAux).find('#div-despacho_ajax')
                        );
            }
            $('#div_aux-' + idAux).remove();
        },
        error: function(data) {
            $('#' + prefixo_tabs + '_parecer-' + idAux).html('Ocorreu um erro ao executar o ajax dos formulários parecer e despacho.');
            $('#' + prefixo_tabs + '_despacho-' + idAux).html('Ocorreu um erro ao executar o ajax dos formulários parecer e despacho.');
        }
    });
}

/**
 * Retorna o id do documento principal passando como referencia algum elemento interno da tabs.
 * @param DOMElement elementoInterno
 * @returns int
 */
function getIdDocumentoPorElemento(elementoInterno) {
    elemento_tabs_principal = $(elementoInterno).closest('.tabs_principal')
    id_tabs_principal = $(elemento_tabs_principal).attr("id");
    //id do documento que é o dono da aba(tabs) principal
    array = id_tabs_principal.split("-");
    id_documento_tabs_principal = array[1];
    return id_documento_tabs_principal;
}

/**
 * 
 * @param DOMElement elemento_tabs_principal
 * @returns {unresolved}
 */
function getIdDocumento(elemento_tabs_principal) {
    id_tabs_principal = $(elemento_tabs_principal).attr("id");
    //id do documento que é o dono da aba(tabs) principal
    array = id_tabs_principal.split("-");
    id_documento_tabs_principal = array[1];
    return id_documento_tabs_principal;
}
