/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas à tela de cadastro de documentos
 * 
 *  Depende dos scripts:
 *  + /js/sisad/mensagem.js
 *  + /js/eadmin/eassinador/api-sdk.js.js
 *  + /js/eadmin/eassinador/manipulador.js
 *  + 
 */
$("document").ready(function() {

    function manipulaCheckboxPorClasse(atributo, valor, classe) {
        $.each($("." + classe), function(index, checkbox) {
            $(checkbox).attr(atributo, valor);
        });
    }

    function toggleAssinatura() {
        $("#SENHA").val("");
        if ($("input[name='TIPO_ASSINATURA']:checked").val() == undefined) {
            $("#TIPO_ASSINATURA-senha").attr("checked", true);
            manipulaCheckboxPorClasse('disabled', false, 'nao_aceita_assinatura_digital');
            $("#assinatura_por_senha").show();
        } else if ($("input[name='TIPO_ASSINATURA']:checked").val() == "senha") {
            manipulaCheckboxPorClasse('disabled', false, 'nao_aceita_assinatura_digital');
            $("#assinatura_por_senha").show();
        } else {
            manipulaCheckboxPorClasse('disabled', true, 'nao_aceita_assinatura_digital');
            $("#assinatura_por_senha").hide();
        }
    }

    toggleAssinatura();
    /**
     * Marca ou desmarca os checkbox com classe igual a check_documento 
     * de dentro de uma tabela
     */
    $(".check_todos_documentos").click(function() {
        checkboxs = $(this).closest("table").find(".check_documento");
        if ($(this).is(":checked")) {
            $.each(checkboxs, function(index, checkbox) {
                if (!$(checkbox).hasClass('nao_aceita_assinatura_digital') || $("input[name='TIPO_ASSINATURA']:checked").val() != "certificado") {
                    $(checkbox).attr("checked", true);
                }
            });
        } else {
            $.each(checkboxs, function(index, checkbox) {
                if (!$(checkbox).hasClass('nao_aceita_assinatura_digital') || $("input[name='TIPO_ASSINATURA']:checked").val() != "certificado") {
                    $(checkbox).attr("checked", false);
                }
            });
        }
    });
    $("input[name='TIPO_ASSINATURA']").change(function() {
        toggleAssinatura();
    });
    $("#form_documentos").submit(function(event) {
        event.preventDefault();
        if ($(".check_documento:checked").length > 0) {
            iniciaAssinatura();
        } else {
            $('html,body').animate({scrollTop: 0}, 'slow');
            montaStatus("Escolha um documento para assinar", "notice");
        }
    });

    function gravaAssinaturaPorSenha(documento, documentos, posicao) {
        $.ajax({
            url: base_url + "/sisad/documento/assinarporsenhasalvarajax/",
            type: "POST",
            modal: true,
            data: {"DOCUMENTO": documento, "SENHA": $("#SENHA").val()},
            beforeSend: function() {
                var progresso = montaProgresso("Guardando assinatura no sistema. " + " <br/>" + posicao + " de " + documentos.length + " documentos foram assinados");
                $("#mensagem").find("#progresso").html(progresso);
            },
            success: function(dado, textStatus, jqXHR) {
                ++posicao;
                if (textStatus == "success") {
                    if (jqXHR.getResponseHeader("Content-Type") != "text/html") {
                        if (dado.SUCESSO == true) {
                            var status = montaStatus("Documento " + documento.MASC_NR_DOCUMENTO + " foi <strong>assinado por senha</strong> com sucesso.", "success");
                            $("#mensagem").find("#status").append(status);
                            //encadeia a próxima assinatura
                            if (documentos[posicao] != undefined) {
                                assinarPorSenha(documentos, posicao);
                            } else {
                                $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
                                var progresso = montaProgresso("Processo de assinatura concluída.", false);
                                $("#mensagem").find("#progresso").html(progresso);
                                $("#SENHA").val("");
                                return;
                            }
                        } else {
                            if (dado.MENSAGEM == "Senha incorreta") {
                                alert("Senha incorreta. Por motivos de segurança a seção foi perdida.");
                                $("html").html("");
                                window.location = base_url + "/login";
                            } else {
                                var status = montaStatus(dado.MENSAGEM, "error");
                                $("#mensagem").find("#status").append(status);
                                if (documentos[posicao] != undefined) {
                                    assinarPorSenha(documentos, posicao);
                                } else {
                                    $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
                                    var progresso = montaProgresso("Processo de assinatura concluída.", false);
                                    $("#mensagem").find("#progresso").html(progresso);
                                    $("#SENHA").val("");
                                    return;
                                }
                            }
                        }
                    } else {
                        var status = montaStatus("O ajax retornou um formato desconhecido", "error");
                        $("#mensagem").find("#status").append(status);
                        if (documentos[posicao] != undefined) {
                            assinarPorSenha(documentos, posicao);
                        } else {
                            $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
                            var progresso = montaProgresso("Processo de assinatura concluída.", false);
                            $("#mensagem").find("#progresso").html(progresso);
                            $("#SENHA").val("");
                            return;
                        }
                    }
                } else {
                    var status = montaStatus("Documento " + documento.MASC_NR_DOCUMENTO + " não foi <strong>assinado por senha</strong>: Ocorreu um erro na execução do ajax.", "error");
                    $("#mensagem").find("#status").append(status);
                    if (documentos[posicao] != undefined) {
                        assinarPorSenha(documentos, posicao);
                    } else {
                        $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
                        var progresso = montaProgresso("Processo de assinatura concluída.", false);
                        $("#mensagem").find("#progresso").html(progresso);
                        $("#SENHA").val("");
                        return;
                    }
                }
            },
            complete: function() {

            },
            error: function() {

            }
        });
    }

    function gravaAssinaturaPorCertificado(assinatura, arquivo, documento, documentos, posicao) {
        if (cancelaAssinatura) {
            $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
            montaStatus("Processo de assinatura encerrado. Os próximos documentos não serão assinados.", 'notice');
            return;
        }
        var cpf = obterDadosCertificado("Cpf");

        if (cpf.indexOf("MSG") != -1 || cpf == "") {
            var progresso = montaProgresso("Processo de assinatura encerrado. Os próximos documentos não serão assinados.", false);
            $("#mensagem").find("#progresso").html(progresso);
            var status = montaStatus("Não foi possível obter o CPF pelo certificado. Mensagem: \"" + traduzMensagem(cpf) + "\".", 'error');
            $("#mensagem").find("#status").append(status);
            return;
        }
        //formata o cpf para o formato da session do e-admin
        cpf = cpf.split(".").join("").split("-").join("");

        $.ajax({
            url: base_url + "/sisad/documento/assinarporcertificadosalvarajax/",
            type: "POST",
            modal: true,
            data: {"ARQUIVO": arquivo, "ASSINATURA": assinatura, "DOCUMENTO": documento, "CPF": cpf},
            beforeSend: function() {
                var progresso = montaProgresso("Guardando assinatura no RED. " + " <br/>" + (posicao - 1) + " de " + documentos.length + " documentos foram assinados");
                $("#mensagem").find("#progresso").html(progresso);
            },
            success: function(dado, textStatus, jqXHR) {
                if (textStatus == "success") {
                    if (jqXHR.getResponseHeader("Content-Type") != "text/html") {
                        if (dado.SUCESSO == true) {
                            $("#" + documento.DOCM_ID_DOCUMENTO).addClass("nao_aceita_assinatura_digital");
                            $("#" + documento.DOCM_ID_DOCUMENTO).attr("disabled", true);

                            var status = montaStatus("Documento " + documento.MASC_NR_DOCUMENTO + " foi <strong>assinado por certificado</strong> com sucesso.", "success");
                            $("#mensagem").find("#status").append(status);
                            //encadeia a próxima assinatura
                            if (documentos[posicao] != undefined) {
                                assinarPorCertificado(documentos, posicao);
                            } else {
                                $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
                                var progresso = montaProgresso("Processo de assinatura concluído.", false);
                                $("#mensagem").find("#progresso").html(progresso);
                            }
                        } else {
                            var status = montaStatus(dado.MENSAGEM, "error");
                            $("#mensagem").find("#status").append(status);
                            if (documentos[posicao] != undefined) {
                                assinarPorCertificado(documentos, posicao);
                            } else {
                                $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
                                var progresso = montaProgresso("Processo de assinatura concluído.", false);
                                $("#mensagem").find("#progresso").html(progresso);
                                $("#SENHA").val("");
                                return;
                            }
                        }

                    }
                } else {
                    var status = montaStatus("Documento " + documento.MASC_NR_DOCUMENTO + " não foi <strong>assinado via certificado digital</strong>: Ocorreu um erro na execução do ajax.", "error");
                    $("#mensagem").find("#status").append(status);
                    if (documentos[posicao] != undefined) {
                        assinarPorCertificado(documentos, posicao);
                    } else {
                        $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
                        var progresso = montaProgresso("Processo de assinatura concluído.", false);
                        $("#mensagem").find("#progresso").html(progresso);
                        $("#SENHA").val("");
                        return;
                    }
                }
            },
            complete: function() {

            },
            error: function() {

            }
        });
    }

    function assinarPorSenha(documentos, posicao) {
        if ($("#SENHA").val() == "") {
            $("#dialog-progresso").dialog("close");
            montaStatus("Digite sua senha", 'notice');
            return;
        }
        if (cancelaAssinatura) {
            $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
            montaStatus("Processo de assinatura encerrado. Os próximos documentos não serão assinados.", 'notice');
            return;
        }
        posicao = parseInt(posicao, 10);
        var documento = documentos[posicao];
        gravaAssinaturaPorSenha(documento, documentos, posicao);
    }

    function assinarPorCertificado(documentos, posicao) {
        if (cancelaAssinatura) {
            $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
            montaStatus("Processo de assinatura encerrado. Os próximos documentos não serão assinados.", 'notice');
            return;
        }

        if (documentos[posicao] == undefined) {
            $("#dialog-progresso").parent("div").find(".ui-dialog-buttonset").find(".ui-button-text").html("Fechar");
            var progresso = montaProgresso("Processo de assinatura finalizado", false);
            $("#mensagem").find("#progresso").html(progresso);
            return;
        }

        if (existeAssinador()) {
            if (!isAtualizar()) {

                posicao = parseInt(posicao, 10);
                var documento = documentos[posicao];
                id = documento.DOCM_ID_DOCUMENTO;
                red = documento.DOCM_NR_DOCUMENTO_RED;
                $.ajax({
                    url: base_url + "/sisad/gerenciared/retorna-arquivo-para-assinatura/id/" + id,
                    type: "GET",
                    modal: true,
                    beforeSend: function() {
                        var progresso = montaProgresso("Baixando PDF do documento" + " <br/>" + (posicao) + " de " + documentos.length + " documentos foram assinados");
                        $("#mensagem").find("#progresso").html(progresso);
                    },
                    success: function(arquivo, textStatus, jqXHR) {
                        var progresso = montaProgresso("Assinando documento " + documento.MASC_NR_DOCUMENTO + " <br/>" + (posicao) + " de " + documentos.length + " documentos foram assinados");
                        $("#mensagem").find("#progresso").html(progresso);

                        if (textStatus == "success" && arquivo.ERROR == "") {
                            if (jqXHR.getResponseHeader("Content-Type") != "text/html") {

                                //testar para ver se iniciar varias vezes da erro

                                //compara cpf com o do certificado

                                var assinatura = assinarConteudoDetached(arquivo.HEXADECIMAL_ARQUIVO);
                                var mensagem = traduzMensagem(assinatura);
                                if (mensagem == "") {
                                    gravaAssinaturaPorCertificado(assinatura, arquivo, documento, documentos, ++posicao);
                                } else {
                                    var status = montaStatus("tentativa para o documento " + documento.MASC_NR_DOCUMENTO + ": " + mensagem);
                                    $("#mensagem").find("#status").append(status);
                                    assinarPorCertificado(documentos, ++posicao);
                                }

                            } else {
                                var status = montaStatus("Ocorreu um erro na execução do ajax de carregamento do arquivo. Não foi retornado um arquivo.");
                                $("#mensagem").find("#status").append(status);
                                assinarPorCertificado(documentos, ++posicao);
                            }
                        } else {
                            var status = '';
                            if (arquivo.TEM_ASSINATURA == true) {
                                status = montaStatus("O arquivo já possui uma assinatura. Caso queira assinar novamente o arquivo do documento " + documento.MASC_NR_DOCUMENTO + " remova a assinatura.");
                            } else {
                                status = montaStatus("Ocorreu um erro na execução do ajax de carregamento do arquivo" + arquivo.ERROR);
                            }

                            $("#mensagem").find("#status").append(status);
                            assinarPorCertificado(documentos, ++posicao);
                        }
                    },
                    complete: function() {

                    },
                    error: function() {

                    }
                });
            } else {
                var status = montaStatus("É necessário a atualização do assinador digital.");
                $("#mensagem").find("#status").append(status);
                return;
            }
        } else {
            var status = montaStatus("É necessário a instalação do assinador digital.");
            $("#mensagem").find("#status").append(status);
            return;
        }
    }
    //efetua chamada de si mesma se ainda tiver outro documento
    function assinar(documentos, posicao) {
        if ($("input[name='TIPO_ASSINATURA']:checked").val() == "certificado") {
            assinarPorCertificado(documentos, posicao);
        } else {
            assinarPorSenha(documentos, posicao);
        }
    }
    function montaEstruturaMensagem() {
        return "<div id='mensagem'>\n\
                    <div id='cabecalho'>" + montaCabecalho() + "</div><br/>\n\
                    <div id='progresso'></div><br/>\n\
                    <div id='status'></div>\n\
                </div>";
    }
    function montaCabecalho() {
        return "<div class='info'>Não feche a tela enquanto a assinatura não terminar. Caso feche apenas os documentos da lista abaixo estarão assinados.</div>";
    }

    function montaProgresso(mensagem, mostraGif) {
        return "<strong>" + (mostraGif !== false ? '<img src="' + base_url + '/img/ajax-loader_1.gif"/>' : '') + mensagem + "</strong>";
    }
    function montaStatus(mensagem, status) {
        var classe;
        var tipo;
        if (status == "" || status == undefined) {
            status = "error";
        }

        if (status == 'notice') {
            classe = status;
            tipo = 'Aviso';
        } else if (status == 'error') {
            classe = status;
            tipo = 'Erro';
        } else if (status == 'success') {
            classe = status;
            tipo = 'Sucesso';
        } else if (status == 'info') {
            classe = status;
            tipo = 'Informação';
        }
        mensagem = '<div class=\'' + classe + '\'><strong>' + tipo + ': </strong>' + mensagem + '</div>';
        $("#flashMessagesView").append(mensagem);
        return mensagem;
    }

    cancelaAssinatura = false;
    function iniciaAssinatura() {

        var documentos = new Array();
        var posicao = 0;
        
        $.each($(".check_documento:checked").not("[disabled = true]"), function(index, checkbox) {
            documentos[posicao++] = $.parseJSON($(checkbox).val());
        });
        if (existeAssinador()) {
            if (!isAtualizar()) {
                fecharSessaoChave();
                iniciarSessaoChave();

                $("#dialog-progresso").dialog({
                    resizable: false,
                    height: 500,
                    modal: true,
                    close: function() {
                        cancelaAssinatura = true;
                        $('html,body').animate({scrollTop: 0}, 'slow');
                    },
                    open: function() {
                        cancelaAssinatura = false;
                    },
                    buttons: {
                        "Cancelar próximos": function() {
                            $(this).dialog("close");
                        }
                    }
                });

                $("#dialog-progresso").html(montaEstruturaMensagem());
                assinar(documentos, 0);
            } else {
                var status = montaStatus("Erro ao iniciar o processo. É necessário a atualização do assinador digital.");
                $("#mensagem").find("#status").append(status);
            }
        } else {
            var status = montaStatus("Erro ao iniciar o processo. É necessário a instalação do assinador digital.");
            $("#mensagem").find("#status").append(status);
        }
    }

    function traduzMensagem(resultado) {
        switch (resultado) {
            case "MSG003":
                return "Assinatura inválida detectada na verificação!";
                break;
            case "MSG009":
                return "Assinatura não encontrada para validação!";
                break;
            case "MSG011":
                return "Propósito do certificado inválido!";
                break;
            case "MSG027":
                return "Certificado não suportado!";
                break;
            case "MSG039":
                return "Problema na busca da chave privada!";
                break;
            case "MSG041":
                return "Problemas na leitura das extenções de uso da chave do certificado!";
                break;
            case "MSG042":
                return "Conteúdo para assinatura inválido!";
                break;
            case "MSG043":
                return "A chave utilizada está inválida!";
                break;
            case "MSG044":
                return "Ocorreu um problema na assinatura!";
                break;
            case "MSG045":
                return "Falha na leitura dos dados do carimbo de tempo!";
                break;
            case "MSG046":
                return "Endereço de carimbo de tempo não foi reconhecido!";
                break;
            case "MSG047":
                return "Tamanho da assinatura inválido!";
                break;
            case "MSG048":
                return "Nenhum dispositivo com chave privada foi encontrado!";
                break;
            case "MSG049":
                return "Envelope inválido!";
                break;
            case "MSG050":
                return "Certificado inválido para o carimbo!";
                break;
            case "MSG051":
                return "O carimbo está inválido!";
                break;
            case "MSG052":
                return "Protocolo inválido!";
                break;
            case "MSG053":
                return "Tempo esgotado do carimbo!";
                break;
            case "MSG054":
                return "Endereço de carimbo de tempo inválido!";
                break;
            case "MSG055":
                return "Sem permissão para acessar o carimbo!";
                break;
            case "MSG056":
                return "Erro ao tentar obter dado inexistente no certificado!";
                break;
            case "MSG057":
                return "Operação cancelado pelo usuário!";
                break;
            case "MSG999":
                return "Ocorreu um erro inesperado!";
                break;
            default:
                return "";
        }
    }
});
