var configuracao_mensagem;
function configurarMensagens(configuracao) {
    configuracao_mensagem = configuracao;
}
var GLOBAL_indice_abas = 0;
var xhr_abrir_documento;

var grid_tbody_tr;
$('document').ready(function() {
    grid_tbody_tr = $("table.grid > tbody > tr");
    grid_tbody_tr.click(function() {
        grid_tbody_tr.removeClass('hover_nav');

        var this_tr = $(this);
        var is_checked_tr = $(this).attr('marcado');

        var input_check_box = $(this).find('input');
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

    grid_tbody_tr.live("dblclick",function() {
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

        url = base_url + '/sisad/detalhedcmto/detalhedcmto';
        xhr_abrir_documento = $.ajax({
            url: url,
            dataType: 'html',
            type: 'POST',
            data: value_input_check_box,
            contentType: 'application/json',
            processData: false,
            beforeSend: function() {
                div_dialog_by_id.dialog("open");
                div_dialog_by_id.html('');
            },
            success: function(data) {
                div_dialog_by_id.html(data);
            },
            complete: function() {

            },
            error: function() {

            }
        });
    });


    $("#dialog-documentos_detalhe").dialog({
        title: 'Detalhe',
        autoOpen: false,
        modal: false,
        show: 'fold',
        hide: 'fold',
        resizable: true,
        width: 800,
        height: 600,
        position: [580, 140, 0, 0],
        buttons: {
            Ok: function() {
                $(this).dialog("close");
            }
        }
    });

    $("#Salvar").click(function() {
        if ($('input[name="documentoPrincipal[]"]:checked').val()) {
            if ($('#MOFA_DS_COMPLEMENTO').val().length >= 5) {
                opcaoVinculo = $("#TP_VINCULO option:selected").text();
                valOpcaoVinculo = $("#TP_VINCULO option:selected").val();
                $("#dialog-confirm").dialog({
                    modal: true,
                    autoOpen: true,
                    resizable: false,
                    width: 450,
                    title: 'Confirmação',
                    buttons: {
                        'Sim': function() {
                            $("form").submit();
                        },
                        'Não': function() {
                            $(this).dialog('close');
                        }
                    }
                });
                mensagem = '';
                if (jQuery.inArray(valOpcaoVinculo, configuracao_mensagem.juntada_sem_volta) != -1 || valOpcaoVinculo == configuracao_mensagem.juntada_sem_volta) {
                    if (configuracao_mensagem.tipo_relacao == "documentoaprocesso") {
                        mensagem = '<div class="notice"><strong>Alerta:</strong> A ação <b>' + opcaoVinculo + '</b> é uma ação <b>sem volta</b> caso o(s) processo(s) administrativo(s) seja(m) movimentado(s).</div>';
                    }
                }
                if (configuracao_mensagem.tipo_relacao == "documentoaprocesso") {
                    mensagem += 'Deseja realmente <b >' + opcaoVinculo + '</b> documento(s) ao(s) processo(s) adimistrativo(s)?';
                }

                $('#dialog-confirm').html(mensagem);
                //retorna o texto de dentro da tag
                $('#dialog-confirm').dialog("open");
            } else {
                $('#flashMessagesView').html('<div class="notice"><strong>Alerta:</strong> O campo <b>Justificativa</b> deve ter no minimo 5 caracteres.</div>');
                $(document).scrollTop(0);
            }
        } else {
            $('#flashMessagesView').html('<div class="notice"><strong>Alerta:</strong> Selecione pelo menos um processo administrativo principal.</div>');
            $(document).scrollTop(0);
        }
    });

    /*----------------------------- BOTÕES */
    $(".remover").button({
        icons: {
            primary: "ui-icon-circle-close"
        }
    }).css('width', '25px').css('height', '20px')
            .live("click", function() {
        jsonDocumento = jQuery.parseJSON($(this).parents('tr:first').attr('value'));
        $(this).parents('tr:first').remove();
        $('#flashMessagesView').html('<div class="info"><strong>Informação:</strong> Documento ' + jsonDocumento.MASC_NR_DOCUMENTO + ' removido do esquema de juntada. Você pode desfazer a remoção clicando no botão <b>Recarregar Documentos</b>.</div>');
        $('#total_documentos').html('Total: ' + $('#documentosList > tr').size());
        if ($("#documentosList > tr").size() == 1) {
            $(".remover").remove();
        }
    });
    documentos_selecionados = $("#documentosList").html();
    documentos_total = $("#documentosList > tr").size();
    $("#recarregar_documentos").click(function() {
        $("#documentosList").html(documentos_selecionados);
        $('#total_documentos').html('Total: ' + documentos_total);
        $('#flashMessagesView').html('<div class="info"><strong>Informação:</strong> Lista de documentos para juntada resetada com sucesso.</div>');
    });


});