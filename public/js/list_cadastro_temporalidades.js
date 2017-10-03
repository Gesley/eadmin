$(document).ready(function() {
    // Ao clicar no batao voltar da grid temporalidade, retorna para list tabela
   $("#cancelar_temporalidade").click(function(){
        $.ajax({
                type: 'POST',
                success: function() {

                    chamaTabelaAtividade();
                }

            });
   });
   
   // Ao clicar no botao voltar do cadastro de vias e temporalidade,
   //  retonra para list tabela
   $("#voltar_grid").click(function(){
      $.ajax({
          type: 'POST',
          success: function(){
              
               chamaTabelaAtividade();
          }
      }) ;
   });
    // Campo alterar da grid
      $(".editar").bind("click", function() {
        $(".value_" + $(this).attr("id")).hide();
        $(".trcampos_" + $(this).attr("id")).show(500);

    });
        // Escondendo tr de atualização
    $(".voltar").bind("click", function() {
        $(".trcampos_" + $(this).attr("id")).hide();
        $(".value_" + $(this).attr("id")).show(500);

    });
    $("td").click(function() {
        $(".value_" + $(this).attr("id")).hide();
        $(".trcampos_" + $(this).attr("id")).show(500);
    });
    
    //Campo grid novo documento
          $("#addTemporalidade").hover(function() {
            $(this).css("cursor", "pointer");
        });
        
        $("#addTemporalidade").click(function() {
            $("#inserir").show('slow');
            $(".AQAP_DS_ASSUNTO_PRINCIPAL ").focus();
            //$(".editar").hide();
            //$(".tr3").hide();
        });

        $("#cancelar").click(function() {
            $("#inserir").hide(300);
            $(".tr3").show('slow');
            $(".editar").show('fast');
            $(".AQAP_DS_ASSUNTO_PRINCIPAL").val('');
            $("#AQAP_DH_CRIACAO").val('');
            $("#AQAP_DH_FIM").val('');
        });
         // Fim Campo grid novo documento/////////////////////////// 
         
         
    $(".btn_enviar").click(function() {
        var id                = $(this).attr('id').split('_')[2];
        var AQAT_ID_ATIVIDADE = $(".trcampos_" + id + "  #AQAT_ID_ATIVIDADE").val();
        var AQAT_CD_ATIVIDADE = $(".trcampos_" + id + "  #AQAT_CD_ATIVIDADE").val();
        var AQAT_DS_ATIVIDADE = $(".trcampos_" + id + "  #AQAT_DS_ATIVIDADE").val();
        var AQAT_DH_CRIACAO   = $(".trcampos_" + id + "   .AQAT_DH_CRIACAO").val();
        var AQAT_DH_FIM       = $(".trcampos_" + id + "       .AQAT_DH_FIM").val();

        // Convertendo valor string para data
        var data_inicial = AQAT_DH_CRIACAO;
        var data_final = AQAT_DH_FIM;
        var data_ini = data_inicial.split('/');
        var data_fim = data_final.split('/');
        // data inicial
        var dia_ini = data_ini[0];
        var mes_ini = data_ini[1];
        var ano_ini = data_ini[2];

        // da final
        var dia_fim = data_fim[0];
        var mes_fim = data_fim[1];
        var ano_fim = data_fim[2];

        if (ano_ini > ano_fim) {
            alert("Data inicial não pode ser maior que a data final");
            return false;
        }
        // se a data inicial for maior que a final o sistema nao continua
        if (dia_ini > dia_fim && mes_ini >= mes_fim && ano_ini >= ano_fim) {

            alert("Data inicial não pode ser maior que a data final");
            return false;

        }
        else {
            $.ajax({
                type: 'GET',
                data: 'AQAT_ID_ATIVIDADE=' + AQAT_ID_ATIVIDADE + '&AQAT_CD_ATIVIDADE=' + AQAT_CD_ATIVIDADE + '&AQAT_DS_ATIVIDADE=' + AQAT_DS_ATIVIDADE + '\n\
                                &AQAT_DH_CRIACAO=' + AQAT_DH_CRIACAO + '&AQAT_DH_FIM=' + AQAT_DH_FIM,
                url: 'edit-avitidades/',
                beforeSend: function() {
                    $(".carregandoAjax").fadeIn(800);
                    $(".carregandoAjax").fadeOut(600);
                    $(".carregandoAjax").html();
                },
                success: function() {

                    chamaTabelaAtividade();
                }

            });
        }
    });
    
     // Trazendo o grid para adicionar um novo campo
    $("#enviar1").click(function() {
        
        $(".formGridClasse").hide();
        var id_cadastro                = $("#id_cadastro").val();
        var AQAP_CD_ASSUNTO_PRINCIPAL  = $(".AQAP_CD_ASSUNTO_PRINCIPAL").val();
        var AQAS_CD_ASSUNTO_SECUNDARIO = $(".AQAS_CD_ASSUNTO_SECUNDARIO").val();
        var AQCL_CD_CLASSE             = $(".AQCL_CD_CLASSE").val();
        var AQSC_CD_SUBCLASSE          = $(".AQSC_CD_SUBCLASSE").val();
        var AQAT_CD_ATIVIDADE          = $(".AQAT_CD_ATIVIDADE").val();
        $.ajax({
            type: 'GET',
            data: 'AQVP_ID_AQAT=' + id_cadastro + "&AQAP_CD_ASSUNTO_PRINCIPAL=" + AQAP_CD_ASSUNTO_PRINCIPAL + "&AQAS_CD_ASSUNTO_SECUNDARIO=" + AQAS_CD_ASSUNTO_SECUNDARIO + "&AQCL_CD_CLASSE=" + AQCL_CD_CLASSE + "&AQSC_CD_SUBCLASSE=" + AQSC_CD_SUBCLASSE + "&AQAT_CD_ATIVIDADE=" + AQAT_CD_ATIVIDADE,
            url: base_url + '/arquivo/pctt/add-atividades-vias',
            beforeSend: function() {
                $(".carregandoAjax").fadeIn(800);
                $(".carregandoAjax").fadeOut(600);
                $(".carregandoAjax").html();
            },
            success: function(data) {
                $("#list_cadastro").html(data);
            }

        });
    });
   // Verificando campos intupts vazios e salvando 
    $("#btn_tmp").click(function(){
       
        if($(".AQVI_CD").val() == ''){
            
            alert("Campo via não pode ser vazio");
            $(".AQVI_CD").focus();
            return false;
        }
        if($(".DESTINO").val() == ''){
            
            alert("Campo destino não pode ser vazio");
            $(".CODIGO_DESTINO ").focus();
            return false;
        }
        
          if($(".CORRENTE").val() == ''){
            
            alert("Campo corrente não pode ser vazio");
            $(".CODIGO_CORRENTE ").focus();
            return false;
        }
          if($(".INTERMEDIARIO").val() == ''){
            
            alert("Campo intermediário não pode ser vazio");
            $(".COGIGO_INTERMEDIARIO").focus();
            return false;
        }
          if($(".DESTINO_FINAL").val() == ''){
            
            alert("Campo detino final não pode ser vazio");
            $(".COGIGO_FINAL ").focus();
            return false;
        }
          if($("#AQVP_DH_CRIACAO").val() == ''){
            
            alert("Campo data início não pode ser vazio");
            $("#AQVP_DH_CRIACAO").focus();
            return false;
        }
        
        // Pegando os valores do select     
        var AQVP_ID_PCTT       = $(".AQVP_ID_PCTT").val();
        var AQVP_CD_PCTT       = $(".AQVP_CD_PCTT").val();
        var AQVP_ID_AQAT       = $(".AQVP_ID_AQAT").val();
        var AQVP_CD_AQVI       = $(".AQVP_CD_AQVI").val();
        var AQVP_CD_AQDE_INI   = $(".AQVP_CD_AQDE_INI").val();
        var AQVP_CD_AQTE_COR   = $(".AQVP_CD_AQTE_COR").val();
        var AQVP_CD_AQTE_INT   = $(".AQVP_CD_AQTE_INT").val();
        var AQVP_CD_AQDE_FIM   = $(".AQVP_CD_AQDE_FIM").val();
        var AQVP_DH_CRIACAO    = $("#AQVP_DH_CRIACAO").val();
        var AQVP_DH_FIM        = $("#AQVP_DH_FIM").val();
        var AQVP_DS_OBSERVACAO = $(".AQVP_DS_OBSERVACAO").val();
        
        //Enviando para controller Pctt saveAction
        $.ajax({
            type: 'get',
            data: 'AQVP_ID_PCTT='+ AQVP_ID_PCTT +'&AQVP_ID_AQAT=' +AQVP_ID_AQAT + '&AQVP_CD_PCTT='+ AQVP_CD_PCTT +'&AQVP_CD_AQVI=' + AQVP_CD_AQVI + '&AQVP_CD_AQDE_INI=' + AQVP_CD_AQDE_INI + '&AQVP_CD_AQTE_COR=' + AQVP_CD_AQTE_COR + '&AQVP_CD_AQDE_FIM=' + AQVP_CD_AQDE_FIM + '&AQVP_DH_CRIACAO=' + AQVP_DH_CRIACAO + '&AQVP_CD_AQTE_COR=' + AQVP_CD_AQTE_COR + '&AQVP_CD_AQTE_INT=' + AQVP_CD_AQTE_INT + '&AQVP_CD_AQDE_FIM=' + AQVP_CD_AQDE_FIM + '&AQVP_DH_CRIACAO=' + AQVP_DH_CRIACAO + '&AQVP_DH_FIM=' + AQVP_DH_FIM + '&AQVP_DS_OBSERVACAO=' + AQVP_DS_OBSERVACAO,
            url : 'save-atividade',
                 beforeSend: function() {
                $(".carregandoAjax").fadeIn(800);
                $(".carregandoAjax").fadeOut(600);
                $(".carregandoAjax").html();
            },
            success: function(data) {
                      if(data == "Ação já cadastrada!"){
                            $(".notice").fadeIn(1000);
                            $(".notice").fadeOut(1700);
                            //$('.notice').html(data);
                            return false;
                          
                      }if(data != "Ação já cadastrada!") {
                            $(".success").show();
                            //$('.success').html(data);
                            setTimeout(function(){chamaTabelaAtividade()}, 1500);
                              
                      }
                    }
            
        });
    });
    
    $(".novo").button({
        icons: {
            primary: "ui-icon-pencil"
        }
    }).attr('style', 'width: 60px; height: 23px;');
    $(".excluir").button({
        icons: {
            primary: "ui-icon-trash"
        }
    }).attr('style', 'width: 40px; height: 16px;');

    // Chamando o Grid  
    $(".atividade_vias").click(function() {
        var AQAT_CD_ATIVIDADE           = this.id.substring(4);
        var ID_ATIVIDADE                = $("#id_atividade_" + AQAT_CD_ATIVIDADE).val();
        var AQAT_DS_ATIVIDADE           = $("#AQAT_DS_ATIVIDADE").val();
        var AQAP_CD_ASSUNTO_PRINCIPAL   = $(".AQAP_CD_ASSUNTO_PRINCIPAL").val();
        var AQAS_CD_ASSUNTO_SECUNDARIO  = $(".AQAS_CD_ASSUNTO_SECUNDARIO").val();
        var AQCL_CD_CLASSE              = $(".AQCL_CD_CLASSE").val();
        var AQSC_CD_SUBCLASSE           = $(".AQSC_CD_SUBCLASSE").val();
        var id                          = $(this).attr('id').substring(4);
        $(".formGridClasse").hide();

        $.ajax({
            type: 'GET',
            data: 'AQAT_DS_ATIVIDADE=' + AQAT_DS_ATIVIDADE + '&ID_ATIVIDADE=' +ID_ATIVIDADE+ '&AQAT_ID_ATIVIDADE=' + id + "&AQAP_CD_ASSUNTO_PRINCIPAL=" + AQAP_CD_ASSUNTO_PRINCIPAL + "&AQAS_CD_ASSUNTO_SECUNDARIO=" + AQAS_CD_ASSUNTO_SECUNDARIO + "&AQCL_CD_CLASSE=" + AQCL_CD_CLASSE + "&AQSC_CD_SUBCLASSE=" + AQSC_CD_SUBCLASSE + "&AQAT_CD_ATIVIDADE=" + AQAT_CD_ATIVIDADE,
            url: base_url + '/arquivo/pctt/list-cadastro-atividades-vias',
            beforeSend: function() {
                $(".carregandoAjax").fadeIn(800);
                $(".carregandoAjax").fadeOut(600);
                $(".carregandoAjax").html();
            },
            success: function(data) {
                $("#list_cadastro").show();
                $("#list_cadastro").html(data);
            }

        });
    });

    // Data inicio
    $(".AQVP_DH_CRIACAO, #AQVP_DH_CRIACAO").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
            'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior',
        changeMonth: true,
        numberOfMonths: 1,
        changeYear: true,
        maxDate: new Date(),
        rules: {
            field: {
                required: true,
                date: true
            }
        }

    });

    // Data fim
    $(".AQVP_DH_FIM, #AQVP_DH_FIM").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
            'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior',
        changeMonth: true,
        numberOfMonths: 1,
        changeYear: true,
        minDate: new Date(),
        rules: {
            field: {
                required: true,
                date: true
            }
        }

    });
    
});
$(function() {
    $(".editar").button({
        icons: {
            primary: "ui-icon-pencil"
        }
    }).attr('style', 'width: 40px; height: 16px;');
    $(".excluir").button({
        icons: {
            primary: "ui-icon-trash"
        }
    }).attr('style', 'width: 40px; height: 16px;');

    $('.AQVI_CD_VIA').change(function() {

        var AQVP_CD_PCTT = $(".AQVP_CD_PCTT").val();
        var $area1       = $('.AQVI_CD_VIA option:selected').text().split('_');
        var codigoGeral  = AQVP_CD_PCTT;
        var campocodigo  = codigoGeral.split('-')[0];
        $(".AQVP_CD_PCTT").val(campocodigo + '-' + $area1);

    });


});
function buscarVias() {

    var id = $(".AQVI_CD_VIA").val();
    $(".AQVI_CD").val(id);

}
function buscarDest() {

    var id = $(".CODIGO_DESTINO").val();

    $.ajax({
        type: 'GET',
        data: 'CODIGO_DESTINO=' + id,
        url: base_url + '/arquivo/pctt/mostrar-destino',
        success: function(data) {
            
            if( id == 'Vazio'){
                
                $(".DESTINO").val('');
            }
            else{
            $(".DESTINO").val(data);
        }
        }

    });
}
function buscarCorrente() {

    var id = $(".CODIGO_CORRENTE").val();
    $.ajax({
        type: 'GET',
        data: 'CODIGO_DESTINO=' + id,
        url: base_url + '/arquivo/pctt/mostrar-corrente',
        success: function(data) {
                if( id == 'vazio'){
                
                $(".CORRENTE").val('');
            }
            else{
            $(".CORRENTE").val(data);
        }
        }

    });

}
function buscarInter() {

    var id = $(".COGIGO_INTERMEDIARIO").val();

    $.ajax({
        type: 'GET',
        data: 'CODIGO_DESTINO=' + id,
        url: base_url + '/arquivo/pctt/mostrar-intermediario',
        success: function(data) {
           if( id == 'vazio'){
                $(".INTERMEDIARIO").val('');
            }
            else{
                $(".INTERMEDIARIO").val(data);
        }
        }
            
            
    });

}
function buscarDestFinal() {

    var id = $(".COGIGO_FINAL").val();

    $.ajax({
        type: 'GET',
        data: 'CODIGO_DESTINO=' + id,
        url: base_url + '/arquivo/pctt/mostrar-destino',
        success: function(data) {
           if( id == 'Vazio'){
                $(".DESTINO_FINAL").val('');
            }
            else{
            $(".DESTINO_FINAL").val(data);
        }
        }

    });

}



