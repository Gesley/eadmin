$(document).ready(function() {
    $(document).bind("ajaxSend", function() {
       $("#loading").show();
    }).bind("ajaxComplete", function() {
       $("#loading").hide();
    });
    /** Perfil do Desenvolvimento e Sustentação */
    if ($("#PERFIL_USER").val() == 'desenv') {
        $("#TASO_IC_ACEITE_SOLICITANTE").hide();
        $("#TASO_IC_ACEITE_SOLICITANTE-label").hide();
        $("#TASO_IC_ACEITE_SOLICITANTE-element").hide();
        $("#TASO_DS_JUSTIF_SOLICITANTE").hide();
        $("#TASO_DS_JUSTIF_SOLICITANTE-label").hide();
        $("#TASO_DS_JUSTIF_SOLICITANTE-element").hide();
        $("#ANEXOS_NEGOCIACAO_GESTAO-0").closest('dd').hide();
        $("#ANEXOS_NEGOCIACAO_GESTAO-label").hide();
//        $("#ANEXOS_NEGOCIACAO_GESTAO-element").hide();
    }
    /** Perfil da gestão de demandas */
    if ($("#PERFIL_USER").val() == 'gestao' && ($("#ID_SAVE_TAREFA").val() != 'Incluir')) {
        $("#TASO_IC_ACEITE_ATENDENTE-S").attr('disabled', 'disabled');
        $("#TASO_IC_ACEITE_ATENDENTE-N").attr('disabled', 'disabled');
        $("#TASO_CD_MATR_ATEND_TAREFA").attr('disabled', 'disabled');
        $("#TARE_DS_TAREFA").attr('disabled', 'disabled');
        $("#TARE_ID_TIPO_TAREFA").attr('disabled', 'disabled');
        $("#ANEXOS_NEGOCIACAO_FABRICA-0").attr('disabled', 'disabled');
        $("#TASO_DS_JUSTIF_ATENDENTE").attr('disabled', 'disabled');
        $("#ANEXOS_TAREFA-0").attr('disabled', 'disabled');
        $('#ANEXOS_NEGOCIACAO_GESTAO-label').append('<div id="anexo-gestao"></div>');
//        $('#TARE_ID_TIPO_TAREFA').attr('disabled', 'disabled');
    } else {
//    alert((($('#TASO_IC_ACEITE_SOLICITANTE-S').val() != "" || $('#TASO_IC_ACEITE_SOLICITANTE-N').val() != "")
//            && ($("#ID_SAVE_TAREFA").val() != 'Incluir')));
        if (($('#TASO_IC_ACEITE_SOLICITANTE-S').val() != "" || $('#TASO_IC_ACEITE_SOLICITANTE-N').val() != "")
                && ($("#ID_SAVE_TAREFA").val() != 'Incluir')) {
    //        $("#TARE_ID_TIPO_TAREFA").attr('disabled', 'disabled');   
    //        $("#TARE_DS_TAREFA").attr('disabled', 'disabled');
            $("#TARE_DS_TAREFA").attr('disabled', 'disabled');
            $("#TARE_ID_TIPO_TAREFA").attr('disabled', 'disabled');
            $("#ANEXOS_TAREFA-0").attr('disabled', 'disabled');
//            $("#TASO_CD_MATR_ATEND_TAREFA").attr('disabled', 'disabled');
//            $("#TASO_IC_ACEITE_ATENDENTE-S").attr('disabled', 'disabled');
//            $("#TASO_IC_ACEITE_ATENDENTE-N").attr('disabled', 'disabled');
//            $("#TASO_DS_JUSTIF_ATENDENTE").attr('disabled', 'disabled');
//            $("#ANEXOS_NEGOCIACAO_FABRICA-0").attr('disabled', 'disabled');
            $("#TASO_IC_ACEITE_SOLICITANTE-S").attr('disabled', 'disabled');
            $("#TASO_IC_ACEITE_SOLICITANTE-N").attr('disabled', 'disabled');
            $("#TASO_DS_JUSTIF_SOLICITANTE").attr('disabled', 'disabled');
            $("#ANEXOS_NEGOCIACAO_GESTAO-0").attr('disabled', 'disabled');
        }
    }
    
    /** Mostra os anexos inseridos */
    $('#ANEXOS_NEGOCIACAO_FABRICA-label').append('<div id="anexo-fabrica"></div>');
    $('#ANEXOS_TAREFA-label').append('<div id="anexo-tarefa"></div>');
    
    var idTarefa = $('#TARE_ID_TAREFA').val();
    listAnexoTarefaAjax(idTarefa, 'anexo-gestao');
    listAnexoTarefaAjax(idTarefa, 'anexo-fabrica');
    listAnexoTarefaAjax(idTarefa, 'anexo-tarefa');
    
    function listAnexoTarefaAjax(idTarefa, list) {
        $.ajax({
            url: base_url + '/tarefa/tarefa/listanexos/idTarefa/' + idTarefa + '/list/' + list,
            type: 'GET',
            data: this.value,
            async: false,
            success: function(data) {
                $('#'+list).html(data);
            },
            error: function(){
                $('#'+list).html('<p>Erro ao carregar</p>');
            }
        });
    }
    
    $('.MultiFile-wrap').change(function () {
        AcceptableFileUpload();
    });
    function AcceptableFileUpload() {
        var totalsize = 0;
        var qtde = 0;
        $('form[class=formSave] input:file').each(function () {
            if ($(this).val().length > 0) {
                totalsize = totalsize + $(this)[0].files[0].size;
            }
        });
        qtde = $('form[class=formSave] input:file').length-1;
        if ((totalsize / 1024 / 1024) > 50) {
            alert('Os arquivos anexados podem ter no Maximo 50 Megas.');
            $('input:file').MultiFile('reset');
            return false;
        } else {
            return true;
        }
        if (qtde > 20) {
            alert('A quantidade de arquivos pode ser no máximo 20.');
            return false;
        } else {
            return true;
        }
    }
    
});