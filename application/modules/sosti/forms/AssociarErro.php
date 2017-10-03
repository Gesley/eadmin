<?php
class Sosti_Form_AssociarErro extends Zend_Form
{
    public function init()
    {
//        $objModelDefeito = new Application_Model_DbTable_SosTbTideTipoDefeito ();
//        $this->setAction('')
//             ->setMethod('post');
//
//        $DEFEITOS = new Zend_Form_Element_Select('DEFEITOS');
//        $DEFEITOS->isArray();
//        $DEFEITOS->setLabel('Seleicone o erro para adicionar na(s) solicitação(ções)');
//        $DEFEITOS->setRequired(true);
//        $rows = $objModelDefeito->fetchAll(array('TIDe_IC_ATIVO=?'=>'S'),array('TIDE_NM_DEFEITO'));
//        
//        foreach ($rows as $value) {
//            $DEFEITOS->addMultiOptions(array($value['TIDE_ID_TIPO_DEFEITO_SISTEMA']=>$value['TIDE_NM_DEFEITO']));
//        } 
//        
//        $TIDE_NM_DEFEITO = new Zend_Form_Element_Multiselect('TIDE_NM_DEFEITO');
//        $TIDE_NM_DEFEITO->setLabel('Selecione os defeitos para remover');
//        $TIDE_NM_DEFEITO->setRequired(false);
//        
//        
//        
//        $ACAO = new Zend_Form_Element_Hidden('acao');
//        $ACAO->setvalue('associar');
        
        $mdsi_nr_defeitos = new Zend_Form_Element_Text('MDSI_NR_DEFEITOS');
        $mdsi_nr_defeitos->setLabel('Quantidade de erros:')
                        ->setAttribs(array('style'=>'width:15px'))
                        ->setValue('0')
                        ->setValidators('Int')
                        ->setRequired()
                        ;
        
        $comentario_defeito = new Zend_Form_Element_Textarea('COMENTARIO_DEFEITO');
        $comentario_defeito->setLabel('Descrição dos Erros:')
                               ->setRequired(false)
                               ->setValue(' ')
                               ->setAttribs(array('style'=>'height:100px','width:250px'))
                               ;
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setvalue('associar');
        
        $submit = new Zend_Form_Element_Submit('Salvar');
        
        $this->addElements(array($mdsi_nr_defeitos,$comentario_defeito,$acao,$submit));
    }
}