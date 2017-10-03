/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas a criação de abas, destruição de abas e outras ações da visualização
 * 
 * Depende dos scripts:
 *  + ordenacao.js
 *  + 
 */
$(function() {

    //alert("Para melhor utilização do visualizador desative o renderizador de pdf do Google Chrome. Para isso copie o seguinte link \" chrome://plugins/ \" e cole na barra de busca do seu navegador. Localize o plug-in \"Chrome PDF Viewer\" e clique em \"Desativar\". Se o seu navegador não for o Google Chrome desconsidere a mensagem.");

//** DECLARAÇÃO DE VARIAVEIS -------------------------------------------------*/
    /**
     * armazena o template da mensagem de carregamento
     * @type string
     */
    gif_load = '<div class="span-4" id="loading">'
            + '<div style="margin: 1px 0 0 30px;" class="span-1 last"><img src="' + base_url + '/img/ajax-loader_1.gif"/></div><div class="span-2"><strong>Aguarde...</strong></div>'
            + '</div>';
    /**
     * armazena o template da li da aba
     * @type string
     */
    _tabTemplate = "<li><a href='#{href}'>#{label} </a> <span class='ui-icon ui-icon-close fecha_aba_documento' role='presentation'>Remove Aba</span> </li>";
//* USO DINÂMICO -------------------------------------------------------------*/
    /**
     * Ao clicar no botão que abre a leitura do documento
     * será carregado o pdf via PdfObject e será exibido na tela
     * os dados de qual pdf abrir estão na tr do botão clicado
     * a ordem do pdf a ser lido está no array global do script ordenacao.js
     * 
     * dadosDocumentoJson JSON value da tr que o botão clicado pertence
     */
    $("#tabs").delegate(".nova_dialog", "click", function() {
        ordemTrDocumento = $(this).closest("tr").find('.ordem_leitura').html();
        id_tabs_interna = $(this).closest(".tabs_documentos_juntados").attr("id");
        id_tabs_principal = $(this).closest(".tabs_principal").attr("id");
        array = id_tabs_principal.split("-");
        id_documento_tabs_principal = array[1];
        qtd_documentos = $(this).closest('tbody').find('tr').length;

        jsonBotao = {
            "ordem": ordemTrDocumento
                    , "id_documento_principal": id_documento_tabs_principal
                    , "id_tabs_interna": id_tabs_interna
                    , "qtd_documentos": qtd_documentos
                    , "is_sem_metadados": false
                    , "is_documento_comum": false
        };
        $("#esquerda_pular-" + id_documento_tabs_principal).val(JSON.stringify(jsonBotao));
        $("#direita_pular-" + id_documento_tabs_principal).val(JSON.stringify(jsonBotao));

        //$("#label_leitura_tabs-" + id_documento_tabs_principal).html("Lendo " + ordemTrDocumento + "º documento de " + qtd_documentos + " documentos");

        ler_pdf_atual(jsonBotao, this);
    });

    /**
     * Ao clicar no botão que abre a leitura do documento comum
     * será carregado o pdf via PdfObject e será exibido na tela
     * os dados de qual pdf abrir estão na value do botão clicado
     * 
     * dadosDocumentoJson JSON value da tr que o botão clicado pertence
     */
    $("#tabs").delegate(".visualizar_documento_comum", "click", function() {
        //abre uma nova aba inferior para leitura de outro documento

        dadosDocumentoJson = jQuery.parseJSON($(this).val());

        ler_pdf_atual(dadosDocumentoJson, $(this));
    });

    /**
     * Ao clicar no botão que abre a leitura do anexo sem metadados
     * será carregado o pdf via PdfObject e será exibido na tela
     * os dados de qual pdf abrir estão na tr do botão clicado
     * a ordem do pdf a ser lido está no array global do script ordenacao.js
     * 
     * dadosDocumentoJson JSON value da tr que o botão clicado pertence
     */
    $("#tabs").delegate(".nova_dialog_sem_metadados", "click", function() {
        ordemTrDocumento = $(this).closest("tr").find('.ordem_leitura').html();
        id_tabs_interna = $(this).closest(".tabs_documentos_juntados").attr("id");
        id_tabs_principal = $(this).closest(".tabs_principal").attr("id");
        array = id_tabs_principal.split("-");
        id_documento_tabs_principal = array[1];
        qtd_documentos = $(this).closest('tbody').find('tr').length;

        jsonBotao = {
            "ordem": ordemTrDocumento
                    , "id_documento_principal": id_documento_tabs_principal
                    , "id_tabs_interna": id_tabs_interna
                    , "qtd_documentos": qtd_documentos
                    , "is_sem_metadados": true
                    , "is_documento_comum": false
        };
        $("#esquerda_pular_sem_metadados-" + id_documento_tabs_principal).val(JSON.stringify(jsonBotao));
        $("#direita_pular_sem_metadados-" + id_documento_tabs_principal).val(JSON.stringify(jsonBotao));

        //$("#label_leitura_tabs_sem_metadados-" + id_documento_tabs_principal).html("Lendo " + ordemTrDocumento + "º anexo de " + qtd_documentos + " anexos sem metadados.");

        ler_pdf_atual_sem_metadados(jsonBotao, this);
    });

    /**
     * É acionada ao clicar no botão que fecha a visualização do documento
     * 
     * numero_tab int value do botão clicado
     */
    $("#tabs").delegate(".fecha_dialog", "click", function() {
        numero_tab = $(this).val();
        //verifica se a dialog é de visualização de documentos sem metadados
        aux = $(this).closest(".navegacao_documento_sem_metadados");
        if (aux.size() == 0) {
            $("#leitura_tabs-" + numero_tab).css("display", "none");
        } else {
            $("#leitura_sem_metadados-" + numero_tab).css("display", "none");
        }
        $("#documento_tabs-" + numero_tab).css("display", "block");
        $(".pode_limpar").html(gif_load);
    });

    /**
     * É acionada ao clicar no botão que fecha a visualização do documento
     * 
     * numero_tab int value do botão clicado
     */
    $("#tabs").delegate(".remover_tr", "click", function() {
        tabs_principal = $(this).closest(".tabs_principal");
        tabs_interna = $(this).closest(".tabs_documentos_juntados");
        ordem_de_leitura = $(this).closest("tr").find("td.ordem_leitura").html();
        //function criada no script ordenacao.js
        remove_posicao_ordem_interna(tabs_principal, tabs_interna, ordem_de_leitura);
        $(this).closest("tr").remove();
        //function criada no script ordenacao.js
        carrega_array_leitura_tabs_interna(tabs_principal, tabs_interna);
    });

    /**
     * Ao clicar no botão que abre os dados do documento que possui outros documentos juntados
     * será aberta uma nova aba ou o usuário será direcionado para a aba já aberta
     * 
     * dadosDocumentoJson JSON value da tr que o botão clicado pertence
     */
    $("#tabs").delegate(".nova_aba", "click", function() {
        //abre uma nova aba inferior para leitura de outro documento
        dadosDocumentoJson = jQuery.parseJSON($(this).parents('tr:first').attr("value"));
        nova_aba(dadosDocumentoJson);
    });

    /**
     * Ao clicar no botão "x" da aba principal, a mesma será fechada
     * 
     * panelId string id da tabs principal cujo x pertence
     */
    $("#tabs").delegate(".fecha_aba_documento", "click", function() {

        href = $(this).closest("li").find("a").attr("href");
        tabs_principal = $(href);
        //function criada no script ordenacao.js
        remove_posicao_ordem_principal(tabs_principal);
        //function criada no script ordenacao.js
        carrega_array_leitura_tabs_principal(tabs_principal);

        var panelId = $(this).closest("li").remove().attr("aria-controls");
        $("#" + panelId).remove();
        $("#tabs").tabs("refresh");
    });

    /**
     * Le o pdf do documento anterior
     * 
     */
    $("#tabs").delegate(".esquerda_pular", "click", function() {

        auxJson = jQuery.parseJSON($(this).val());
        if (auxJson.is_sem_metadados) {
            ler_pdf_anterior_sem_metadado(this);
        } else {
            ler_pdf_anterior(this);
        }

    });
    /**
     * Le o pdf do documento posterior
     * 
     */
    $("#tabs").delegate(".direita_pular", "click", function() {
        auxJson = jQuery.parseJSON($(this).val());
        if (auxJson.is_sem_metadados) {
            ler_pdf_proximo_sem_metadado(this);
        } else {
            ler_pdf_proximo(this);
        }

    });
});
//* FUNCTIONS ----------------------------------------------------------------*/
/**
 * Exibe a leitura de um documento
 * 
 * @param {DOMElement} botao
 * @returns {none}
 */
