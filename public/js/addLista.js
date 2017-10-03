/* Proprietarios de Grupos de Divulgação */


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
    $(".idGrupo").show();/*alterado para mostrar primeiro os Grupos de Divulgação*/
        
    $("#ID_LISTA").change( function (){
        $(".li_parte").hide();
        var $value = $(this).val(),
        classes = {
            'C': ".idComp",
            'G': ".idGrupo"
        };
        $( classes[$value] ).show();
    });
             
               
    $("#LIST_ID_COMPONENTE").autocomplete({
        source: base_url+'/sisad/aviso/ajaxgruposdedivulgacao',
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
                tr += "<input type='hidden' value='"+ui.item.value+"-"+ui.item.id+"' name='list_id_componente[]' />";
                tr += "</tr>",
                config.tabela.append(tr);
                $(this).val("");
                return false;
            }
        }
    });
            
                     
    $("#LIST_ID_GRUPO_DIVULGACAO").autocomplete({
        source: base_url+'/sisad/aviso/ajaxgruposdedivulgacao',
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
            tr += "<input type='hidden' value='"+ui.item.value+"' name='list_id_grupo_divulgacao[]' />";
            tr += "</tr>";
                     
            config.tabela.append(tr);
            $(this).val("");
            return false;
        }
    });
            
    $(function() {
        $('#LIST_DT_INICIO_DIVULGACAO').datetimepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
            dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
            dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
            monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro',
            'Outubro','Novembro','Dezembro'],
            monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            changeMonth: true,
            numberOfMonths: 1,
            changeMonth: true,
            changeYear: true,
            changeMonth: true,
            showSecond: true,
            timeFormat: 'hh:mm:ss',
            timeOnlyTitle: 'Escolha o intervalo de tempo',
            timeText: 'Tempo',
            hourText: 'Hora',
            minuteText: 'Minutos',
            secondText: 'Segundos',
            currentText: 'Agora',
            closeText: 'OK',
            onClose: function(dateText, inst) {
                var endDateTextBox = $('#DATA_FINAL');
                if (endDateTextBox.val() != '') {
                    var testStartDate = new Date(dateText);
                    var testEndDate = new Date(endDateTextBox.val());
                    if (testStartDate > testEndDate)
                        endDateTextBox.val((dateText)?(dateText.substring(0,11) + '23:59:59'):(''));
                }
                else {
                    endDateTextBox.val((dateText)?(dateText.substring(0,11) + '23:59:59'):(''));
                }
            },
            onSelect: function (selectedDateTime){
                var start = $(this).datetimepicker('getDate');
                $('#DATA_FINAL').datetimepicker('option', 'minDate', new Date(start.getTime()));
            }
        });
        $('#LIST_DT_FIM_DIVULGACAO').datetimepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
            dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
            dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
            monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro',
            'Outubro','Novembro','Dezembro'],
            monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            changeMonth: true,
            numberOfMonths: 1,
            changeMonth: true,
            changeYear: true,
            changeMonth: true,
            showSecond: true,
            timeFormat: 'hh:mm:ss',
            timeOnlyTitle: 'Escolha o intervalo de tempo',
            timeText: 'Tempo',
            hourText: 'Hora',
            minuteText: 'Minutos',
            secondText: 'Segundos',
            currentText: 'Agora',
            closeText: 'OK'
        });

    });
            
}); 
     