/* Dialog das Notificações */

//function mostrarNotf(){
//    var botao_notf = $( "#botao_cx_notf" );
//    $( ".tooltip_notf" ).dialog({
//        title    : 'Notificação',
//        autoOpen : true,
//        modal    : true,
//        show: 'blind',
//        hide: 'slow',
//        width: 500,
//        height: 250,
//        resizable: true,
//        closeOnEscape: false,
//        buttons : {
//            Ok: function() {
//                var valores = $(this).attr("dados");
//                var replaced = valores.split('/').join('.');
//                url = base_url+'/admin/notificacoes/setnotificacaolida/setwritten/'+replaced;
//                dadosAjx = $.ajax({
//                    url: url,
//                    dataType: 'html',
//                    type: 'POST',
//                    success: function(data) {
//                        botao_notf.hide();
//                    }                                    
//                });
//                $(this).dialog("close");
//            },
//            Excluir: function() {
//                var valores = $(this).attr("dados");
//                var replaced = valores.split('/').join('.');
//                url = base_url+'/admin/notificacoes/delnotificacoes/setdel/'+replaced;
//                dadosAjx = $.ajax({
//                    url: url,
//                    dataType: 'html',
//                    type: 'POST',
//                    success: function(data) {
//                        botao_notf.hide();
//                    }                                    
//                });
//                $(this).dialog("close");
//            }
//        },
//        close: function() {
//        }
//    });
//    $('.ui-dialog-titlebar-close').hide('');
//};
  
$(function() {
    $( ".tooltip_notf" ).hide();
    $( "#botao_cx_notf" ).button({
        icons: {
            primary: "ui-icon-comment"
        }
    }).attr('style','position: absolute; right: 35px; width: 28px; height: 16px; display: none;')
      .delay(150).show('bounce')
      .click(
        function(){
            var botao_notf = $( "#botao_cx_notf" );
            $(".tooltip_notf").each(
                function(){
                    var valores = $(this).attr("dados")
                    var replaced = valores.split('/').join('.');
                    url = base_url+'/admin/notificacoes/setnotificacaolida/setwritten/'+replaced;
                    dadosAjx = $.ajax({
                        url: url,
                        dataType: 'html',
                        type: 'POST'
//                        success: function(data) {
//                        }                                    
                    });
                }
            );
            window.location = base_url+'/admin/notificacoes/minhasnotificacoes'
            botao_notf.hide();
        //            $('body').append(mostrarNotf());
        }
      ).addClass("ui-state-highlight");
});

$(document).ready(function(){
    if(window.location.pathname.search('/sosti/solicitacao/vincular') >= 0) {
        $('form[name="vincular"]').submit(function (e) {
            if (!$(this).find('[name="principal"]:checked').val()) {
                $("#flashMessages").html('<div class="notice"><strong>Alerta:</strong> Escolha a solicitação principal!</div>');
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }
});