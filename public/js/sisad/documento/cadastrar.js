/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas à tela de cadastro de documentos
 * 
 *  Depende dos scripts:
 *  + /js/sisad/mensagem.js
 *  + 
 */
var jsonComboSubsecoesAgrupadas = {};
var jsonComboUnidadesAgrupadas = {};
var jsonResponsaveisAgrupadosPorUnidade = {};
var jsonUnidadesAgrupadasPorResponsavel = {};
var jsonPessoasFisicasTrf1AgrupadasPorLotacao = {};
$('document').ready(function() {

    /*--------------------- EDITOR DE TEXTO -------------------------
      *
      * Configuração do editor de texto para o campo de Ementa
     */
    tinyMCE.init({
        // General options
        mode : "exact",
        elements : "DOCM_DS_ASSUNTO_DOC",
        theme : "advanced",
        theme_advanced_toolbar_location : "top",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",
        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_toolbar_align : "left",
        // Example content CSS (should be your site CSS)
        content_css : "css/content.css",
        // Style formats
        style_formats : [
        {
            title : 'Bold text', 
            inline : 'b'
        },

        {
            title : 'Red text', 
            inline : 'span', 
            styles : {
                color : '#ff0000'
            }
        },

        {
            title : 'Red header', 
            block : 'h1', 
            styles : {
                color : '#ff0000'
            }
        },

        {
            title : 'Example 1', 
            inline : 'span', 
            classes : 'example1'
        },

        {
            title : 'Example 2', 
            inline : 'span', 
            classes : 'example2'
        },

        {
            title : 'Table styles'
        },

        {
            title : 'Table row 1', 
            selector : 'tr', 
            classes : 'tablerow1'
        }
        ],
        template_replace_values : {
            username : "Some User",
            staffid : "991234"
        }      
    });

    //*--------------------- DESIGNER PÁGINA -------------------------*/
    $("input[type='text'],textarea").css({
        "width": "500px"
    });
    $("fieldset").css({
        "width": "487px"
    });
    $("select").css({
        "width": "507px"
    });
    $("#salvar").button();
    $("#DOCM_ID_PCTT").combobox();
    $("#combobox-input-text-DOCM_ID_PCTT").attr('style', 'width: 500px;');


    $("#DOCM_CD_LOTACAO_GERADORA").combobox();
    $("#combobox-input-text-DOCM_CD_LOTACAO_GERADORA").attr('style', 'width: 500px;');

    $("#DOCM_ID_PESSOA_EXTERNO").combobox();
    $("#combobox-input-text-DOCM_ID_PESSOA_EXTERNO").attr('style', 'width: 500px;');
    $("#combobox-input-button-DOCM_ID_PESSOA_EXTERNO").attr('style', 'display: none;');

    $("#DOCM_CD_LOTACAO_REDATORA").combobox();
    $("#combobox-input-text-DOCM_CD_LOTACAO_REDATORA").attr('style', 'width: 500px;');

    $("#DOCM_ID_TIPO_DOC").combobox();
    $("#combobox-input-text-DOCM_ID_TIPO_DOC").attr('style', 'width: 500px;');

    $("#DOCM_ID_TIPO_SITUACAO_DOC").combobox();
    $("#combobox-input-text-DOCM_ID_TIPO_SITUACAO_DOC").attr('style', 'width: 500px;');

    $("#DOCM_ID_CONFIDENCIALIDADE").combobox({
        selected: function(event, ui) {
        },
        changed: function(event, ui) {
        }
    });
    $("#combobox-input-text-DOCM_ID_CONFIDENCIALIDADE").attr('style', 'width: 500px;');

    $("#anexos").MultiFile({
        STRING: {
            file: '<em title="Clique para Remover" onclick="$(this).parent().prev().click()">$file</em>',
            remove: '<img class="excluirpdf" title="Clique para Remover" height="16" width="16"/>'
        }
    });

    $("input[name='radio_tipo_encaminhamento']").change(function() {
        mostraEncaminhamentoPeloEscopo();
    });

    $("#checkbox_minha_caixa_pessoal").click(function() {
        mostraCaixaPessoalPeloEscopo();
    });

    $("#checkbox_apenas_responsaveis").click(function() {
        mostraCaixaPessoalPeloEscopo();
        //se tiver alguma caixa selecionada
        if ($("#caixa_minha_responsabilidade").val() !== "") {
            //verifica se apenas responsaveis pela unidade está marcado
            if ($("#checkbox_apenas_responsaveis").attr("checked") === "checked") {
                if ($("#combobox-input-text-responsaveis_pela_unidade").attr("disabled") === "disabled") {
                    removeFlashMessage();
                    mostraFlashMessage("A unidade selecionada não possui responsáveis.", "notice");
                } else if ($("#combobox-input-text-pessoas_da_unidade").attr("disabled") === "disabled") {
                    removeFlashMessage();
                    mostraFlashMessage("A unidade selecionada não possui pessoas lotadas.", "notice");
                }
            } else {

            }
        }
    });

    $("#caixa_minha_responsabilidade").combobox({
        selected: function(event, ui) {
            //chama function do javascript mensagem.js
            removeFlashMessage();
            encadeiaResponsavelUnidade(event, ui);
            encadeiaPessoaUnidade(event, ui);
        },
        changed: function(event, ui) {
            //chama function do javascript mensagem.js
            removeFlashMessage();
            encadeiaResponsavelUnidade(event, ui);
            encadeiaPessoaUnidade(event, ui);
        }
    });
    $("#combobox-input-text-caixa_minha_responsabilidade").attr("style", "width: 500px;");

    $("#pessoas_da_unidade").combobox();
    $("#combobox-input-text-pessoas_da_unidade").attr("style", "width: 500px;");

    $("#responsaveis_pela_unidade").combobox();
    $("#combobox-input-text-responsaveis_pela_unidade").attr("style", "width: 500px;");

    //*----------------------- FILTRO CARREGAMENTO PÁGINA ---------------------*/
    //se tiver marcado documentos internos
    if ($("#radio_tipo_cadastro-interno").attr("checked") === "checked") {
        trocaParaDocumentosInternos();
    } else if ($("#radio_tipo_cadastro-externo").attr("checked") === "checked") {
        //se tiver marcados documentos externos
        trocaParaDocumentosExternos();
    } else if ($("#radio_tipo_cadastro-pessoal").attr("checked") === "checked") {
        //se tiver marcados documento pessoal
        trocaParaDocumentoPessoal();
    } else {
        //se não tiver marcado nenhum o padrão deve ser documentos internos
        $("#radio_tipo_cadastro-interno").attr("checked", "checked");
        trocaParaDocumentosInternos();
    }

    //se tiver marcado para autuar
    if ($("#check_box_autuacao").attr("checked") === "checked") {
        exibeCamposAutuacao();
    } else {
        escondeCamposAutuacao();
    }
    //organiza campos do encaminhamento
    mostraEncaminhamentoPeloEscopo();




    //*-------------------- EVENTOS --------------------------------*/
    $("#check_box_autuacao").click(function() {
        if ($(this).attr("checked") === "checked") {
            exibeCamposAutuacao();
        } else {
            escondeCamposAutuacao();
        }
    });
    $("input[name='radio_tipo_cadastro']").change(function() {
        if ($(this).val() === 'interno') {
            trocaParaDocumentosInternos();
        } else if($(this).val() === 'externo'){
            trocaParaDocumentosExternos();
        }else{
            trocaParaDocumentoPessoal();
        }
    });
    
//Fim do Ready function
});

