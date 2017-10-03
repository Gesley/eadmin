<?php
class Sosti_Form_DevolucaoTombo extends Zend_Form
{
    public function init()
    {
    	
    	$this->setAction('');
        $this->setMethod('post');

       	$DOCM_NR_DOCUMENTO = new Zend_Form_Element_Text('DOCM_NR_DOCUMENTO');
		$DOCM_NR_DOCUMENTO->setAttrib('readonly', 'readonly');
		$DOCM_NR_DOCUMENTO->setLabel('Número da Solicitação:');
		$DOCM_NR_DOCUMENTO->setAttrib('size', 40);
		
       	$LSBK_ID_DOCUMENTO = new Zend_Form_Element_Hidden('LSBK_ID_DOCUMENTO');

		$LSBK_NR_TOMBO = new Zend_Form_Element_Hidden('LSBK_NR_TOMBO');
		$LSBK_NR_TOMBO->setAttrib('readonly', 'readonly')
						->removeDecorator('Label')
						->removeDecorator('HtmlTag');
		
		$LSBK_TP_TOMBO = new Zend_Form_Element_Hidden('LSBK_TP_TOMBO');
		$LSBK_TP_TOMBO->setAttrib('readonly', 'readonly')
					  ->removeDecorator('Label')
					  ->removeDecorator('HtmlTag');
		
		$LSBK_DT_EMPRESTIMO = new Zend_Form_Element_Text('LSBK_DT_EMPRESTIMO');
		$LSBK_DT_EMPRESTIMO->setAttrib('readonly', 'readonly');
		$LSBK_DT_EMPRESTIMO->setLabel('Data do Empréstimo:');
		
		$LSBK_RECEBIMENTO_USUARIO = new Zend_Form_Element_Text('LSBK_DT_RECEBIMENTO_USUARIO');
		$LSBK_RECEBIMENTO_USUARIO->setAttrib('readonly', 'readonly');
		$LSBK_RECEBIMENTO_USUARIO->setLabel('Data do Recebimento do Usuário:');
		
		$LSBK_DT_RECEBIMENTO_DEVOLUCAO = new Zend_Form_Element_Text('LSBK_DT_RECEBIMENTO_DEVOLUCAO');
		$LSBK_DT_RECEBIMENTO_DEVOLUCAO->setLabel('Data da Devolução:');
		$LSBK_DT_RECEBIMENTO_DEVOLUCAO->setRequired(true);
		
		$DT_RECEBIMENTO_DEVOLUCAO = new Zend_Form_Element_Hidden('DT_RECEBIMENTO_DEVOLUCAO');
		$DT_RECEBIMENTO_DEVOLUCAO->setLabel('Data da Devolução:')
									  ->removeDecorator('Label')
					  				  ->removeDecorator('HtmlTag');
		
		$LSBK_CD_MAT_EMPRESTIMO = new Zend_Form_Element_Text('LSBK_CD_MAT_EMPRESTIMO');
		$LSBK_CD_MAT_EMPRESTIMO->setAttrib('readonly', 'readonly');
		$LSBK_CD_MAT_EMPRESTIMO->setLabel('Matrícula Empréstimo:');
		
		$LSBK_CD_MAT_RECEB_USUARIO = new Zend_Form_Element_Text('LSBK_CD_MAT_RECEB_USUARIO');
		
		$LSBK_CD_MAT_RECEB_USUARIO->setLabel('Dmatrícula Recebimento Usuário:');
		
		$LSBK_CD_MAT_RECEB_USUARIO = new Zend_Form_Element_Text('LSBK_CD_MAT_RECEB_DEVOLUCAO');
		
		$LSBK_CD_MAT_RECEB_USUARIO->setLabel('Matrícula Recebimento Devolução:');
		
		
		
       	$submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($DOCM_NR_DOCUMENTO, $LSBK_NR_TOMBO, $LSBK_TP_TOMBO,$LSBK_DT_EMPRESTIMO,$LSBK_RECEBIMENTO_USUARIO,$LSBK_DT_RECEBIMENTO_DEVOLUCAO,$LSBK_CD_MAT_EMPRESTIMO,
                                  $submit,$LSBK_ID_DOCUMENTO,$DT_RECEBIMENTO_DEVOLUCAO));
    }

}