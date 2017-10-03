$(document).ready(function() {
    $('#editar_1').click(function() { return false; });
    $('#excluir_1').click(function() { return false; });
    $('#painel_1').attr('class', 'painel ui-state-disabled');
    $(".excluir").click( function() {
        var id;
        id = $(this).attr('id');
        if (id !== '1') {
            var dados = $(this).serialize();
            $.ajax({
                url: base_url + '/tarefa/tipotarefa/jsonverificatipotarefa/id/' + id,
                type: 'POST',
                data: dados,
                success: function(data) {
                    $('#msn').text(data.message);
                },
                error: function(){
                    $("#form").html('<p>Erro ao carregar</p>');
                }
            });
            $("#dialog-tipo-tarefa").dialog({
                resizable: false,
                height:140,
                modal: true,
                buttons: {
                    "Confirmar": function() {
                        $(location).attr('href',base_url + '/tarefa/tipotarefa/excluir/id/' + id);
                    },
                    Cancelar: function() {
                        $(this).dialog("close");
                    }
                }
            });
        }
    });    
});