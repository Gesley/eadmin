var GLOBAL_indice_abas = 0;
var xhr_abrir_documento;

var grid_tbody_tr;
$(function () {
    grid_tbody_tr = $("table.grid > tbody > tr");
    grid_tbody_tr.click(function () {
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
    });
    grid_tbody_tr.dblclick(function () {
        var this_tr = $(this);
        var input_check_box = $(this).find('input');

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
    $("input[type=checkbox][name=input_check_all_grid]").click(
        function () {
            if ($(this).attr('checked')) {
                $(".nav_check_boxes").attr('checked', 'checked');
                $("tr[name=rowList]").addClass('hover');
            } else {
                $(".nav_check_boxes").removeAttr('checked');
                $("tr[name=rowList]").removeClass('hover');
            }
        });
    $('input[name=acao]').click(
        function () {
            var acao = this.value;
            var formhelpdesk = $('form[name=helpdesk]');
            if (acao == 'Encaminhar') {
                formhelpdesk.attr('action', base_url + '/sosti/caixapessoal/encaminhar');
            } else if (acao == 'Baixar') {
                formhelpdesk.attr('action', base_url + '/sosti/caixapessoal/baixarcaixa');
            } else if (acao == 'Espera') {
                formhelpdesk.attr('action', base_url + '/sosti/caixapessoal/esperacaixa');
            }
        }
    );
    $('form[name=helpdesk]').submit(
        function () {
            var solictacaoSelecionada = $("input[type=checkbox][name=solicitacao[]]:checked").val();
            if (solictacaoSelecionada == undefined) {
                var mensagem = "<div class='notice'><strong>Alerta:</strong> Escolha uma solicitação!</div>";
                $('#flashMessages').html(mensagem);
                return false;
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
            Ok: function () {
                $(this).dialog("close");
            }
        }
    });
    $(".historico").hide('');
});