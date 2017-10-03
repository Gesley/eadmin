/* Partes/interessados dos documentos e dos processos 
 * Utilizado para documentos e para autuação de processos
 * */


$(function() {
          
        $(document.body).delegate(".remover-parte","click", function(){
            $(this).parent().parent().remove();
        });
        
         $(document.body).delegate(".removerTodos","click", function(){
             var config = $.data(document.body,'config');
                 linhas_removidas = $(config.tabela.find("."+config.descParte)); 
                // console.log(linhas_removidas);
             linhas_removidas.remove();
        });
        
        $(document.body).delegate(".removerPartesDocs","click", function(){
             var config = $.data(document.body,'config');
                 linhas_removidas = $(config.containerPartesDocumentos.find("."+config.descParte)); 
                //console.log(linhas_removidas);
             linhas_removidas.remove();
        });
        
        // inicializacoes
        $(".li_parte").hide();
        $("#UNIDADE_PARTE").attr("disabled", true);
        $(".pessTRF").show();
        
            $("#TIPO_PARTE").change( function (){
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
             
               
            $("#PAPD_CD_MATRICULA_INTERESSADO").autocomplete({
                source: base_url+'/sisad/autuar/ajaxpessoaspartes',
                minLength: 3,
                delay: 100,
                select: function(event, ui){
                    var config = $.data(document.body,'config');
                    
                     existe_na_lista = config.containerPartes.find("input[value="+ui.item.matricula+"-"+ui.item.id+"]");  
                     encontrou = existe_na_lista.attr('value');
                     if(encontrou != undefined){
                         alert('A pessoa já existe na lista');
                         return;
                     }else{ 
                         
                        encaminharChecado = config.containerPartes.find("input[type='radio']:checked");
                        encaminharChecado.attr('checked', false);
                        
                        var tr = "<tr class='"+config.descParte+"'>";
                        tr += "<td style='width: 12%'><a href='#' class='remover-parte' rel='"+ui.item.matricula+"-"+ui.item.id+"-"+config.tipoParte+"' >Remover</a></td>";
                        tr += "<td style='width: 45%'>"+ ui.item.label +" </td>";
                        tr += "<td> <input type='checkbox' name='partes_pessoa_trf[]' id='partes_pessoa_trf[]' value='"+ui.item.matricula+"-"+ui.item.id+"-1' checked='checked'/></td>";
                        tr += "<td> <input type='checkbox' name='partes_pessoa_trf[]' id='partes_pessoa_trf[]' value='"+ui.item.matricula+"-"+ui.item.id+"-3' checked='checked'/></td>";
                        tr += "<td> <input type='radio' name='encaminhar' value='"+ui.item.matricula+"' checked='checked'/></td>";
                        //tr += "<input type='hidden' value='"+ui.item.matricula+"-"+ui.item.id+"' name='partes_pessoa_trf[]' />";
                        tr += "</tr>";

                        config.tabela.append(tr);
                        $(this).val("");
                        return false;
                     }
                }
            });
            
                     
             $("#PAPD_ID_PESSOA_FISICA").autocomplete({
                source: base_url+'/sisad/partes/ajaxpessoaexterna',
                minLength: 3,    
                delay: 100,
                select: function(event, ui){
                     
                     var config = $.data(document.body,'config');                        
                     existe_na_lista = config.containerPartes.find("input[value="+ui.item.value+"-"+config.tipoParte+"]");
                     encontrou = existe_na_lista.attr('value')
                     if(encontrou != undefined){
                         alert('A Pessoa já existe na lista');
                         return;
                     }
                     var  tr = "<tr class='"+config.descParte+"'>";
                     tr += "<td style='width: 5%'><a href='#' class='remover-parte' rel='"+ui.item.value+"-"+config.tipoParte+"' >Remover</a></td>";
                     tr += "<td>"+ ui.item.label +" </td>"; 
                     tr += "<input type='hidden' value='"+ui.item.value+"-"+config.tipoParte+"' name='partes_pess_ext[]' />";
                     tr += "</tr>";
                     
                     config.tabela.append(tr);
                     $(this).val("");
                     return false;
                }
            });
            
            $("#PAPD_ID_PESSOA_JURIDICA").autocomplete({
                source: base_url+'/sisad/partes/ajaxpessoajuridica',
                minLength: 3,
                delay: 100,
                select: function(event, ui){
                          
                     var config = $.data(document.body,'config'); 
                     existe_na_lista = config.containerPartes.find("input[value="+ui.item.value+"-"+config.tipoParte+"]");
                     encontrou = existe_na_lista.attr('value')
                     if(encontrou != undefined){
                         alert('A Pessoa já existe na lista');
                         return;
                     }
                     
                     var tr = "<tr class='"+config.descParte+"'>";
                     tr += "<td style='width: 5%'><a href='#' class='remover-parte' rel='"+ui.item.value+"-"+config.tipoParte+"' >Remover</a></td>";
                     tr += "<td>"+ ui.item.label +" </td>"; 
                     tr += "<input type='hidden' value='"+ui.item.id+"-"+config.tipoParte+"' name='partes_pess_jur[]' />";
                     tr += "</tr>";
                     
                     config.tabela.append(tr);
                     $(this).val("");
                     return false;
                }
            });
            
            $("#TRF1_SECAO").change( function (){
                    $("#UNIDADE_PARTE").attr("disabled", false);
                    $("#UNIDADE_PARTE").val(""); 
                    $dados = $(this).val().split("|");
                    
                    $("#UNIDADE_PARTE").autocomplete({
                        source: base_url+'/sisad/partes/ajaxunidade/sigla/'+$dados[0]+'/cod/'+$dados[1],
                        minLength: 3,
                        delay: 100,
                        select: function(event, ui){
                             var config = $.data(document.body,'config');
                             existe_na_lista = config.containerPartes.find("input[value="+ui.item.sigla_secao+"-"+ui.item.cod_lota+"-"+config.tipoParte+"]"); 
                             encontrou = existe_na_lista.attr('value')
                             if(encontrou != undefined){
                                 alert('A unidade já existe na lista');
                                 return;
                             }else{ 
                                var tr = "<tr class='"+config.descParte+"'>";
                                tr += "<td style='width: 5%'><a href='#' class='remover-parte' rel='"+ui.item.sigla_secao+"-"+ui.item.cod_lota+"-"+config.tipoParte+"' >Remover</a></td>";
                                tr += "<td>"+ ui.item.label +" </td>";
                                tr += "<input type='hidden' value='"+ui.item.sigla_secao+"-"+ui.item.cod_lota+"-"+config.tipoParte+"' name='partes_unidade[]' />";
                                tr += "</tr>",

                                config.tabela.append(tr);
                                $(this).val("");
                                return false; 

                             }
                        }
                    });
             });
            
     }); 