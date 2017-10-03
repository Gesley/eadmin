
function grid() {

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
                            $("#AQAS_DH_CRIACAO").removeClass('hasDatepicker');
                            $("#AQAS_DH_FIM").removeClass('hasDatepicker');
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

    $(".novo").button({
        icons: {
            primary: "ui-icon-document"
        }
    });
    $(".enviar").button().attr('style', 'width: 65px; height: 25px;');
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
        //var AQAS_ID_ASSUNTO_SECUNDARIO = $(".trcampos_" + id + "  #AQAS_ID_ASSUNTO_SECUNDARIO").val();
        var AQAS_ID_ASSUNTO_SECUNDARIO = $(".trcampos_" + id + "  #AQAS_ID_ASSUNTO_SECUNDARIO").val();
        var AQAS_CD_ASSUNTO_SECUNDARIO = $(".trcampos_" + id + "  #AQAS_CD_ASSUNTO_SECUNDARIO").val();
        var AQAS_DS_ASSUNTO_SECUNDARIO = $(".trcampos_" + id + "  #AQAS_DS_ASSUNTO_SECUNDARIO").val();
        var AQAS_DH_CRIACAO = $(".trcampos_" + id + "  .AQAS_DH_CRIACAO").val();
        var AQAS_DH_FIM = $(".trcampos_" + id + "  .AQAS_DH_FIM").val();

        // Convertendo valor string para data
        var data_inicial = AQAS_DH_CRIACAO;
        var data_final = AQAS_DH_FIM;
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

        } else {

            $.ajax({
                type: 'GET',
                data: 'AQAS_ID_ASSUNTO_SECUNDARIO=' + AQAS_ID_ASSUNTO_SECUNDARIO + '&AQAS_CD_ASSUNTO_SECUNDARIO=' + AQAS_CD_ASSUNTO_SECUNDARIO + '&AQAS_DS_ASSUNTO_SECUNDARIO=' + AQAS_DS_ASSUNTO_SECUNDARIO + '\n\
                                 &AQAS_DH_CRIACAO=' + AQAS_DH_CRIACAO + '&AQAS_DH_FIM=' + AQAS_DH_FIM,
                url: 'edit-assunto-secundario/',
                beforeSend: function() {
                    $(".carregandoAjax").fadeIn(800);
                    $(".carregandoAjax").fadeOut(600);
                    $(".carregandoAjax").html();
                },
                success: function() {

                    buscar_secundario()();
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
        $(".AQAS_DS_ASSUNTO_SECUNDARIO ").focus();
        //$(".editar").hide();
        //$(".tr3").hide();
    });

    //Botao cancelar ao clicar esconde o formulario de inserssão
    $("#cancelar").click(function() {
        $("#inserir").hide(300);
        $(".tr3").show('slow');
        $(".editar").show('fast');
        $(".AQAS_DS_ASSUNTO_SECUNDARIO").val('');
        $("#AQAS_DH_CRIACAO").val('');
        $("#AQAS_DH_FIM").val('');
    });


    // Enviando os dados via ajax


    //Ao clicar os dados serao enviado para a contorller
    $("#enviar").click(function() {


        var AQAS_ID_ASSUNTO_SECUNDARIO = $(".AQAS_ID_ASSUNTO_SECUNDARIO").val();
        var AQAS_CD_ASSUNTO_PRINCIPAL = $(".AQAS_CD_ASSUNTO_PRINCIPAL").val();
        var AQAS_CD_ASSUNTO_SECUNDARIO = $(".AQAS_CD_ASSUNTO_SECUNDARIO").val();
        var AQAS_DS_ASSUNTO_SECUNDARIO = $(".AQAS_DS_ASSUNTO_SECUNDARIO").val();
        var AQAS_DH_CRIACAO = $("#AQAS_DH_CRIACAO").val();
        var AQAS_DH_FIM = $("#AQAS_DH_FIM").val();
        URL;

        //Validando os campo para nao serem vazios
        if (AQAS_CD_ASSUNTO_SECUNDARIO == '') {

            alert("Campo código não pode ser vazio!");
            $(".AQAS_CD_ASSUNTO_SECUNDARIO ").focus();
            return false;
        }


        if (AQAS_DS_ASSUNTO_SECUNDARIO == '') {

            alert("Campo Assunto não pode ser vazio!");
            $(".AQAS_DS_ASSUNTO_SECUNDARIO  ").focus();
            return false;
        }

        if (AQAS_DH_CRIACAO == '') {

            alert("Campo Data início não pode ser vazio!");
            $("#AQAS_DH_CRIACAO  ").focus();
            return false;
        }

        //Recuperando os dados para envivar para controller add-pctt
        $.ajax({
            url: base_url + '/arquivo/pctt/add-assunto-secundario',
            type: 'GET',
            data: 'AQAS_ID_ASSUNTO_SECUNDARIO=' + AQAS_ID_ASSUNTO_SECUNDARIO + '&AQAS_CD_ASSUNTO_PRINCIPAL=' + AQAS_CD_ASSUNTO_PRINCIPAL + '&AQAS_CD_ASSUNTO_SECUNDARIO=' + AQAS_CD_ASSUNTO_SECUNDARIO + '&AQAS_DS_ASSUNTO_SECUNDARIO=' + AQAS_DS_ASSUNTO_SECUNDARIO + '&AQAS_DH_CRIACAO=' + AQAS_DH_CRIACAO + '&AQAS_DH_FIM=' + AQAS_DH_FIM,
            beforeSend: function() {
                $(".carregandoAjax").fadeIn(800);
                $(".carregandoAjax").fadeOut(600);
                $(".carregandoAjax").html('');
            },
            success: function(data) {

                if (data == "Ação já cadastrada!") {
                    $(".notice").fadeIn(1000);
                    $(".notice").fadeOut(1700);
                    //$('.notice').html(data);
                    return false;

                }
                if (data != "Ação já cadastrada!") {
                    $(".success").show();
                    //$('.success').html(data);
                    setTimeout(function() {
                        buscar_secundario()
                    }, 1500);

                }

            }
        });

    });
    /*
     /* Fim da função ajax 
     */

// DatePicker
// Data inicial
    $(".AQAS_DH_CRIACAO,#AQAS_DH_CRIACAO").datepicker({
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

    // Data final

    $(".AQAS_DH_FIM, #AQAS_DH_FIM ").datepicker({
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
    $(".AQAS_DH_CRIACAO, .AQAS_DH_FIM").validate({
        rules: {
            field: {
                required: true,
                date: true
            }
        }
    });

    $(".dialogo").dialog();
}
;
function buscar_secundario() {
    var secundario = $(".AQAP_CD_ASSUNTO_PRINCIPAL option:selected").val();
    //Enviando o id via ajax
    $.ajax({
        url: base_url + '/arquivo/pctt/grid',
        dataType: "html",
        type: "get",
        data: "AQAS_CD_ASSUNTO_SECUNDARIO=" + secundario,
        // Barra de carregamento
        beforeSend: function() {

            //$("#gridClasse").html("");
            //$("#AQSC_CD_SUBCLASSE option:selected").val("");
            $(".carregandoAjax").fadeIn(800);
            $(".carregandoAjax").fadeOut(600);
            //$(".evento").html("");
        },
        // Retornando o conteúdo para a view list-classe
        success: function(data) {

            if (data === '') {

                //chama function do javascript mensagem.js

                mostraFlashMessage(
                        "A seção selecionada não possui subseções", "notice"
                        );

            } else {

                $(".evento").html(data);

            }
        },
        error: function(jqXHR, textStatus, errorThrown) {

            mostraFlashMessage("Ocorreu o seguinte erro no ajax da seção: "
                    + textStatus, "error");

        }

    });
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

