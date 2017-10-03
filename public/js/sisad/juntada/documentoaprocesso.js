/* A função é chamada dentro do .click do botão. O javascript está sendo chamado em 'juntada_generico.js'*/

/*
 * NÃO MUDAR O NOME DA VARIAVEL 
 * configuracao_mensagem 
 */
function botaoSalvar() {
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
            //teste de replicação desconsiderar o comentário
            if($('input[name="documentoPrincipal[]"]:checked').size() > 1){
                mensagem += '<div class="notice"><strong>Alerta:</strong> Foram selecionados mais de um processo administrativo. Caso o documento não esteja anexado em nenhum outro processo administrativo, ele será anexado ao primeiro processo como original e nos demais como cópia anexada.</div>';
            }else{
                jsonAux = jQuery.parseJSON($('input[name="documentoPrincipal[]"]:checked').val());
                //console.log(jsonAux.FLAG_HAS_APENSOS);
                if(jsonAux.FLAG_HAS_APENSOS){
                    mensagem += '<div class="notice"><strong>Alerta:</strong> O processo Administrativo selecionado possui apensos. Logo, os documentos serão anexados como originais, caso já não esteja anexado em outro processo, e nos processos apensos serão anexados como cópias anexadas.</div>';
                }
            }
            
            if (jQuery.inArray(valOpcaoVinculo, configuracao_mensagem.juntada_sem_volta) != -1 || valOpcaoVinculo == configuracao_mensagem.juntada_sem_volta) {
                mensagem += '<div class="notice"><strong>Alerta:</strong> A ação <b>' + opcaoVinculo + '</b> é uma ação <b>sem volta</b> caso o(s) documento(s) seja(m) movimentado(s). As vistas do processo administrativo PRINCIPAL serão replicadas para o documento.</div>';
            }
            mensagem += 'Deseja realmente <b >' + opcaoVinculo + '</b> o(s) documento(s) ao(s) processo(s) administrativo(s)?';
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
}