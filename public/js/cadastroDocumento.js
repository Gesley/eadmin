/* Partes/interessados dos documentos e dos processos */


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
    $("#selecionados_vistas").hide();
    $("#dialog_cadastra_parte_doc").dialog('option', 'title','Cadastro de Partes');
    $("#dialog_cadastra_parte_doc").dialog('open');
        
        // inicializacoes
        $(".li_parte").hide();
        $("#UNIDADE_PESSOA").attr("disabled", true);
        $(".pessTRF").show();
        
            $("#TIPO_PESSOA").change( function (){
                $(".li_parte").hide();
                var $value = $(this).val(),
                    classes = {
                        'P': ".pessTRF",
                        'U': ".unidade",
                        'F': ".pessExterna",
                        'J': ".pessJuridica"
                    };
                $( classes[$value] ).show();
             });
             
               
            $("#COMP_CD_MATRICULA_TRF").autocomplete({
//                source: base_url+'/sisad/autuar/ajaxpessoaspartes',
                source: base_url+'/sisad/aviso/ajaxpessoasemjuds',
                minLength: 3,
                delay: 100,
               /* focus: function( event, ui ) {
                    $(this).val( ui.item.label );
                    return false;
                },*/
                select: function(event, ui){
                    var config = $.data(document.body,'config');
                    
                     existe_na_lista = config.containerPartes.find("input[value="+ui.item.value+"-"+ui.item.id+"]");  
                     encontrou = existe_na_lista.attr('value')
                     if(encontrou != undefined){
                         alert('A pessoa já existe na lista');
                         return;
                     }else{ 
                        var tr = "<tr class='linha_interessado'>";
                        tr += "<td style='width: 5%'><a href='#' class='remover-parte' rel='"+ui.item.value+"-"+ui.item.id+"' >Remover</a></td>";
                        tr += "<td>"+ ui.item.label +" </td>";
                        tr += "<input type='hidden' value='"+ui.item.value+"-"+ui.item.id+"' name='pessoa_trf[]' />";
                        tr += "</tr>",
                        config.tabela.append(tr);
                        $(this).val("");
                        return false;
                     }
                }
            });
            
                     
             $("#COMP_ID_PESSOA_FISICA").autocomplete({
                source: base_url+'/sisad/partes/ajaxpessoaexterna',
                minLength: 3,    
                delay: 100,
                /*focus: function( event, ui ) {
                    $(this).val( ui.item.label );
                    return false;
                },*/
                select: function(event, ui){
                     
                     var config = $.data(document.body,'config');                        
                     existe_na_lista = config.containerPartes.find("input[value="+ui.item.value+"]");
                     encontrou = existe_na_lista.attr('value')
                     if(encontrou != undefined){
                         alert('A Pessoa já existe na lista');
                         return;
                     }
                     var  tr = "<tr class='linha_interessado'>";
                     tr += "<td style='width: 5%'><a href='#' class='remover-parte' rel='"+ui.item.value+"' >Remover</a></td>";
                     tr += "<td>"+ ui.item.label +" </td>"; 
                     tr += "<input type='hidden' value='"+ui.item.value+"' name='pess_ext[]' />";
                     tr += "</tr>";
                     
                     config.tabela.append(tr);
                     $(this).val("");
                     return false;
                }
            });
            
            $("#COMP_ID_PESSOA_JURIDICA").autocomplete({
                source: base_url+'/sisad/partes/ajaxpessoajuridica',
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
                         alert('A Pessoa já existe na lista');
                         return;
                     }
                     
                     var tr = "<tr class='linha_interessado'>";
                     tr += "<td style='width: 5%'><a href='#' class='remover-parte' rel='"+ui.item.value+"' >Remover</a></td>";
                     tr += "<td>"+ ui.item.label +" </td>"; 
                     tr += "<input type='hidden' value='"+ui.item.value+"' name='pess_jur[]' />";
                     tr += "</tr>";
                     
                     config.tabela.append(tr);
                     $(this).val("");
                     return false;
                }
            });
            
            $("#TRF1_SECAO").change( function (){
                    $("#UNIDADE_PESSOA").attr("disabled", false);
                    $("#UNIDADE_PESSOA").val(""); 
                    $dados = $(this).val().split("|");
                    
                    $("#UNIDADE_PESSOA").autocomplete({
                        source: base_url+'/sisad/partes/ajaxunidade/sigla/'+$dados[0]+'/cod/'+$dados[1],
                        minLength: 3,
                        delay: 100,
                        /*focus: function( event, ui ) {
                            $(this).val( ui.item.label );
                            return false;
                        },*/
                        select: function(event, ui){
                        //     console.log(ui.item.label);
                             var config = $.data(document.body,'config');
                             existe_na_lista = config.containerPartes.find("input[value="+ui.item.sigla_secao+"-"+ui.item.cod_lota+"]"); 
                             encontrou = existe_na_lista.attr('value')
                             if(encontrou != undefined){
                                 alert('A unidade já existe na lista');
                                 return;
                             }else{ 
                                var tr = "<tr class='linha_interessado'>";
                                tr += "<td style='width: 5%'><a href='#' class='remover-parte' rel='"+ui.item.sigla_secao+"-"+ui.item.cod_lota+"' >Remover</a></td>";
                                tr += "<td>"+ ui.item.label +" </td>";
                                tr += "<input type='hidden' value='"+ui.item.sigla_secao+"-"+ui.item.cod_lota+"' name='unidade_adm[]' />";
                                tr += "</tr>",

                                config.tabela.append(tr);
                                $(this).val("");
                                return false; 

                             }
                        }
                    });
             });
            
             
            
     }); 