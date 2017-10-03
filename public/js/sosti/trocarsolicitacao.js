/*
 * Classe para controle da funcionalidade de troca de solicitante do sosti
 * Author: Luis Fernando Meireles Arantes
 * Date: 09/09/2014
 * */
var TrocarSolicitacao = function () {
    //Mantém o escopo da classe
    'use strict';

    var _sigla = "";
    //Armazena o valor da TRF1/Seção
    var _secao = "";
    //Armazena o valor da Seção/Subseção
    var _subSecao = "";

    var _unidade = "";

    var _cod_lota = "";


    /*
     * Método construtor da classe
     * Carrega os métodos necessários na inicialização do objeto
     * */
    var init = function () {
        $(document).on('submit', 'form:eq(1)', function (e) {
            if($(document).find('[name^="sostisSelecionados"]:checked').length == 0){
                e.preventDefault();
                $('#flashMessages').html('<div class="notice "><strong>Alerta: </strong>É necessario escolher uma Solicitação. </div>');
                $(document).scrollTop(0);
            }
        });
        _secao = $(document).find('#TRF1_SECAO').val().split('|')[1];
        _sigla = $(document).find('#TRF1_SECAO').val().split('|')[0];
        _subSecao = $(document).find('#SECAO_SUBSECAO').val().split('|')[1];
        _cod_lota = (_unidade) ? _unidade : (_subSecao) ? _subSecao : _secao;
        dbClick();
        habilitaSubSecao();
        habilitaUnidadeAdministrativa();
        autocompleteUnidadeAdministrativa();
        autocompleteSolicitante();
        filtro();
        radioController();
        $(document).on('click', '#Voltar', function () {
            window.location = base_url + "/sosti/trocarsolicitante";
        });

        $("#dialog-documentos_detalhe").dialog({
            title: 'Detalhe',
            autoOpen: false,
            modal: false,
            show: 'fold',
            hide: 'fold',
            resizable: true,
            width: 800,
            position: [580, 140, 0, 0],
            buttons: {
                Ok: function () {
                    $(this).dialog("close");
                }
            }
        });
    }

    /*
     * Inicializa o autocomplete do campo "Unidade Administrativa"
     * */
    var autocompleteUnidadeAdministrativa = function () {
        var solicitante = $(document).find('#DOCM_CD_MATRICULA_CADASTRO');
        $("#DOCM_CD_LOTACAO_GERADORA").autocomplete({
            source: base_url + "/sosti/solicitacao/ajaxunidade/secao/" + _secao + "/subsecao/" + _subSecao,
            minLength: 3,
            select: function (event, ui) {
                if (ui.item.length == 0) {
                    _unidade = ""
                }
                else {
                    _unidade = ui.item.cod_lota;
                }
                var _cod_lota = (_unidade) ? _unidade : (_subSecao) ? _subSecao : _secao;
                solicitante.autocomplete("option", 'source', base_url + "/sosti/solicitacao/ajaxnomesolicitante/sigla/" + _sigla + "/secao_subsecao_unidade/" + _cod_lota);
            }
        });
    }

    /*
     * Inicializa o autocomplete do campo "Solicitante"
     * */
    var autocompleteSolicitante = function () {
        $("#DOCM_CD_MATRICULA_CADASTRO").autocomplete({
            source: base_url + "/sosti/solicitacao/ajaxnomesolicitante/sigla/" + _sigla + "/secao_subsecao_unidade/" + _cod_lota,
            minLength: 3,
            select: function (e, ui) {
                console.log(ui);
            }

        });
    }

    /*
     * Habilita o campo "Seção/Subseção" de acordo com o valor escolhido em "TRF1/Seção"
     * */
    var habilitaSubSecao = function () {
        // pega o objeto do campo "TRF1/Seção"
        var secao = $(document).find('#TRF1_SECAO');
        // pega o objeto do campo "Seção/Subseção"
        var secaoSub = $(document).find('#SECAO_SUBSECAO');
        // pega o objeto do campo "Unidade Administrativa"
        var unidadeAdministrativa = $(document).find('#DOCM_CD_LOTACAO_GERADORA');
        var solicitante = $(document).find('#DOCM_CD_MATRICULA_CADASTRO');


        // Observa o evento "change" do campo "TRF1/Seção"
        $(document).on('change', '#TRF1_SECAO', function (e) {
            /*
             * Caso o valor do select não seja nulo é excutada a requisição e atualizado os dados da subseção
             * Caso o valor seja nulo é escrito o option padrão e o campo desabilitado
             * */
            if (this.value != "") {
                var secaoVal = {
                    SESB_SIGLA_SECAO_SUBSECAO: this.value.split('|')[0],
                    LOTA_COD_LOTACAO: this.value.split('|')[1],
                    LOTA_TIPO_LOTACAO: this.value.split('|')[2]
                };

                _sigla = secaoVal.SESB_SIGLA_SECAO_SUBSECAO;
                _secao = secaoVal.LOTA_COD_LOTACAO;

                //atualiza o valor da seção selecionada no select
                unidadeAdministrativa.autocomplete("option", 'source', base_url + "/sosti/solicitacao/ajaxunidade/sigla/" + _sigla + "/secao/" + _secao + "/subsecao/" + _subSecao);
                _cod_lota = (_unidade) ? _unidade : (_subSecao) ? _subSecao : _secao;
                solicitante.autocomplete("option", 'source', base_url + "/sosti/solicitacao/ajaxnomesolicitante/sigla/" + _sigla + "/secao_subsecao_unidade/" + _cod_lota);

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    data: secaoVal,
                    url: base_url + '/sosti/trocarsolicitante/busca-ajax-secao-subsecao/format/json',
                    beforeSend: function () {
                        secaoSub.addClass('carregandoInputSelect');
                    },
                    success: function (data) {
                        secaoSub.html('<option value=""></option>');
                        console.log(data);
                        $.each(data, function () {
                            if(this.key && this.value)
                                secaoSub.append('<option value="' + this.key + '">' + this.value + '</option>');
                        });
                        secaoSub.prop('disabled', false);
                        unidadeAdministrativa.prop('disabled', false);
                        unidadeAdministrativa.prop('placeholder', '');
                    }
                }).done(function () {
                    secaoSub.removeClass('carregandoInputSelect');
                });
            }
            else {
                secaoSub.html('<option value="">Primeiro escolha a TRF1/Seção</option>');
                secaoSub.prop('disabled', true);

                unidadeAdministrativa.prop('placeholder', 'Primeiro escolha a TRF1/Seção');
                unidadeAdministrativa.prop('disabled', true);

                _secao = "";
                _cod_lota = (_unidade) ? _unidade : (_subSecao) ? _subSecao : _secao;
                solicitante.autocomplete("option", 'source', base_url + "/sosti/solicitacao/ajaxnomesolicitante/sigla/" + _sigla + "/secao_subsecao_unidade/" + _cod_lota);
            }
        });
    }

    var habilitaUnidadeAdministrativa = function () {
        var secaoSub = $(document).find('#SECAO_SUBSECAO');
        var unidadeAdministrativa = $(document).find('#DOCM_CD_LOTACAO_GERADORA');
        var solicitante = $(document).find('#DOCM_CD_MATRICULA_CADASTRO');
        $(document).on('change', '#SECAO_SUBSECAO', function (e) {
            if (this.value != "") {
                var secaoSubVal = {
                    LOTA_SIGLA_SECAO: this.value.split('|')[0],
                    LOTA_COD_LOTACAO: this.value.split('|')[1],
                    LOTA_TIPO_LOTACAO: this.value.split('|')[2]
                };
                _subSecao = secaoSubVal.LOTA_COD_LOTACAO;
                unidadeAdministrativa.autocomplete("option", 'source', base_url + "/sosti/solicitacao/ajaxunidade/sigla/" + _sigla + "/secao/" + _secao + "/subsecao/" + _subSecao);

                _cod_lota = (_unidade) ? _unidade : (_subSecao) ? _subSecao : _secao;
                solicitante.autocomplete("option", 'source', base_url + "/sosti/solicitacao/ajaxnomesolicitante/sigla/" + _sigla + "/secao_subsecao_unidade/" + _cod_lota);
            }
            else {
                _subSecao = '';
                unidadeAdministrativa.autocomplete("option", 'source', base_url + "/sosti/solicitacao/ajaxunidade/sigla/" + _sigla + "/secao/" + _secao + "/subsecao/" + _subSecao);

                _cod_lota = (_unidade) ? _unidade : (_subSecao) ? _subSecao : _secao;
                solicitante.autocomplete("option", 'source', base_url + "/sosti/solicitacao/ajaxnomesolicitante/sigla/" + _sigla + "/secao_subsecao_unidade/" + _cod_lota);
            }
        });
    }

    var filtro = function () {
        $(document).on('click', '#filter', function (e) {
            e.preventDefault();

            var form = $(document).find('form.hide');
            if (form.length == 0) {
                $(document).find('form:eq(0)').addClass('hide');
            }
            else {
                form.removeClass('hide');
            }

        });
    }

    var radioController = function () {

        //$(document).on('change', '#checkAll', function(){
        //
        //});

        $(document).on('change', '[name^="sostisSelecionados"], #checkAll', function () {
            var map = [];
            if ($(document).find("#checkSostisSelecionados").length == 0) {
                $('form:last-child').append('<input type="hidden" name="checkSostisSelecionados" id="checkSostisSelecionados" value=\'\'>');
            }
            var hidden = $(document).find("#checkSostisSelecionados");

            if ($(this).prop('id') == 'checkAll') {
                if ($(this).is(':checked')) {
                    $('input[type="checkbox"]:not(#checkAll)').prop('checked', true);
                }
                else {
                    $('input[type="checkbox"]:not(#checkAll)').prop('checked', false);
                }
            }

            var checks = $(document).find('input[type="checkbox"]');
            $.each(checks, function () {
                if ($(this).is(":not(#checkAll):checked")) {
                    map.push(JSON.parse($(this).val()));
                }
            });
            hidden.val(JSON.stringify(map));
        });
    }

    var dbClick = function () {
        var grid_tbody_tr = $("table.grid > tbody > tr");

        grid_tbody_tr.click(function () {
                grid_tbody_tr.removeClass('hover_nav');

                var this_tr = $(this);
                var is_checked_tr = $(this).attr('marcado');

                var input_check_box = $(this).find('input[type=checkbox]');
                var is_checked_input = input_check_box.attr('checked');
                if (input_check_box.length > 0) {
                    if ((is_checked_input == undefined && is_checked_tr == undefined) || (is_checked_input != undefined && is_checked_tr == undefined)) {
                        input_check_box.attr('checked', 'checked');
                        this_tr.attr('marcado', 'marcado');
                        this_tr.addClass('hover');
                    } else {
                        input_check_box.removeAttr('checked');
                        this_tr.removeAttr('marcado');
                        this_tr.removeClass('hover');
                    }
                    input_check_box.focus();
                }
                input_check_box.trigger("change");
            }
        );

        grid_tbody_tr.dblclick(function () {
            var this_tr = $(this);
            var input_radio = $(this).find('input[type="checkbox"]');
            var div_dialog_by_id = $("#dialog-documentos_detalhe");
            var value_input_radio = input_radio.val();
            input_radio.attr('checked', 'checked');
//                this_tr.attr('marcado','marcado');
            this_tr.addClass('hover');

            if (xhr_abrir_documento) {
                xhr_abrir_documento.abort();
            }

            var url = base_url + '/sosti/detalhesolicitacao/detalhesol';
            var xhr_abrir_documento = $.ajax({
                url: url,
                dataType: 'html',
                type: 'POST',
                data: value_input_radio,
                contentType: 'application/json',
                processData: false,
                beforeSend: function () {
                    div_dialog_by_id.dialog("open");
                },
                success: function (data) {
                    div_dialog_by_id.html(data);

                },
                complete: function () {

                },
                error: function () {

                }
            });
        });
    }

    return init();
};

var tr = new TrocarSolicitacao();