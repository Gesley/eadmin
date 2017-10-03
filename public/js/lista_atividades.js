
function atividades() {

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

     // Data inicio
    $(".AQAT_DH_CRIACAO, #AQAT_DH_CRIACAO").datepicker({
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
    $(".AQAT_DH_FIM, #AQAT_DH_FIM").datepicker({
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
                            $("#AQAT_DH_CRIACAO").removeClass('hasDatepicker');
                            $("#AQAT_DH_FIM").removeClass('hasDatepicker');
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
        $(".value_" + $(this).attr("id")).show(500);

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
        var AQAT_ID_ATIVIDADE = $(".trcampos_" + id + "  #AQAT_ID_ATIVIDADE").val();
        var AQAT_CD_ATIVIDADE = $(".trcampos_" + id + "  #AQAT_CD_ATIVIDADE").val();
        var AQAT_DS_ATIVIDADE = $(".trcampos_" + id + "  #AQAT_DS_ATIVIDADE").val();
        var AQAT_DH_CRIACAO = $(".trcampos_" + id + "   .AQAT_DH_CRIACAO").val();
        var AQAT_DH_FIM = $(".trcampos_" + id + "       .AQAT_DH_FIM").val();

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

// Funções para inserir os dados via ajax

    //Mudando o status do botao ao passar o mouse 
    $("#addlink").hover(function() {

        $(this).css("cursor", "pointer");
    });

    //Exibindo o formulario de inserssão
    $("#addlink").click(function() {
        $("#inserir").show('slow');
        $(".AQAT_DS_ATIVIDADE ").focus();
        //$(".editar").hide();
        //$(".tr3").hide();
    });

    //Botao cancelar ao clicar esconde o formulario de inserssão
    $("#cancelar").click(function() {
        $("#inserir").hide(300);
        $(".tr3").show('slow');
        $(".editar").show('fast');
        $(".AQAT_DS_ATIVIDADE").val('');
        $("#AQAT_DH_CRIACAO").val('');
        $("#AQAT_DH_FIM").val('');
    });


    // Enviando os dados via ajax


    //Ao clicar os dados serao enviado para a contorller
    $("#enviar").click(function() {

        var AQAT_ID_ATIVIDADE = $(".AQAT_ID_ATIVIDADE").val();
        var AQAT_ID_AQSC      = $(".AQAT_ID_AQSC").val();
        var AQAT_CD_ATIVIDADE = $(".AQAT_CD_ATIVIDADE").val();
        var AQAT_DS_ATIVIDADE = $(".AQAT_DS_ATIVIDADE").val();
        var AQAT_DH_CRIACAO   = $("#AQAT_DH_CRIACAO").val();
        var AQAT_DH_FIM       = $("#AQAT_DH_FIM").val();

        //Validando os campo para nao serem vazios
        if (AQAT_CD_ATIVIDADE == '') {

            alert("Campo código não pode ser vazio!");
            $(".AQAT_CD_ATIVIDADE ").focus();
            return false;
        }


        if (AQAT_DS_ATIVIDADE == '') {

            alert("Campo Assunto não pode ser vazio!");
            $(".AQAT_DS_ATIVIDADE  ").focus();
            return false;
        }

        if (AQAT_DH_CRIACAO == '') {

            alert("Campo Data início não pode ser vazio!");
            $("#AQAT_DH_CRIACAO  ").focus();
            return false;
        }

        //Recuperando os dados para envivar para controller add-pctt
        $.ajax({
            url: base_url + '/arquivo/pctt/add-cadastro-atividades',
            type: 'GET',
            data: 'AQAT_ID_ATIVIDADE=' + AQAT_ID_ATIVIDADE + '&AQAT_ID_AQSC=' + AQAT_ID_AQSC + '&AQAT_CD_ATIVIDADE=' + AQAT_CD_ATIVIDADE + '&AQAT_DS_ATIVIDADE=' + AQAT_DS_ATIVIDADE + '&AQAT_DH_CRIACAO=' + AQAT_DH_CRIACAO + '&AQAT_DH_FIM=' + AQAT_DH_FIM,
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
                        chamaTabelaAtividade()
                    }, 1500);

                }

            }
        });

    });
    /*
     /* Fim da função ajax 
     */
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
function chamaTabelaAtividade() {
    var atividade                  = $("#AQSC_CD_SUBCLASSE option:selected").val();
    var AQAP_CD_ASSUNTO_PRINCIPAL  = $("#AQAP_CD_ASSUNTO_PRINCIPAL").val();
    var AQAS_CD_ASSUNTO_SECUNDARIO = $("#AQAS_CD_ASSUNTO_SECUNDARIO").val();
    var AQCL_CD_CLASSE             = $("#AQCL_CD_CLASSE").val();
    
    //Enviando o id via ajax
    $.ajax({
        url: base_url + '/arquivo/pctt/list-atividade-tabela',
        dataType: "html",
        type: "GET",
        data: "AQSC_CD_SUBCLASSE=" + atividade + "&AQAP_CD_ASSUNTO_PRINCIPAL=" + AQAP_CD_ASSUNTO_PRINCIPAL + "&AQAS_CD_ASSUNTO_SECUNDARIO=" + AQAS_CD_ASSUNTO_SECUNDARIO + "&AQCL_CD_CLASSE=" + AQCL_CD_CLASSE,
        // Barra de carregamento
        beforeSend: function() {

            //$("#gridClasse").html("");
            //$("#AQSC_CD_SUBCLASSE option:selected").val("");
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

function buscarAtivadeSecundario() {

    var principal = $('#AQAP_CD_ASSUNTO_PRINCIPAL ').val();
    $.ajax({
        url: base_url + '/arquivo/pctt/list-atividade-secundario',
        dataType: "html",
        type: "GET",
        data: "AQAP_CD_ASSUNTO_PRINCIPAL=" + principal,
        beforeSend: function() {
            $(".carregandoAjax").fadeIn(800);
            $(".carregandoAjax").fadeOut(600);
            $(".carregandoAjax").html();
            $(".atividade").html("");
            $(".secundario").html("");
            $(".classe").html("");
            $("#gridClasse").html("")

        },
        success: function(data) {

            if (data === '') {

                //chama function do javascript mensagem.js

                mostraFlashMessage("A seção selecionada não possui subseções", "notice");

            } else {

                $(".atividade").html(data);

            }

        },
        complete: function() {

        },
    });
}
;

// Chamando o função para carregar as classes

function buscarAtividadeClasse() {

    var classe = $('#AQAS_CD_ASSUNTO_SECUNDARIO').val();
    var AQAP_CD_ASSUNTO_PRINCIPAL = $('#AQAP_CD_ASSUNTO_PRINCIPAL').val();
    $.ajax({
        url: base_url + '/arquivo/pctt/list-atividade-classe',
        dataType: "html",
        type: "GET",
        data: "AQAS_CD_ASSUNTO_SECUNDARIO=" + classe + "&AQAP_CD_ASSUNTO_PRINCIPAL=" + AQAP_CD_ASSUNTO_PRINCIPAL,
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

                $(".secundario").html(data);

            }

        },
        complete: function() {

        },
    });

}

function buscarAtividadeSubClasse() {

    var subClasse = $('#AQCL_CD_CLASSE').val();
    var AQAP_CD_ASSUNTO_PRINCIPAL = $('#AQAP_CD_ASSUNTO_PRINCIPAL').val();
    var AQAS_CD_ASSUNTO_SECUNDARIO = $('#AQAS_CD_ASSUNTO_SECUNDARIO').val();
    //var AQCL_CD_CLASSE = $('#AQCL_CD_CLASSE').val();
    $.ajax({
        url: base_url + '/arquivo/pctt/list-atividade-sub-classe',
        dataType: "html",
        type: "GET",
        data: "AQCL_CD_CLASSE=" + subClasse + "&AQAP_CD_ASSUNTO_PRINCIPAL=" + AQAP_CD_ASSUNTO_PRINCIPAL + "&AQAS_CD_ASSUNTO_SECUNDARIO=" + AQAS_CD_ASSUNTO_SECUNDARIO,
        beforeSend: function() {

            $(".classe").html("");
            $("classe").val("");
            $(".carregandoAjax").fadeIn(800);
            $(".carregandoAjax").fadeOut(600);
            $(".carregandoAjax").html();

        },
        success: function(data) {

            if (data === '') {

                //chama function do javascript mensagem.js

                mostraFlashMessage("A seção selecionada não possui subseções", "notice");

            } else {

                $(".classe").html(data);

            }

        },
        complete: function() {

        },
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
 