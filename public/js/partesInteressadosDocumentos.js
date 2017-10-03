$(function() {
        
         $(".cadPartes, .cadVistas, .cadPartesVistasEncaminhar").button();
         var $cadPartes = $(".cadPartes");
         var $cadVistas = $(".cadVistas");
         var $cadPartesVistasEncaminhar = $(".cadPartesVistasEncaminhar");
            
         $cadPartes.click( function (){
                $.data(document.body,'config',
                    {
                        containerPartes: $("#partes_adicionadas"),
                        tabela: $("#selecionados_partes tbody"),
                        descParte: 'linha_interessado',
                        tipoParte: '1'
                    }
                );
                    $("#selecionados_partes").show();
                    $("#selecionados_vistas").hide();
                    $("#dialog_cadastra_parte_doc").dialog('option', 'title','Cadastro de Partes');
                    $("#dialog_cadastra_parte_doc").dialog('open');
           });
           
           
           $cadVistas.click( function (){
                $.data(document.body,'config',
                    {
                        containerPartes: $("#vistas_adicionadas"),
                        tabela: $("#selecionados_vistas tbody"),
                        descParte: 'linha_interessado',
                        tipoParte: '3'
                    }
                );
                    $("#selecionados_vistas").show();
                    $("#selecionados_partes").hide();
                    $("#dialog_cadastra_parte_doc").dialog('option', 'title','Cadastro de Vistas');
                    $("#dialog_cadastra_parte_doc").dialog('open');
           });
           
           $cadPartesVistasEncaminhar.click( function (){
                $.data(document.body,'config',
                    {
                        containerPartes: $("#partes_vistas_encaminhar_adicionadas"),
                        tabela: $("#selecionados_partes_vistas_encam tbody"),
                        descParte: 'linha_interessado',
                        tipoParte: '3'
                    }
                );
                    $("#selecionados_partes_vistas_encam").show();
                    $("#dialog_cadastra_parte_vista_encaminha").dialog('option', 'title','Cadastro de Partes/Vistas/Encaminhar');
                    $("#dialog_cadastra_parte_vista_encaminha").dialog('open');
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
                            var conf = $("#DOCM_ID_CONFIDENCIALIDADE").val();
                            if (/^(3|4)$/.test(conf)){  
                                $("option[value=U]").hide();
                                $("tr.partes_unidade").remove();
                            }else{
                                $("option[value=U]").show();
                            }
                        },
                        close: function() {
                            var config = $.data(document.body,'config'),
                                inputs = config.tabela.find("input[type='hidden']").clone(),
                                checkbox = $("#dialog_cadastra_parte").find("input[type='checkbox']").clone();
                                //console.log(checkbox);
                                $containerParte = config.containerPartes;
                                $containerParte.html(inputs);
                                $containerParte.append(checkbox);
                                
                            $.data(document.body,'config','');
                       }
            });
            
            
            $("#dialog_cadastra_parte_vista_encaminha").dialog({
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
                            var conf = $("#DOCM_ID_CONFIDENCIALIDADE").val();
                            if (/^(3|4)$/.test(conf)){  
                                $("option[value=U]").hide();
                                $("tr.partes_unidade").remove();
                            }else{
                                $("option[value=U]").show();
                            }
                        },
                        close: function() {
                            var config = $.data(document.body,'config'),
                                checkbox = $("#dialog_cadastra_parte_vista_encaminha").find("input[type='checkbox']:checked").clone();
                                encaminhar = $("#dialog_cadastra_parte_vista_encaminha").find("input[type='radio']:checked").clone();
                                $containerParte = config.containerPartes;
                                $containerParte.html(encaminhar);
                                $containerParte.append(checkbox);
                                
                            $.data(document.body,'config','');
                       }
    });
            
    });