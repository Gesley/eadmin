$(document).ready(function () {
    $('.tooltip').tooltipster({
        fixedWidth: 650,
        position: 'bottom-left'
    });
    $(function () {
        $(".tooltip").each(function () {
            $(this).attr("data-oldhref", $(this).attr("href"));
            $(this).removeAttr("href");
        });
    })
});

function verificaNumero(e) {
    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
        return false;
    }
}

$(document).ready(function () {
    $("#SSOL_NR_TOMBO").keypress(verificaNumero);

});


var GLOBAL_indice_abas = 0;
var xhr_abrir_documento;

var grid_tbody_tr;
$(function () {

    grid_tbody_tr = $("table.grid > tbody > tr");
    grid_tbody_tr.click(
            function () {
                grid_tbody_tr.removeClass('hover_nav');

                var this_tr = $(this);
                var is_checked_tr = $(this).attr('marcado');

                var input_check_box = $(this).find('input[type=checkbox]');
                var is_checked_input = input_check_box.attr('checked');

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
    );
    grid_tbody_tr.dblclick(
            function () {
                var this_tr = $(this);
                var input_check_box = $(this).find('input');

                grid_tbody_tr.each(
                        function () {
                            var this_tr = $(this);

                            this_tr.removeAttr('marcado');
                            this_tr.removeClass('hover');
                        }
                );

                var div_dialog_by_id = $("#dialog-documentos_detalhe");
                value_input_check_box = input_check_box.val();
                input_check_box.attr('checked', 'checked');
                this_tr.attr('marcado', 'marcado');
                this_tr.addClass('hover');

                if (xhr_abrir_documento) {
                    xhr_abrir_documento.abort();
                }

                url = base_url + '/sosti/detalhesolicitacao/detalhesol/idcaixa/2';
                xhr_abrir_documento = $.ajax({
                    url: url,
                    dataType: 'html',
                    type: 'POST',
                    data: value_input_check_box,
                    contentType: 'application/json',
                    processData: false,
                    beforeSend: function () {
                        if (!div_dialog_by_id.dialog("isOpen")) {
                            div_dialog_by_id.dialog("open");
                        }
                    },
                    success: function (data) {
                        div_dialog_by_id.html(data);

                    },
                    complete: function () {

                    },
                    error: function () {

                    }
                });
            }
    );
    $("input[type=checkbox][name=input_check_all_grid]").click(
            function () {
                if ($(this).attr('checked')) {
                    $(".nav_check_boxes").attr('checked', 'checked');
                    $("tr[name=rowList]").addClass('hover');
                } else {
                    $(".nav_check_boxes").removeAttr('checked');
                    $("tr[name=rowList]").removeClass('hover');
                }
            }
    );
    var form_valido = false;
    $('input[name=acao]').click(
            function () {
                var acao = this.value;
                var formlistaOs = $('form[name=listaOs]');
                if (acao == 'Salvar') {
                    formlistaOs.attr('action', base_url + '/os/associar/salvar');
                }
                //                else if (acao == 'Baixar') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/baixarcaixa');
                //                } else if (acao == 'Espera') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/desenvolvimentosustentacao/esperacaixa');
                //                } else if (acao == 'Solicitar Informação') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/solicitarinformacao');
                //                } else if (acao == 'Trocar Serviço') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/trocarservico');
                //                } else if (acao == 'Vincular') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/vincular');
                //                } else if (acao == 'Desvincular') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/desvincular');
                //                } else if (acao == 'Parecer') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/parecer');
                //                } else if (acao == 'Extensão de Prazo') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/extenderprazo');
                //                } else if (acao == 'Categorias') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/categorias/categorizar');
                //                } else if (acao == 'Cancelar') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/cancelar');
                //                } else if (acao == 'Acompanhar') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/acompanharsolicitacaocaixa');
                //                } else if (acao == 'Excel') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/detalhesolicitacao/detalhesolexportacao/param/detalhexls');
                //                } else if (acao == 'PDF') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/detalhesolicitacao/detalhesolexportacao/param/detalhepdf');
                //                } else if (acao == 'Excel Gerencial DSV') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/detalhesolicitacao/egd');
                //                } else if (acao == 'Desvincular Atendente') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/solicitacao/desvincularatendente');
                //                } else if (acao == 'Associar Sostis') {
                //                    formlistaOs.attr('action', '<?php echo $this->baseUrl(); ?>/sosti/pesquisarsolicitacoes/associarpesquisar');
                //                }

            }
    );

    $("#Filtrar").click(
            function () {
                form_valido = true;
            }
    );

        $('form[name=listaOs]').submit(function () {
            if (form_valido) {
                return true;
            }
            var solictacaoSelecionada = $(".radio_cx:checked").val();
            if (solictacaoSelecionada == undefined) {
                var mensagem = "<div class='notice'><strong>Alerta:</strong> Escolha uma solicitação!</div>";
                $('#flashMessages').html(mensagem);
                return false;
            } else {
    //                    if (acao == 'Salvar') {
    //                        formlistaOs.attr('action', base_url + '/os/associar/salvar');
    //                    }
                return true;
            }

        }
    );
    grid_tbody_tr = $("table.grid > tbody > tr");
    grid_tbody_tr.each(function (i, val) {
        var $this = $(this).find('input[type=checkbox]').attr('value');
        var objeto = jQuery.parseJSON($this);
        if (objeto != null) {
            var fase = (objeto.MOFA_ID_FASE) ? objeto.MOFA_ID_FASE : null;
            if (fase == 1056) {
                $(this).find('td').first().addClass('pedidoCancelamento');
            }
        }
    });

    grid_tbody_tr = $("table.grid > tbody > tr");
    grid_tbody_tr.each(function (i, val) {
        var $this = $(this).find('input[type=checkbox]').attr('value');
        var objeto = jQuery.parseJSON($this);
        if (objeto != null) {
            var fase = objeto.MOFA_ID_FASE;
            if (fase == 1091) {
                $(this).find('td').first().removeClass();
            }
        }
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
});

$(function () {
    $("#DOCM_CD_MATRICULA_CADASTRO").autocomplete({
        source: base_url + "/sosti/solicitacao/ajaxnomesolicitante",
        minLength: 3,
        delay: 300,
        select: function (event, ui) {
            if (ui.item.value != null) {
                $("#DOCM_CD_MATRICULA_CADASTRO_VALUE").val(ui.item.value);
            }
        },
        change: function (event, ui) {
            if (ui.item.value != null) {
                $("#DOCM_CD_MATRICULA_CADASTRO_VALUE").val(ui.item.value);
            }
        }

    }).keyup(
            function () {
                if (this.value == "") {
                    $("#DOCM_CD_MATRICULA_CADASTRO_VALUE").val('');
                }
            });

    $("#SSOL_CD_MATRICULA_ATENDENTE").autocomplete({
        source: base_url + "/sosti/solicitacao/ajaxnomesolicitante",
        minLength: 3,
        delay: 300,
        select: function (event, ui) {
            if (ui.item.value != null) {
                $("#SSOL_CD_MATRICULA_ATENDENTE_VALUE").val(ui.item.value);
            }
        },
        change: function (event, ui) {
            if (ui.item.value != null) {
                $("#SSOL_CD_MATRICULA_ATENDENTE_VALUE").val(ui.item.value);
            }
        }

    }).keyup(
            function () {
                if (this.value == "") {
                    $("#SSOL_CD_MATRICULA_ATENDENTE_VALUE").val('');
                }
            });

    $("#DOCM_CD_LOTACAO_GERADORA").autocomplete({
        source: base_url + "/sosti/solicitacao/ajaxunidade",
        minLength: 3,
        delay: 500,
        select: function (event, ui) {
            if (ui.item.value != null) {
                $("#DOCM_CD_LOTACAO_GERADORA_VALUE").val(ui.item.value);
            }
        },
        change: function (event, ui) {
            if (ui.item.value != null) {
                $("#DOCM_CD_LOTACAO_GERADORA_VALUE").val(ui.item.value);
            }
        }
    }).keyup(
            function () {
                if (this.value == "") {
                    $("#DOCM_CD_LOTACAO_GERADORA_VALUE").val('');
                }
            });


    $('#pesquisar')
            .click(function () {
                var pesq_div = $("#pesq_div")

                if (pesq_div.css('display') == "none") {
                    pesq_div.show('');
                } else {
                    pesq_div.hide('');
                }
            });

    $('#Filtrar').button();

    if ($('#statusfiltro').text() == 'Filtro Ativo') {
        $("#pesq_div").hide();
    }

    $("#botao_ajuda_recolhe").click(
            function () {
                $("#pesq_div").hide();
                $("#pesquisar").show();
            });
});

$(function () {
    var dates = $("#DATA_INICIAL, #DATA_FINAL").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
            'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior',
        changeMonth: true,
        numberOfMonths: 1,
        changeMonth: true,
                changeYear: true,
        changeMonth: true,
                onSelect: function (selectedDate) {
                    var option = this.id == "DATA_INICIAL" ? "minDate" : "maxDate",
                            instance = $(this).data("datepicker");
                    date = $.datepicker.parseDate(
                            instance.settings.dateFormat ||
                            $.datepicker._defaults.dateFormat,
                            selectedDate, instance.settings);
                    dates.not(this).datepicker("option", option, date);
                }
    });

    var dates_cadastro = $("#DATA_INICIAL_CADASTRO, #DATA_FINAL_CADASTRO").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
            'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior',
        changeMonth: true,
        numberOfMonths: 1,
        changeMonth: true,
                changeYear: true,
        changeMonth: true,
                onSelect: function (selectedDate) {
                    var option = this.id == "DATA_INICIAL_CADASTRO" ? "minDate" : "maxDate",
                            instance = $(this).data("datepicker");
                    date = $.datepicker.parseDate(
                            instance.settings.dateFormat ||
                            $.datepicker._defaults.dateFormat,
                            selectedDate, instance.settings);
                    dates_cadastro.not(this).datepicker("option", option, date);
                }
    });
});

