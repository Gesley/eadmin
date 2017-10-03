<?php

class Sosti_Form_Labgrupo extends Zend_Form
{

    public function init()
    {
        $this->setAction('')
                ->setMethod('post');
        $objMarca = new Application_Model_DbTable_OcsTbMarcMarca();
        $dataMarca = $objMarca->getMarcaCheckBox();

        $grupo_descrição = new Zend_Form_Element_Text('GRUP_DS_GRUPO_MAT_SERV');
        $grupo_descrição->setLabel('*Descrição do Grupo:')
                ->addFilter('StripTags')
                ->setAttrib('maxLength', 60)
                ->addValidator('StringLength', false, array(5, 60))
                ->addValidator('NotEmpty')
                ->setRequired(true)
                ->setAttrib('size', 80);

        $hiddenGRUP_ID_GRUPO_MAT_SERV = new Zend_Form_Element_Hidden('GRUP_ID_GRUPO_MAT_SERV');

        $checkBoxes = new Zend_Form_Element_MultiCheckbox('GRMA_ID_MARCA', array(
                    'multiOptions' => $dataMarca,
                    'separator' => PHP_EOL
                ));
        
        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');

        $this->addElements(array($grupo_descrição, $checkBoxes, $hiddenGRUP_ID_GRUPO_MAT_SERV, $submit, $obrigatorio));
    }

}