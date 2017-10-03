$(document).ready(function() {
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

    $("td").click(function() {
        $(".value_" + $(this).attr("id")).hide();
        $(".trcampos_" + $(this).attr("id")).show(500);
    });
    $("tr").mouseover(function() {
        this.title = 'Clique para editar o texto';
        this.style.margin = '400px;';
    });

    $(".btn_enviar").click(function() {
        URL = base_url + '/arquivo/pctt/edit-cadastro-vias';
        var id = $(this).attr('id').split('_')[2];
        var AQVI_ID_VIA = $(".trcampos_" + id + "  #AQVI_ID_VIA").val();
        var AQVI_CD_VIA = $(".trcampos_" + id + "  #AQVI_CD_VIA").val();
        var AQVI_QT_VIA = $(".trcampos_" + id + "  #AQVI_QT_VIA").val();

        $.ajax({
            type: 'get',
            data: 'AQVI_ID_VIA=' + AQVI_ID_VIA + '&AQVI_CD_VIA=' + AQVI_CD_VIA + '&AQVI_QT_VIA=' + AQVI_QT_VIA,
            url: URL,
            beforeSend: function() {
                $(".carregandoAjax").fadeIn(800);
                $(".carregandoAjax").fadeOut(600);
                $(".carregandoAjax").html();
            },
            success: function() {
                location.reload();
            }

        });
    });

    $(':input').keyup(function() {
        this.value = this.value.toUpperCase();
    });

// Funções para inserir os dados via ajax

    //Mudando o status do botao ao passar o mouse 

    $("#addlink").hover(function() {

        $(this).css("cursor", "pointer");
    });

    //Exibindo o formulario de inserssão
    $("#addlink").click(function() {
        $("#inserir").show('slow');
        $(".AQVI_CD_VIA ").focus();
        //$(".editar").hide();
        //$(".tr3").hide();
    });
    //Botao cancelar ao clicar esconde o formulario de inserssão
    $("#cancelar").click(function() {
        $("#inserir").hide(300);
        $(".tr3").show('slow');
        $(".editar").show('fast');
        $(".AQVI_CD_VIA").val('');
        $(".AQVI_QT_VIA").val('');
    });
    // Enviando os dados via ajax
    //Ao clicar os dados serao enviado para a contorller
    $("#enviar").click(function() {
        var AQVI_ID_VIA = $(".AQVI_ID_VIA").val();
        var AQVI_CD_VIA = $(".AQVI_CD_VIA").val();
        var AQVI_QT_VIA = $(".AQVI_QT_VIA").val();
        URL;

        //Validando os campo para nao serem vazios
        if (AQVI_CD_VIA == '') {

            alert("Campo código não pode ser vazio!");
            $(".AQVI_CD_VIA ").focus();
            return false;
        }


        if (AQVI_QT_VIA == '') {

            alert("Campo descrição não pode ser vazio!");
            $(".AQVI_QT_VIA  ").focus();
            return false;
        }

        //Recuperando os dados para envivar para controller add-pctt
        $.ajax({
            url: base_url + '/arquivo/pctt/add-cadastro-vias',
            type: 'GET',
            data: 'AQVI_ID_VIA=' + AQVI_ID_VIA + '&AQVI_CD_VIA=' + AQVI_CD_VIA + '&AQVI_QT_VIA=' + AQVI_QT_VIA,
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
                        buscarAtividadeSubClasse()
                    }, 1500);

                }

            }
        });

    });
    /*
     /* Fim da função ajax 
     */
});