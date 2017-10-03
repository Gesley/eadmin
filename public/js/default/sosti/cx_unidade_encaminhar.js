/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(function(){
    var aux_button_style;
    var verificaSubmissao = true;

    $("#POST_CD_PESSOA_DESTINO").autocomplete({
        //source: "sosti/solicitacao/ajaxnomesolicitante",
        source: base_url+"/sisad/caixaunidade/ajaxnomedestinatario",
        minLength: 3,
        delay: 300
    });
        
    $("select#MODE_CD_SECAO_UNID_DESTINO").removeAttr('disabled');
    $("select#SECAO_SUBSECAO").change(
        function () {
            var secao = $(this).val().split('|')[0];
            var lotacao = $(this).val().split('|')[1];
            var tipolotacao = $(this).val().split('|')[2];
            $.ajax({
                url: base_url + '/sisad/caixaunidade/ajaxunidadebysecao/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                dataType : 'html',
                beforeSend:function() {
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).removeAttr('disabled','disabled');
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).attr('value','');
                    $( "#combobox-input-button-MODE_CD_SECAO_UNID_DESTINO" ).removeAttr('disabled','disabled');
                
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).removeClass('erroInputSelect');
                    $('select#MODE_CD_SECAO_UNID_DESTINO').html('');
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).addClass('carregandoInputSelect');
                    $( "#combobox-input-button-MODE_CD_SECAO_UNID_DESTINO" ).attr('style',aux_button_style+' z-index: -1000;');
                },
                success: function(data) {
                        
                    $('select#MODE_CD_SECAO_UNID_DESTINO').html(data);
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).removeClass('carregandoInputSelect');
                    $( "#combobox-input-button-MODE_CD_SECAO_UNID_DESTINO" ).attr('style',aux_button_style+' z-index: 0;');
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).focus();
                    init_combobox_app_jquery();
                },
                error: function(){
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).removeClass('x-form-field');
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).val('Erro ao carregar.');
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).addClass('erroInputSelect');
                    $( "#combobox-input-button-MODE_CD_SECAO_UNID_DESTINO" ).attr('style',aux_button_style+' z-index: 0;');
                    $('select#MODE_CD_SECAO_UNID_DESTINO').html('<option>Erro ao carregar</option>');
                }
            });
        });
    $("select#MODE_SG_SECAO_UNID_DESTINO").change(
        function () {
            var secao = $(this).val().split('|')[0];
            var lotacao = $(this).val().split('|')[1];
            var tipolotacao = $(this).val().split('|')[2];
            $.ajax({
                url: base_url + '/sisad/caixaunidade/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                dataType : 'html',
                beforeSend:function() {
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).attr('value','');
                    $( "#combobox-input-button-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('erroInputSelect');
                    $('select#SECAO_SUBSECAO').html('');
                    $('select#MODE_CD_SECAO_UNID_DESTINO').html('');
                    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).attr('value','');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).addClass('carregandoInputSelect');
                    $( "#combobox-input-button-SECAO_SUBSECAO" ).attr('style',aux_button_style+' z-index: -1000;');
                },
                success: function(data) {
                        
                    $('select#SECAO_SUBSECAO').html(data);
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('carregandoInputSelect');
                    $( "#combobox-input-button-SECAO_SUBSECAO" ).attr('style',aux_button_style+' z-index: 0;');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).focus();
                    init_combobox_app_jquery();
                },
                error: function(){
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('x-form-field');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).val('Erro ao carregar.');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).addClass('erroInputSelect');
                    $( "#combobox-input-button-SECAO_SUBSECAO" ).attr('style',aux_button_style+' z-index: 0;');
                    $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
                }
            });
        });
       
    $('#ANEXOS').MultiFile({
        STRING: {
            file: '<em title="Clique para Remover" onclick="$(this).parent().prev().click()">$file</em>',
            remove: '<img class="excluirpdf" title="Clique para Remover" height="16" width="16"/>'
        }
    });
       
        
    $('input[name=tipoAcao]').click(function(){
        var acao = this.value;
        var $formulario = $('form[name=encaminhamento]');
        if(acao == 'Salvar'){
              
            if( $("#MODE_SG_SECAO_UNID_DESTINO").val() == ""){
                alert("É necessário escolher o Destino: TRF1/Seção.");
                return false;
            }
            if( $("#SECAO_SUBSECAO").val() == ""){
                alert("É necessário escolher o Destino: Seção/Subseção.");
                return false;
            }
            if( $("#MODE_CD_SECAO_UNID_DESTINO").val() == ""){
                alert("É necessário escolher a Unidade de Destino.");
                return false;
            }
            if( $("#MOFA_DS_COMPLEMENTO").val() == ""){
                alert("É necessário preencher a Descrição do Encaminhamento.");
                return false;
            }
            if(verificaSubmissao==true){
                $formulario.attr('action',base_url+'/sisad/caixaunidade/encaminhar');
                document.encaminhamento.submit();
            }else{
                $formulario.submit(function(){
                    return false;
                });
            }
        }
    });
});
    
