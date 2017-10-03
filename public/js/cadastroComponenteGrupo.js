//$(function() {
//        
//         $(".cadPartes, .cadVistas").button();
//         var $cadPartes = $(".cadPartes");
//         var $cadVistas = $(".cadVistas");
//            
//         $cadPartes.click( function (){
//                $.data(document.body,'config',
//                    {
//                        containerPartes: $("#partes_adicionadas"),
//                        tabela: $("#selecionados_partes tbody"),
//                        descParte: 'linha_interessado',
//                        tipoParte: '1'
//                    }
//                );
//                    $("#selecionados_partes").show();
//                    $("#selecionados_vistas").hide();
//                    $("#dialog_cadastra_parte_doc").dialog('option', 'title','Cadastro de Partes');
//                    $("#dialog_cadastra_parte_doc").dialog('open');
//           });
//           
//           
//           $cadVistas.click( function (){
//                $.data(document.body,'config',
//                    {
//                        containerPartes: $("#vistas_adicionadas"),
//                        tabela: $("#selecionados_vistas tbody"),
//                        descParte: 'linha_interessado',
//                        tipoParte: '3'
//                    }
//                );
//                    $("#selecionados_vistas").show();
//                    $("#selecionados_partes").hide();
//                    $("#dialog_cadastra_parte_doc").dialog('option', 'title','Cadastro de Vistas');
//                    $("#dialog_cadastra_parte_doc").dialog('open');
//           });
//           
//            $("#dialog_cadastra_parte_doc").dialog({
//                        //title    : 'Cadastro de Partes/Interessados do Documento',
//                        autoOpen : false,
//                        modal    : false,
//                        show: 'fold',
//                        hide: 'fold',
//                        resizable: true,
//                        width: 800,
//                        height: 600,
//                        position: [300,50,0,0],
//                        buttons : {
//                                Ok: function() {
//                                        $(this).dialog("close");
//                                }
//                        },
//                        open: function (){
//                            //console.log($("#DOCM_ID_CONFIDENCIALIDADE").val());
//                            if( $("#DOCM_ID_CONFIDENCIALIDADE").val() == "4"){
//                 //               console.log($("#TIPO_PARTE").html());
//                                $("option[value=U]").hide();
//                                $("tr.partes_unidade").remove();
//                            }else{
//                                $("option[value=U]").show();
//                            }
//                        },
//                        close: function() {
//                            var config = $.data(document.body,'config');
//                                inputs = config.tabela.find("input[type='hidden']").clone(),
//                                $containerParte = config.containerPartes;
//                                $containerParte.html(inputs);
//                                
//                            $.data(document.body,'config','');
//                       }
//            });
//    });