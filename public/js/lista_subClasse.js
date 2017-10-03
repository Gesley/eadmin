
function gridSubClasse() {
    
    $('#add-link').each(function() {
        var $link = $(this);
        var $dialog = $('<div></div>')
                .load($link.attr('href'))
                .dialog({
                    autoOpen: false,
                    title: "Nova Subclasse",//$link.attr('title'),
                    width: 400,
                    height: 450,
                    modal: true,
                    buttons: {
                        "Cancelar": function() {
                            $(this).dialog("close");
                            $("#AQSC_DH_CRIACAO").removeClass('hasDatepicker');
                            $("#AQSC_DH_FIM").removeClass('hasDatepicker');
                        }
                    }
                });

        $link.click(function() {
            $dialog.dialog('open');
            return false;
        });
    });

    // then attach hide handler
    $(".editar").bind("click", function() {
        $(".value_" + $(this).attr("id")).hide();
        $(".trcampos_" + $(this).attr("id")).show(500);

    });

    $(".voltar").bind("click", function() {
        $(".trcampos_" + $(this).attr("id")).hide();
        //$(".trcampos_" + $(this).attr("id").removeClass('hasDatepicker'));
        $(".value_" + $(this).attr("id")).show(500);

    });
    $(":not(.voltar)").click(function() {

    });


    $("td").click(function() {
        $(".value_" + $(this).attr("id")).hide();
        $(".trcampos_" + $(this).attr("id")).show(500);
    });
    $("tr").mouseover(function() {
        this.title = 'Clique para editar o texto';
        this.style.margin = '400px;';
    });

    $(".btn_enviar").click(function() {

        var id = $(this).attr('id').split('_')[2];
        var AQSC_ID_SUBCLASSE = $(".trcampos_" + id + "  #AQSC_ID_SUBCLASSE").val();
        var AQSC_CD_SUBCLASSE = $(".trcampos_" + id + "  #AQSC_CD_SUBCLASSE").val();
        var AQSC_DS_SUBCLASSE = $(".trcampos_" + id + "  #AQSC_DS_SUBCLASSE").val();
        var AQSC_DH_CRIACAO = $(".trcampos_" + id + "    .AQSC_DH_CRIACAO").val();
        var AQSC_DH_FIM = $(".trcampos_" + id + "        .AQSC_DH_FIM").val();

        // Convertendo valor string para data
        var data_inicial = AQSC_DH_CRIACAO;
        var data_final   = AQSC_DH_FIM;
        var data_ini     = data_inicial.split('/');
        var data_fim     = data_final.split('/');
        // data inicial
        var dia_ini     = data_ini[0];
        var mes_ini     = data_ini[1];
        var ano_ini     = data_ini[2];
        
        // da final
        var dia_fim     = data_fim[0];
        var mes_fim     = data_fim[1];
        var ano_fim     = data_fim[2];
        
        if(ano_ini > ano_fim){
            alert("Data inicial não pode ser maior que a data final");
            return false;
        }
        // se a data inicial for maior que a final o sistema nao continua
        if(dia_ini > dia_fim && mes_ini >= mes_fim && ano_ini >= ano_fim){
            
            alert("Data inicial não pode ser maior que a data final");
            return false;
            
        }else{
                $.ajax({
                  type: 'GET',
                  data: 'AQSC_ID_SUBCLASSE=' + AQSC_ID_SUBCLASSE + '&AQSC_CD_SUBCLASSE=' + AQSC_CD_SUBCLASSE + '&AQSC_DS_SUBCLASSE=' + AQSC_DS_SUBCLASSE + '&AQSC_DH_CRIACAO=' + AQSC_DH_CRIACAO + '&AQSC_DH_FIM=' + AQSC_DH_FIM,
                  url: 'edit-sub-classe',
                  beforeSend: function() {
                      $(".carregandoAjax").val("");
                      $(".carregandoAjax").fadeIn(800);
                      $(".carregandoAjax").fadeOut(600);
                      $(".carregandoAjax").html();
                  },
                  success: function() {

                      buscarSubClasse();
                  }

              });
            }
    });

// Funções para inserir os dados via ajax

    //Mudando o status do botao ao passar o mouse 
           
   $("#addlink").hover(function() {

            $(this).css("cursor", "pointer");
        });
        
        //Exibindo o formulario de inserssão
        $("#addlink").click(function() {
            $("#inserir").show('slow');
            $(".AQSC_DS_SUBCLASSE ").focus();
            //$(".editar").hide();
            //$(".tr3").hide();
        });
        
        //Botao cancelar ao clicar esconde o formulario de inserssão
        $("#cancelar").click(function() {
            $("#inserir")           .hide(300);
            $(".tr3")               .show('slow');
            $(".editar")            .show('fast');
            $(".AQSC_DS_SUBCLASSE") .val('');
            $("#AQSC_DH_CRIACAO")   .val('');
            $("#AQSC_DH_FIM")       .val('');
        });

         //alert('deu'); return false;
            
 // Enviando os dados via ajax
 
 
    //Ao clicar os dados serao enviado para a contorller
    $("#enviar").click(function(){
        
                       
        var AQSC_ID_SUBCLASSE          = $(".AQSC_ID_SUBCLASSE").val();
        var AQSC_ID_AQCL               = $(".AQSC_ID_AQCL").val();
        var AQSC_CD_SUBCLASSE          = $(".AQSC_CD_SUBCLASSE").val();
        var AQSC_DS_SUBCLASSE          = $(".AQSC_DS_SUBCLASSE").val();
        var AQSC_DH_CRIACAO            = $("#AQSC_DH_CRIACAO").val();
        var AQSC_DH_FIM                = $("#AQSC_DH_FIM").val();
        URL;
        
        //Validando os campo para nao serem vazios
        if(AQSC_CD_SUBCLASSE == ''){
            
            alert("Campo código não pode ser vazio!");
            $(".AQSC_CD_SUBCLASSE ").focus();
             return false;
        }
        
      
          if(AQSC_DS_SUBCLASSE == ''){
            
            alert("Campo Assunto não pode ser vazio!");
            $(".AQSC_DS_SUBCLASSE  ").focus();
             return false;
        }
        
          if(AQSC_DH_CRIACAO == ''){
            
            alert("Campo Data início não pode ser vazio!");
            $("#AQSC_DH_CRIACAO  ").focus();
             return false;
        }
        
       //Recuperando os dados para envivar para controller add-pctt
        $.ajax({
                    url: base_url + '/arquivo/pctt/add-sub-class',
                    type: 'GET',
                    data: 'AQSC_ID_SUBCLASSE=' + AQSC_ID_SUBCLASSE + '&AQSC_ID_AQCL=' + AQSC_ID_AQCL + '&AQSC_CD_SUBCLASSE=' + AQSC_CD_SUBCLASSE + '&AQSC_DS_SUBCLASSE=' + AQSC_DS_SUBCLASSE + '&AQSC_DH_CRIACAO=' + AQSC_DH_CRIACAO + '&AQSC_DH_FIM=' + AQSC_DH_FIM,
                    beforeSend: function() {
                        $(".carregandoAjax").fadeIn(800);
                        $(".carregandoAjax").fadeOut(600);
                        $(".carregandoAjax").html('');
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
                            setTimeout(function(){buscarSubClasse()}, 1500);
                              
                      }
                      
                    }
                });
   
    });
    /*
   /* Fim da função ajax 
   */
   // Data inicio
    $(".AQSC_DH_CRIACAO,#AQSC_DH_CRIACAO").datepicker({
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
    $(".AQSC_DH_FIM, #AQSC_DH_FIM").datepicker({
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
    jQuery.validator.setDefaults({
        debug: true,
        success: "valid"
    });
    $(".AQCL_DH_CRIACAO, .AQCL_DH_FIM").validate({
        rules: {
            field: {
                required: true,
                date: true
            }
        }
    });

    $(".dialogo").dialog();
    }
    

// Funçao para validar campos somente com numeros
function Onlynumbers(e)
{
    var tecla = new Number();
    if (window.event) {
        tecla = e.keyCode;
    }
    else if (e.which) {
        tecla = e.which;
    }
    else {
        return true;
    }
    if ((tecla >= "97") && (tecla <= "122")) {
        alert("Somente números");
        return false;
    }
  
}
 // Carregando as subClasses
 function assuntoClasse(){
         var secundario = $("#AQAP_CD_ASSUNTO_PRINCIPAL").val();
         //Enviando o id via ajax
        $.ajax({
            url: base_url 
                    + '/arquivo/pctt/list-sub-secundario',
            dataType: "html",
            type: "GET",
            data: "AQAP_CD_ASSUNTO_PRINCIPAL=" + secundario,
            // Barra de carregamento
            beforeSend: function() {

                $(".AQAS_CD_ASSUNTO_SECUNDARIO").html("");
                $(".AQAS_CD_ASSUNTO_SECUNDARIO").val("");
                $(".carregandoAjax").fadeIn(800);
                $(".carregandoAjax").fadeOut(600);
                $(".carregandoAjax").html();
            },
            // Retornando o conteúdo para a view list-classe
            success: function(data) {

                if (data === '') {

                    //chama function do javascript mensagem.js

                    mostraFlashMessage(
                            "A seção selecionada não possui subseções", "notice"
                            );

                } else {

                    $("#Resultclasse").html(data);

                }
            },
            error: function(jqXHR, textStatus, errorThrown) {

               mostraFlashMessage("Ocorreu o seguinte erro no ajax da seção: " 
                       + textStatus, "error");

            }

        });
    }
    
     /*
     * Funçao para chamar os subclasse do menú subclasse
     */
     function buscarSubClasse(){
          var AQCL_ID_AQAS = $("#AQCL_ID_AQAS").val();
          var subClasse = $("#AQCL_CD_CLASSE").val();
          var AQAP_CD_ASSUNTO_PRINCIPAL = $("#AQAP_CD_ASSUNTO_PRINCIPAL").val();
          var AQAS_CD_ASSUNTO_SECUNDARIO = $("#AQAS_CD_ASSUNTO_SECUNDARIO").val();
         //Enviando o id via ajax
        $.ajax({
            url: base_url 
                    + '/arquivo/pctt/list-sub-menu-tabela',
            dataType: "html",
            type: "GET",
            data: "AQCL_ID_AQAS =" + AQCL_ID_AQAS + "&AQCL_CD_CLASSE=" + subClasse + "&AQAP_CD_ASSUNTO_PRINCIPAL=" + AQAP_CD_ASSUNTO_PRINCIPAL + "&AQAS_CD_ASSUNTO_SECUNDARIO=" + AQAS_CD_ASSUNTO_SECUNDARIO,
            // Barra de carregamento
            beforeSend: function() {

                 $(".Resultclasse2").html("");

                $(".AQCL_CD_CLASSE").val("");
                $(".carregandoAjax").fadeIn(800);
                $(".carregandoAjax").fadeOut(600);
                $(".carregandoAjax").html();
            },
            // Retornando o conteúdo para a view list-classe
            success: function(data) {
                    $("#gridClasse").html(data);
                }
       

        });
    }
    // Chamando a classe
    function Classe() {

    var classe = $('#AQAS_CD_ASSUNTO_SECUNDARIO').val();
    var AQAP_CD_ASSUNTO_PRINCIPAL = $('#AQAP_CD_ASSUNTO_PRINCIPAL').val();
    $.ajax({
        url: base_url + '/arquivo/pctt/list-sub-classe',
        dataType: "html",
        type: "GET",
        data: "AQAS_CD_ASSUNTO_SECUNDARIO=" + classe + "&AQAP_CD_ASSUNTO_PRINCIPAL=" +AQAP_CD_ASSUNTO_PRINCIPAL,
        beforeSend: function() {
            $(".secundario").html("");
            $("secundario").val("");
            $(".carregandoAjax").fadeIn(800);
            $(".carregandoAjax").fadeOut(600);
            $(".carregandoAjax").html();

        },
        success: function(data) {

            if (data === '') {

                //chama function do javascript mensagem.js

                mostraFlashMessage("A seção selecionada não possui subseções", "notice");

            } else {

                $("#Resultclasse3").html(data);

            }

        },
        complete: function() {

        },
    });

}