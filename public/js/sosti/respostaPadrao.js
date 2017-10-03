/**
 * 
 * Arquivo JAVASCRIPT para a funcionalidade Resposta Padrão 
 * 
 */
$(document).ready(function(){
    
    // CONFIGURAÇÃO DA DIALOG DAS RESPOSTAS PADRÕES
    $("#dialog_escolha_resposta").dialog({
        title    : 'Resposta Padrão',
        autoOpen : false,
        modal    : false,
        show: 'fold',
        hide: 'fold',
        resizable: true,
        width: 800,
        position: [580,140,0,0],
        buttons : {
            Escolher: function() {
                textRespostaPadrao = $('input[name=resposta]:checked').val();
                $("#MOFA_DS_COMPLEMENTO").val(textRespostaPadrao);
                $("#SOPR_DS_DESCRICAO_PRAZO").val(textRespostaPadrao);
                $(this).dialog("close");
            }
        }
    });
        
    // ABRE A DIALOG PARA ESCOLHA DA RESPOSTA PADRÃO  
    $('#MOFA_DS_COMPLEMENTO-element').append("<br><input type='button' value='Resposta Padrão' id='repd' /><br>");
    $('#SOPR_DS_DESCRICAO_PRAZO-element').append("<br><input type='button' value='Resposta Padrão' id='repd' /><br>");
    $('#repd').click(function(){
        //$('#dialog_escolha_resposta').css('display','block');
        $("#dialog_escolha_resposta").dialog("open");
    });
        

    // FAZ A BUSCA DAS RESPOSTAS PADRÕES VIA AJAX    
    $('#Buscar').click(function(){
        var url =  base_url + '/sosti/respostapadrao/escolherespostapadrao';
        $.ajax({
            url: url,
            dataType: "html",
            type: "POST",
            data: $('#form_repd').serialize(),
            processData: false, 
            before: function(){
                $("#resultados_resposta").html("Carregando dados...");
            },
            success: function(data) {
                console.log(data);
                $("#resultados_resposta").html(data);
            },
            error : function(){
                $("#resultados_resposta").html("Erro durante a requisição.");
            }
        });
    }); 
    
    //TOOGLE DO FILTRO
    $("#filtro_repd").hide();
    $("#button_filtro").toggle(function(){
        $("#filtro_repd").show();
    }, function(){
        $("#filtro_repd").hide();
    });
})