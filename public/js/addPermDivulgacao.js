

$(function() {
          
        $(document.body).delegate(".remover-parte","click", function(){
        $(this).parent().parent().remove();
    });
        
    $(document.body).delegate(".removerTodos","click", function(){
        var config = $.data(document.body,'config'),
        linhas_removidas = $(config.tabela.find("."+config.descParte)); 
        //console.log(linhas_removidas);
        linhas_removidas.remove();
    });
        
    $(document.body).delegate(".removerPartesDocs","click", function(){
        var config = $.data(document.body,'config'),
        linhas_removidas = $(config.containerPartesDocumentos.find("."+config.descParte)); 
        linhas_removidas.remove();
    });
        
    $.data(document.body,'config',
    {
        containerPartes: $("#partes_adicionadas"),
        tabela: $("#selecionados_partes tbody"),
        descParte: 'linha_interessado'
    }
    );
    $("#selecionados_partes").show();
    $("#dialog_cadastra_parte_doc").dialog('option', 'title','Cadastro de Partes');
    $("#dialog_cadastra_parte_doc").dialog('open');
        
        // inicializacoes
//    $(".li_parte").hide();
//    $("#UNIDADE_PESSOA").attr("disabled", true);
//    $(".pessTRF").show();
                        
    $("#DOCM_ID_TIPO_DOC").autocomplete({
        source: base_url+'/sisad/aviso/ajaxnometipodocumento',
        minLength: 3,
        delay: 100,
        /* focus: function( event, ui ) {
                    $(this).val( ui.item.label );
                    return false;
                },*/
        select: function(event, ui){
            var config = $.data(document.body,'config');
                    
            existe_na_lista = config.containerPartes.find("input[value="+ui.item.value+"]");  
            encontrou = existe_na_lista.attr('value')
            if(encontrou != undefined){
                alert('A pessoa já existe na lista');
                return;
            }else{ 
                var tr = "<tr class='linha_interessado'>";
                tr += "<td style='width: 5%'><a href='#' class='remover-parte' rel='"+ui.item.value+"' >Remover</a></td>";
                tr += "<td>"+ ui.item.label +" </td>";
                tr += "<input type='hidden' value='"+ui.item.value+"' name='tipo_doc[]' />";
                tr += "</tr>",
                config.tabela.append(tr);
                $(this).val("");
                return false;
            }
        }
    });
            
     }); 