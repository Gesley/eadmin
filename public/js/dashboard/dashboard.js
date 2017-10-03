configs = {
    'pizza'   : {
        'tipo': 'pie',
        'f' : graficoPie
    }/*,
    'barras'  : {
       'tipo' : 'bar',
       'f' : graficoBar
    },

    'colunas' : {
       'tipo' : 'column',
       'f' : graficoColumn
    },
    'tabela'  : {
       'tipo' : 'grid',
       'f' : graficoGrid
    }*/
};
    
$(function () {
    
    $( "div.grafico" ).each(function(){
        var 
            $item  = $(this),
            id     = '',
            type   = $item.data('grafico'),
            href   = $item.data('url')
            ;
                
        id = $item.find('.content').attr('id');
        //idg = id;
        $.ajax({
            type     : "GET",
            url      : href,
            //cache    : false,
            async    : false,
            dataType : "json",
            success  : function(data){
               // var f = '';
                    
                if ((/^(azulescuro|azul|cinza|verde|laranja)$/.test(data.cor))) {
                    $item
                    .removeClass('portlet-cinza')
                    .addClass('portlet-' + data.cor)
                    .find('.footer').html(data.footer);
                }
                configs[type].f(id,data);
            }
        });
    });
});