/* Caixa das Notificações 
 * 
 * Javascript construido para as acoes de marcação de lida e exclusão de notificações na caixa de notificacoes
 * 
 * */

$(function(){
   //adiciona o base_url no caminho do link 'ir para caixa'
   $( '.irparacaixa' ).click(function() {
       var $href = $(this).attr('href');
       var $novohref = base_url+$href; 
       $(this).attr("href", $novohref);
    }); 
   
   
   
   $( '.excluirNotif' ).button({
        icons: {
            primary: "ui-icon-trash"
        }
    })
    .attr('style','width: 28px; height: 20px;')
    .click(
        function (){
            var valores = $(this).attr("dados");
            var replaced = valores.split('/').join('.');
            var linha = $(this);
            url = base_url+'/admin/notificacoes/delnotificacoes/setdel/'+replaced;
            dadosAjx = $.ajax({
                url: url,
                dataType: 'html',
                type: 'GET',
                beforeSend:function() {
                    
                },
                success: function() {
                    linha.closest('.tr_notf').hide('slow');
                },
                complete: function(){
                     
                },
                error : function(){
                    
                }
            });
        }
      );
});