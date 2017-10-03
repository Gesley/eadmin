$(document).ready(function() {
    $("#SGRS_ID_GRUPO").hide();
    $("#SGRS_ID_GRUPO-label").hide();
    $("#SGRS_ID_GRUPO-element").hide();
    $("#EMERGENCIAL").hide();
    $("#EMERGENCIAL-label").hide();
    $("#EMERGENCIAL-element").hide();
    $("#ASIS_IC_NIVEL_CRITICIDADE").hide();
    $("#ASIS_IC_NIVEL_CRITICIDADE-label").hide();
    $("#ASIS_IC_NIVEL_CRITICIDADE-element").hide();
    $("#CAUSA_PROBLEMA").hide();
    $("#CAUSA_PROBLEMA-element").hide();
    $("#CAUSA_PROBLEMA-label").hide();
    $("#SOLIC_PROBLEMAS").hide();
    $("#SOLIC_PROBLEMAS-element").hide();
    $("#SOLIC_PROBLEMAS-label").hide();
    var grupo_servico = $("#SGRS_ID_GRUPO").val();
    $(function(){
        botao_ajuda_by_id.delay(200).show('scale');
        $("#SSOL_NR_TELEFONE_EXTERNO").mask("(99)9999-9999");
    });
    if(grupo_servico != ''){
        $('#SSER_ID_SERVICO').empty();
        $('#combobox-input-text-SSER_ID_SERVICO').val('');

        var valorRaw = $('#SGRS_ID_GRUPO').val();
        if(valorRaw != ''){
            var obj = jQuery.parseJSON(valorRaw);
            var grupoID = obj.SGRS_ID_GRUPO;

            url = base_url + '/sosti/solicitacao/ajaxservicos';
            $.ajax({
                url:url,
                type: 'get',
                data: 'grupoID='+grupoID,
                dataType:'html',
                error:function(){
                    alert('error');
                },
                success:function(data){
                    $('#SSER_ID_SERVICO').html(data);
                    $('#combobox-input-text-SSER_ID_SERVICO').attr('style','width:650px');
            }});  
        }

    };
    $(function(){ 
        $('#ANEXOS').MultiFile({
            STRING: {
                file: '<em title="Clique para Remover" onclick="$(this).parent().prev().click()">$file</em>',
                remove: '<img class="excluirpdf" title="Clique para Remover" height="16" width="16"/>'
            }
        });
    }); 
    
     /* Partes/interessados dos documentos e dos processos */
    $(function() {
        $(document.body).delegate(".remover-parte","click", function(){
            $(this).parent().parent().remove();
        });
        $(document.body).delegate(".removerTodos","click", function(){
            var config = $.data(document.body,'config'),
            linhas_removidas = $(config.tabela.find("."+config.descParte)); 
            //console.log(linhas_removidas);
            linhas_removidas.remove();
        });        
        $(document.body).delegate(".removerPartesDocs","click", function(){
            var config = $.data(document.body,'config'),
            linhas_removidas = $(config.containerPartesDocumentos.find("."+config.descParte)); 
            linhas_removidas.remove();
        });
        $.data(document.body,'config',
        {
            containerPartes: $("#partes_adicionadas"),
            tabela: $("#selecionados_partes tbody"),
            descParte: 'linha_interessado'
        });
    $("#selecionados_partes").show();
    $("#selecionados_vistas").hide();
    $("#dialog_cadastra_parte_doc").dialog('option', 'title','Cadastro de Partes');
    $("#dialog_cadastra_parte_doc").dialog('open');

    // inicializacoes
    $(".li_parte").hide();
    $("#UNIDADE_PESSOA").attr("disabled", true);
    $(".pessTRF").show();

    $("#TIPO_PESSOA").change( function (){
        $(".li_parte").hide();
        var $value = $(this).val(),
        classes = {
            'P': ".pessTRF",
            'U': ".unidade",
            'F': ".pessExterna",
            'J': ".pessJuridica"
        };
        $( classes[$value] ).show();
    });

    $("#PAPD_CD_MATRICULA_INTERESSADO").autocomplete({
        source: base_url + '/sosti/solicitacao/ajaxpessoasacompanhamento',
        minLength: 3,
        delay: 100,
        select: function(event, ui){
            var config = $.data(document.body,'config');
            existe_na_lista = config.containerPartes.find("input[value="+ui.item.value+"-"+ui.item.id+"]");  
            encontrou = existe_na_lista.attr('value')
            if(encontrou != undefined){
                alert('A pessoa já existe na lista');
                return;
            }else{ 
                var tr = "<tr class='linha_interessado'>";
                tr += "<td style='width: 5%'><a href='#' class='remover-parte' rel='"+ui.item.value+"-"+ui.item.id+"' >Remover</a></td>";
                tr += "<td>"+ ui.item.label +" </td>";
                tr += "<input type='hidden' value='"+ui.item.value+"-"+ui.item.id+"' name='acompanhante_sosti[]' />";
                tr += "</tr>",
                config.tabela.append(tr);
                $(this).val("");
                return false;
            }
        }
    });
    $("#PORORDEMDE").autocomplete({
        source: base_url+'/sosti/solicitacao/ajaxpessoasporordemde',
        minLength: 3,
        delay: 100
    });
    }); 

    var optionsGruposTRF = null;
    $(function() {
        $("#SSOL_GARANTIA_OBSERVACAO").hide();
        $("#SSOL_GARANTIA_OBSERVACAO-element").hide();
        $("#SSOL_GARANTIA_OBSERVACAO-label").hide();
        optionsGruposTRF = $('select#SGRS_ID_GRUPO').html();
        $("select#TRF1_SECAO").change(
            function () {
                    var secao = $(this).val().split('|')[0];
                    var lotacao = $(this).val().split('|')[1];
                    var tipolotacao = $(this).val().split('|')[2];
                    //var retiraCaixa1 = 118; /* caixa gestao de demandas de TI */
                    $("select#SSER_ID_SERVICO").html("");
                $.ajax({
                    url: base_url + '/sosti/solicitacaousertisecoes/ajaxgruposervicosecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                    dataType : 'html',
                    beforeSend:function() {
                    },
                    success: function(data) {
                        $('select#SGRS_ID_GRUPO').html(data);
                        if ($(data).length == 1){
                            $('select#SGRS_ID_GRUPO').trigger("change");
                        }							
                    },
                    error: function(){
                    }
                });
            });

        $('#SGRS_ID_GRUPO').change(
            function(){
                $("#SSER_ID_SERVICO").removeAttr('disabled');
                $.ajax({
                    url: base_url + '/sosti/solicitacao/ajaxservicos',
                    dataType: 'html',
                    type: 'POST',
                    data: this.value,
                    contentType: 'application/json',
                    processData: false,
                  beforeSend:function() {
                      $("#SSER_ID_SERVICO").removeClass('erroInputSelect');
                      $("#SSER_ID_SERVICO").html('');
                      $("#SSER_ID_SERVICO").addClass('carregandoInputSelect');
                  },
                  success: function(data) {
                      $("#SSER_ID_SERVICO").html(data);
                      $("#SSER_ID_SERVICO").removeClass('carregandoInputSelect');
                      $("#SSER_ID_SERVICO").focus();
                  },
                  error: function(){
                      $("#SSER_ID_SERVICO").removeClass('x-form-field');
                      $("#SSER_ID_SERVICO").val('Erro ao carregar.');
                      $("#SSER_ID_SERVICO").addClass('erroInputSelect');
                      $("#SSER_ID_SERVICO").html('<option>Erro ao carregar</option>');
                  }
                });  
                hideALL();
                datainput = jQuery.parseJSON(this.value);
                if(datainput.CXEN_ID_CAIXA_ENTRADA == 2){
                    showALL();
                }else{
                    hideALL();
                }
            }
        );
        /**
         * Ao escolher a categoria de serviço podem aparecer outros campos
         * como emergencial ou nível de criticidade.
         */ 
        $('#CTSS_NM_CATEGORIA_SERVICO').change(function(){
            var idCatServico = $('#CTSS_NM_CATEGORIA_SERVICO').val();
            if(idCatServico == '2'){
                $("#CAUSA_PROBLEMA").show();
                $("#CAUSA_PROBLEMA-element").show();
                $("#CAUSA_PROBLEMA-label").show();
            }else{
                $("#CAUSA_PROBLEMA").hide();
                $("#CAUSA_PROBLEMA-element").hide();
                $("#CAUSA_PROBLEMA-label").hide();
            }
            if(idCatServico == '2'){
                $("#ASIS_IC_NIVEL_CRITICIDADE").show();
                $("#ASIS_IC_NIVEL_CRITICIDADE-element").show();
                $("#ASIS_IC_NIVEL_CRITICIDADE-label").show();
            }else{
                $("#ASIS_IC_NIVEL_CRITICIDADE").hide();
                $("#ASIS_IC_NIVEL_CRITICIDADE-element").hide();
                $("#ASIS_IC_NIVEL_CRITICIDADE-label").hide();
                $("#SOLIC_PROBLEMAS").hide();
                $("#SOLIC_PROBLEMAS-element").hide();
                $("#SOLIC_PROBLEMAS-label").hide();
            }
            if(idCatServico == '1' || idCatServico == '7' || idCatServico == '8'|| idCatServico == '2'|| idCatServico == ''){
                $("#EMERGENCIAL").hide();
                $("#EMERGENCIAL-element").hide();
                $("#EMERGENCIAL-label").hide();
            }else{
                $("#EMERGENCIAL").show();
                $("#EMERGENCIAL-element").show();
                $("#EMERGENCIAL-label").show();
            }
        });
        /**
         * Exibe ou oculta o input textarea onde podem ser listadas as 
         * solicitações relacionadas.
         */
        $("input[name='CAUSA_PROBLEMA']").click(function(){
            var valor = $(this).val();
            if(valor == 1){
                $("#SOLIC_PROBLEMAS").show();
                $("#SOLIC_PROBLEMAS-element").show();
                $("#SOLIC_PROBLEMAS-label").show();
            } else {
                $("#SOLIC_PROBLEMAS").hide();
                $("#SOLIC_PROBLEMAS-element").hide();
                $("#SOLIC_PROBLEMAS-label").hide();
            }
        });
        /**
         * Exibe ou oculta o campo textarea onde pode ser incluída a 
         * justificativa da solicitação de garantia.
         */
        $('#SSOL_FLAG_GARANTIA').click(function(){
            if($("input[name='SSOL_FLAG_GARANTIA']").is(':checked') == true){
                $("#SSOL_GARANTIA_OBSERVACAO").show();
                $("#SSOL_GARANTIA_OBSERVACAO-element").show();
                $("#SSOL_GARANTIA_OBSERVACAO-label").show();
            } else {
                $("#SSOL_GARANTIA_OBSERVACAO").hide();
                $("#SSOL_GARANTIA_OBSERVACAO-element").hide();
                $("#SSOL_GARANTIA_OBSERVACAO-label").hide();
            }
        });
        var tamObsGarant = ($('#SSOL_GARANTIA_OBSERVACAO').val()).length;
        if($("input[name='SSOL_FLAG_GARANTIA']").is(':checked') == true){
            $("#SSOL_GARANTIA_OBSERVACAO").show();
            $("#SSOL_GARANTIA_OBSERVACAO-element").show();
            $("#SSOL_GARANTIA_OBSERVACAO-label").show();
            if(tamObsGarant < 5){
                $("#SSOL_GARANTIA_OBSERVACAO-element").append("<ul class='errors'><li>'' é menor que 5 (tamanho mínimo desse campo)</li>\n\
<li>Preenchimento Obrigatório</li>\n\
<li>ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.</li></ul>");
            }
        } 
        else {
            $("#SSOL_GARANTIA_OBSERVACAO").hide();
            $("#SSOL_GARANTIA_OBSERVACAO-element").hide();
            $("#SSOL_GARANTIA_OBSERVACAO-label").hide();
        }
        /**
         * Validação do nível de criticidade quando o formulário for submetido 
         * faltando campos obrigatórios
         */
        if ($('#CTSS_NM_CATEGORIA_SERVICO').val() == 2) {
            $("#ASIS_IC_NIVEL_CRITICIDADE").show();
            $("#ASIS_IC_NIVEL_CRITICIDADE-label").show();
            $("#ASIS_IC_NIVEL_CRITICIDADE-element").show();
            $("#CAUSA_PROBLEMA").show();
            $("#CAUSA_PROBLEMA-element").show();
            $("#CAUSA_PROBLEMA-label").show();
            $("#SOLIC_PROBLEMAS").show();
            $("#SOLIC_PROBLEMAS-element").show();
            $("#SOLIC_PROBLEMAS-label").show();
        }
    });

    var GLOBAL_indice_abas =  0;
    var xhr_abrir_documento; 

    var grid_tbody_tr;
    $(function(){

        grid_tbody_tr = $("table.grid > tbody > tr");
        grid_tbody_tr.click(
        function(){
            grid_tbody_tr.removeClass('hover_nav');

            var this_tr = $(this);
            var is_checked_tr = $(this).attr('marcado');

            var input_check_box = $(this).find('input[type=checkbox]');
            var is_checked_input = input_check_box.attr('checked');

            if( (is_checked_input == undefined && is_checked_tr == undefined) || (is_checked_input != undefined && is_checked_tr == undefined) ){
                input_check_box.attr('checked','checked');
                this_tr.attr('marcado','marcado');
                this_tr.addClass('hover');
            }else{
                input_check_box.removeAttr('checked');
                this_tr.removeAttr('marcado');
                this_tr.removeClass('hover');
            }
            input_check_box.focus();
        }
    );
    grid_tbody_tr.dblclick(
    function(){
        var this_tr = $(this);
        var input_check_box = $(this).find('input');

        var div_dialog_by_id =  $("#dialog-documentos_detalhe");
        value_input_check_box = input_check_box.val();
        input_check_box.attr('checked', 'checked');
        this_tr.attr('marcado','marcado');
        this_tr.addClass('hover');

        if (xhr_abrir_documento) {
            xhr_abrir_documento.abort();
        }

        url = base_url + '/sosti/detalhesolicitacao/detalhesol';
        xhr_abrir_documento = $.ajax({
            url: url,
            dataType: 'html',
            type: 'POST',
            data: value_input_check_box,
            contentType: 'application/json',
            processData: false, 
            beforeSend:function() {
                div_dialog_by_id.dialog("open");
            },
            success: function(data) {
                div_dialog_by_id.html(data);

            },
            complete: function(){

            },
            error : function(){

            }
        });
    }); 
    $("input[type=checkbox][name=input_check_all_grid]").click(
    function(){
        if($(this).attr('checked')){
            $(".nav_check_boxes").attr('checked','checked');
            $("tr[name=rowList]").addClass('hover');
        }else{
            $(".nav_check_boxes").removeAttr('checked');
            $("tr[name=rowList]").removeClass('hover');
        }
    });
    $('input[name=acao]').click(
    function(){
        var acao = this.value;
        var formhelpdesk = $('form[name=helpdesk]');
        if(acao == 'Encaminhar'){
            formhelpdesk.attr('action', base_url + '/sosti/gestaodedemandasti/encaminhar');
        }else if(acao == 'Baixar'){
            formhelpdesk.attr('action', base_url + '/sosti/gestaodedemandasti/baixarcaixa');
        }else if(acao == 'Espera'){
            formhelpdesk.attr('action', base_url + '/sosti/gestaodedemandasti/esperacaixa');
        }
    });
    $('form[name=helpdesk]').submit(
    function(){          
        var solictacaoSelecionada = $("input[type=checkbox][name=solicitacao[]]:checked").val();
        if (solictacaoSelecionada == undefined){ 
            var mensagem = "<div class='notice'><strong>Alerta:</strong> Escolha uma solicitação!</div>";
            $('#flashMessages').html(mensagem);
            return false;
        }
    });
    $('form[name=save]').submit( function() {
        var tamObsGarant = ($('#SSOL_GARANTIA_OBSERVACAO').val()).length;
        if($("input[name='SSOL_FLAG_GARANTIA']").is(':checked') == true){
            $("#SSOL_GARANTIA_OBSERVACAO").show();
            $("#SSOL_GARANTIA_OBSERVACAO-element").show();
            $("#SSOL_GARANTIA_OBSERVACAO-label").show();
            if(tamObsGarant < 5){
                $("#msg").remove();
                $("#SSOL_GARANTIA_OBSERVACAO-element").append("<ul id='msg' class='errors'><li>'' é menor que 5 (tamanho mínimo desse campo)</li>\n\
                    <li>Preenchimento Obrigatório</li>\n\
                    <li>ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.</li></ul>");
            return false;
            }
        } 
    });
    $("#dialog-documentos_detalhe").dialog({
        title    : 'Detalhe',
        autoOpen : false,
        modal    : false,
        show: 'fold',
        hide: 'fold',
        resizable: true,
        width: 800,
        position: [580,140,0,0],
        buttons : {
            Ok: function() {
                $(this).dialog("close");
            }
        }
    });
    $(".historico").hide('');
    });
    $('.MultiFile-wrap').change(function () {
        AcceptableFileUpload();
    });
    function AcceptableFileUpload() {
        var totalsize = 0;
        var qtde = 0;
        $('form[name=save] input:file').each(function () {
            if ($(this).val().length > 0) {
                totalsize = totalsize + $(this)[0].files[0].size;
            }
        });
        qtde = $('form[name=save] input:file').length-1;
        if ((totalsize / 1024 / 1024) > 50) {
            alert('Os arquivos anexados podem ter no Maximo 50 Megas.');
            $('input:file').MultiFile('reset');
            return false;
        } else {
            return true;
        }
        if (qtde > 20) {
            alert('A quantidade de arquivos pode ser no máximo 20.');
            return false;
        } else {
            return true;
        }
    }
    /** Importa os anexos dos sostis escolhidos para gerar a OS */
    $("#importAnex").click( function () {
        var dados = $('#SOLICITACOES_OS').serialize();
        $.ajax({
            url: base_url + '/os/solicitacao/importaranexo',
            type: 'POST',
            data: dados,
            success: function(data) {
                $('#import-anexo').html(data);
            },
            error: function(){
                $("#formCadastroOs").html('<p>Erro ao carregar</p>');
            }
        });
        $("#dialog-import-anexo").dialog({
            resizable: false,
            height:140,
            modal: true,
            width: 800,
            height: 600,
            hide: {
                effect: "explode",
                duration: 500
            },
            position: [300, 140, 0, 0],
            buttons: {
                "Importar": function() {
                    var marcou = $("input[name='import-solicit[]']").is(':checked');
                    var camposMarcados = new Array();
                    $("input[name='import-solicit[]']").each( function() {
                        if ($(this).is(':checked')) {
                            var arrayArq = $(this).val().split('|;');
                            var idArq = arrayArq[0];
                            var nomeArq = arrayArq[1];
                             $('#tr_importar_anexos').after("\
                                \n\<tr class='linha_anexo'>\n\
                                    \n\<td style='width: 5%'>\n\
                                         \n\<a class='remover-parte' rel='"+idArq+"' href='#'>Remover</a>\n\
                                    \n\</td>\n\
                                    \n\<td>"+idArq+" - "+nomeArq+"</td>\n\
                                    \n\<input type='hidden' name='importar_anexos[]' value='"+$(this).val()+"' >\n\
                                \n\</tr>\n\
                           ");
                        }
                    });
                    if (marcou == false) {
                        return false;
                    }
                    $(this).dialog("close");
                },
                Cancelar: function() {
                    
                    $(this).dialog("close");
                }
            }
        });
        /** Remover todos os anexos selecionados */
        $(".removerTodosAnexos").click( function() {
            $('.linha_anexo').remove();
        });   
    });
    
});