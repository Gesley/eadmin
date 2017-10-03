/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function hideALL(){
    $('#OSIS_NM_OCORRENCIA').hide();
    $('#OSIS_NM_OCORRENCIA-element').hide();
    $('#OSIS_NM_OCORRENCIA-label').hide();

    $("#CTSS_NM_CATEGORIA_SERVICO").hide();
    $("#CTSS_NM_CATEGORIA_SERVICO-element").hide();
    $("#CTSS_NM_CATEGORIA_SERVICO-label").hide();

    $("#ASIS_IC_NIVEL_CRITICIDADE").hide();
    $("#ASIS_IC_NIVEL_CRITICIDADE-element").hide();
    $("#ASIS_IC_NIVEL_CRITICIDADE-label").hide();
    
    $("#EMERGENCIAL").hide();
    $("#EMERGENCIAL-element").hide();
    $("#EMERGENCIAL-label").hide();

    $("#SOLIC_PROBLEMAS").hide();
    $("#SOLIC_PROBLEMAS-element").hide();
    $("#SOLIC_PROBLEMAS-label").hide();
    
    $("#CAUSA_PROBLEMA").hide();
    $("#CAUSA_PROBLEMA-element").hide();
    $("#CAUSA_PROBLEMA-label").hide();
    
    $('#CTSS_NM_CATEGORIA_SERVICO').val('');
    
    $("#NEGA_IC_SOLICITA").hide();
    $("#NEGA_IC_SOLICITA-element").hide();
    $("#NEGA_IC_SOLICITA-label").hide();
    $("#GARANTIA_CHECKBOX").hide();
    
    $("#NEGA_DS_JUSTIFICATIVA_PEDIDO").hide();
    $("#NEGA_DS_JUSTIFICATIVA_PEDIDO-element").hide();
    $("#NEGA_DS_JUSTIFICATIVA_PEDIDO-label").hide();
    
}

function showALL(){
    $('#OSIS_NM_OCORRENCIA').show();
    $('#OSIS_NM_OCORRENCIA-element').show();
    $('#OSIS_NM_OCORRENCIA-label').show();

    $("#CTSS_NM_CATEGORIA_SERVICO").show();
    $("#CTSS_NM_CATEGORIA_SERVICO-element").show();
    $("#CTSS_NM_CATEGORIA_SERVICO-label").show();
    
    $("#NEGA_IC_SOLICITA").show();
    $("#NEGA_IC_SOLICITA-element").show();
    $("#NEGA_IC_SOLICITA-label").show();
    $("#GARANTIA_CHECKBOX").show();
    
}


$(function(){
    $('#OSIS_NM_OCORRENCIA').change(
        function(){
            $.ajax({
                url: base_url+'/sosti/gestaodedemandasti/ajaxcategoriaservicobyocorrencia/id_ocorrencia/'+this.value,
                dataType: 'html',
                type: 'POST',
                data: this.value,
                contentType: 'application/json',
                processData: false,
                beforeSend:function() {
                    $("#CTSS_NM_CATEGORIA_SERVICO").removeClass('erroInputSelect');
                    $("#CTSS_NM_CATEGORIA_SERVICO").html('');
                    $("#CTSS_NM_CATEGORIA_SERVICO").addClass('carregandoInputSelect');
                },
                success: function(data) {
                    $("#CTSS_NM_CATEGORIA_SERVICO").html(data);
                    $("#CTSS_NM_CATEGORIA_SERVICO").removeClass('carregandoInputSelect');
                    $("#CTSS_NM_CATEGORIA_SERVICO").focus();
                },
                error: function(){
                    $("#CTSS_NM_CATEGORIA_SERVICO").removeClass('x-form-field');
                    $("#CTSS_NM_CATEGORIA_SERVICO").val('Erro ao carregar.');
                    $("#CTSS_NM_CATEGORIA_SERVICO").addClass('erroInputSelect');
                    $("#CTSS_NM_CATEGORIA_SERVICO").html('<option>Erro ao carregar</option>');
                }
            });  
            $("#CAUSA_PROBLEMA").hide();
            $("#CAUSA_PROBLEMA-element").hide();
            $("#CAUSA_PROBLEMA-label").hide();
        });
        
    $('#CTSS_NM_CATEGORIA_SERVICO').change(
        function(){
            $("#SOLIC_PROBLEMAS").hide();
            $("#SOLIC_PROBLEMAS-element").hide();
            $("#SOLIC_PROBLEMAS-label").hide();
          if(this.value == '2'){
              $("#CAUSA_PROBLEMA").show();
              $("#CAUSA_PROBLEMA-element").show();
              $("#CAUSA_PROBLEMA-label").show();
          }else{
              $("#CAUSA_PROBLEMA").hide();
              $("#CAUSA_PROBLEMA-element").hide();
              $("#CAUSA_PROBLEMA-label").hide();
          }
          
          if(this.value == '2'){
              $("#ASIS_IC_NIVEL_CRITICIDADE").show();
              $("#ASIS_IC_NIVEL_CRITICIDADE-element").show();
              $("#ASIS_IC_NIVEL_CRITICIDADE-label").show();
          }else{
              $("#ASIS_IC_NIVEL_CRITICIDADE").hide();
              $("#ASIS_IC_NIVEL_CRITICIDADE-element").hide();
              $("#ASIS_IC_NIVEL_CRITICIDADE-label").hide();
          }
          
           $("#EMERGENCIAL:checkbox").checked = false;
           $("#EMERGENCIAL:checkbox").attr("checked", false);
           $("#ASIS_IC_NIVEL_CRITICIDADE").val('');
           $("#SOLIC_PROBLEMAS").val('');
           
           $("#CAUSA_PROBLEMA-1:radio").checked = false;
           $("#CAUSA_PROBLEMA-1:radio").attr("checked", false);
           $("#CAUSA_PROBLEMA-2:radio").checked = true;
           $("#CAUSA_PROBLEMA-2:radio").attr("checked", true);
           
          if(this.value == '1' || this.value == '7' || this.value == '8'|| this.value == '2'|| this.value == ''){
              $("#EMERGENCIAL").hide();
              $("#EMERGENCIAL-element").hide();
              $("#EMERGENCIAL-label").hide();
          }else{
              $("#EMERGENCIAL").show();
              $("#EMERGENCIAL-element").show();
              $("#EMERGENCIAL-label").show();
          }
          
        });
        
        $('input[type=radio][name=CAUSA_PROBLEMA]').click(
        function(){
            if(this.value == 1){
                $("#SOLIC_PROBLEMAS").show();
                $("#SOLIC_PROBLEMAS-element").show();
                $("#SOLIC_PROBLEMAS-label").show();
            }else{
                $("#SOLIC_PROBLEMAS").hide();
                $("#SOLIC_PROBLEMAS-element").hide();
                $("#SOLIC_PROBLEMAS-label").hide();
            }          
        })
})
