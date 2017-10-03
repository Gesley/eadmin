 $(document).ready(function(){
    $(".enviar").button().attr('style', 'width: 65px; height: 25px;');
    $("#codigo").blur(function(){
           URL = base_url + '/arquivo/pctt/grid-busca-atividades';
           var codigo = $(this).val();
            $.ajax({
            type: 'get',
            data: 'codigo=' + codigo,
            url: URL,
            beforeSend: function() {
                $(".carregandoAjax").fadeIn(400);
                $(".carregandoAjax").fadeOut(200);
                $(".carregandoAjax").html();
            },
            success: function(data) {
             $(".grid").html(data);
         }

        });
        
        
    });
    
    $("#codigo").focus(function(){
        $(this).val('');
    });
    $("#buscar").click(function(){
        URL = base_url + '/arquivo/pctt/grid-busca-atividades';
           var codigo = $(this).val();
            $.ajax({
            type: 'get',
            data: 'codigo=' + codigo,
            url: URL,
            beforeSend: function() {
                $(".carregandoAjax").fadeIn(400);
                $(".carregandoAjax").fadeOut(200);
                $(".carregandoAjax").html();
            },
            success: function(data) {
             $(".grid").html(data);
         }

        });
    });
});
        
      
 
