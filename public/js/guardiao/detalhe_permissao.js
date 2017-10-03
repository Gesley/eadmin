$('document').ready(function() {

    xhr_abrir_detalhe_permissao = null;
    GLOBAL_indice_abas = 1;
    $( "#tabs" ).tabs();
    $( "#tabs" ).tabs("select",GLOBAL_indice_abas);
    $( "#tabs" ).tabs({
        select: function(event, ui) {
            GLOBAL_indice_abas = ui.index;
        }
    });
    
    
    $("#dialog-detalhe_permissao").dialog({
        title    : 'Detalhe',
        autoOpen : false,
        modal    : false,
        show: 'fold',
        hide: 'fold',
        resizable: true,
        width: 800,
        height: 600,
        position: [580,140,0,0],
        buttons : {
            Ok: function() {
                $(this).dialog("close");
            }
        }
    });
        
    $('#historico').click(function(){
        
        //var this_button = $(this);
        var div_dialog_by_id =  $("#dialog-detalhe_permissao");
            
        if (xhr_abrir_detalhe_permissao) {
            xhr_abrir_detalhe_permissao.abort();
        }
        
        xhr_abrir_detalhe_permissao = $.ajax({
            url: base_url + '/guardiao/detalhepermissao/detalhepermissao',
            dataType: 'html',
            type: 'POST',
            data: $('#form').serialize(),
            processData: false, 
            beforeSend:function() {
                div_dialog_by_id.dialog("open");
            },
            success: function(data) {
                div_dialog_by_id.html(data);
                
            },
            complete: function(){
                
            },
            error : function(){
                
            }
        });
    });
});