$(function(){
    $( "select#MODE_CD_SECAO_UNID_DESTINO" ).combobox();
    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).attr('style','width: 500px;');
                
    /*$('#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO').autocomplete({
                    
        select: function(event, ui) {
            var $sg_secao = ui.item.option.value.split('|')[0];
            var $cd_secao = ui.item.option.value.split('|')[1];
                        
            $('.documentos').css("color",'#000');
            $(".msgInfo").css('display','none');
            $('.documentos').each(function(index, ui){
                           
                var $data = $(this).data('options');
                if($data.existe.restricao == '1'){
                                
                    var $unidadeEscolhida = $sg_secao+"-"+$cd_secao;
                                
                    if( $.inArray($unidadeEscolhida, $data.existe.unidades) == -1 ){
                        $(this).css("color",'#CCC');
                        $(".msgInfo").css('display','block').html("<strong>Atenção:</strong> Os documentos que foram desabilitados n&atilde;o ser&atilde;o encaminhados pois não possuem a Unidade de destino cadastrada com vistas.");
                    }
                }
                            
            });
                        
            $( "#MODE_CD_SECAO_UNID_DESTINO" ).attr('value',ui.item.option.value);
                    
            $.ajax({
                url: base_url + '/sisad/caixaunidade/ajaxverificapessoaunidade/',
                type: "POST",
                data: { 
                    sg_secao: ui.item.option.value.split('|')[0],
                    cd_secao: ui.item.option.value.split('|')[1]
                },
                dataType: "json",
                success: function(data) {
                    // console.log(data.status);

                    if(data.status == true){
                        verificaSubmissao = true;
                        $('#flashMessagesView').html("");
                    }else{
                        //alert('entrou false');
                        verificaSubmissao = false;
                        //$('#flashMessagesView').html("");
                        $('#flashMessagesView').html("<div class='notice'><strong>Atenção: </strong> "+data.mensagem+"</div>");
                        $('html, body').scrollTop(0);
                    }
                }
                ,
                error: function(e, jqxhr, settings){
                    alert(settings);
                    $('html, body').scrollTop(0);
                }
            });
        }
    });*/
                
                
    aux_button_style =  $( "#combobox-input-button-MODE_CD_SECAO_UNID_DESTINO" ).attr('style');
    $( "#combobox-input-button-MODE_CD_SECAO_UNID_DESTINO" ).attr('style',aux_button_style+' left: -20px; top: 5px;');
    aux_button_style =  $( "#combobox-input-button-MODE_CD_SECAO_UNID_DESTINO" ).attr('style');
    $( "#combobox-input-text-MODE_CD_SECAO_UNID_DESTINO" ).attr('value','');

});

/* Função para controlar o aparecimento de campos quando selecionamos o Tipo de Movimentação*/
$(function(){
    $('#TIPO_MOVIMENTACAO-internaunidade').attr('checked','checked');
    $("div#internalista").hide();        
    if( $('#TIPO_MOVIMENTACAO-internaunidade').is(':checked') == true){
        $("div#internaunidade").show();
        $("div#input_anexo").show();
        $("input[type=hidden][name=acao]").val('EncaminhamentoInterno');
        $("div#internalista").hide();
    }else if ( $('#TIPO_MOVIMENTACAO-internalista').is(':checked') == true) {
        $("div#internalista").show();
        $("input[type=hidden][name=acao]").val('EncaminhamentoInternoLista');
        $("div#internaunidade").hide();
        $("div#input_anexo").hide();
    } 
    $('input[type=radio][name=TIPO_MOVIMENTACAO]').click(
        function(){
            if(this.value == 'internaunidade'){
                $("input[type=hidden][name=acao]").val('EncaminhamentoInterno');
                $("div#internaunidade").show();
                $("div#input_anexo").show();
                $("div#internalista").hide();
                
            }else if (this.value == 'internalista'){
                $("input[type=hidden][name=acao]").val('EncaminhamentoInternoLista');
                $("div#internalista").show();
                $("div#internaunidade").hide();
                $("div#input_anexo").hide();
            }
        });
});

