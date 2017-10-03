<?php

class Sosti_Form_LabEntradaLicencaSoftware extends Zend_Form {

    public function init() {

        $this->setAction('')
                ->setMethod('post')
                ->setName('CadastroSoftware');

        $marcas = new Application_Model_DbTable_OcsTbMarcMarca();
        $lstMarcas = $marcas->getMarca();

        $lsfw_id_marca = new Zend_Form_Element_Select('LSFW_ID_MARCA');
        $lsfw_id_marca->setLabel('*Marca:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->addMultiOption("", '::Selecione a Marca::');
        foreach ($lstMarcas as $v) {
            $lsfw_id_marca->addMultiOptions(array($v["MARC_ID_MARCA"] => $v["MARC_DS_MARCA"]));
        }

        $lsfw_id_modelo = new Zend_Form_Element_Select('LSFW_ID_MODELO');
        $lsfw_id_modelo->setLabel('*Modelo:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true);
        $lsfw_id_modelo->addMultiOption("",'::Selecione uma Marca Primeiro::');

        $lsfw_ds_software = new Zend_Form_Element_Select('LISW_ID_SOFTWARE');
        $lsfw_ds_software->setRequired(true)
                ->setLabel('*Software:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
//                ->addValidator('NotEmpty')
                ->addValidator('Alnum', false, true)
                ->setAttrib('maxlength', 200)
                ->setAttrib('style', 'width: 500px; ')
                ->setAttrib('StringLength', false, array(5, 200));
        $lsfw_ds_software->addMultiOption("", '::Selecione o Modelo Primeiro::');

        $lisw_qt_licenca = new Zend_Form_Element_Text('LISW_QT_LICENCA');
        $lisw_qt_licenca->setLabel('*Quantidade de Licenças:')
                ->setAttrib('StringLength', false, array(1, 4))
                ->setAttrib('maxlength', 4)
                ->setRequired(true);

        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $this->addElements(array(
            $lsfw_id_marca,
            $lsfw_id_modelo,
            $lsfw_ds_software,
            $lisw_qt_licenca,
            $submit,
            $obrigatorio));
    }

}