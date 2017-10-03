/*
 * Cadastro de partes / vistas na hora da autuação de processos
 * A regra é um pouco diferente do cadastro de documentos, pois na autuação é necessário
 * buscar as partes / vistas que já estão cadastradas nos documentos.
 */
$(function() {

            $(".cadPartes, .cadVistas ").button();
             var $cadPartes = $(".cadPartes");
             var $cadVistas = $(".cadVistas");

            $cadPartes.click( function (){
                // seto as configuracoes da dialog de partes
                $.data(document.body,'config',
                    {
                        containerPartes: $("#partes_adicionadas"),  // div com os campos escondidos relacionados as partes inseridas pelo usuario
                        tabela: $("#selecionados_partes tbody"),            // tabela que mostra o nome das partes na dialog
                        containerPartesDocumentos: $("#partes_documentos"), // div com as partes ja cadastradas nos documentos, se houver
                        descParte: 'linha_partes',                     // classe inserida a cada parte adicionada
                        tipoParte: '1'
                    }
                );
                    $("#selecionados_partes").show();
                    $("#selecionados_vistas").hide();
                    $("#dialog_cadastra_parte_doc").dialog('option', 'title','Cadastro de Partes do Processo');
                    $("#dialog_cadastra_parte_doc").dialog('open');
                    
             });
             
             $cadVistas.click( function (){
                // seto as configuracoes da dialog de vistas
                $.data(document.body,'config',
                    {
                        containerPartes: $("#vistas_adicionadas"),  // div com os campos escondidos relacionados as partes inseridas pelo usuario
                        tabela: $("#selecionados_vistas tbody"),            // tabela que mostra o nome das partes na dialog
                        containerPartesDocumentos: $("#vistas_documentos"), // div com as partes ja cadastradas nos documentos, se houver
                        descParte: 'linha_vistas',                     // classe inserida a cada parte adicionada
                        tipoParte: '3'
                    }
                );
                    $("#selecionados_vistas").show();
                    $("#selecionados_partes").hide();
                    $("#dialog_cadastra_parte_doc").dialog('option', 'title','Cadastro de Vistas do Processo');
                    $("#dialog_cadastra_parte_doc").dialog('open');
                    
             });


            $("#dialog_cadastra_parte_doc").dialog({
                        autoOpen : false,
                        modal    : false,
                        show: 'fold',
                        hide: 'fold',
                        resizable: true,
                        width: 800,
                        height: 600,
                        position: [300,50,0,0],
                        buttons : {
                                Ok: function() {
                                        $(this).dialog("close");
                                }
                        },
                        open: function (){
                            var config = $.data(document.body,'config');
                            var conf = $("#DOCM_ID_CONFIDENCIALIDADE").val();
                            config.containerPartesDocumentos.show();
                            
                            if (/^(3|4)$/.test(conf)){  
                                $("option[value=U]").hide();
                                $("tr.partes_unidade").remove();
                            }else{
                                $("option[value=U]").show();
                            }
                        },
                        close: function() {
                            var config = $.data(document.body,'config');
                            $containerParte = config.containerPartes,
                            $containerParteDocs = config.containerPartesDocumentos,
                            $tipoParte = $("."+config.descParte+""),
                            checkbox = $("#dialog_cadastra_parte").find("input[type='checkbox']").clone(),
                            $inputs = $tipoParte.find("input[type='hidden']").clone(); //busco os campos hidden de acordo com a classe (linha_parte, linha_interessado)
                            $containerParte.html($inputs);  //adiciono todas as partes/interessados na div dentro do form
                            $containerParte.append(checkbox);
                            config.containerPartesDocumentos.hide(); //escondo a div das partes que ja veem cadastradas nos documentos
                            $.data(document.body,'config',''); // zero a variavel config para quando abrir novamente a dialog setar os novos valores
                       }
            });
 
 });

