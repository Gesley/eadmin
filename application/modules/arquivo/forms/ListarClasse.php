<?php
class Arquivo_Form_ListarClasse extends Zend_Form{
  public function init() {
        $classe= new Zend_Form_Element_Select(
                'AQCL_CD_CLASSE'
                );
        $classe->setRequired(true)
                ->setLabel('Cód - Classe:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addMultiOptions(array('' => 'SELECIONE UM ASSUNTO'));

        $this->addElements(array($id, $classe));
}

  }