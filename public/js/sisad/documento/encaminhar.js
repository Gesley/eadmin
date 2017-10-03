/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas à tela de encaminhar de documentos
 * 
 *  Depende dos scripts:
 *  + /js/sisad/mensagem.js
 *  + 
 */

/**
 * Iniciando variaveis
 */
var jsonComboSubsecoesAgrupadas = {};
var jsonComboUnidadesAgrupadas = {};
var jsonResponsaveisAgrupadosPorUnidade = {};
var jsonUnidadesAgrupadasPorResponsavel = {};
var jsonPessoasFisicasTrf1AgrupadasPorLotacao = {};

$("document").ready(function() {
    
    /* -------------------------------- INICIALIZANDO CAMPOS ---------------------------*/
    
    $("select#MODE_SG_SECAO_UNID_DESTINO").change(
        function () {
            var secao = $(this).val().split('|')[0];
            var lotacao = $(this).val().split('|')[1];
            var tipolotacao = $(this).val().split('|')[2];

            $.ajax({
                url: base_url + '/guardiao/perfilpessoaadm/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                dataType : 'html',
                beforeSend:function() {
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).val('');
                    $( "#combobox-input-text-pessoas_da_unidade" ).val('');
                    $( "#pessoas_da_unidade" ).val('');
                    $( "#MODE_CD_SECAO_UNID_DESTINO" ).val('');
                    $( "select#SECAO_SUBSECAO" ).addClass('carregandoInputSelect');
                    $("#encaminhar #combobox-input-text-pessoas_da_unidade").attr("disabled", "disabled");
                    $("#encaminhar #combobox-input-button-pessoas_da_unidade").attr("disabled", "disabled");
                    $("#encaminhar #combobox-input-text-MODE_CD_SECAO_UNID_DESTINO").attr("disabled","disabled");
                    $("#encaminhar #combobox-input-button-MODE_CD_SECAO_UNID_DESTINO").attr("disabled","disabled");
                    $("#MODE_CD_SECAO_UNID_DESTINO").val("");
                },
                success: function(data) {
                    $( "select#SECAO_SUBSECAO").html(data);
                    $( "select#SECAO_SUBSECAO" ).removeClass('carregandoInputSelect');
                    $( "#selectSECAO_SUBSECAO" ).focus();
                },
                error: function(){
                    $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
                }
            });
        });
        
    $("select#SECAO_SUBSECAO").change(
        function () {
            /*
         * Verifica se a subseção é vazia
         */
            if($("select#SECAO_SUBSECAO").val() != ""){
                
                secao = $(this).val().split('|')[0];
                lotacao = $(this).val().split('|')[1];
                tipolotacao = $(this).val().split('|')[2];
    
                $.ajax({
                    url: base_url + '/guardiao/perfilpessoaadm/ajaxunidadebysecao/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                    dataType : 'html',
                    beforeSend:function() {
                        $('select#MODE_CD_SECAO_UNID_DESTINO').html('');
                        $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).val('');
                        $("#encaminhar #combobox-input-text-MODE_CD_SECAO_UNID_DESTINO").removeAttr("disabled");
                        $("#encaminhar #combobox-input-button-MODE_CD_SECAO_UNID_DESTINO").removeAttr("disabled");
                        $("#encaminhar #combobox-input-text-pessoas_da_unidade").attr("disabled", "disabled");
                    },
                    success: function(data) {
                        $( "#MODE_CD_SECAO_UNID_DESTINO" ).html(data);
                        $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).focus();
                    },
                    error: function(){
                        $('select#LOTA_COD_LOTACAO').html('<option>Erro ao carregar</option>');
                    }
                });
            }
        });
    
    /*
     * Transforma em button 
     */
    $("#encaminhar #salvar").button();
    
    /**
     * Caixa de minha responsabilidade
     */
    $("#encaminhar #caixa_minha_responsabilidade").combobox({
        selected: function(event, ui) {
            encadeiaPessoasDaUnidade(ui);
        //encadeiaResponsaveisPelaUnidade(event, ui, "#caixa_minha_responsabilidade");
        },
        changed: function(event, ui) { 
            encadeiaPessoasDaUnidade(ui);
        //encadeiaResponsaveisPelaUnidade(event, ui, "#caixa_minha_responsabilidade");
        }
    });
    $("#encaminhar #combobox-input-text-caixa_minha_responsabilidade").attr("style", "width: 490px;");

    /*
     * Seção e TRF
     */
    $("#encaminhar #combo_secao_e_trf").combobox({
        selected: function(event, ui) {
            encadeiaSubsecao(event, ui);
        },
        changed: function(event, ui) {
            encadeiaSubsecao(event, ui);
        }
    });
    $("#encaminhar #combobox-input-text-combo_secao_e_trf").attr("style", "width: 500px;");

    /*
     * Subseção
     */
    $("#encaminhar #combo_secao_trf_subsecao").combobox({
        selected: function(event, ui) {
            encadeiaUnidade(event, ui);
        },
        changed: function(event, ui) {
            encadeiaUnidade(event, ui);
        }
    });
    $("#encaminhar #combobox-input-text-combo_secao_trf_subsecao").attr("style", "width: 490px;");

    /*
     * Unidade
     */
    $("#encaminhar #MODE_CD_SECAO_UNID_DESTINO").combobox({
        selected: function(event, ui) {
            encadeiaPessoasDaUnidade(ui);
        },
        changed: function(event, ui) {
            encadeiaPessoasDaUnidade(ui);
        }
    });
    $("#encaminhar #combobox-input-text-MODE_CD_SECAO_UNID_DESTINO").attr("style", "width: 490px;");
    if($('select#MODE_CD_SECAO_UNID_DESTINO').val == ""){   
        $("#encaminhar #combobox-input-text-MODE_CD_SECAO_UNID_DESTINO").attr("disabled", "disabled");
        $("#encaminhar #combobox-input-button-MODE_CD_SECAO_UNID_DESTINO").attr("disabled", "disabled");
    }

    /*
     * Pessoas TRF1
     */
    $('#pessoa_trf1').autocomplete({
        source: base_url+"/guardiao/perfilpessoaadm/ajaxpessoastribunal",
        minLength: 3,
        delay: 500,
        select: function( event, ui ) {
            encadeiaUnidadeDeResponsabilidade(ui);
        }
    });
    
    /*
     * Pessoas da Unidade
     */
    $("#encaminhar #pessoas_da_unidade").combobox({});
    $("#encaminhar #combobox-input-text-pessoas_da_unidade").attr("style", "width: 490px;");
    if($('#MODE_CD_SECAO_UNID_DESTINO').val() == ""){   
        $("#encaminhar #combobox-input-text-pessoas_da_unidade").attr("disabled", "disabled");
        $("#encaminhar #combobox-input-button-pessoas_da_unidade").attr("disabled", "disabled");
    }
    $("#pessoas_da_unidade").attr('style','display: none');

    /*
     * Caixa Pessoal Usuario
     */
    $("#encaminhar #CAIXAS_PESSOAL_USUARIO").combobox();
    $("#encaminhar #combobox-input-text-CAIXAS_PESSOAL_USUARIO").attr("style", "width: 500px;");

    /*
     * Pessoa da Unidade
     */
    $("#encaminhar #MODE_CD_MATR_RECEBEDOR").combobox();
    $("#encaminhar #combobox-input-text-MODE_CD_MATR_RECEBEDOR").attr("style", "width: 500px;");

    /*
     * Responsavel Pela Unidade
     */
    $("#encaminhar #MODE_CD_MATR_RECEBEDOR_UNIDADES").combobox();
    $("#encaminhar #combobox-input-text-MODE_CD_MATR_RECEBEDOR_UNIDADES").attr("style", "width: 500px;");

    /**
     *
     * Habilita Apenas Responsavel
     */ 
    $('#checkbox_apenas_responsaveis').click(function(){
        habilitaResponsaveisUnidade(false);
    });
    

    /* -------------------------------- ENCAMINHAR PARA ---------------------------*/

    /*
     * Selecionar o Radio Button Encaminhar Para
     */
    $("#encaminhar input[name='radio_tipo_encaminhamento']").change(function() {
        mostraEncaminhamentoPeloEscopo();
    });

    /*
     * Checkbox de Filtro do formulário
     */
    $("#encaminhar #check_apenas_caixa_minha_responsabilidade").click(function() {
        mostraCaixaPeloEscopo();
    });

    $("#encaminhar #checkbox_minha_caixa_pessoal").click(function() {
        mostraCaixaPessoalPeloEscopo();
    });

    $("#encaminhar #check_apenas_responsaveis").click(function() {
        mostraResponsavelOuPessoaPeloEscopo();
    });
    
    
    /* -------------------------------- FUNÇÕES -----------------------------------*/
    
    function habilitaResponsaveisUnidade(carregamento){
        
        if(carregamento == false){
            $('#MODE_CD_SECAO_UNID_DESTINO').html('');
            $('#pessoas_da_unidade').html('');
            $('#SECAO_SUBSECAO').html('');
            $('#combobox-input-text-pessoas_da_unidade').val('');
            $('#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO').val('');
            $('#combobox-input-text-caixa_minha_responsabilidade').val('');
        }
        if($("#checkbox_apenas_responsaveis").is(":checked")){
            $('#pessoas_da_unidade-label').html('<b>*Responsáveis pela Unidade:</b>');
        }else{
            $('#pessoas_da_unidade-label').html('<b>*Pessoas da Unidade:</b>');
        }
    }
    
    /**
     * Responsável por encadear o combo de subsecao
     * @param {object} event
     * @param {object} ui
     * @returns {undefined}
     */
    function encadeiaSubsecao(event, ui) {
        $("option", "#combo_secao_trf_subsecao").remove();
        $("option", "#MODE_CD_SECAO_UNID_DESTINO").remove();
        $.each(jsonComboSubsecoesAgrupadas[ui.item.value], function(key, value) {
            $("#encaminhar #combo_secao_trf_subsecao").append(new Option(value, key, true, true));
        });
        $("#encaminhar #combobox-input-text-combo_secao_trf_subsecao").focus().val("");
        $("#encaminhar #combobox-input-text-MODE_CD_SECAO_UNID_DESTINO").val("");
    }

    /**
     * Responsável por encadear o combo de unidades
     * @param {object} event
     * @param {object} ui
     * @returns {undefined}
     */
    function encadeiaUnidade(event, ui) {
        $("option", "#MODE_CD_SECAO_UNID_DESTINO").remove();
        $.each(jsonComboUnidadesAgrupadas[ui.item.value], function(key, value) {
            $("#encaminhar #MODE_CD_SECAO_UNID_DESTINO").append(new Option(value, key, true, true));
        });
        $("#encaminhar #combobox-input-text-MODE_CD_SECAO_UNID_DESTINO").focus().val("");
    }

    /**
     * Responsavel por encadear as unidades de responsabilidade do usuário
     * @param {object} ui
     * @returns {undefined}
     */
    function encadeiaUnidadeDeResponsabilidade(ui) {
        
        var matricula = ui.item.value.split(' - ')[0];
        
        $.ajax({
            url: base_url + '/guardiao/perfilpessoaadm/ajaxcaixaspessoa/PMAT_CD_MATRICULA/'+matricula,
            dataType : 'html',
            beforeSend:function() {
                $( "select#caixa_responsabilidade_usuario" ).html('');
                $( "select#caixa_responsabilidade_usuario" ).addClass('carregandoInputSelect');
            },
            success: function(data) {
                $( "select#caixa_responsabilidade_usuario" ).html(data);
                $( "select#caixa_responsabilidade_usuario" ).removeClass('carregandoInputSelect');
                $( "select#caixa_responsabilidade_usuario" ).focus();
            },
            error: function(){
                $('select#caixa_responsabilidade_usuario').html('<option>Erro ao carregar</option>');
            }
        }); 

    }

    /**
     * Responsavel por encadear as pessoas lotadas em uma unidade
     * @param {object} ui
     * @returns {undefined}
     */
    function encadeiaPessoasDaUnidade(ui) {
        
        var urlAjax = '';
        if($("#checkbox_apenas_responsaveis").is(":checked")){
            urlAjax = base_url + '/guardiao/perfilpessoaadm/ajaxpessoasresponsaveiscaixa/unidade/'+ui.item.value;
        }else{
            urlAjax = base_url + '/guardiao/perfilpessoaadm/ajaxpessoasdaunidade/unidade/'+ui.item.value;
        }
        
        $.ajax({
            url: urlAjax,
            dataType : 'html',
            beforeSend:function() {
                $( "select#combobox-input-text-pessoas_da_unidade" ).addClass('carregandoInputSelect');
                $( "select#pessoas_da_unidade" ).html('');
            },
            success: function(data) {
                $("#encaminhar #combobox-input-text-pessoas_da_unidade").removeAttr("disabled");
                $("#encaminhar #combobox-input-button-pessoas_da_unidade").removeAttr("disabled");
                $( "select#pessoas_da_unidade" ).html(data);
                $( "select#combobox-input-text-pessoas_da_unidade" ).removeClass('carregandoInputSelect');
                $( "select#combobox-input-text-pessoas_da_unidade" ).focus();
            },
            error: function(){
                $('select#combobox-input-text-pessoas_da_unidade').html('<option>Erro ao carregar</option>');
            }
        }); 
    }

    /**
     * Responsavel por encadear os responsáveis pela unidade
     * @param {object} event
     * @param {object} ui
     * @param {string} idCampo
     * @returns {none}
     */
    function encadeiaResponsaveisPelaUnidade(event, ui, idCampo) {
        $("option", "#MODE_CD_MATR_RECEBEDOR_UNIDADES").remove();
        $("#encaminhar #combobox-input-text-MODE_CD_MATR_RECEBEDOR_UNIDADES").val("");
        if (typeof jsonResponsaveisAgrupadosPorUnidade[ui.item.value] !== "undefined") {
            $.each(jsonResponsaveisAgrupadosPorUnidade[ui.item.value], function(key, value) {
                $("#encaminhar #MODE_CD_MATR_RECEBEDOR_UNIDADES").append(new Option(value, key, true, true));
            });
            if (!$("#encaminhar #check_apenas_caixa_minha_responsabilidade").is(":checked") && $("#encaminhar #checkbox_apenas_responsaveis").is(":checked")) {
                abilitaCampo("MODE_CD_MATR_RECEBEDOR_UNIDADES");
                //chama function do javascript mensagem.js
                removeFlashMessage();
                $("#encaminhar #combobox-input-text-MODE_CD_MATR_RECEBEDOR_UNIDADES").focus();
            }
        } else {
            desabilitaCampo("MODE_CD_MATR_RECEBEDOR_UNIDADES");
            //chama function do javascript mensagem.js
            mostraFlashMessage("Não existem pessoas responsáveis por esta unidade.", "notice");
        }
    }
    
    /**
     * Responsável por desabilitar um campo e sua representação jquery caso tenha
     * @param {string} id
     * @returns {none}
     */
    function desabilitaCampo(id) {
        $("#encaminhar #" + id).attr("disabled", true);
        $("#encaminhar #combobox-input-button-" + id).attr("disabled", true);
        $("#encaminhar #combobox-input-text-" + id).attr("disabled", true);
    }

    /**
     * Responsável por abilitar um campo e sua representação jquery caso tenha
     * @param {string} id
     * @returns {none}
     */
    function abilitaCampo(id) {
        $("#encaminhar #" + id).removeAttr("disabled");
        $("#encaminhar #combobox-input-button-" + id).removeAttr("disabled");
        $("#encaminhar #combobox-input-text-" + id).removeAttr("disabled");
    }

    function escondeCampo(name) {
        $("#encaminhar #" + name).hide();
        $("#encaminhar #" + name + "-label").hide();
        $("#encaminhar #" + name + "-element").hide();
        if ($("#encaminhar #" + name).next().attr("class") === "description") {
            $("#encaminhar #" + name).next().hide();
        }
    }

    function exibeCampo(name, flag_nao_jquery) {
        if (flag_nao_jquery) {
            $("#encaminhar #" + name).show();
        }
        $("#encaminhar #" + name + "-label").show();
        $("#encaminhar #" + name + "-element").show();
        if ($("#encaminhar #" + name).next().attr("class") === "description") {
            $("#encaminhar #" + name).next().show();
        }
    }

    function escondeComboEncadeada(classe) {
        $(classe).each(function(index, value) {
            name = $(value).attr('name');
            escondeCampo(name);
        });
    }

    function exibeComboEncadeada(classe) {
        $(classe).each(function(index, value) {
            name = $(value).attr('name');
            exibeCampo(name, true);
        });
    }

    function trocaParaCaixaUnidade() {
        
        $("#encaminhar input[name='radio_tipo_encaminhamento'][value='caixa_unidade']").attr("checked", true);
        
        escondeCampo('pessoa_trf1');
        escondeCampo('caixa_responsabilidade_usuario');
        escondeCampo('checkbox_minha_caixa_pessoal');
        escondeCampo('checkbox_apenas_responsaveis');
        escondeCampo('pessoas_da_unidade');
        escondeCampo('responsaveis_pela_unidade');
        $('#campos-divulgacao').hide();
        
        exibeCampo('check_apenas_caixa_minha_responsabilidade', true);
        exibeCampo('ANEXOS', false);
        exibeCampo('ANEXOS-0_wrap', true);
       
        mostraCaixaPeloEscopo();
    }

    function trocaParaCaixaPessoal() {
        
        $("#encaminhar input[name='radio_tipo_encaminhamento'][value='caixa_pessoal']").attr("checked", true);
        
        escondeCampo('MODE_SG_SECAO_UNID_DESTINO');
        escondeCampo('SECAO_SUBSECAO');
        escondeCampo('MODE_CD_SECAO_UNID_DESTINO');
        escondeCampo('check_apenas_caixa_minha_responsabilidade');
        escondeCampo('caixa_minha_responsabilidade');
        escondeCampo('responsaveis_pela_unidade');
        escondeCampo('pessoas_da_unidade');
        escondeCampo('checkbox_apenas_responsaveis');
        $('#campos-divulgacao').hide();
        
        exibeCampo('ANEXOS', false);
        exibeCampo('ANEXOS-0_wrap', true);
        
        mostraCaixaPessoalPeloEscopo();
    }

    function trocaParaPessoaDaUnidade() {
        
        habilitaResponsaveisUnidade(true);
        escondeCampo('pessoa_trf1');
        escondeCampo('caixa_responsabilidade_usuario');
        escondeCampo('checkbox_minha_caixa_pessoal');
        escondeCampo('responsaveis_pela_unidade');
        escondeCampo('MODE_SG_SECAO_UNID_DESTINO');
        escondeCampo('SECAO_SUBSECAO');
        escondeCampo('MODE_CD_SECAO_UNID_DESTINO');
        escondeCampo('check_apenas_caixa_minha_responsabilidade');
        escondeCampo('caixa_minha_responsabilidade');
        escondeCampo('pessoas_da_unidade');
        $('#campos-divulgacao').hide();
        
        exibeCampo('checkbox_apenas_responsaveis', true);
        exibeCampo('check_apenas_caixa_minha_responsabilidade', true);
        exibeCampo('ANEXOS', false);
        exibeCampo('ANEXOS-0_wrap', true);
        
        mostraCaixaPessoaUnidadePeloEscopo();
    }
    
    function trocaParaListasInternas() {
        
        escondeCampo('pessoa_trf1');
        escondeCampo('ANEXOS-0_wrap');
        escondeCampo('ANEXOS');
        escondeCampo('caixa_responsabilidade_usuario');
        escondeCampo('checkbox_minha_caixa_pessoal');
        escondeCampo('checkbox_apenas_responsaveis');
        escondeCampo('responsaveis_pela_unidade');
        escondeCampo('MODE_SG_SECAO_UNID_DESTINO');
        escondeCampo('SECAO_SUBSECAO');
        escondeCampo('MODE_CD_SECAO_UNID_DESTINO');
        escondeCampo('check_apenas_caixa_minha_responsabilidade');
        escondeCampo('caixa_minha_responsabilidade');
        escondeCampo('check_apenas_caixa_minha_responsabilidade');
        escondeCampo('pessoas_da_unidade');
        
        $('#campos-divulgacao').show();
    }

    /**
     * Mostra tipo de seleção de caixa. se será minha caixa pessoal ou 
     * combobox encadeadas para achar a caixa
     * @returns {undefined}
     */
    function mostraCaixaPeloEscopo() {
        if ($("#encaminhar #check_apenas_caixa_minha_responsabilidade").attr("checked") === "checked") {
            exibeCampo("caixa_minha_responsabilidade");
            escondeComboEncadeada(".combo_encadeada_caixa");
            $('#combobox-input-text-caixa_minha_responsabilidade').val('');
            $('#combobox-input-text-pessoas_da_unidade').val('');
            $('#pessoas_da_unidade').val('');
            $('#SECAO_SUBSECAO').val('');
            $('#MODE_SG_SECAO_UNID_DESTINO').val('');
            $('#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO').val('');
        } else {
            exibeComboEncadeada(".combo_encadeada_caixa");
            exibeComboEncadeada("pessoas_da_unidade");
            escondeCampo("responsaveis_pela_unidade");
            escondeCampo("caixa_minha_responsabilidade");
            $('#MODE_CD_SECAO_UNID_DESTINO').hide();
        }
    }
    
    /**
     * Mostra tipo de seleção de caixa. se será minha caixa pessoal ou 
     * combobox encadeadas para achar a caixa
     * @returns {undefined}
     */
    function mostraCaixaPessoaUnidadePeloEscopo() {
        if ($("#encaminhar #check_apenas_caixa_minha_responsabilidade").attr("checked") === "checked") {
            exibeCampo("caixa_minha_responsabilidade");
            exibeCampo("pessoas_da_unidade", true);
            $("combobox-input-text-pessoas_da_unidade").addClass('carregandoInputSelect');
            $("#pessoas_da_unidade").attr('style', 'display: none;');
            $("#combobox-input-text-pessoas_da_unidade").attr('style', 'display: block; float: left; width: 470px;');
            $("#combobox-input-button-pessoas_da_unidade").attr('style', 'display: block; width: 20px; height: 21px;');
            escondeComboEncadeada(".combo_encadeada_caixa");
            if($('#caixa_minha_responsabilidade').val() == ''){
                $("#encaminhar #combobox-input-text-pessoas_da_unidade").attr("disabled", "disabled");
                $("#encaminhar #combobox-input-button-pessoas_da_unidade").attr("disabled", "disabled");
            }else{
                $("#encaminhar #combobox-input-text-pessoas_da_unidade").removeAttr("disabled");
                $("#encaminhar #combobox-input-button-pessoas_da_unidade").removeAttr("disabled");
            }
        } else {
            exibeComboEncadeada(".combo_encadeada_caixa");
            exibeCampo("pessoas_da_unidade", true);
            $("#pessoas_da_unidade").attr('style', 'display: none;');
            $("#combobox-input-text-pessoas_da_unidade").attr('style', 'display: block; float: left; width: 470px;');
            $("#combobox-input-button-pessoas_da_unidade").attr('style', 'display: block; width: 20px; height: 21px;');
            $('#MODE_CD_SECAO_UNID_DESTINO').hide();
        }
    }

    /**
     * Mostra os campos de encaminhamento pelo escopo.
     * @returns {undefined}
     */
    function mostraEncaminhamentoPeloEscopo() {
        if ($("#encaminhar input[name='radio_tipo_encaminhamento']:checked").val() === "caixa_unidade") {
            trocaParaCaixaUnidade();
        } else if ($("#encaminhar input[name='radio_tipo_encaminhamento']:checked").val() === "caixa_pessoal") {
            trocaParaCaixaPessoal();
        } else if ($("#encaminhar input[name='radio_tipo_encaminhamento']:checked").val() === "pessoa_unidade") {
            trocaParaPessoaDaUnidade();
        }else if ($("#encaminhar input[name='radio_tipo_encaminhamento']:checked").val() === "listas_internas") {
            trocaParaListasInternas();
        } else {
            trocaParaCaixaUnidade();
        }
    }

    function mostraCaixaPessoalPeloEscopo() {

        if ($("#encaminhar #checkbox_minha_caixa_pessoal").attr("checked") === "checked") {
            exibeCampo('checkbox_minha_caixa_pessoal', true);
            escondeCampo('pessoa_trf1');
            escondeCampo('caixa_responsabilidade_usuario');
        } else {
            exibeCampo('checkbox_minha_caixa_pessoal', true);
            exibeCampo('pessoa_trf1', true);
            exibeCampo('caixa_responsabilidade_usuario', true);
        }
    }

    function mostraResponsavelOuPessoaPeloEscopo() {
        if ($("#encaminhar #check_apenas_responsaveis").attr("checked") === "checked") {
            exibeCampo("MODE_CD_MATR_RECEBEDOR_UNIDADES");
            escondeCampo("MODE_CD_MATR_RECEBEDOR");
        } else {
            exibeCampo("MODE_CD_MATR_RECEBEDOR");
            escondeCampo("MODE_CD_MATR_RECEBEDOR_UNIDADES");
        }
    }

    function iniciaPagina() {
        mostraEncaminhamentoPeloEscopo();
    }

    /*AO INICIAR PÁGINA VALIDAR O SEGUINTE*/
    iniciaPagina();
    
    /**
     * Submit do formulario
     */
    $('#encaminhar').submit(function(){
        var validacao = true;
        if($("#encaminhar input[name='radio_tipo_encaminhamento']:checked").val() === "listas_internas"){
            if($('.linha_interessado').length == 0){
                $('#msg-lista').remove();
                var msg1 = "<ul class='errors' id='msg-lista'><li>Selecione pelo menos uma Lista.</li></ul>";
                $('#selecionados_partes').after(msg1);
                validacao = false;
            }
            if($('#LIST_DT_INICIO_DIVULGACAO').val() == ''){
                $('#msg-dt-ini').remove();
                var msg2 = "<ul class='errors' id='msg-dt-ini'><li>Selecione pelo menos uma Lista.</li></ul>";
                $('#LIST_DT_INICIO_DIVULGACAO').after(msg2);
                validacao = false;
            }
            if($('#LIST_DT_FIM_DIVULGACAO').val() == ''){
                $('#msg-dt-fim').remove();
                var msg3 = "<ul class='errors' id='msg-dt-fim'><li>Selecione pelo menos uma Lista.</li></ul>";
                $('#LIST_DT_FIM_DIVULGACAO').after(msg3);
                validacao = false;
            }
        }
        if(validacao == false){
            return false;
        }
    });
});
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
        if (typeof obj.jsonComboUnidadesAgrupadas !== "undefined") {
            jsonComboUnidadesAgrupadas = obj.jsonComboUnidadesAgrupadas;
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