$("form#documento").submit(function(event) {
    validaSubmit(event);
});
//*------------------------ FUNÇÕES ----------------------------*/

/**
 * Responsável por desabilitar um campo e sua representação jquery caso tenha
 * @param {string} id
 * @returns {none}
 */
function desabilitaCampo(id) {
    $("#" + id).attr("disabled", true);
    $("#combobox-input-button-" + id).attr("disabled", true);
    $("#combobox-input-text-" + id).attr("disabled", true);
}

/**
 * Responsavel por encadear a combo dos responsaveis pela unidade
 * @param {object} event
 * @param {object} ui
 * @returns {undefined}
 */
function encadeiaResponsavelUnidade(event, ui) {
    $("option", "#responsaveis_pela_unidade").remove();
    if (typeof jsonResponsaveisAgrupadosPorUnidade[ui.item.value] !== "undefined") {
        $.each(jsonResponsaveisAgrupadosPorUnidade[ui.item.value], function(key, value) {
            $("#responsaveis_pela_unidade").append(new Option(value, key, true, true));
        });
        $("#combobox-input-text-responsaveis_pela_unidade").focus().val("");
    } else {
        desabilitaCampo("responsaveis_pela_unidade");

        if ($("input[name='checkbox_apenas_responsaveis']").is(':checked') === true) {
            //desativa o fluxo do evento de marcar da combobox. 
            //Faz com que o campo não seja preenchido.
            event.preventDefault();
            //foca no campo
            $("#combobox-input-text-caixa_minha_responsabilidade").focus();
            //chama function do javascript mensagem.js
            mostraFlashMessage("A unidade selecionada não possui responsáveis.", "notice");
        }
    }
}

