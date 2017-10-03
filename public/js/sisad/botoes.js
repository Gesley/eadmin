/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas a criação e personalização de botões
 */
$(function() {
//* CARREGAMENTO DEFAULT ------------------------------------------------------*/
    carregaBotoes();
//* USO DINÂMICO ------------------------------------------------------*/



    /*
     $(".submitComum").button();
     $(".sair").button({
     icons: {
     primary: "ui-icon-power"
     }
     });
     $(".novo").button({
     icons: {
     primary: "ui-icon-document"
     }
     });
     $(".editar").button({
     icons: {
     primary: "ui-icon-pencil"
     }
     });
     $(".excluir").button({
     icons: {
     primary: "ui-icon-trash"
     }
     });
     $(".ordemDESC").button({
     icons: {
     primary: "ui-icon-triangle-1-s"
     }
     });
     $(".ordemASC").button({
     icons: {
     primary: "ui-icon-triangle-1-n"
     }
     });
     $(".abrirAnexo").button({
     icons: {
     primary: "ui-icon-folder-open"
     }
     }).attr('style', 'width: 40px; height: 16px;');
     $(".alertaButton").button({
     icons: {
     primary: "ui-icon-alert"
     }
     }).attr('style', 'width: 40px; height: 16px;');
     
     $(".historico").button({
     icons: {
     primary: "ui-icon-clock"
     }
     }).attr('style', 'width: 40px; height: 16px;');
     
     
     
     $("button.botao, input[type=submit].botao, a.botao").button();
     $("a", ".paginationControl").button();
     $("span.disabled", ".paginationControl").button({disabled: true});
     
     $("#botao_ajuda").button({
     icons: {
     primary: "ui-icon-help"
     }
     }).attr('style', 'position: absolute; right: 0px; width: 28px; height: 16px; display: none;');
     $("#botao_ajuda_recolhe").button({
     icons: {
     primary: "ui-icon-arrowstop-1-n"
     }
     }).attr('style', 'position: relative; left: 350px; bottom: -10px; width: 28px; height: 16px;')
     .attr('title', 'Recolher ajuda');
     
     $("#botao_informacao").button({
     icons: {
     primary: "ui-icon-notice"
     }
     }).attr('style', 'position: absolute; right: 30px; width: 28px; height: 16px; display: none; ');
     */

});
//* FUNCTIONS ----------------------------------------------------------------*/
/**
 * Carrega os botões a serem usados na página
 * 
 * @returns none
 */
function carregaBotoes() {
    $(".painel").buttonset();
    
    $("button").css({
        "height": "20px"
                , "width": "28px"
    });
    $("button.botao, input[type=submit].botao, a.botao").button();
    $(".subir").button({
        icons: {
            primary: "ui-icon-triangle-1-n"
        }
    });
    $(".descer").button({
        icons: {
            primary: "ui-icon-triangle-1-s"
        }
    });
    $(".esquerda").button({
        icons: {
            primary: "ui-icon-carat-1-w"
        }
    });
    $(".esquerda_pular").button({
        icons: {
            primary: "ui-icon-seek-prev"
        }
    });
    $(".direita").button({
        icons: {
            primary: "ui-icon-carat-1-e"
        }
    });
    $(".direita_pular").button({
        icons: {
            primary: "ui-icon-seek-next"
        }
    });
    $(".nova_aba").button({
        icons: {
            primary: "ui-icon-plus"
        }
    });
    $(".fecha_dialog").button({
        icons: {
            primary: "ui-icon-close"
        }
    });
    $(".nova_dialog").button({
        icons: {
            primary: "ui-icon-newwin"
        }
    });
    $(".nova_dialog_sem_metadados").button({
        icons: {
            primary: "ui-icon-newwin"
        }
    });
    $(".remover_tr").button({
        icons: {
            primary: "ui-icon-close"
        }
    });
    $(".submitComum").button().css({
        "height": "25px"
                , "width": "auto"
    });
    $(".botaoComum").button().css({
        "height": "25px"
                , "width": "80px"
    });
}