function ler_pdf(jsonBotao, botao) {
    //mostra a parte da leitura do documento
    ler_pdf_atual(jsonBotao, botao);

}
/**
 * Ao clicar no botão "x" da aba principal, a mesma será fechada
 * 
 * @param {JSON} dados_documento
 * @returns {none}
 */
function nova_aba(dados_documento) {





    //PASSAR TUDO QUE ESTA AQUI PARA A CARREGA_DOCUMENTOS.JS EM FORMA DE FUNCTION NA PRÓXIMA VEZ QUE FOR ARRUMAR





    //se não tiver aberto a aba do documento
    if ($("#tabs-" + dados_documento.DOCM_ID_DOCUMENTO).size() == 0) {
        var label = dados_documento.DTPD_NO_TIPO + ": " + dados_documento.MASC_NR_DOCUMENTO,
                id = "tabs-" + dados_documento.DOCM_ID_DOCUMENTO,
                li = $(_tabTemplate.replace(/#\{href\}/g, "#" + id).replace(/#\{label\}/g, label));

        $.ajax({
            url: base_url + "/sisad/leitura/leituradocumentosajax",
            dataType: 'html',
            data: dados_documento,
            type: 'post',
            beforeSend: function() {
                //localizada na index.phtml da action
                $("#dialog-modal").html(gif_load);
                $("#dialog-modal").dialog({
                    height: 140,
                    modal: true,
                    buttons: []
                });
            },
            success: function(data) {
                $("#tabs > .ui-tabs-nav").append(li);
                $("#tabs").append("<div id='" + id + "' class='tabs_principal'>" + data + "</div>");
                $("#tabs").tabs("refresh");
                //ativa a ultima tab
                $("#tabs").tabs({active: $("#tabs > .ui-tabs-nav > li").size() - 1});

                //function criada no script tabs.js
                carregaCssTabs();
                //valida caso o usuário recarregue a página. Logo ela deverá subir a tela
                $("#leitura_tabs-" + dados_documento.DOCM_ID_DOCUMENTO).css({
                    "display": "none"
                });
                $("#leitura_sem_metadados-" + dados_documento.DOCM_ID_DOCUMENTO).css({
                    "display": "none"
                });
                //function criada no script botoes.js
                carregaBotoes();
                //function criada no script ordenacao.js
                carrega_array_leitura_tabs_principal('#tabs-' + dados_documento.DOCM_ID_DOCUMENTO);
                //function criada no javascript filtro.js
                carregaJqueryCampos();
                $("#dialog-modal").dialog('close');

                //function criada no script tabs.js
                carregaFormParecerDespacho(dados_documento, 'tabs');
            },
            error: function() {
                //apagar modal carregamento
                //aparecer alert com erro
                alert("erro");
            }
        });
    } else {
        //caso ja tenha inserido a aba do documento então mostrar ela
        total_tabs_principais = $("#tabs > .ui-tabs-nav > li").size();
        total_tabs_depois_documento = $("a[href='#tabs-" + dados_documento.DOCM_ID_DOCUMENTO + "']").parents('li:first').find("~li").size();
        posicao_tabs_documento = total_tabs_principais - total_tabs_depois_documento - 1;
        $("#tabs").tabs({active: posicao_tabs_documento});
    }
}
/**
 * Exibe a visualização do pdf no elemento especificado
 * 
 * @param {string} url_pdf url do pdf a ser chamado
 * @param {string} idDestino id do elemento que o pdf será carregado
 * @returns {none}
 */
function exibe_leitura(url_pdf, idDestino, height) {
    var pdfOpen_params = {
        navpanes: 1
                , view: "FitH"
                , pagemode: "thumbs"
    };

    var pdfAttributes = {
        url: url_pdf
                , pdfOpenParams: pdfOpen_params
                , width: "100%"
                , height: height

    };

    var pdf = new PDFObject(pdfAttributes);
    pdf.embed(idDestino);
}

function ler_pdf_anterior(botao) {
    jsonBotao = jQuery.parseJSON($(botao).val());
    ordem_atual_visualizacao = parseInt(jsonBotao.ordem, 10);
    if (jsonBotao.ordem == 1) {
        // alert("O documento atual é o primeiro documento a ser lido.");
        return 0;
    } else {
        dadosDocumentoLeitura = _ordemLeitura[jsonBotao.id_documento_principal][jsonBotao.id_tabs_interna][ordem_atual_visualizacao - 2];
        if (dadosDocumentoLeitura.DTPD_ID_TIPO_DOC == 152 || dadosDocumentoLeitura.QTD_ANEXOS_SEM_METADADOS != 0) {
            $("#dialog-modal").html("O documento possui outros documentos ou anexos sem metadados. <br>Deseja abrir em uma nova guia ou pular para o documento anterior?");
            //caso não exista anterior ao anterior
            if (ordem_atual_visualizacao - 2 <= 0) {
                $("#dialog-modal").dialog({
                    resizable: false,
                    height: 160,
                    modal: true,
                    title: "Atenção",
                    buttons: {
                        "Abrir": function() {
                            $(this).dialog("close");
                            nova_aba(dadosDocumentoLeitura);
                        },
                        "Cancelar": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            } else {
                $("#dialog-modal").dialog({
                    resizable: false,
                    height: 160,
                    modal: true,
                    title: "Atenção",
                    buttons: {
                        "Anterior": function() {
                            $(this).dialog("close");
                            //pega a ordem do anterior do desejado
                            jsonBotao.ordem = ordem_atual_visualizacao - 1;

                            //$(botao).val(JSON.stringify(jsonBotao));
                            //$(botao).closest(".navegacao_documento").find("#esquerda_pular-" + jsonBotao.id_documento_principal).val(JSON.stringify(jsonBotao));
                            //$(botao).closest(".navegacao_documento").find("#direita_pular-" + jsonBotao.id_documento_principal).val(JSON.stringify(jsonBotao));
                            atualiza_botoes_visualizacao(botao, jsonBotao);

                            ler_pdf_anterior(botao);
                        },
                        "Abrir": function() {
                            $(this).dialog("close");
                            nova_aba(dadosDocumentoLeitura);
                        },
                        "Cancelar": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        } else {
            jsonBotao.ordem = ordem_atual_visualizacao - 1;

            atualiza_botoes_visualizacao(botao, jsonBotao);

            abre_tela_leitura(jsonBotao, dadosDocumentoLeitura);
        }
    }
}
function ler_pdf_anterior_sem_metadado(botao) {
    jsonBotao = jQuery.parseJSON($(botao).val());
    ordem_atual_visualizacao = parseInt(jsonBotao.ordem, 10);
    if (jsonBotao.ordem == 1) {
//        alert("O documento atual é o ultimo documento a ser lido.");
        return 0;
    } else {
        //_ordemLeitura vem do javascript ordenacao.js
        dadosDocumentoLeitura = _ordemLeitura[jsonBotao.id_documento_principal][jsonBotao.id_tabs_interna][ordem_atual_visualizacao - 2];
        jsonBotao.ordem = ordem_atual_visualizacao - 1;
        atualiza_botoes_visualizacao(botao, jsonBotao);
        abre_tela_leitura_sem_metadados(jsonBotao, dadosDocumentoLeitura);
    }
}

function ler_pdf_proximo(botao) {
    jsonBotao = jQuery.parseJSON($(botao).val());
    ordem_atual_visualizacao = parseInt(jsonBotao.ordem, 10);
    if (jsonBotao.ordem >= jsonBotao.qtd_documentos) {
//        alert("O documento atual é o ultimo documento a ser lido.");
        return 0;
    } else {
        //_ordemLeitura vem do javascript ordenacao.js
        dadosDocumentoLeitura = _ordemLeitura[jsonBotao.id_documento_principal][jsonBotao.id_tabs_interna][ordem_atual_visualizacao];
        if (dadosDocumentoLeitura.DTPD_ID_TIPO_DOC == 152 || dadosDocumentoLeitura.QTD_ANEXOS_SEM_METADADOS != 0) {
            $("#dialog-modal").html("O documento possui outros documentos ou anexos sem metadados. <br>Deseja abrir em uma nova guia ou pular para o próximo documento?");
            //caso não exista um documento depois
            if (ordem_atual_visualizacao + 2 <= 0) {
                $("#dialog-modal").dialog({
                    resizable: false,
                    height: 250,
                    modal: true,
                    buttons: {
                        "Abrir": function() {
                            $(this).dialog("close");
                            nova_aba(dadosDocumentoLeitura);
                        },
                        "Cancelar": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            } else {
                $("#dialog-modal").dialog({
                    resizable: false,
                    height: 250,
                    modal: true,
                    buttons: {
                        "Próximo": function() {
                            $(this).dialog("close");
                            //pega a ordem do anterior do desejado
                            jsonBotao.ordem = ordem_atual_visualizacao + 1;

                            atualiza_botoes_visualizacao(botao, jsonBotao);

                            ler_pdf_proximo(botao);
                        },
                        "Abrir": function() {
                            $(this).dialog("close");
                            nova_aba(dadosDocumentoLeitura);
                        },
                        "Cancelar": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }


        } else {
            jsonBotao.ordem = ordem_atual_visualizacao + 1;

            atualiza_botoes_visualizacao(botao, jsonBotao);

            abre_tela_leitura(jsonBotao, dadosDocumentoLeitura);
        }
    }
}
function ler_pdf_proximo_sem_metadado(botao) {
    jsonBotao = jQuery.parseJSON($(botao).val());
    ordem_atual_visualizacao = parseInt(jsonBotao.ordem, 10);
    if (jsonBotao.ordem >= jsonBotao.qtd_documentos) {
//        alert("O documento atual é o ultimo documento a ser lido.");
        return 0;
    } else {
        //_ordemLeitura vem do javascript ordenacao.js
        dadosDocumentoLeitura = _ordemLeitura[jsonBotao.id_documento_principal][jsonBotao.id_tabs_interna][ordem_atual_visualizacao];
        jsonBotao.ordem = ordem_atual_visualizacao + 1;
        atualiza_botoes_visualizacao(botao, jsonBotao);
        abre_tela_leitura_sem_metadados(jsonBotao, dadosDocumentoLeitura);
    }
}

function ler_pdf_atual(jsonBotao, botao) {
    if (!jsonBotao.is_documento_comum) {
        valueTr = $(botao).closest("tr").attr("value");
        dadosDocumentoLeitura = jQuery.parseJSON(valueTr);
        abre_tela_leitura(jsonBotao, dadosDocumentoLeitura);
    } else {
        abre_tela_leitura_sem_metadados(jsonBotao, jsonBotao);
    }

}

function ler_pdf_atual_sem_metadados(jsonBotao, botao) {
    valueTr = $(botao).closest("tr").attr("value");
    dadosDocumentoLeitura = jQuery.parseJSON(valueTr);
    abre_tela_leitura_sem_metadados(jsonBotao, dadosDocumentoLeitura);
}

function abre_tela_leitura(jsonBotao, dadosDocumentoLeitura) {
    $("#tabs_visualizador-" + jsonBotao.id_documento_principal).tabs();
    $(".conteudo_visualizador").css({
        "padding": 0
                , "margin": 0
                , "height": "512px"
    });
    $(".com_overflow").css({
        "overflow": "auto"
    });
    $(".span-4").css({
        "margin-top": "100px"
    });

    $("#documento_tabs-" + jsonBotao.id_documento_principal).css("display", "none");
    $("#leitura_tabs-" + jsonBotao.id_documento_principal).css("display", "block");

    //documentos que serão lidos
    idDocumento = dadosDocumentoLeitura.DOCM_ID_DOCUMENTO;
    nrRedDocumento = dadosDocumentoLeitura.DOCM_NR_DOCUMENTO_RED;

    $("#resumo_metadados_leitura-" + jsonBotao.id_documento_principal).html(
            '<b>Número: </b>' + dadosDocumentoLeitura.MASC_NR_DOCUMENTO
            + '<br/><b>Tipo Documento: </b>' + dadosDocumentoLeitura.DTPD_NO_TIPO
            + '<br/><b>Assunto: </b>' + dadosDocumentoLeitura.AQVP_CD_PCTT + ' - ' + dadosDocumentoLeitura.AQAT_DS_ATIVIDADE
            + '<br/><b>Tipo de juntada: </b>' + dadosDocumentoLeitura.TIPO_JUNTADA
            );
    //troca a label da quantidade de documentos lidos
    $("#label_leitura_tabs-" + jsonBotao.id_documento_principal).html("Lendo " + jsonBotao.ordem + "º documento de " + jsonBotao.qtd_documentos + " documentos");
    //function criada no javascript carrega_documentos.js
    carrega_metadados("#tabs_visualizador_metadados-" + jsonBotao.id_documento_principal, dadosDocumentoLeitura);
    //function criada no javascript carrega_documentos.js
    carrega_historico("#tabs_visualizador_historico-" + jsonBotao.id_documento_principal, dadosDocumentoLeitura);
    //function criada no javascript tabs.js
    carregaFormParecerDespacho(dadosDocumentoLeitura, 'tabs_visualizador');
    exibe_leitura(base_url + "/sisad/gerenciared/recuperarbrowser/id/" + idDocumento + "/dcmto/" + nrRedDocumento, "visualizador_pdf-" + jsonBotao.id_documento_principal, 449);
}

function abre_tela_leitura_sem_metadados(jsonBotao, dadosDocumentoLeitura) {
//    $(".conteudo_visualizador").css({
//     "padding": 0
//     , "margin": 0
//     , "height": "505px"
//     });
//     $(".com_overflow").css({
//     "overflow": "auto"
//     });
    $(".span-4").css({
        "margin-top": "100px"
    });
    if (jsonBotao.is_documento_comum) {
        $("#documento_tabs-" + jsonBotao.DOCM_ID_DOCUMENTO).css("display", "none");
        $("#leitura_sem_metadados-" + jsonBotao.DOCM_ID_DOCUMENTO).css("display", "block");

        //documentos que serão lidos
        idDocumento = dadosDocumentoLeitura.DOCM_ID_DOCUMENTO;
        nrRedDocumento = dadosDocumentoLeitura.DOCM_NR_DOCUMENTO_RED;
        //troca a label da quantidade de documentos lidos
        $("#label_leitura_sem_metadados-" + jsonBotao.DOCM_ID_DOCUMENTO).html("Leitura do documento " + jsonBotao.DTPD_NO_TIPO + ": " + jsonBotao.MASC_NR_DOCUMENTO);
        exibe_leitura(base_url + "/sisad/gerenciared/recuperarbrowser/id/" + idDocumento + "/dcmto/" + nrRedDocumento, "visualizador_pdf_sem_metadados-" + jsonBotao.DOCM_ID_DOCUMENTO, 549);

    } else {
        $("#documento_tabs-" + jsonBotao.id_documento_principal).css("display", "none");
        $("#leitura_sem_metadados-" + jsonBotao.id_documento_principal).css("display", "block");

        //documentos que serão lidos
        idDocumento = dadosDocumentoLeitura.ANEX_ID_DOCUMENTO;
        nrRedDocumento = dadosDocumentoLeitura.ANEX_NR_DOCUMENTO_INTERNO;
        //troca a label da quantidade de documentos lidos
        $("#label_leitura_sem_metadados-" + jsonBotao.id_documento_principal).html("Lendo " + jsonBotao.ordem + "º documento de " + jsonBotao.qtd_documentos + " documento(s) sem metadados.");
        exibe_leitura(base_url + "/sisad/gerenciared/recuperarbrowser/id/" + idDocumento + "/dcmto/" + nrRedDocumento, "visualizador_pdf_sem_metadados-" + jsonBotao.id_documento_principal, 549);

    }



}

function atualiza_botoes_visualizacao(botao_referencia, json) {

    if (json.is_sem_metadados) {
        $(botao_referencia).closest(".navegacao_documento").find("#esquerda_pular_sem_metadados-" + json.id_documento_principal).val(JSON.stringify(json));
        $(botao_referencia).closest(".navegacao_documento").find("#direita_pular_sem_metadados-" + json.id_documento_principal).val(JSON.stringify(json));
    } else {
        $(botao_referencia).closest(".navegacao_documento").find("#esquerda_pular-" + json.id_documento_principal).val(JSON.stringify(json));
        $(botao_referencia).closest(".navegacao_documento").find("#direita_pular-" + json.id_documento_principal).val(JSON.stringify(json));
    }
}