/**
 * Responsável por encadear a combo das pessoas pela unidade
 * @param {object} event
 * @param {object} ui
 * @returns {undefined}
 */
function encadeiaPessoaUnidade(event, ui) {
    $("option", "#pessoas_da_unidade").remove();
    if (typeof jsonPessoasFisicasTrf1AgrupadasPorLotacao[ui.item.value] !== "undefined") {
        $.each(jsonPessoasFisicasTrf1AgrupadasPorLotacao[ui.item.value], function(key, value) {
            $("#pessoas_da_unidade").append(new Option(value, key, true, true));
        });
        $("#combobox-input-text-pessoas_da_unidade").focus().val("");
    } else {
        desabilitaCampo("pessoas_da_unidade");

        if ($("input[name='checkbox_apenas_responsaveis']").is(':checked') === false) {
            //desativa o fluxo do evento de marcar da combobox. 
            //Faz com que o campo não seja preenchido.
            event.preventDefault();
            //foca no campo
            $("#combobox-input-text-caixa_minha_responsabilidade").focus();
            //chama function do javascript mensagem.js
            mostraFlashMessage("A unidade selecionada não possui pessoas lotadas.", "notice");
        }
    }
}

function mostraUmEscondeOutro(exibir, esconder) {
    $(exibir).each(function(index, value) {
        name = $(value).attr('name');
        exibeCampo(name);
    });
    $(esconder).each(function(index, value) {
        name = $(value).attr('name');
        escondeCampo(name);
    });
}

function trocaParaDocumentosInternos() {


    $("#" + name + "-label").hide();
    $("#" + name + "-element").hide();
    //joga os campos de unidade emissora e redatora para baixo da seleção de tipo de documento
    $("#DOCM_CD_LOTACAO_GERADORA-label, #DOCM_CD_LOTACAO_GERADORA-element")
    .insertAfter("#radio_tipo_cadastro-element");

    $("#DOCM_CD_LOTACAO_REDATORA-label, #DOCM_CD_LOTACAO_REDATORA-element")
    .insertAfter("#DOCM_CD_LOTACAO_GERADORA-element");
    
    $('#radio_tipo_encaminhamento-caixaunidade').attr('checked','checked');
    
    exibeCampo("DOCM_CD_LOTACAO_GERADORA");
    exibeCampo("DOCM_CD_LOTACAO_REDATORA");
    exibeCampo('check_box_autuacao');
    exibeCampo('radio_tipo_encaminhamento');
    exibeCampo('caixa_minha_responsabilidade');
    
    if ($("#check_box_autuacao").attr("checked") === "checked"){
        $("#check_box_autuacao").attr("checked", "checked");
        exibeCamposAutuacao();
    }

    mostraUmEscondeOutro($(".apenas_documento_interno"), $(".apenas_documento_externo"));
    $("#DOCM_NR_DCMTO_USUARIO-label").find('label').html("Número de controle do usuário:");
}

function trocaParaDocumentoPessoal() {

    $("#" + name + "-label").hide();
    $("#" + name + "-element").hide();
    escondeCampo('DOCM_CD_LOTACAO_GERADORA');
    escondeCampo('DOCM_CD_LOTACAO_REDATORA');
    escondeCampo('DOCM_DS_NOME_EMISSOR_EXTERNO');
    escondeCampo('DOCM_ID_PESSOA_EXTERNO');
    escondeCampo('DOCM_ID_PESSOA_EXTERNO');
    escondeCampo('check_box_autuacao');
    escondeCampo('radio_tipo_encaminhamento');
    escondeCampo('caixa_minha_responsabilidade');
    escondeCampo('checkbox_minha_caixa_pessoal');
    escondeCampo('checkbox_apenas_responsaveis');
    escondeCampo('pessoas_da_unidade');
    escondeCampo('responsaveis_pela_unidade');
    escondeCampo('PRDI_DS_TEXTO_AUTUACAO');
    
    $("#DOCM_NR_DCMTO_USUARIO-label").find('label').html("Número de controle do usuário:");
    $("#combobox-input-text-DOCM_ID_PESSOA_EXTERNO").hide(); 
}

