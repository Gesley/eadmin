$(function() {
  
     $("select#TRF1_SECAO").change(
            function () {
                    var secao = $(this).val().split('|')[0];
                    var lotacao = $(this).val().split('|')[1];
                    
                    $('#DOCM_CD_LOTACAO_GERADORA').val('');
                    
                    
                $.ajax({
                    url: base_url + '/guardiao/unidadeperfil/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao,
                    dataType : 'html',
                    beforeSend:function() {
                        $('select#SECAO_SUBSECAO').removeAttr('disabled','disabled').html('');
                        $( "#combobox-input-text-SECAO_SUBSECAO" ).removeAttr('disabled','disabled').attr('value','').removeClass('erroInputSelect').addClass('carregandoInputSelect');
                        $( "#combobox-input-button-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                    },
                    success: function(data) {
                        
                        $('select#SECAO_SUBSECAO').html(data);
                        $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('carregandoInputSelect').focus();
                        init_combobox_app_jquery();
                    },
                    error: function(){
                        $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('x-form-field').val('Erro ao carregar.').addClass('erroInputSelect');
                        $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
                    }
                });
            });
            
           
           
     $("select#SECAO_SUBSECAO").change(
            function () {
                
                    var $dados = $(this).val().split("|");
                    
                    $("#DOCM_CD_LOTACAO_GERADORA").autocomplete({
                            source: base_url+'/sisad/partes/ajaxunidade/sigla/'+$dados[0]+'/cod/'+$dados[1],
                            minLength: 3,
                            delay: 100
                    });
                    
                
            });       
         
            //$('#DATA_INICIAL').datepicker();
            //$('#DATA_FINAL').datepicker();
            
            $("#novaPesquisa").click( function(){
                $("#pesq_div").show();
                $("#container_pagination").hide();
            });
            
            $("form[name='pesquisarsolicitacoes']").submit( function(){
               
               if( $("#TRF1_SECAO").val() == "0"  ){
                   alert('É necessario escolher TRF ou Seção.');
                   return false;
               }
               
               if( $("#SECAO_SUBSECAO").val() == ""  ){
                   alert('É necessario escolher uma Seção ou Subseção.');
                   return false;
               }
               
               if( $("#DATA_INICIAL").val() == ""  ){
                   alert('É necessario escolher uma Data Inicial.');
                   return false;
               }
               
               if( $("#DATA_FINAL").val() == ""  ){
                   alert('É necessario escolher uma Data Final.');
                   return false;
               }
                             
               return true;
               
            });
            
       
     
 });

