/**
 * @category    GUARDIAO
 * @copyright   Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author                        Daniel Rodrigues
 * @license                       FREE, keep original copyrights
 * @version                       controlada pelo SVN
 */

/**
 * Variaveis globais Serão usadas para salvar a ultima consulta do formulario
 */
var valor_secao;
var valor_subsecao;
var valor_unidade;
var label_unidade;
var valor_pesquisa;
var pupe_matricula;
var pmat_matricula;
var resp_matricula;

/**
 * 
 * Carrega os valores das variaveis via JSON
 */
function carregaValores(valores) {
    if (valores.unidade != "") {
        valor_unidade = valores.unidade;
    }
    if (valores.labelunidade != "") {
        label_unidade = valores.labelunidade;
    }
    if (valores.pesquisa != "") {
        valor_pesquisa = valores.pesquisa;
    }
    if (valores.pupe_matricula != "") {
        pupe_matricula = valores.pupe_matricula;
    }
    if (valores.pmat_matricula != "") {
        pmat_matricula = valores.pmat_matricula;
    }
    if (valores.resp_matricula != "") {
        resp_matricula = valores.resp_matricula;
    }
}


$(document).ready(function() {

    /*
     * Esconde Dialog de confirmação
     */
    $("#confirma").css('display', 'none');
    $('#historico').css('display', 'none');

    /**
     * Campo Pesquisa de Pessoas
     * #######################################################################################
     * 
     */
    $("#PMAT_CD_MATRICULA").attr("style", "width: 500px;");
    $("#PMAT_CD_MATRICULA").autocomplete({
        source: base_url + "/guardiao/perfilpessoaadm/ajaxpessoastribunal",
        minLength: 3,
        delay: 500,
        select: function(event, ui) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $("#PMAT_CD_MATRICULA").blur();
        }
    });
    $("#PMAT_CD_MATRICULA").css('text-transform','uppercase');

    $("#RESPCAIXA_CD_MATRICULA").combobox({
        selected: function(event, ui) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $('#RESPCAIXA_CD_MATRICULA').val(ui.item.value);
            $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").blur();
        },
        changed: function(event, ui) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $('#RESPCAIXA_CD_MATRICULA').val(ui.item.value);
            $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").blur();
        }
    });
    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").attr("style", "width: 492px;");
    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").css('text-transform', 'uppercase');

    $("#PUPE_CD_MATRICULA").combobox({
        selected: function(event, ui) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $('#PUPE_CD_MATRICULA').val(ui.item.value);
            $("#combobox-input-text-PUPE_CD_MATRICULA").blur();
        },
        changed: function(event, ui) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $('#PUPE_CD_MATRICULA').val(ui.item.value);
            $("#combobox-input-text-PUPE_CD_MATRICULA").blur();
        }
    });
    $("#combobox-input-text-PUPE_CD_MATRICULA").attr("style", "width: 492px;");
    $("#combobox-input-text-PUPE_CD_MATRICULA").css('text-transform', 'uppercase');

    // removido seção

    /*
     * Campo de pesquisa de Pessoas da Secao Judiciária Está aqui em baixo por
     * causa do carregamento da variavel do parametro da Secao
     */
    $("#SECAO_CD_MATRICULA").attr("style","width: 500px;");
        $("#SECAO_CD_MATRICULA").autocomplete({
            source: base_url + "/guardiao/perfilpessoaadm/ajaxpessoassecaoautosg",
            minLength: 3,
            delay: 500,
            select: function( event, ui ) {
                buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
                $("#SECAO_CD_MATRICULA").blur();
            }
        });
    $("#SECAO_CD_MATRICULA").css('text-transform', 'uppercase');

    // REMOVIDO SUBSEÇÃO

    /**
     * Configuração do campo da Unidade
     * ############################################################################
     */
    $("select#LOTA_COD_LOTACAO").combobox({
        selected: function(event, ui) {

            var tipo_pesquisa = $('#GRUPOPESSOAS').val();
            var unidade = $('#LOTA_COD_LOTACAO').val();


            if ((tipo_pesquisa != '') && (unidade != '')) {
                $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                $("#PMAT_CD_MATRICULA-label").css('display', 'none');
                $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');
                $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                $("#SECAO_CD_MATRICULA-label").css('display', 'none');
                $("#PUPE_CD_MATRICULA-element").css('display', 'block');
                $("#PUPE_CD_MATRICULA-label").css('display', 'block');
                $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled', 'disabled');
                $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled', 'disabled');
            } else {
                return false;
            }

            if (tipo_pesquisa != "") {

                if (tipo_pesquisa == "pessoasunidade") {

                    unidade = $("#LOTA_COD_LOTACAO").val();
                    $.ajax({
                        url: base_url + "/guardiao/perfilpessoaadm/ajaxpessoasdaunidade/",
                        data: {
                            "unidade": unidade
                        },
                        beforeSend: function() {
                        },
                        success: function(data) {
                            $("#PUPE_CD_MATRICULA-element").css('display', 'block');
                            $("#PUPE_CD_MATRICULA-label").css('display', 'block');

                            $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                            $("#PMAT_CD_MATRICULA").attr('value', '');
                            $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                            $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                            $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
                            $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                            $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                            $("#SECAO_CD_MATRICULA").attr('value', '');
                            $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                            $("#combobox-input-text-PUPE_CD_MATRICULA").removeAttr('disabled', 'disabled');
                            $("#PUPE_CD_MATRICULA").html(data);
                            $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value', '');
                            $("#combobox-input-text-PUPE_CD_MATRICULA").focus();
                            $("#combobox-input-button-PUPE_CD_MATRICULA").removeAttr('disabled', 'disabled');
                        },
                        error: function() {
                        }
                    });

                }
                if (tipo_pesquisa == "pessoaacesso") {

                    unidade = $("#LOTA_COD_LOTACAO").val();

                    if ((tipo_pesquisa != '') && (unidade != '')) {

                        $.ajax({
                            url: base_url + "/guardiao/perfilpessoaadm/ajaxpessoasresponsaveiscaixa/",
                            data: {
                                "unidade": unidade
                            },
                            beforeSend: function() {
                            },
                            success: function(data) {
                                $("#PUPE_CD_MATRICULA-element").css('display', 'none');
                                $("#PUPE_CD_MATRICULA").attr('value', '');
                                $("#PUPE_CD_MATRICULA-label").css('display', 'none');

                                $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                                $("#PMAT_CD_MATRICULA").attr('value', '');
                                $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                                $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'block');
                                $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'block');

                                $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                                $("#SECAO_CD_MATRICULA").attr('value', '');
                                $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                                $("#RESPCAIXA_CD_MATRICULA").html(data);
                                $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").removeAttr('disabled', 'disabled');
                                $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").attr('value', '');
                                $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").focus();
                                $("#combobox-input-button-RESPCAIXA_CD_MATRICULA").removeAttr('disabled', 'disabled');
                            },
                            error: function() {
                            }
                        });
                    }
                }
                if (tipo_pesquisa == "pessoastribunal") {

                    $('#combobox-input-text-PMAT_CD_MATRICULA').removeAttr('disabled', 'disabled');

                    $("#PUPE_CD_MATRICULA-element").css('display', 'none');
                    $("#PUPE_CD_MATRICULA").attr('value', '');
                    $("#PUPE_CD_MATRICULA-label").css('display', 'none');

                    $("#PMAT_CD_MATRICULA-element").css('display', 'block');
                    $("#PMAT_CD_MATRICULA-label").css('display', 'block');

                    $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                    $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
                    $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                    $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                    $("#SECAO_CD_MATRICULA").attr('value', '');
                    $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                    $("#PMAT_CD_MATRICULA").attr('value', '');
                    $("#PMAT_CD_MATRICULA").val('');
                    $('#PMAT_CD_MATRICULA').removeAttr('disabled', 'disabled');
                    $("#PMAT_CD_MATRICULA").focus();
                }
                if (tipo_pesquisa == "pessoassecao") {

                    $("#SECAO_CD_MATRICULA").removeAttr('disabled', 'disabled');
                    $('#combobox-input-text-PMAT_CD_MATRICULA').removeAttr('disabled', 'disabled');

                    $("#PUPE_CD_MATRICULA-element").css('display', 'none');
                    $("#PUPE_CD_MATRICULA").attr('value', '');
                    $("#PUPE_CD_MATRICULA-label").css('display', 'none');

                    $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                    $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                    $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                    $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
                    $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                    $("#SECAO_CD_MATRICULA-element").css('display', 'block');
                    $("#SECAO_CD_MATRICULA-label").css('display', 'block');

                }
            }
        }
    });// fim da combobox

    /**
     * Configuração do combobox UNIDADE
     */
    $("#combobox-input-text-LOTA_COD_LOTACAO").attr('style', 'width: 492px;');
    $("#combobox-input-text-LOTA_COD_LOTACAO").css('text-transform', 'uppercase');
    aux_button_style = $("#combobox-input-button-LOTA_COD_LOTACAO").attr('style');
    $("#combobox-input-button-LOTA_COD_LOTACAO").attr('style', aux_button_style + ' left: -20px; top: 5px;');
    aux_button_style = $("#combobox-input-button-LOTA_COD_LOTACAO").attr('style');

    /**
     * Tipo de pesquisa
     * ##########################################################################################
     */
    $("#GRUPOPESSOAS").change(function() {

        var tipo_pesquisa = $(this).val();

        $("#LOTA_COD_LOTACAO").attr('value', '');


        /**
         * Se o tipo de pesquisa for escolhido, sleciona o ajax a ser utilizado
         */

        if (tipo_pesquisa != "") {

            if (tipo_pesquisa == "pessoasunidade") {

                $("#combobox-input-text-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                $("#combobox-input-button-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                $('#combobox-input-text-LOTA_COD_LOTACAO').attr('value', '');

                $("#PUPE_CD_MATRICULA-element").css('display', 'block');
                $("#PUPE_CD_MATRICULA-label").css('display', 'block');

                $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                $("#PMAT_CD_MATRICULA").attr('value', '');
                $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
                $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                $("#SECAO_CD_MATRICULA").attr('value', '');
                $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                $("#PUPE_CD_MATRICULA").html(data);
                $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value', '');
                $("#combobox-input-text-PUPE_CD_MATRICULA").focus();

                unidade = $("#LOTA_COD_LOTACAO").val();

                /* se ja tem valor na unidade carrega o ajax ao mudar somente a combo de pesquisa */
                if (unidade != '') {
                    $.ajax({
                        url: base_url + "/guardiao/perfilpessoaadm/ajaxpessoasdaunidade/",
                        data: {
                            "unidade": unidade
                        },
                        beforeSend: function() {
                        },
                        success: function(data) {

                            $("#combobox-input-text-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                            $("#combobox-input-button-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                            $('#combobox-input-text-LOTA_COD_LOTACAO').attr('value', '');

                            $("#PUPE_CD_MATRICULA-element").css('display', 'block');
                            $("#PUPE_CD_MATRICULA-label").css('display', 'block');

                            $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                            $("#PMAT_CD_MATRICULA").attr('value', '');
                            $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                            $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                            $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
                            $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                            $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                            $("#SECAO_CD_MATRICULA").attr('value', '');
                            $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                            $("#combobox-input-text-PUPE_CD_MATRICULA").removeAttr('disabled', 'disabled');
                            $("#PUPE_CD_MATRICULA").html(data);
                            $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value', '');
                            $("#combobox-input-text-PUPE_CD_MATRICULA").focus();
                            $("#combobox-input-button-PUPE_CD_MATRICULA").removeAttr('disabled', 'disabled');
                        },
                        error: function() {
                        }
                    });
                }
            }

            if (tipo_pesquisa == "pessoaacesso") {
                unidade = $("#LOTA_COD_LOTACAO").val();

                if (unidade == '') {
                    $("#combobox-input-text-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                    $("#combobox-input-button-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                    $('#combobox-input-text-LOTA_COD_LOTACAO').attr('value', '');
                }
            }

            if (tipo_pesquisa == "pessoastribunal") {

                $("#combobox-input-text-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                $("#combobox-input-button-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                $('#combobox-input-text-LOTA_COD_LOTACAO').attr('value', '');

                $("#PUPE_CD_MATRICULA-element").css('display', 'none');
                $("#PUPE_CD_MATRICULA").attr('value', '');
                $("#PUPE_CD_MATRICULA-label").css('display', 'none');

                $("#PMAT_CD_MATRICULA-element").css('display', 'block');
                $("#PMAT_CD_MATRICULA-label").css('display', 'block');

                $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
                $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                $("#SECAO_CD_MATRICULA").attr('value', '');
                $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                $("#PMAT_CD_MATRICULA").attr('value', '');
                $("#PMAT_CD_MATRICULA").val('');
                $('#PMAT_CD_MATRICULA').attr('disabled', 'disabled');
            }

            if (tipo_pesquisa == "pessoassecao") {

                $("#SECAO_CD_MATRICULA").attr('disabled', 'disabled');
                $("#combobox-input-text-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                $("#combobox-input-button-LOTA_COD_LOTACAO").removeAttr('disabled', 'disabled');
                $('#combobox-input-text-LOTA_COD_LOTACAO').attr('value', '');

                $("#PUPE_CD_MATRICULA-element").css('display', 'none');
                $("#PUPE_CD_MATRICULA").attr('value', '');
                $("#PUPE_CD_MATRICULA-label").css('display', 'none');

                $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
                $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                $("#SECAO_CD_MATRICULA-element").css('display', 'block');
                $("#SECAO_CD_MATRICULA").attr('value', '');
                $("#SECAO_CD_MATRICULA-label").css('display', 'block');

                $("#SECAO_CD_MATRICULA").attr('value', '');
                $("#SECAO_CD_MATRICULA").val('');
                $("#SECAO_CD_MATRICULA").focus();
                
                
            }

        } else {
            $("#PUPE_CD_MATRICULA-element").css('display', 'block');
            $("#PUPE_CD_MATRICULA-label").css('display', 'block');

            $("#PMAT_CD_MATRICULA-element").css('display', 'none');
            $("#PMAT_CD_MATRICULA-label").css('display', 'none');

            $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
            $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

            $("#SECAO_CD_MATRICULA-element").css('display', 'none');
            $("#SECAO_CD_MATRICULA").attr('value', '');
            $("#SECAO_CD_MATRICULA-label").css('display', 'none');

            $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled', 'disabled');
            $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value', '');
            $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled', 'disabled');
        }
    });


    /**
     * Função que busca os perfis da pessoa em determinada Unidade Parâmetros:
     * Unidade e Matricula do usuário Retornará as ListBox preenchidas
     */
    function buscaPermissoes(unidade, matricula) {
        if (unidade != '' && matricula != '') {
            url = base_url + '/guardiao/perfilpessoaadm/ajaxperfilpessoaadm/unidade/' + unidade + '/matricula/' + matricula;
            $.ajax({
                url: url,
                dataType: 'html',
                processData: false,
                success: function(data) {
                    /**
                     * Carega os campos de perfis Configura a listbox Configura
                     * os botões
                     */
                    $('#div_associar_perfil').html(data);
                    $('#historico').css('display', 'block');
                    $.configureBoxes();
                    $("#Salvar").focus();
                    $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width', '30px').css('height', '28px').css('margin-top', '5px');
                }
            });
        } else {
            $('#Associar').css('display', 'none');
            $('#historico').css('display', 'none');
            return false;
        }
    }

    /**
     * #################################################################################################################
     * Ao clicar no botão Desfazer, voltam as configurações originais dos perfis
     * daquela Unidade
     */
    $("#desfazer").live('click', function() {
        /**
         * Captura os valores
         */
        matricula = "";
        unidade = $("#LOTA_COD_LOTACAO").val();
        if ($('#GRUPOPESSOAS').val() == "pessoasunidade") {
            matricula = $('#combobox-input-text-PUPE_CD_MATRICULA').val();
        }
        if ($('#GRUPOPESSOAS').val() == "pessoaacesso") {
            matricula = $('#combobox-input-text-RESPCAIXA_CD_MATRICULA').val();
        }
        if ($('#GRUPOPESSOAS').val() == "pessoastribunal") {
            matricula = $('#PMAT_CD_MATRICULA').val();
        }

        if (unidade != '' && matricula != '') {
            url = base_url + '/guardiao/perfilpessoaadm/ajaxperfilpessoaadm/unidade/' + unidade + '/matricula/' + matricula;
            $.ajax({
                url: url,
                dataType: 'html',
                processData: false,
                success: function(data) {
                    /**
                     * Carega os campos de perfis Configura a listbox Configura
                     * os botões
                     */
                    $('#div_associar_perfil').html(data);
                    $.configureBoxes();
                    $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width', '30px').css('height', '28px').css('margin-top', '5px');
                }
            });
        } else {
            $('#Associar').css('display', 'none');
            $('#historico').css('display', 'none');
            return false;
        }

    });

    /*
     * Selecionar os perfis ao submeter
     */
    $("#form").submit(function() {
        $("#box2View option").attr("selected", "selected");
        return true;
    });


    /*
     * Configurações iniciais da página
     * 
     */

    // Tipo de pesquisa
    $("#PMAT_CD_MATRICULA-element").css('display', 'none');
    $("#PMAT_CD_MATRICULA-label").css('display', 'none');

    $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
    $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

    $("#SECAO_CD_MATRICULA-element").css('display', 'none');
    $("#SECAO_CD_MATRICULA-label").css('display', 'none');

    $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled', 'disabled');
    $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled', 'disabled');

    $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
    $("#combobox-input-text-LOTA_COD_LOTACAO").attr('disabled', 'disabled');
    $("#combobox-input-button-LOTA_COD_LOTACAO").attr('disabled', 'disabled');
    $('#combobox-input-text-LOTA_COD_LOTACAO').attr('value', '');

//        $("#combobox-input-text-LOTA_COD_LOTACAO").removeAttr('disabled','disabled');
//        $("#combobox-input-button-LOTA_COD_LOTACAO" ).removeAttr('disabled','disabled');;

    /*
     * Confirmação de SAVE
     * #############################################################################
     */

    $("#combobox-input-text-LOTA_COD_LOTACAO").focus(function() {
        /*
         * Se existir o campo da validacao, entao perguntar se o usuario qer
         * salvar as alteracoes
         */
        if ($("#form_validator").length) {
            /**
             * 
             * Dialog de confirmação
             */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons: {
                    Sim: function() {
                        $(this).dialog('close');
                        $("#form").submit();
                    },
                    Não: function() {
                        $(this).dialog('close');
                        $("#div_associar_perfil").html('');
                        $('#historico').css('display', 'none');

                        // Tipo de pesquisa
                        $("#GRUPOPESSOAS").val('');

                        $("#PUPE_CD_MATRICULA-element").css('display', 'block');
                        $("#PUPE_CD_MATRICULA-label").css('display', 'block');

                        $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                        $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                        $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                        $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                        $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled', 'disabled');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value', '');
                        $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled', 'disabled');

                        $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
                    }
                }
            });
        }
    });

    $("#GRUPOPESSOAS").focus(function() {
        /*
         * Se existir o campo da validacao, entao perguntar se o usuario qer
         * salvar as alteracoes
         */
        if ($("#form_validator").length) {
            /**
             * 
             * Dialog de confirmação
             */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons: {
                    Sim: function() {
                        $(this).dialog('close');
                        $("#form").submit();
                    },
                    Não: function() {
                        $(this).dialog('close');
                        $("#div_associar_perfil").html('');
                        $('#historico').css('display', 'none');

                        // Tipo de pesquisa
                        $("#GRUPOPESSOAS").val('');

                        $("#PUPE_CD_MATRICULA-element").css('display', 'block');
                        $("#PUPE_CD_MATRICULA-label").css('display', 'block');

                        $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                        $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                        $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                        $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                        $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled', 'disabled');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value', '');
                        $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled', 'disabled');
                    }
                }
            });
        }
    });

    $("#PMAT_CD_MATRICULA").focus(function() {
        /*
         * Se existir o campo da validacao, entao perguntar se o usuario qer
         * salvar as alteracoes
         */
        if ($("#form_validator").length) {
            /**
             * 
             * Dialog de confirmação
             */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons: {
                    Sim: function() {
                        $(this).dialog('close');
                        $("#form").submit();
                    },
                    Não: function() {
                        $(this).dialog('close');
                        $("#div_associar_perfil").html('');
                        $('#historico').css('display', 'none');

                        $("#PUPE_CD_MATRICULA-element").css('display', 'none');
                        $("#PUPE_CD_MATRICULA-label").css('display', 'none');

                        $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                        $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                        $("#PMAT_CD_MATRICULA-element").css('display', 'block');
                        $("#PMAT_CD_MATRICULA-label").css('display', 'block');

                        $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                        $("#PMAT_CD_MATRICULA").attr('value', '');

                    }
                }
            });
        }
    });

    $("#SECAO_CD_MATRICULA").focus(function() {
        /*
         * Se existir o campo da validacao, entao perguntar se o usuario qer
         * salvar as alteracoes
         */
        if ($("#form_validator").length) {
            /**
             * 
             * Dialog de confirmação
             */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons: {
                    Sim: function() {
                        $(this).dialog('close');
                        $("#form").submit();
                    },
                    Não: function() {
                        $(this).dialog('close');
                        $("#div_associar_perfil").html('');
                        $('#historico').css('display', 'none');

                        $("#PUPE_CD_MATRICULA-element").css('display', 'none');
                        $("#PUPE_CD_MATRICULA-label").css('display', 'none');

                        $("#SECAO_CD_MATRICULA-element").css('display', 'block');
                        $("#SECAO_CD_MATRICULA-label").css('display', 'block');

                        $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                        $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                        $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                        $("#SECAO_CD_MATRICULA").attr('value', '');

                    }
                }
            });
        }
    });

    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").focus(function() {
        /*
         * Se existir o campo da validacao, entao perguntar se o usuario qer
         * salvar as alteracoes
         */
        if ($("#form_validator").length) {
            /**
             * 
             * Dialog de confirmação
             */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons: {
                    Sim: function() {
                        $(this).dialog('close');
                        $("#form").submit();
                    },
                    Não: function() {
                        $(this).dialog('close');
                        $("#div_associar_perfil").html('');
                        $('#historico').css('display', 'none');

                        $("#PUPE_CD_MATRICULA-element").css('display', 'none');
                        $("#PUPE_CD_MATRICULA-label").css('display', 'none');

                        $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                        $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                        $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                        $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                        $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'block');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'block');

                        $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").attr('value', '');

                    }
                }
            });
        }
    });

    $("#combobox-input-text-PUPE_CD_MATRICULA").focus(function() {
        /*
         * Se existir o campo da validacao, entao perguntar se o usuario qer
         * salvar as alteracoes
         */
        if ($("#form_validator").length) {
            /**
             * 
             * Dialog de confirmação
             */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons: {
                    Sim: function() {
                        $(this).dialog('close');
                        $("#form").submit();
                    },
                    Não: function() {
                        $(this).dialog('close');
                        $("#div_associar_perfil").html('');
                        $('#historico').css('display', 'none');

                        $("#PUPE_CD_MATRICULA-element").css('display', 'block');
                        $("#PUPE_CD_MATRICULA-label").css('display', 'block');

                        $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                        $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                        $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                        $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                        $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value', '');

                    }
                }
            });
        }
    });

    if (valor_pesquisa != "") {

        if (valor_pesquisa == "pessoasunidade") {

            unidade = valor_unidade;

            $.ajax({
                url: base_url + "/guardiao/perfilpessoaadm/ajaxpessoasdaunidade/",
                data: {
                    "unidade": unidade
                },
                beforeSend: function() {
                },
                success: function(data) {
                    $("#PUPE_CD_MATRICULA-element").css('display', 'block');
                    $("#PUPE_CD_MATRICULA-label").css('display', 'block');

                    $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                    $("#PMAT_CD_MATRICULA").attr('value', '');
                    $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                    $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
                    $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
                    $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

                    $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                    $("#SECAO_CD_MATRICULA").attr('value', '');
                    $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                    $("#combobox-input-text-PUPE_CD_MATRICULA").removeAttr('disabled', 'disabled');
                    $("#PUPE_CD_MATRICULA").html(data);
                    $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value', '');
                    $("#combobox-input-text-PUPE_CD_MATRICULA").focus();
                    $("#combobox-input-button-PUPE_CD_MATRICULA").removeAttr('disabled', 'disabled');
                },
                error: function() {
                }
            });

        }
        if (valor_pesquisa == "pessoaacesso") {

            unidade = valor_unidade;

            $.ajax({
                url: base_url + "/guardiao/perfilpessoaadm/ajaxpessoasresponsaveiscaixa/",
                data: {
                    "unidade": unidade
                },
                beforeSend: function() {
                },
                success: function(data) {
                    $("#PUPE_CD_MATRICULA-element").css('display', 'none');
                    $("#PUPE_CD_MATRICULA").attr('value', '');
                    $("#PUPE_CD_MATRICULA-label").css('display', 'none');

                    $("#PMAT_CD_MATRICULA-element").css('display', 'none');
                    $("#PMAT_CD_MATRICULA").attr('value', '');
                    $("#PMAT_CD_MATRICULA-label").css('display', 'none');

                    $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'block');
                    $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'block');

                    $("#SECAO_CD_MATRICULA-element").css('display', 'none');
                    $("#SECAO_CD_MATRICULA").attr('value', '');
                    $("#SECAO_CD_MATRICULA-label").css('display', 'none');

                    $("#RESPCAIXA_CD_MATRICULA").html(data);
                    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").removeAttr('disabled', 'disabled');
                    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").attr('value', '');
                    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").focus();
                    $("#combobox-input-button-RESPCAIXA_CD_MATRICULA").removeAttr('disabled', 'disabled');
                },
                error: function() {
                }
            });
        }
        if (valor_pesquisa == "pessoastribunal") {
            $("#PUPE_CD_MATRICULA-element").css('display', 'none');
            $("#PUPE_CD_MATRICULA").attr('value', '');
            $("#PUPE_CD_MATRICULA-label").css('display', 'none');

            $("#PMAT_CD_MATRICULA-element").css('display', 'block');
            $("#PMAT_CD_MATRICULA-label").css('display', 'block');

            $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
            $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
            $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

            $("#SECAO_CD_MATRICULA-element").css('display', 'none');
            $("#SECAO_CD_MATRICULA").attr('value', '');
            $("#SECAO_CD_MATRICULA-label").css('display', 'none');

            $("#PMAT_CD_MATRICULA").attr('value', '');
            $("#PMAT_CD_MATRICULA").val('');
            $("#PMAT_CD_MATRICULA").focus();
        }
        if (valor_pesquisa == "pessoassecao") {
            $("#PUPE_CD_MATRICULA-element").css('display', 'none');
            $("#PUPE_CD_MATRICULA").attr('value', '');
            $("#PUPE_CD_MATRICULA-label").css('display', 'none');

            $("#PMAT_CD_MATRICULA-element").css('display', 'none');
            $("#PMAT_CD_MATRICULA-label").css('display', 'none');

            $("#RESPCAIXA_CD_MATRICULA-element").css('display', 'none');
            $("#RESPCAIXA_CD_MATRICULA").attr('value', '');
            $("#RESPCAIXA_CD_MATRICULA-label").css('display', 'none');

            $("#SECAO_CD_MATRICULA-element").css('display', 'block');
            $("#SECAO_CD_MATRICULA").attr('value', '');
            $("#SECAO_CD_MATRICULA-label").css('display', 'block');

            $("#SECAO_CD_MATRICULA").attr('value', '');
            $("#SECAO_CD_MATRICULA").val('');
            $("#SECAO_CD_MATRICULA").focus();
        }

    }

});