function trocaParaDocumentosExternos() {

    //joga os campos de unidade emissora e redatora para baixo do checkbox de autuação
    $("#DOCM_CD_LOTACAO_GERADORA-label, #DOCM_CD_LOTACAO_GERADORA-element")
    .insertBefore("#PRDI_DS_TEXTO_AUTUACAO-label");

    $("#DOCM_CD_LOTACAO_REDATORA-label, #DOCM_CD_LOTACAO_REDATORA-element")
    .insertAfter("#DOCM_CD_LOTACAO_GERADORA-element");

    $('#radio_tipo_encaminhamento-caixaunidade').attr('checked','checked');
    
    exibeCampo('check_box_autuacao');
    exibeCampo('radio_tipo_encaminhamento');
    exibeCampo('caixa_minha_responsabilidade');
    
    if ($("#check_box_autuacao").attr("checked") === "checked") {
        exibeCampo("DOCM_CD_LOTACAO_GERADORA");
        exibeCampo("DOCM_CD_LOTACAO_REDATORA");
        exibeCamposAutuacao();
    } else {
        escondeCampo("DOCM_CD_LOTACAO_GERADORA");
        escondeCampo("DOCM_CD_LOTACAO_REDATORA");
    }

    $("#combobox-input-text-DOCM_ID_PESSOA_EXTERNO, #combobox-input-text-DOCM_ID_PESSOA_EXTERNO").show(); 

    mostraUmEscondeOutro($(".apenas_documento_externo"), $(".apenas_documento_interno"));
    $("#DOCM_NR_DCMTO_USUARIO-label").find('label').html("Número do documento externo:");
}

function escondeCamposAutuacao() {
    escondeCampo("PRDI_DS_TEXTO_AUTUACAO");
    if ($("#radio_tipo_cadastro-externo").attr("checked") === "checked") {
        escondeCampo("DOCM_CD_LOTACAO_GERADORA");
        escondeCampo("DOCM_CD_LOTACAO_REDATORA");
    }
}

function exibeCamposAutuacao() {
    exibeCampo("PRDI_DS_TEXTO_AUTUACAO");
    if ($("#radio_tipo_cadastro-externo").attr("checked") === "checked") {
        exibeCampo("DOCM_CD_LOTACAO_GERADORA");
        exibeCampo("DOCM_CD_LOTACAO_REDATORA");
    }
}

function escondeCampo(name) {
    $("#" + name + "-label").hide();
    $("#" + name + "-element").hide();
}

function exibeCampo(name) {
    $("#" + name + "-label").show();
    $("#" + name + "-element").show();
}

function validaSubmit(event) {
    
    //chama function do javascript mensagem.js
    removeFlashMessage();
    
    //Validar a soma do tamanho dos anexos
    var somaAnexos = 0;
    var cont = 0;
    $('.campo-anexo').each(function(){
        if(this.files[0]){
            somaAnexos += parseInt(this.files[0].size);
        }
        cont++;
    });
    //Valida a quantidade de anexos
    var files = $('input[name="ANEXOS[]"]').length - 1;
    if (files > 20) {
        mostraFlashMessage("A quantidade de anexos deve ser menor ou igual a 20. ", "notice");
        event.preventDefault();
    } else{
        if(somaAnexos > 52428800){
            //Se for apenas um anexo maior que 50 megas
            if(cont == 2){
                mostraFlashMessage("O arquivo informado no campo Anexos ultrapassou o limite de 50 Megas.", "notice");
            }else{
                //Se for a soma de N arquivos
                mostraFlashMessage("A soma dos arquivos do campo Anexos ultrapassou o limite de 50 Megas.", "notice");
            }
            event.preventDefault();
        }else{
            if ($("#arquivo_principal").val() != "") {
                var tamanhoArquivo = 0;
                $('#arquivo_principal').each(function(){
                    if(this.files[0]){
                        tamanhoArquivo = parseInt(this.files[0].size);
                    }
                });
                if(tamanhoArquivo > 52428800){
                    mostraFlashMessage("O Arquivo principal informado ultrapassou o limite de 50 Megas.", "notice");
                    event.preventDefault();
                }else{
                    //chama function do javascript parteVista.js
                    flag = validaConfidencialidade(event);
                    if (flag) {
                        if ($("input[name='radio_tipo_encaminhamento']:checked").val() === "caixa_rascunho" && $("#check_box_autuacao").attr("checked") === "checked") {
                            //chama function do javascript mensagem.js
                            mostraFlashMessage("Não é possível encaminhar um processo para a caixa de rascunho. Marque outro tipo de encaminhamento ou cancele a autuação.", "notice");
                            event.preventDefault();
                        }
                    }
                }
            } else {
                mostraFlashMessage("É obrigatório escolher um arquivo principal para o documento. Se for necessário assinar o documento, o arquivo principal será utilizado.", "notice");
                event.preventDefault();
            }
        }
    }
}

