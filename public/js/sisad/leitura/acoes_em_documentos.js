/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações em documento
 * 
 * Depende dos scripts:
 *  + 
 */
$(function() {
    //** DECLARAÇÃO DE VARIAVEIS -------------------------------------------------*/

    //* CARREGAMENTO DEFAULT ------------------------------------------------------*/

    //* USO DINÂMICO -------------------------------------------------------------*/
    $("#tabs").delegate(".form_parecer", "submit", function(event) {
        form = $(this);
        event.preventDefault();
        arrayDados = $(this).serializeArray();

        documentos_alvo = {};
        $.each(arrayDados, function(index, objeto) {
            if (objeto.name == 'documentos_alvo') {
                documentos_alvo = $.parseJSON(objeto.value);
                return false;//similar ao break
            }
        });
        $.ajax({
            url: base_url + "/sisad/leitura/parecerajax",
            data: arrayDados,
            type: 'post',
            beforeSend: function() {
                $(form).closest(".ui-tabs-panel").find(".notice,.error,.success,.info").remove();
                $(form).closest(".ui-tabs-panel").prepend('<div id="loading"style="margin-top: 10px;">'
                        + '<div class="span-1 last" style="margin: 1px 0 0 30px;"><img src="' + base_url + '/img/ajax-loader_1.gif"></div><strong>Aguarde enquanto o parecer é efetuado...</strong>'
                        + '</div>');
            },
            success: function(data) {
                $(form).closest(".ui-tabs-panel").find("#loading").remove();
                $(form).closest(".ui-tabs-panel").prepend(data);
                if (documentos_alvo[0]) {
                    $.each(documentos_alvo, function(index, obj) {
                        if ($("#tabs_historico-" + obj.DOCM_ID_DOCUMENTO).size() > 0) {
                            //function criada no javascript carrega_documentos.js
                            carrega_historico("#tabs_historico-" + obj.DOCM_ID_DOCUMENTO, obj);
                        }
                        if ($("#tabs_visualizador_historico-" + obj.DOCM_ID_DOCUMENTO).size() > 0) {
                            //function criada no javascript carrega_documentos.js
                            carrega_historico("#tabs_visualizador_historico-" + obj.DOCM_ID_DOCUMENTO, obj);
                        }
                    });
                } else {
                    if ($("#tabs_historico-" + documentos_alvo.DOCM_ID_DOCUMENTO).size() > 0) {
                        //function criada no javascript carrega_documentos.js
                        carrega_historico("#tabs_historico-" + documentos_alvo.DOCM_ID_DOCUMENTO, documentos_alvo);
                    }
                    /*
                     * Desativado pois o id do historico visualizador não é do documento
                     * if ($("#tabs_visualizador_historico-" + documentos_alvo.DOCM_ID_DOCUMENTO).size() > 0) {
                     //function criada no javascript carrega_documentos.js
                     carrega_historico("#tabs_visualizador_historico-" + documentos_alvo.DOCM_ID_DOCUMENTO, documentos_alvo);
                     }*/

                }
            },
            error: function(data) {
                $(form).closest(".ui-tabs-panel").find("#loading").remove();
                $(form).closest(".ui-tabs-panel").prepend('<div class="error"><strong>Erro: </strong>Ocorreu um erro interno na requisição ajax.</div>');
            }
        });
    });

    $("#tabs").delegate(".form_despacho", "submit", function(event) {
        form = $(this);
        event.preventDefault();
        arrayDados = $(this).serializeArray();
        documentos_alvo = {};
        $.each(arrayDados, function(index, objeto) {
            if (objeto.name == 'documentos_alvo') {
                documentos_alvo = $.parseJSON(objeto.value);
                return false;//similar ao break
            }
        });

        $.ajax({
            url: base_url + "/sisad/leitura/despachoajax",
            data: arrayDados,
            type: 'post',
            beforeSend: function() {
                $(form).closest(".ui-tabs-panel").find(".notice,.error,.success,.info").remove();
                $(form).closest(".ui-tabs-panel").prepend('<div id="loading"style="margin-top: 10px;">'
                        + '<div class="span-1 last" style="margin: 1px 0 0 30px;"><img src="' + base_url + '/img/ajax-loader_1.gif"></div><strong>Aguarde enquanto o despacho é efetuado...</strong>'
                        + '</div>');
            },
            success: function(data) {
                $(form).closest(".ui-tabs-panel").find("#loading").remove();
                $(form).closest(".ui-tabs-panel").prepend(data);
                if (documentos_alvo[0]) {
                    $.each(documentos_alvo, function(index, obj) {
                        if ($("#tabs_historico-" + obj.DOCM_ID_DOCUMENTO).size() > 0) {
                            //function criada no javascript carrega_documentos.js
                            carrega_historico("#tabs_historico-" + obj.DOCM_ID_DOCUMENTO, obj);
                        }
                        if ($("#tabs_visualizador_historico-" + obj.DOCM_ID_DOCUMENTO).size() > 0) {
                            //function criada no javascript carrega_documentos.js
                            carrega_historico("#tabs_visualizador_historico-" + obj.DOCM_ID_DOCUMENTO, obj);
                        }
                    });
                } else {
                    if ($("#tabs_historico-" + documentos_alvo.DOCM_ID_DOCUMENTO).size() > 0) {
                        //function criada no javascript carrega_documentos.js
                        carrega_historico("#tabs_historico-" + documentos_alvo.DOCM_ID_DOCUMENTO, documentos_alvo);
                    }
                    /*
                     * Desativado pois o id do historico visualizador não é do documento
                     * if ($("#tabs_visualizador_historico-" + documentos_alvo.DOCM_ID_DOCUMENTO).size() > 0) {
                     //function criada no javascript carrega_documentos.js
                     carrega_historico("#tabs_visualizador_historico-" + documentos_alvo.DOCM_ID_DOCUMENTO, documentos_alvo);
                     }*/

                }
            },
            error: function(data) {
                $(form).closest(".ui-tabs-panel").find("#loading").remove();
                $(form).closest(".ui-tabs-panel").prepend('<div class="error"><strong>Erro: </strong>Ocorreu um erro interno na requisição ajax.</div>');
            }
        });
    });
});