/*@ danilo
 * Função que carrega o campo select do assunto secundário
 */
function assuntoSecundario() {
    var secundario = $("#AQAP_CD_ASSUNTO_PRINCIPAL option:selected").val();
    //Enviando o id via ajax
    $.ajax({
        url: base_url
                + '/arquivo/pctt/grid-classe',
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

                $("#texte").html(data);

            }
        },
        error: function(jqXHR, textStatus, errorThrown) {

            mostraFlashMessage("Ocorreu o seguinte erro no ajax da seção: "
                    + textStatus, "error");

        }

    });
}
/*
 * Função para carregar os conteúdo da tabela classe de acordo com a opção
 * selecionada do assunto secundário
 */
function chamaClasse() {
    var AQCL_ID_AQAS = $("#AQCL_ID_AQAS").val();
    var classe = $("#AQAS_CD_ASSUNTO_SECUNDARIO option:selected").val();
    //Enviando o id via ajax
    $.ajax({
        url: base_url + '/arquivo/pctt/grid-list-classe-tabela',
        dataType: "html",
        type: "GET",
        data: "AQCL_ID_AQAS=" + AQCL_ID_AQAS + "&AQAS_CD_ASSUNTO_SECUNDARIO=" + classe,
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

                $("#gridClasse").html(data);

            }
        },
        error: function(jqXHR, textStatus, errorThrown) {

            mostraFlashMessage("Ocorreu o seguinte erro no ajax da seção: "
                    + textStatus, "error");

        }

    });
}
/*
 * Funçao para chamar as Classes do menú subclasse
 */



function gridGenerica() {

    $('#add-link').each(function() {
        var $link = $(this);
        var $dialog = $('<div></div>')
                .load($link.attr('href'))
                .dialog({
                    autoOpen: false,
                    title: "Nova Classe",
                    width: 400,
                    height: 450,
                    modal: true,
                    buttons: {
                        "Cancelar": function() {
                            $(this).dialog("close");
                            $("#AQCL_DH_CRIACAO").removeClass('hasDatepicker');
                            $("#AQAS_DH_FIM ").removeClass('hasDatepicker');
                        }
                    }
                });

        $link.click(function() {
            $dialog.dialog('open');
            return false;
        });
    });
    
       $( ".novo" ).button({
        icons: {
            primary: "ui-icon-document"
        }
    });
         $( ".enviar" ).button().attr('style','width: 65px; height: 25px;');
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
        // $(".trcampos_" + $(this).attr("id").removeClass('hasDatepicker'));
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
        var AQCL_ID_CLASSE = $(".trcampos_" + id + " #AQCL_ID_CLASSE").val();
        var AQCL_CD_CLASSE = $(".trcampos_" + id + " #AQCL_CD_CLASSE").val();
        var AQCL_DS_CLASSE = $(".trcampos_" + id + " #AQCL_DS_CLASSE").val();
        var AQCL_DH_CRIACAO = $(".trcampos_" + id + " .AQCL_DH_CRIACAO").val();
        var AQCL_DH_FIM = $(".trcampos_" + id + " .AQCL_DH_FIM").val();

        // Convertendo valor string para data
        var data_inicial = AQCL_DH_CRIACAO;
        var data_final = AQCL_DH_FIM;

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
                data: 'AQCL_ID_CLASSE=' + AQCL_ID_CLASSE + '&AQCL_CD_CLASSE=' + AQCL_CD_CLASSE + '&AQCL_DS_CLASSE=' + AQCL_DS_CLASSE + '&AQCL_DH_CRIACAO=' + AQCL_DH_CRIACAO + '&AQCL_DH_FIM=' + AQCL_DH_FIM,
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


    // Funções para inserir os dados via ajax

    //Mudando o status do botao ao passar o mouse 

    $("#addlink").hover(function() {

        $(this).css("cursor", "pointer");
    });

    //Exibindo o formulario de inserssão
    $("#addlink").click(function() {
        $("#inserir").show('slow');
        $(".AQCL_DS_CLASSE ").focus();
        //$(".editar").hide();
        //$(".tr3").hide();
    });

    //Botao cancelar ao clicar esconde o formulario de inserssão
    $("#cancelar").click(function() {
        $("#inserir").hide(300);
        $(".tr3").show('slow');
        $(".editar").show('fast');
        $(".AQCL_DS_CLASSE").val('');
        $("#AQCL_DH_CRIACAO").val('');
        $("#AQCL_DH_FIM").val('');
    });

    //alert('deu'); return false;

    // Enviando os dados via ajax


    //Ao clicar os dados serao enviado para a contorller
    $("#enviar").click(function() {


        var AQCL_ID_CLASSE = $(".AQCL_ID_CLASSE").val();
        var AQCL_ID_AQAS = $(".AQCL_ID_AQAS").val();
        var AQCL_CD_CLASSE = $(".AQCL_CD_CLASSE").val();
        var AQCL_DS_CLASSE = $(".AQCL_DS_CLASSE").val();
        var AQCL_DH_CRIACAO = $("#AQCL_DH_CRIACAO").val();
        var AQCL_DH_FIM = $("#AQCL_DH_FIM").val();
        URL;

        //Validando os campo para nao serem vazios
        if (AQCL_CD_CLASSE == '') {

            alert("Campo código não pode ser vazio!");
            $(".AQCL_CD_CLASSE ").focus();
            return false;
        }


        if (AQCL_DS_CLASSE == '') {

            alert("Campo Assunto não pode ser vazio!");
            $(".AQCL_DS_CLASSE  ").focus();
            return false;
        }

        if (AQCL_DH_CRIACAO == '') {

            alert("Campo Data início não pode ser vazio!");
            $("#AQCL_DH_CRIACAO  ").focus();
            return false;
        }

        //Recuperando os dados para envivar para controller add-pctt
        $.ajax({
            url: base_url + '/arquivo/pctt/add-classe',
            type: 'GET',
            data: 'AQCL_ID_AQAS=' + AQCL_ID_AQAS + '&AQCL_CD_CLASSE=' + AQCL_CD_CLASSE + '&AQCL_DS_CLASSE=' + AQCL_DS_CLASSE + '&AQCL_DH_CRIACAO=' + AQCL_DH_CRIACAO + '&AQCL_DH_FIM=' + AQCL_DH_FIM + '&AQCL_ID_CLASSE=' + AQCL_ID_CLASSE,
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
                        chamaClasse()
                    }, 1500);

                }
            }
        });

    });
    /*
     /* Fim da função ajax 
     */
    // Data inicial
    $(".AQCL_DH_CRIACAO,#AQCL_DH_CRIACAO").datepicker({
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
    $(".AQCL_DH_FIM,#AQCL_DH_FIM").datepicker({
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



 