$(function () {
    $('#SGRS_ID_GRUPO').change(
            function () {
                $("#SSER_ID_SERVICO").removeAttr('disabled');
                $.ajax({
                    url: base_url + '/sosti/solicitacao/ajaxservicos',
                    dataType: 'html',
                    type: 'POST',
                    data: this.value,
                    contentType: 'application/json',
                    processData: false,
                    beforeSend: function () {
                        $("#SSER_ID_SERVICO").removeClass('erroInputSelect');
                        $("#SSER_ID_SERVICO").html('');
                        $("#SSER_ID_SERVICO").addClass('carregandoInputSelect');
                    },
                    success: function (data) {
                        $("#SSER_ID_SERVICO").html(data);
                        $("#SSER_ID_SERVICO").removeClass('carregandoInputSelect');
                        $("#SSER_ID_SERVICO").focus();
                    },
                    error: function () {
                        $("#SSER_ID_SERVICO").removeClass('x-form-field');
                        $("#SSER_ID_SERVICO").val('Erro ao carregar.');
                        $("#SSER_ID_SERVICO").addClass('erroInputSelect');
                        $("#SSER_ID_SERVICO").html('<option>Erro ao carregar</option>');
                    }
                });
            }
    );


    $("#DE_MAT-label").hide();
    $("#DE_MAT-element").hide();

    if ($('#SERVICO-nomecompleto').is(':checked') == true) {

        $('#SSER_DS_SERVICO').hide();
        $('#SSER_DS_SERVICO-label').hide();
        $('#SSER_DS_SERVICO').attr('disabled', 'disabled');

        $('#SSER_ID_SERVICO').show();
        $('#SSER_ID_SERVICO-label').show();
        $('#SSER_ID_SERVICO').removeAttr('disabled');
        var description_obj = $('#SSER_ID_SERVICO-element').find('.description');
        description_obj.show();


    } else if ($('#SERVICO-partenome').is(':checked') == true) {


        $('#SSER_ID_SERVICO').hide();
        $('#SSER_ID_SERVICO-label').hide();
        $('#SSER_ID_SERVICO').attr('disabled', 'disabled');
        var description_obj = $('#SSER_ID_SERVICO-element').find('.description');
        description_obj.hide();

        $('#SSER_DS_SERVICO').show();
        $('#SSER_DS_SERVICO-label').show();
        $('#SSER_DS_SERVICO').removeAttr('disabled');


    }
    $('input[type=radio][name=SERVICO]').click(function () {
        if (this.value == 'nomecompleto') {

            $('#SSER_DS_SERVICO').hide();
            $('#SSER_DS_SERVICO').val("");
            $('#SSER_DS_SERVICO-label').hide();
            $('#SSER_DS_SERVICO').attr('disabled', 'disabled');

            $('#SSER_ID_SERVICO').show();
            $('#SSER_ID_SERVICO-label').show();
            $('#SSER_ID_SERVICO').removeAttr('disabled');
            var description_obj = $('#SSER_ID_SERVICO-element').find('.description');
            description_obj.show();

        } else if (this.value == 'partenome') {
            $('#SSER_ID_SERVICO').hide();
            $('#SSER_ID_SERVICO').val("");
            $('#SSER_ID_SERVICO-label').hide();
            $('#SSER_ID_SERVICO').attr('disabled', 'disabled');
            var description_obj = $('#SSER_ID_SERVICO-element').find('.description');
            description_obj.hide();

            $('#SSER_DS_SERVICO').show();
            $('#SSER_DS_SERVICO-label').show();
            $('#SSER_DS_SERVICO').removeAttr('disabled');
        }

    });
    $('.errors').hide();
    $('#SERVICO-nomecompleto').attr('checked', true);

    $("#DOCM_CD_LOTACAO_GERADORA-element").append($('#esc').html());

});

