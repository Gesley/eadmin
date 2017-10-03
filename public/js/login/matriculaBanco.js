/**
 * @category    Login
 * @copyright   Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author      Marcelo Caixeta Rocha
 * @license     FREE, keep original copyrights
 */

$(window).load(function() {
    /**
     * Posiciona o cursor no campo Matrícula
     */
    $("#COU_COD_MATRICULA").focus();
    /**
     * Verifica se o browser é baseado em webkit: Google Chrome, Safari...
     */
    if ($.browser.webkit == true) {
        setTimeout(function() {
            if ($("#COU_COD_MATRICULA").val() != '') {
                carrega_bancos($("#COU_COD_MATRICULA").val()); 
            }
        }, 100); 
    }
    /**
     * Se o campo da matrícula vier preenchido ao carregar a página chama a função
     * que carrega os bancos
     */
    if ($("#COU_COD_MATRICULA").val() != '') {
        carrega_bancos($("#COU_COD_MATRICULA").val() ); 
    }
    /**
     * Verifica se o usuário tentou selecionar o banco de dados antes de informar
     * a matrícula
     */
    $("#COU_NM_BANCO").click(
        function () {
            if ($("#COU_COD_MATRICULA").val() != '') {
                return false;
            } else {
                $( "#dialog-message" ).dialog({
                    modal: true,
                    hide: {
                        effect: "explode",
                        duration: 500
                    },
                    buttons: {
                      Ok: function() {
                        $( this ).dialog( "close" );
                      }
                    }
                });
                return true;
            }
    });
    /**
     * Faz a requisição ajax para carregar os bancos de dados do usuário depois que
     * a matrícula foi digitada
     */
    $("#COU_COD_MATRICULA").change(
        function () {
            var matricula = $(this).val();
            carrega_bancos(matricula);
        });
    /**
     * Função que carrega via Ajax os bancos de dados que foram cadastrados para uma
     * matrícula
     */   
    function carrega_bancos(matricula) {
         $.ajax({
            url: base_url + '/login/ajaxbanco/matricula/'+matricula.toUpperCase(),
            beforeSend:function() {
                $("#COU_NM_BANCO").removeClass('erroInputSelect');
                $("#COU_NM_BANCO").val("");
                $("#COU_NM_BANCO").addClass('carregandoInputSelect');
            },
            success: function(data) {
                $("#COU_NM_BANCO").html(data);
                $("#COU_NM_BANCO").removeClass('carregandoInputSelect');
            },
            error: function(){
                $("#COU_NM_BANCO").removeClass('x-form-field');
                $("#COU_NM_BANCO").val('Erro ao carregar.');
                $("#COU_NM_BANCO").addClass('erroInputSelect');
                $("#COU_NM_BANCO").html('<option>Erro ao carregar</option>');
            }
        });
    }
});