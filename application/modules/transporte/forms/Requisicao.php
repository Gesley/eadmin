<?php
class Transporte_Form_Requisicao extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $requ_id_requisicao = new Zend_Form_Element_Hidden('REQU_ID_REQUISICAO');
        $requ_id_requisicao->addFilter('Int')
                     ->removeDecorator('Label')
                     ->removeDecorator('HtmlTag');   
        
//        $requ_aa_requisicao = new Zend_Form_Element_Text('REQU_AA_REQUISICAO');
//        $requ_aa_requisicao//->setRequired(true)
//                     ->setLabel('Ano:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
//        
//        $requ_mm_requisicao = new Zend_Form_Element_Text('REQU_MM_REQUISICAO');
//        $requ_mm_requisicao//->setRequired(true)
//                     ->setLabel('Mês:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));       
        
        $requ_sg_secao = new Zend_Form_Element_Select('REQU_SG_SECAO');
        $requ_sg_secao->setRequired(true)
                 ->setLabel('Seção:');
        foreach ($getUf as $ufs):
            $estado->addMultiOptions(array($ufs["CAP_UF"] => $ufs["UF_NOME"]));
        endforeach;                
        
        $requ_cd_lotacao = new Zend_Form_Element_Select('REQU_CD_LOTACAO');
        $requ_cd_lotacao->setRequired(true)
                 ->setLabel('Lotação:');
        foreach ($getUf as $ufs):
            $estado->addMultiOptions(array($ufs["CAP_UF"] => $ufs["UF_NOME"]));
        endforeach;         
        
        $requ_nr_fone_usuario = new Zend_Form_Element_Text('REQU_NR_FONE_USUARIO');
        $requ_nr_fone_usuario//->setRequired(true)
                     ->setLabel('Telefone:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(5, 100));
        
//        $requ_id_veiculo_requisicao = new Zend_Form_Element_Text('REQU_ID_VEICULO_REQUISICAO');
//        $requ_id_veiculo_requisicao//->setRequired(true)
//                     ->setLabel('Veiculo LIST:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
//        
//        $requ_cd_matr_motorista = new Zend_Form_Element_Text('REQU_CD_MATR_MOTORISTA');
//        $requ_cd_matr_motorista//->setRequired(true)
//                     ->setLabel('Motorista LIST:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
        
        $requ_ic_tp_requisicao = new Zend_Form_Element_Text('REQU_IC_TP_REQUISICAO');
        $requ_ic_tp_requisicao//->setRequired(true)
                     ->setLabel('Tipo Requisição:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(5, 100));
        
        $requ_ds_itinerario = new Zend_Form_Element_Text('REQU_DS_ITINERARIO');
        $requ_ds_itinerario//->setRequired(true)
                     ->setLabel('Descrição Itinerário:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->setOptions(array('style' => 'width: 550px'))
                     ->addValidator('StringLength', false, array(5, 100));
        
        $requ_ds_natureza_justificativa = new Zend_Form_Element_Text('REQU_DS_NATUREZA_JUSTIFICATIVA');
        $requ_ds_natureza_justificativa//->setRequired(true)
                     ->setLabel('Natureza ou Justificativa:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->setOptions(array('style' => 'width: 550px'))
                     ->addValidator('StringLength', false, array(5, 100));
        
//        $requ_ds_ocorrencia = new Zend_Form_Element_Text('REQU_DS_OCORRENCIA');
//        $requ_ds_ocorrencia//->setRequired(true)
//                     ->setLabel('Descrição Ocorrência:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
        
        $requ_ic_avs = new Zend_Form_Element_Text('REQU_IC_AVS');
        $requ_ic_avs//->setRequired(true)
                     ->setLabel('Viagem a Serviço(AVS)?')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(5, 100));    
              
//        $requ_dh_abertura = new Zend_Form_Element_Text('REQU_DH_ABERTURA');
//        $requ_dh_abertura//->setRequired(true)
//                     ->setLabel('Data-Hora Abertura:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
        
//        $requ_dh_saida = new Zend_Form_Element_Text('REQU_DH_SAIDA');
//        $requ_dh_saida//->setRequired(true)
//                     ->setLabel('Data-Hora Saída:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
//        
//        $requ_qt_km_saida = new Zend_Form_Element_Text('REQU_QT_KM_SAIDA');
//        $requ_qt_km_saida//->setRequired(true)
//                     ->setLabel('Quilometragem Saída:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
//        
//        $requ_dh_retorno = new Zend_Form_Element_Text('REQU_DH_RETORNO');
//        $requ_dh_retorno//->setRequired(true)
//                     ->setLabel('Data-Hora Retorno:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
//        
//        $requ_qt_km_retorno = new Zend_Form_Element_Text('REQU_QT_KM_RETORNO');
//        $requ_qt_km_retorno//->setRequired(true)
//                     ->setLabel('Quilometragem Retorno:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
//        
        $requ_dh_utilizacao = new Zend_Form_Element_Text('REQU_DH_UTILIZACAO');
        $requ_dh_utilizacao//->setRequired(true)
                     ->setLabel('Data-Hora Utilização:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(5, 100));
//        
//        $requ_qt_hh_previsto = new Zend_Form_Element_Text('REQU_QT_HH_PREVISTO');
//        $requ_qt_hh_previsto//->setRequired(true)
//                     ->setLabel('Tempo Previsto:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
//        
//        $requ_qt_hh_uso = new Zend_Form_Element_Text('REQU_QT_HH_USO');
//        $requ_qt_hh_uso//->setRequired(true)
//                     ->setLabel('Tempo Utilizado:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
        
//        $requ_cd_matr_user_cadastro = new Zend_Form_Element_Text('REQU_CD_MATR_USER_CADASTRO');
//        $requ_cd_matr_user_cadastro//->setRequired(true)
//                     ->setLabel('Matrícula Usuário:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
        
//        $requ_ic_autorizacao = new Zend_Form_Element_Text('REQU_IC_AUTORIZACAO');
//        $requ_ic_autorizacao//->setRequired(true)
//                     ->setLabel('Autorização:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));
//        
//        $requ_cd_matr_user_autorizador = new Zend_Form_Element_Text('REQU_CD_MATR_USER_AUTORIZADOR');
//        $requ_cd_matr_user_autorizador//->setRequired(true)
//                     ->setLabel('Matrícula Autorizador:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));         
//        
//        $requ_dh_autorizacao = new Zend_Form_Element_Text('REQU_DH_AUTORIZACAO');
//        $requ_dh_autorizacao//->setRequired(true)
//                     ->setLabel('Data-Hora Autorização:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));  
//        
//        $requ_cd_matr_user_baixa = new Zend_Form_Element_Text('REQU_CD_MATR_USER_BAIXA');
//        $requ_cd_matr_user_baixa//->setRequired(true)
//                     ->setLabel('Matrícula Baixa:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));  
//
//        $requ_dh_baixa = new Zend_Form_Element_Text('REQU_DH_BAIXA');
//        $requ_dh_baixa//->setRequired(true)
//                     ->setLabel('Data-Hora Baixa:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
//                     ->addValidator('StringLength', false, array(5, 100));  
        
        

//        $fadm_ic_fase_ativa = new Zend_Form_Element_Radio('FADM_IC_FASE_ATIVA');
//        $fadm_ic_fase_ativa->setLabel('Fase ativa?')
//                           ->setRequired(true)
//                           ->setMultiOptions(array('S'=>'Sim', 'N'=>'Não'));

        

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($requ_aa_requisicao, $requ_mm_requisicao, $requ_id_requisicao,
                                 $requ_sg_secao, $requ_cd_lotacao, $requ_nr_fone_usuario,
                                 $requ_id_veiculo_requisicao, $requ_cd_matr_motorista,
                                 $requ_ic_tp_requisicao, $requ_ds_itinerario,
                                 $requ_ds_natureza_justificativa, $requ_ds_ocorrencia,
                                 $requ_ic_avs, $requ_dh_abertura, $requ_dh_saida,
                                 $requ_qt_km_saida, $requ_dh_retorno, $requ_qt_km_retorno,
                                 $requ_dh_utilizacao, $requ_qt_hh_previsto, $requ_qt_hh_uso,
                                 $requ_cd_matr_user_cadastro, /*$requ_ic_autorizacao,
                                 $requ_cd_matr_user_autorizador, $requ_dh_autorizacao,
                                 $requ_cd_matr_user_baixa, $requ_dh_baixa,*/ $submit));

        //$this->setElementDecorators(array('Label','ViewHelper', 'Errors')); # sempre no final do form
    }
}