$(function () {
    // Função para verificar se o campo atendente esta vazio
    $(".grid").each(function () {
        $(".desvincular").click(function () {
            var solictacaoSelecionada = $("input[type=checkbox][name=solicitacao[]]").is(':checked');
            var ckeckBox = $("input[type=checkbox][name=solicitacao[]]:checked").val();
            var splite = ckeckBox.split(',')[2];
            var splite2 = splite.split(':')[1];
            var spliteMatricula = ckeckBox.split(',')[1];
            var spliteMatriculaAtendentes = spliteMatricula.split('-')[0];
            var string = spliteMatriculaAtendentes.split(':')[1];
            var spliteMatriculaAtendentesArray = string.substring(1, 10);
            var usuarioLogado = $("#usuarioLogado").val();
            var idPerfil = $("#idPerfil").val();
            if (solictacaoSelecionada == true) {
                if (splite2 == 'null') {
                    var mensagem = "<div class='notice'><strong>Alerta:</strong> Campo atendente vazio!</div>";
                    $('#flashMessages').html(mensagem);
                    return false;
                }
                ;
            }
            ;
            if ((usuarioLogado != spliteMatriculaAtendentesArray) && (idPerfil != 9)) {

                var mensagem = "<div class='notice'>\n\
                             <strong>Alerta:</strong>\n\
                               Desvinculação somente com atendente ou o responsável pela caixa!\n\
                             </div>";
                $('#flashMessages').html(mensagem);
                return false;
            }
        });
    });
    function h2d(h) {
        return parseInt(h, 16);
    }

    var labels = $("#CATE_ID_CATEGORIA-element").find('label');
    var cores = $("input[type=checkbox][name=CATE_ID_CATEGORIA[]] ").attr('cores');
    cores = jQuery.parseJSON(cores);
    if (cores) {
        $.each(cores, function (chave, cor) {
            $(labels[chave]).css('background-color', cor);
            var cor_numeros = cor.substr(1, cor.length);
            var aux_numeros = h2d(cor_numeros);
            if (aux_numeros >= (h2d('FFFFFF') / 2)) {
                $(labels[chave]).css('color', '#000');
            } else {
                $(labels[chave]).css('color', '#FFFFFF');
            }
        });
    }
});