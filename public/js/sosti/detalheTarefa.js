$(document).ready(function() {
    var idDocumento = '';
    idDocumento = $('#idDocumento').val();
    idMovimentacao = $('#idMovimentacao').val();
    $('#tarefa').click(function() {
        reloadGrid();
    });
    $('.save_'+idDocumento).live('click', function() {
        $('#message').hide();
        $('.save_'+idDocumento).attr('name', 'save');
        var legend;
        if ($(this).val() === 'Incluir') {
            legend = 'Incluir Tarefa';
//            configurarPerfil();
//            alert($("#PERFIL_USER").val());
        } else {
            legend = 'Editar Tarefa';
//            configurarPerfil();
//            alert('pegou 3');
        }
        $.ajax({
            url: base_url + '/tarefa/tarefa/save/id/' + $(this).val() + '/idDocumento/' + idDocumento + '/idMovimentacao/' + idMovimentacao,
            type: 'GET',
            data: this.value,
            async: false,
            success: function(data) {
//                aguardeLoadAjax();
                $('#form').html(data);
                $("#fieldSave").text(legend);
            },
            error: function(){
                $("#form").html('<p>Erro ao carregar</p>');
            }
            
        }); 
    });
    $('.visualizar').live('click', function() {
//    alert('pegou');
//    $("#loadingTarefa").show();
        $('#message').hide();
//        aguardeLoadAjax();
        $.ajax({
            url: base_url + '/tarefa/tarefa/visualizar/id/' + $(this).val(),
            type: 'GET',
            data: this.value,
            async: false,
            success: function(data) {
                $('#form').html(data);
            },
            error: function(){
                $("#form").html('<p>Erro ao carregar</p>');
            }
        }); 
    });
    $('.excluir').live('click', function() {
        var id = '';
        id = $(this).val();
        $("#dialog-tarefa").dialog({
            resizable: false,
            height:140,
            modal: true,
            buttons: {
                "Sim": function() {
                    $('.dialog-tarefa').dialog("close");
                    var dados = $(this).serialize();
                    $.ajax({
                        url: base_url + '/tarefa/tarefa/excluir/id/' + id,
                        type: 'POST',
                        data: dados,
                        success: function(data) {
                            if (data.status == undefined) {
                                $('#message').attr('class', 'error');
                                $('#message').html("<strong>Erro: </strong> Existe avaliação, não foi possível excluir a tarefa!" );
                                $('#fieldsetSave').hide();
                            } else {
                                $('#message').attr('class', data.status);
                                $('#message').html("<strong>Sucesso: </strong>" + data.message);
                                $('#fieldsetSave').hide();
                            }
                            reloadGrid();
                        },
                        error: function(){
                            $("#form").html('<p>Erro ao carregar</p>');
                        }
                    }); 
                }, 
                Não: function() {
                    $('.dialog-tarefa').dialog("close");
                }
            }
        });
    });
    $('#tarefa_'+idDocumento).live('submit', function() {
        var formData = new FormData($(this)[0]);
        $.ajax({
        url: base_url + '/tarefa/tarefa/save/idDocumento/' + idDocumento,
        type: "POST",
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function () {
            /** Validações de campos vazios no perfil Gestão na edição */
            if ($('#ID_SAVE_TAREFA').val() != 'Incluir') {
                if (($("input[name='TASO_IC_ACEITE_SOLICITANTE']:checked").val() == undefined) && ($('#PERFIL_USER').val() == 'gestao')) {
                    $('#TASO_IC_ACEITE_SOLICITANTE-element').after("<ul class='errors'><li>Preenchimento Obrigatório</li></ul>");
                    $('#message').hide();
                    return false;
                } else {
                    $('.errors').hide();
                }
                if (($('#TASO_DS_JUSTIF_SOLICITANTE').val() == "") && ($('#PERFIL_USER').val() == 'gestao')) {
                    $('#TASO_DS_JUSTIF_SOLICITANTE').after("<ul class='errors'>\n\
                        <li>'' é menor que 5 (tamanho mínimo desse campo)</li>\n\
                        <li>Preenchimento Obrigatório</li></ul>");
                    $('#message').hide();
                    return false;
                } else {
                    $('.errors').hide();
                }
                if (($('#TASO_DS_JUSTIF_SOLICITANTE').val().length < 5) && ($('#PERFIL_USER').val() == 'gestao')) {
                    $('#TASO_DS_JUSTIF_SOLICITANTE').after("<ul class='errors'>\n\
                        <li>'"+$('#TASO_DS_JUSTIF_SOLICITANTE').val()+"' é menor que 5 (tamanho mínimo desse campo)</li>\n\
                        <li>Preenchimento Obrigatório</li></ul>");
                    $('#message').hide();
                    return false;
                } else {
                    $('.errors').hide();
                }
                /** Validação dos campos vazios do perfil desenv */
                if (($("input[name='TASO_IC_ACEITE_ATENDENTE']:checked").val() == undefined) && ($('#PERFIL_USER').val() == 'desenv')) {
                    $('#TASO_IC_ACEITE_ATENDENTE-element').after("<ul class='errors'><li>Preenchimento Obrigatório</li></ul>");
                    $('#message').hide();
                    return false;
                } else {
                    $('.errors').hide();
                }
                if (($('#TASO_DS_JUSTIF_ATENDENTE').val() == "") && ($('#PERFIL_USER').val() == 'desenv')) {
                    $('#TASO_DS_JUSTIF_ATENDENTE').after("<ul class='errors'>\n\
                        <li>'' é menor que 5 (tamanho mínimo desse campo)</li>\n\
                        <li>Preenchimento Obrigatório</li></ul>");
                    $('#message').hide();
                    return false;
                } else {
                    $('.errors').hide();
                }
                if (($('#TASO_DS_JUSTIF_ATENDENTE').val().length < 5) && ($('#PERFIL_USER').val() == 'desenv')) {
                    $('#TASO_DS_JUSTIF_ATENDENTE').after("<ul class='errors'>\n\
                        <li>'"+$('#TASO_DS_JUSTIF_ATENDENTE').val()+"' é menor que 5 (tamanho mínimo desse campo)</li>\n\
                        <li>Preenchimento Obrigatório</li></ul>");
                    $('#message').hide();
                    return false;
                } else {
                    $('.errors').hide();
                }
            /** Validação dos campos na inclusão */ 
            } else {
//                alert($('#TARE_DS_TAREFA').val());
                if (($('#TARE_DS_TAREFA').val() == "") && ($('#PERFIL_USER').val() == 'gestao')) {
                    $('#TARE_DS_TAREFA').after("<ul class='errors'>\n\
                        <li>'' é menor que 5 (tamanho mínimo desse campo)</li>\n\
                        <li>Preenchimento Obrigatório</li></ul>");
                    $('#message').hide();
                    return false;
                } else {
                    $('.errors').hide();
                }
                if (($('#TARE_DS_TAREFA').val().length < 5) && ($('#PERFIL_USER').val() == 'gestao')) {
                    $('#TARE_DS_TAREFA').after("<ul class='errors'>\n\
                        <li>'"+$('#TARE_DS_TAREFA').val()+"' é menor que 5 (tamanho mínimo desse campo)</li>\n\
                        <li>Preenchimento Obrigatório</li></ul>");
                    $('#message').hide();
                    return false;
                } else {
                    $('.errors').hide();
                }
                if ((encodeURI($('#TARE_DS_TAREFA').val()).length > 500) && ($('#PERFIL_USER').val() == 'gestao')) {
                    $('#TARE_DS_TAREFA').after("<ul class='errors'>\n\
                        <li>'"+$('#TARE_DS_TAREFA').val()+"' é maior que 500 (tamanho máximo desse campo)</li></ul>");
                    $('#message').hide();
                    return false;
                } else {
                    $('.errors').hide();
                }
            }
        },
        success: function(data) {

            $('#message').show();
            /** Validação dos campos do form de tipo de tarefa */
            if(data.status === 'success'){
                $('.errors').hide();
                $('#message').attr('class', data.status);
                $('#message').html("<strong>Sucesso: </strong>" + data.message);
            }
            if(data.status === 'error'){
                $('.errors').hide();
                $('#message').attr('class', data.status);
                $('#message').html("<strong>Erro: </strong>" + data.message);
            }
//            alert(data.TARE_DS_TAREFA.isEmpty);
//            if(data.TARE_DS_TAREFA.isEmpty){
//                $('#TARE_DS_TAREFA').after("<ul class='errors'>\n\
//                    <li>"+data.TARE_DS_TAREFA.stringLengthTooShort+"</li>\n\
//                    <li>"+data.TARE_DS_TAREFA.isEmpty+"</li></ul>");
//            }else{
//                $('.errors').hide();
//            }
            
            /** Recarrega o form após a atualização */
            if ($('#ID_SAVE_TAREFA').val() != 'Incluir') {
                $.ajax({
                    url: base_url + '/tarefa/tarefa/save/id/' + $('#TARE_ID_TAREFA').val() + '/idDocumento/' + idDocumento + '/idMovimentacao/' + $('#MOFA_ID_MOVIMENTACAO').val(),
                    type: 'GET',
                    data: this.value,
                    async: false,
                    success: function(data) {
                        $('#form').html(data);
                        $("#fieldSave").text('Editar Tarefa');
                    },
                    error: function(){
                        $("#form").html('<p>Erro ao carregar</p>');
                    }

                });
            }
            reloadGrid();
        }
        });
        return false;
    });
    
    function reloadGrid () {
        $.ajax({
            url: base_url + '/tarefa/tarefa/index/idDocumento/' + idDocumento,
            type: 'POST',
            data: this.value,
            success: function(data) {
                $('#grid').html(data);
            },
            error: function(){
                $("#grid").html('<p>Erro ao carregar</p>');
            }
        });
    }
    
    function aguardeLoadAjax() {
        $(document).bind("ajaxSend", function() {
            $("#loadingTarefa").show();
        })
        .bind("ajaxComplete", function() {
            $("#loadingTarefa").hide();
        });
    }

});