function escondeCamposEncaminhamento() {
    escondeCampo("checkbox_minha_caixa_pessoal");
    escondeCampo("checkbox_apenas_responsaveis");
    escondeCampo("caixa_minha_responsabilidade");
    escondeCampo("pessoas_da_unidade");
    escondeCampo("responsaveis_pela_unidade");
}

function trocaParaCaixaUnidade() {
    $("input[name='radio_tipo_encaminhamento'][value='caixa_unidade']").attr("checked", true);
    escondeCamposEncaminhamento();
    exibeCampo("caixa_minha_responsabilidade");
}

function mostraCaixaPessoalPeloEscopo() {
    escondeCamposEncaminhamento();
    exibeCampo("checkbox_minha_caixa_pessoal");
    if ($("#checkbox_minha_caixa_pessoal").attr("checked") !== "checked") {
        exibeCampo("checkbox_apenas_responsaveis");
        if ($("#checkbox_apenas_responsaveis").attr("checked") === "checked") {
            exibeCampo("responsaveis_pela_unidade");
            exibeCampo("caixa_minha_responsabilidade");
        } else {
            exibeCampo("pessoas_da_unidade");
            exibeCampo("caixa_minha_responsabilidade");
        }
    }
}

function trocaParaCaixaPessoal() {
    $("input[name='radio_tipo_encaminhamento'][value='caixa_pessoal']").attr("checked", true);
    mostraCaixaPessoalPeloEscopo();
}

function trocaParaMinhaCaixaRascunho() {
    $("input[name='radio_tipo_encaminhamento'][value='caixa_rascunho']").attr("checked", true);
    escondeCamposEncaminhamento();
}

/**
 * Mostra os campos de encaminhamento pelo escopo.
 * @returns {undefined}
 */
function mostraEncaminhamentoPeloEscopo() {
    if($("input[name='radio_tipo_cadastro']:checked").val() != "pessoal"){
        if ($("input[name='radio_tipo_encaminhamento']:checked").val() === "caixa_unidade") {
            trocaParaCaixaUnidade();
        } else if ($("input[name='radio_tipo_encaminhamento']:checked").val() === "caixa_pessoal") {
            trocaParaCaixaPessoal();
        } else if ($("input[name='radio_tipo_encaminhamento']:checked").val() === "caixa_rascunho") {
            trocaParaMinhaCaixaRascunho();
        } else {
            trocaParaCaixaUnidade();
        }
    }
}

/**
 * Responsável por inicializar os dados das variaveis a serem utilizadas pelo javascript
 * @param {type} obj
 * @returns {undefined}
 */
function inicializaVariaveis(obj) {
    if (typeof obj !== "undefined") {
        if (typeof obj.jsonComboSubsecoesAgrupadas !== "undefined") {
            jsonComboSubsecoesAgrupadas = obj.jsonComboSubsecoesAgrupadas;
        }
        if (typeof obj.jsonResponsaveisAgrupadosPorUnidade !== "undefined") {
            jsonResponsaveisAgrupadosPorUnidade = obj.jsonResponsaveisAgrupadosPorUnidade;
        }
        if (typeof obj.jsonUnidadesAgrupadasPorResponsavel !== "undefined") {
            jsonUnidadesAgrupadasPorResponsavel = obj.jsonUnidadesAgrupadasPorResponsavel;
        }
        if (typeof obj.jsonPessoasFisicasTrf1AgrupadasPorLotacao !== "undefined") {
            jsonPessoasFisicasTrf1AgrupadasPorLotacao = obj.jsonPessoasFisicasTrf1AgrupadasPorLotacao;
        }
    } else {
        alert("Nenhuma variável de encaminhamento foi inicializada!");
    }
}