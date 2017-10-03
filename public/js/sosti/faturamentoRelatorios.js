$(function() {
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
                onSelect: function(selectedDate) {
            var option = this.id == "DATA_INICIAL" ? "minDate" : "maxDate",
                    instance = $(this).data("datepicker");
            date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings);
            dates.not(this).datepicker("option", option, date);
        }
    });
    var datesInicial = $("#DATA_ENTRADA_CAIXA_INICIAL, #DATA_ENTRADA_CAIXA_FINAL").datepicker({
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
                onSelect: function(selectedDate) {
            var option = this.id == "DATA_ENTRADA_CAIXA_INICIAL" ? "minDate" : "maxDate",
                    instance = $(this).data("datepicker");
            date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings);
            datesInicial.not(this).datepicker("option", option, date);
        }
    });
    
    
    var datesInicial = $("#PFAF_DH_PREVISAO_RETORNO_LOTE, #PFAF_DH_RETORNO_LOTE, #PFTR_DH_FATURAMENTO").datepicker({
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
                onSelect: function(selectedDate) {
            var option = this.id == "DATA_ENTRADA_CAIXA_INICIAL" ? "minDate" : "maxDate",
                    instance = $(this).data("datepicker");
        }
    });
});
$(function() {
    $('#pesquisar')
            .click(function() {
        var pesq_div = $("#pesq_div")

        if (pesq_div.css('display') == "none") {
            pesq_div.show('');
        } else {
            pesq_div.hide('');
        }
    });

    $('#Pesquisar').button();
    if ($('#statusfiltro').text() == 'Filtro Ativo') {
        $("#pesq_div").hide();
    }

    $("#botao_ajuda_recolhe").click(
            function() {
                $("#pesq_div").hide();
                $("#pesquisar").show();
            });
});
var GLOBAL_indice_abas = 0;
var xhr_abrir_documento;

var grid_tbody_tr;
$(function() {

    grid_tbody_tr = $("table#sosti > tbody > tr");
    grid_tbody_tr.click(
            function() {
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
            function() {
                var this_tr = $(this);
                var input_check_box = $(this).find('input');

                grid_tbody_tr.each(
                        function() {
                            var this_tr = $(this);
                            var input_check_box = $(this).find('input');

                            input_check_box.removeAttr('checked');
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

                url = base_url + '/sosti/detalhesolicitacao/detalhesol';
                xhr_abrir_documento = $.ajax({
                    url: url,
                    dataType: 'html',
                    type: 'POST',
                    data: value_input_check_box,
                    contentType: 'application/json',
                    processData: false,
                    beforeSend: function() {
                        if (!div_dialog_by_id.dialog("isOpen")) {
                            div_dialog_by_id.dialog("open");
                        }
                    },
                    success: function(data) {
                        div_dialog_by_id.html(data);

                    },
                    complete: function() {

                    },
                    error: function() {

                    }
                });
            }
    );
    $("input[type=checkbox][name=input_check_all_grid]").click(
            function() {
                if ($(this).attr('checked')) {
                    $(".nav_check_boxes").attr('checked', 'checked');
                    $("tr[name=rowList]").addClass('hover');
                } else {
                    $(".nav_check_boxes").removeAttr('checked');
                    $("tr[name=rowList]").removeClass('hover');
                }
            }
    );

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
            Ok: function() {
                $(this).dialog("close");
            }
        }
    });
    var form_valido = false;
    $('input[name=acao]').click(
            function() {
                var acao = this.value;
                var formhelpdesk = $('form[name=relatorios]');
                if (acao === 'Excel') {
                    formhelpdesk.attr('action', base_url + '/sosti/faturamento/relatorios/param/xls');
                } else if (acao === 'Exportar') {
                    formhelpdesk.attr('action', base_url + '/sosti/faturamento/exportardocumentos');
                }else if (acao === 'SLA') {
                    formhelpdesk.attr('action', base_url + '/sosti/sladesenvolvimento/indicadoresnivelservico');
                }else if (acao === 'Liberar para Aferição') {
                    formhelpdesk.attr('action', base_url + '/sosti/faturamento/liberarafericao');
                }else if (acao === 'Gerar Faturamento') {
                    formhelpdesk.attr('action', base_url + '/sosti/faturamento/liberarfaturamento');
                }
            }
    );
    $('form[name=relatorios]').submit(
            function() {

                if (form_valido) {
                    return true;
                }

                var solictacaoSelecionada = $("input[type=checkbox][name=solicitacao[]]:checked").val();
                if (solictacaoSelecionada == undefined) {
                    var mensagem = "<div class='notice'><strong>Alerta:</strong> Escolha uma solicitação!</div>";
                    $('#flashMessages').html(mensagem);
                    return false;
                } else {
                    return true;
                }

            }
    );
    $("#MOFA_CD_MATRICULA").autocomplete({
        //source: "sosti/solicitacao/ajaxnomesolicitante",
        source: base_url + "/sosti/solicitacao/ajaxnomesolicitante",
        minLength: 3,
        delay: 300
    });
    s
});