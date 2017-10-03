<?php
class Sosti_Form_AtendenteCaixa extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
           
        $tbcaixas = new Application_Model_DbTable_SadTbCxenCaixaEntrada();
        $caixas = $tbcaixas->getCaixas(2);
        
        $idCaixa = new Zend_Form_Element_Hidden('ATCX_ID_CAIXA_ENTRADA');
        $idCaixa->setRequired(false);

        $atendente = new Zend_Form_Element_Text('ATENDENTE');
        $atendente->setRequired(false)
                  ->setLabel('Atendente:')
                  ->setOptions(array('style' => 'width:500px'));
        
        $atcx_cd_matricula = new Zend_Form_Element_Hidden('ATCX_CD_MATRICULA');
        $atcx_cd_matricula->setRequired(false);
                
        $atcx_ic_atividade = new Zend_Form_Element_Select('ATCX_IC_ATIVIDADE');
        $atcx_ic_atividade->setRequired(true)
                          ->setLabel('Ativo:')
                          ->addValidator('NotEmpty')
                          ->setRequired(true)
                          ->setMultiOptions(array('S'=>'Sim', 'N'=>'Não')
                          );

        $nome_sistema = new Zend_Form_Element_Hidden('ATCX_NM_SISTEMA');
        $nome_sistema->setRequired(false);
        
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($idCaixa,
                                 $atendente,
                                 $atcx_cd_matricula,
                                 $atcx_ic_atividade, 
                                 $nome_sistema,
                                 $submit));        
    }
}
?>
