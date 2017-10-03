    
function gridPrincipal() {
    $('#add-link').each(function() {
        var $link = $(this);
        var $dialog = $('<div></div>')
                .load($link.attr('href'))
                .dialog({
                    autoOpen: false,
                    title: $link.attr('title'),
                    width: 400,
                    height: 450,
                    modal: true,
                    buttons: {
                        "Cancelar": function() {
                            $(this).dialog("close");
                            location.reload();
                        }
                    }
                });

        $link.click(function() {
            $dialog.dialog('open');
            return false;
        });
    });
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

    // then attach hide handler
    $(".editar").bind("click", function() {
        $(".value_" + $(this).attr("id")).hide();
        $(".trcampos_" + $(this).attr("id")).show(500);

    });

    $(".voltar").bind("click", function() {
        $(".trcampos_" + $(this).attr("id")).hide();
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
        var AQAP_CD_ASSUNTO_PRINCIPAL = $(".trcampos_" + id + "  #AQAP_CD_ASSUNTO_PRINCIPAL").val();
        var AQAP_DS_ASSUNTO_PRINCIPAL = $(".trcampos_" + id + "  #AQAP_DS_ASSUNTO_PRINCIPAL").val();
        var AQAP_DH_CRIACAO = $(".trcampos_" + id + " .AQAP_DH_CRIACAO").val();
        var AQAP_DH_FIM = $(".trcampos_" + id + "     .AQAP_DH_FIM").val();
        
          // Convertendo valor string para data
        var data_inicial = AQAP_DH_CRIACAO;
        var data_final   = AQAP_DH_FIM;
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
            data: 'AQAP_CD_ASSUNTO_PRINCIPAL=' + AQAP_CD_ASSUNTO_PRINCIPAL + '&AQAP_DS_ASSUNTO_PRINCIPAL=' + AQAP_DS_ASSUNTO_PRINCIPAL + '&AQAP_DH_CRIACAO=' + AQAP_DH_CRIACAO + '&AQAP_DH_FIM=' + AQAP_DH_FIM,
            url: 'edit-classe',
            beforeSend: function() {
                $(".carregandoAjax").fadeIn(800);
                $(".carregandoAjax").fadeOut(600);
                $(".carregandoAjax").html();
            },
            success: function() {

                chamaClasse();
            }

        });
    }
    });

    $(".AQAP_DH_CRIACAO,.AQAP_DH_FIM").datepicker({
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
    
    function assunto() {

    var assunto = $("#AQAP_CD_ASSUNTO_PRINCIPAL").val();

    alert(assunto);
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
 


 