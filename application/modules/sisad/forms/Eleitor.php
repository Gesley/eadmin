<?php
class Application_Form_Eleitor extends Zend_Form
{
    public function init()
    {
        $this->setAction('eleitor/save')
             ->setMethod('post');

        $eletElelCodigo = new Zend_Form_Element_Hidden('ELET_ELEL_CODIGO');
        $eletElelCodigo->addFilter('Int')
                   ->removeDecorator('Label')
                   ->removeDecorator('HtmlTag');

    $funcJuiz = new Zend_Form_Element_Select('ELET_FUNC_COD_FUNCIONARIO');
        $funcJuiz->setRequired(true)
                 ->setLabel('Eleitor:')
                 ->addMultiOption('',':: Selecione ::');
                 $juiz = new Application_Model_DbTable_Funcionario();
                 foreach($juiz->fetchAll($juiz
                              ->select()
                              ->from(array('P'=> 'RH_PESSOAL'), array('P.*', 'P.PESS_NOME as PESSOA'))
                              ->setIntegrityCheck(false)  # permite que seja utilizad join no projeto
                              ->join(array('F'=> 'RH_FUNCIONARIO'), 'F.FUNC_PESS_C_P_F = P.PESS_C_P_F')
                              ->where('F.FUNC_SESB_SIGLA_SECAO_SUBSECAO = ?', 'DS')
                              ->where('FUNC_DAT_DESLIG IS NULL')
                              ->order('FUNC_COD_FUNCIONARIO')) as $j) {
                     $funcJuiz->addMultiOption($j->FUNC_COD_FUNCIONARIO, $j->PESS_NOME);
                 }
                    
        $situacao = new Zend_Form_Element_Select('RH_ELEI_ELEITORES');
        $situacao->setRequired(true)
                      ->setLabel('Situação:')
                      ->addMultiOption('',':: Selecione ::');
                      $dados = new Application_Model_DbTable_TipoSituacao();
                      foreach($dados->fetchAll($dados->select()->where('ELTS_ELEITOR_CAND = ?', 'E')) as $d) {
                          $situacao->addMultiOption($d->ELTS_CODIGO, $d->ELTS_DESCRICAO);
                      }
                    
        //echo "Descrição do tipo da Eleição";
        
        $descricao = new Zend_Form_Element_Textarea('ELET_MOTIVO_ALTER_SITUACAO');
        $descricao->setRequired(true)
        		  ->setOptions(array('rows'=>5, 'cols'=>41))
                  ->setLabel('Motivo da Situção:')
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('NotEmpty')
                  ->addValidator('StringLength', false, array(5,500));
             

        $submit = new Zend_Form_Element_Submit('Adicionar');
        $voltar = new Zend_Form_Element_Button('Voltar');
        $voltar->setOptions(array('onclick'=>'javascript:history.go(-1)'));
        
        
        
        $this->addElements(array($eletElelCodigo,$funcJuiz, $situacao, $descricao, $submit, $voltar));

        //$this->setElementDecorators(array('Label','ViewHelper', 'Errors')); # sempre no final do form
    }

}