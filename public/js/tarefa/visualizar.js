$(document).ready(function() {
    /** Mostra os anexos inseridos */
    $('#ANEXOS_NEGOCIACAO_GESTAO-label').append('<div id="anexo-gestao"></div>');
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
});