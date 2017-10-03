/* Solicitar Documentos da Pesquisa de Documentos. */

$(function() {
    $( '.solicitarDocm' ).click(
        function(){
            var valores = $(this).attr("dados");
            url = base_url+'/admin/notificacoes/setsolicitardocumento/data/'+valores;
            dadosAjx = $.ajax({
                url: url,
                dataType: 'html',
                type: 'POST',
                beforeSend:function() {
                    
                },
                success: function() {
                    var mensagem = "<div class='success'><strong>Sucesso:</strong> Documento solicitado com sucesso</div>";
                    $('#flashMessages').html(mensagem);
                },
                complete: function(){
                     
                },
                error : function(){
                    var mensagem = "<div class='Error'><strong>Erro:</strong> Não foi possível solicitar documento.</div>";
                    $('#flashMessages').html(mensagem);
                }
            });
        }
    )
    .button()
    ;
}); 
     