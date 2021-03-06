$('document').ready(function() {

    if ($("#qtd_processos_apensos").html() == "") {
        $("#qtd_processos_apensos").html($(".processo_apenso_ativo").size());
    }
    if ($("#qtd_processos_anexados").html() == "") {
        $("#qtd_processos_anexados").html($(".processo_anexo_ativo").size());
    }


    $("#tabs").tabs();
    $("#tabs").tabs("select", GLOBAL_indice_abas);
    $("#tabs").tabs({
        select: function(event, ui) {
            GLOBAL_indice_abas = ui.index;
        }
    });
    $("#buttonsetmanifestacao").buttonset();
    $(".abrirAnexo").button({
        icons: {
            primary: "ui-icon-folder-open"
        }
    }).attr('style', 'width: auto; height: 26px;');
    $(".alertaButton").button({
        icons: {
            primary: "ui-icon-alert"
        }
    }).attr('style', 'width: auto; height: 26px;');

    $(".proc_docs_icon_closed").button({
        icons: {
            primary: "ui-icon-radio-on"
        }
    });

    $(".novoDespacho").button({
        icons: {
            primary: "ui-icon-document"
        }
    });

    /* ABRIR DOCUMENTOS DE UM PROCESSO */
    var xhr_abrir_documentos_processo;
    var dialog_processo_documentos = '';

    $(".docs_pro").click(function() {
        var this_a = this;

        if (dialog_processo_documentos != '') {
            //dialog_processo_documentos.html(' ');
            dialog_processo_documentos.remove();
        }

        $("#dialog_proceso_container").html("<div id='dialog_processo_documentos'></div>");

        dialog_processo_documentos = $("#dialog_processo_documentos");
        dialog_processo_documentos.dialog({
            title: 'Detalhe do documento do processo',
            autoOpen: false,
            modal: false,
            show: 'fold',
            hide: 'fold',
            resizable: true,
            width: 800,
            height: 600,
            position: [300, 140, 0, 0],
            buttons: {
                Ok: function() {
                    dialog_processo_documentos.remove();
                    $(this).dialog("close");
                }
            }
        });

        if (xhr_abrir_documentos_processo) {
            xhr_abrir_documentos_processo.abort();
        }

        url = base_url + '/sisad/detalhedcmto/detalhedcmto';
        xhr_abrir_documentos_processo = $.ajax({
            url: url,
            dataType: 'html',
            type: 'POST',
            data: $(this).attr('value'),
            contentType: 'application/json',
            processData: false,
            beforeSend: function() {
                dialog_processo_documentos.html('');
            },
            success: function(data) {
                dialog_processo_documentos.html(data);

                obj = dialog_processo_documentos.find("div#tabs")

                $(obj).addClass('pro_docs_tabs');
                $(".pro_docs_tabs").tabs();

                dialog_processo_documentos.dialog("open");

                $(this_a).addClass("ui-state-highlight");
                $(this_a).button({
                    icons: {
                        primary: "ui-icon-check"
                    }
                });

            },
            complete: function() {
                $("#dialog_processo_documentos #qtd_processos_apensos").html($("#dialog_processo_documentos .processo_apenso_ativo").size());
                $("#dialog_processo_documentos #qtd_processos_anexados").html($("#dialog_processo_documentos .processo_anexo_ativo").size());
            },
            error: function() {

            }
        });
    });
    $('.removerDocsProc').click(function() {
        var excluirdados = $(this).attr('codigo');
        var xhr_excluirDocsPro;
        $("#divexcluirdocspro").dialog({
            title: 'Confirmar',
            modal: true,
            buttons: {
                "Remover Documento": function() {
                    if (xhr_excluirDocsPro) {
                        xhr_excluirDocsPro.abort();
                    }
                    $(this).dialog("close");

                    url = base_url + '/sisad/autuar/removerdocspro';
                    xhr_excluirDocsPro = $.ajax({
                        url: url,
                        dataType: 'html',
                        type: 'POST',
                        data: excluirdados,
                        contentType: 'application/json',
                        processData: false,
                        beforeSend: function() {
                        },
                        success: function(data) {
                            var dados = $.parseJSON(data);
                            if (dados.Retorno === 1) {
                                var fieldsets = $('#tabs-5, #tabs-8').find('fieldset');
                                fieldsets.each(
                                        function() {
                                            var fieldset = $(this);
                                            var removerDocsProc = fieldset.find('.removerDocsProc');
                                            var codigo = $(removerDocsProc).attr('codigo');
                                            //console.log(codigo);
                                            //console.log(excluirdados);
                                            if (excluirdados == codigo) {
                                                fieldset.remove();
                                            }
                                            if (dados.DocsProcesso <= 1) {
                                                removerDocsProc.remove();
                                            }
                                        }
                                );
                            } else {
                                alert('Erro 1: Não foi possível excluir documento do Processo.');
                            }
                        },
                        complete: function() {

                        },
                        error: function() {
                            alert('Erro 2: Não foi possível excluir documento do Processo.');
                        }
                    });
                },
                Cancelar: function() {
                    $(this).dialog("close");
                }
            }
        });
    });
    $('.removerProcAnexo').click(function() {
        var excluirdados = $(this).attr('codigo');
        var xhr_excluirApenso;
        var dadosExcluidos = $.parseJSON(excluirdados);
        $("#div_removerAnexo").dialog({
            title: 'Confirmar',
            modal: true,
            buttons: {
                "Remover Anexo": function() {
                    if (xhr_excluirApenso) {
                        xhr_excluirApenso.abort();
                    }
                    $(this).dialog("close");

                    url = base_url + '/sisad/juntada/removeranexoprocesso';
                    xhr_excluirApenso = $.ajax({
                        url: url,
                        dataType: 'html',
                        type: 'POST',
                        data: excluirdados,
                        contentType: 'application/json',
                        processData: false,
                        beforeSend: function() {
                            $('#fieldSet_' + dadosExcluidos.codigo_anexo_detalhe + ' > .carregaAjax').show('slow');
                        },
                        success: function(data) {
                            if (data == '"sucesso"') {
                                $('#fieldSet_' + dadosExcluidos.codigo_anexo_detalhe).removeClass('processo_anexo_ativo');
                                $("#qtd_processos_anexados").html($(".processo_anexo_ativo").size());
                                $('#excluirProAnex_' + dadosExcluidos.codigo_anexo_detalhe).remove();
                                $('#fieldSet_' + dadosExcluidos.codigo_anexo_detalhe).css('background', '#ff9');
                                $('#fieldSet_' + dadosExcluidos.codigo_anexo_detalhe + ' > div > div > p').prepend('<br/><b style="color: red">Anexo removido</b>');
                            } else {
                                $('#fieldSet_' + dadosExcluidos.codigo_anexo_detalhe + ' > .carregaAjax').hide('slow');
                                alert('Erro 1: Não foi possível excluir o anexo.');
                            }
                        },
                        complete: function() {
                            $('#fieldSet_' + dadosExcluidos.codigo_anexo_detalhe + ' > .carregaAjax').hide('slow');
                        },
                        error: function() {
                            alert('Erro 2: Não foi possível excluir o anexo.');
                        }
                    });
                },
                Cancelar: function() {
                    $(this).dialog("close");
                }
            }
        });
    });
    $('.removerProcApenso').click(function() {
        var excluirdados = $(this).attr('codigo');
        console.log(excluirdados);
        var xhr_excluirApenso;
        var dadosExcluidos = $.parseJSON(excluirdados);
        $("#div_removerApenso").dialog({
            title: 'Confirmar',
            modal: true,
            buttons: {
                "Remover Apenso": function() {
                    if (xhr_excluirApenso) {
                        xhr_excluirApenso.abort();
                    }
                    $(this).dialog("close");

                    url = base_url + '/sisad/juntada/removerapenso';
                    xhr_excluirApenso = $.ajax({
                        url: url,
                        dataType: 'html',
                        type: 'POST',
                        data: excluirdados,
                        contentType: 'application/json',
                        processData: false,
                        beforeSend: function() {
                            $('#fieldSet_' + dadosExcluidos.codigo_apenso_detalhe + ' > .carregaAjax').show('slow');
                        },
                        success: function(data) {
                            if (data == '"sucesso"') {
                                $('#fieldSet_' + dadosExcluidos.codigo_apenso_detalhe).removeClass('processo_apenso_ativo');
                                $("#qtd_processos_apensos").html($(".processo_apenso_ativo").size());
                                $('#excluirdocspro_' + dadosExcluidos.codigo_apenso_detalhe).remove();
                                $('#fieldSet_' + dadosExcluidos.codigo_apenso_detalhe).css('background', '#ff9');
                                $('#fieldSet_' + dadosExcluidos.codigo_apenso_detalhe + ' > div > div > p').prepend('<br/><b style="color: red">Apenso removido</b>');
                            } else {
                                $('#fieldSet_' + dadosExcluidos.codigo_apenso_detalhe + ' > .carregaAjax').hide('slow');
                                alert('Erro 1: Não foi possível excluir o apenso.');
                            }
                        },
                        complete: function() {
                            $('#fieldSet_' + dadosExcluidos.codigo_apenso_detalhe + ' > .carregaAjax').hide('slow');
                        },
                        error: function() {
                            alert('Erro 2: Não foi possível excluir o apenso.');
                        }
                    });
                },
                Cancelar: function() {
                    $(this).dialog("close");
                }
            }
        });
    });

    $('input[name=acao]').click(function() {
        var acao = this.value;
        var cx_unidade = $('form[name=cx_unid_entrada]');
        //console.log(cx_unidade);
        if (acao == 'Novo Despacho') {
            cx_unidade.attr('action', base_url + '/sisad/caixaunidade/despacho');

        }
    });
});
