<?php

class Sosti_Form_LabEntradaMaterial extends Zend_Form {

    public function init() {

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
        $getLotacao = $rh_central->getLotacao();

        $this->setAction('')
                ->setMethod('post')
                ->setName('EntradaMaterial');

        $mten_id_hardware = new Zend_Form_Element_Hidden('MTEN_ID_HARDWARE');
        $mten_id_hardware->removeDecorator('label');

        $lhdw_ds_hardware = new Zend_Form_Element_Text('LHDW_DS_HARDWARE');
        $lhdw_ds_hardware->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setDescription('A lista será carregada após digitar no mínimo 3 caracteres. Poderá pesquisar pelo nome ou número do material.')
                ->setLabel('*Hardware:');
        
        $lhdw_ds_hardware_aux = new Zend_Form_Element_Hidden('LHDW_DS_HARDWARE_AUX');
        $lhdw_ds_hardware_aux->setRequired(true)
                ->removeDecorator('Errors');
        
        $mten_ds_hardware = new Zend_Form_Element_Text('MTEN_DS_OBSERVACAO');
        $mten_ds_hardware->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setLabel('*Observação sobre o Hardware:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('maxLength', 200)
                ->addValidator('StringLength', false, array(5, 200));

        $mten_cd_marca = new Zend_Form_Element_Hidden('MTEN_CD_MARCA');
        $mten_cd_marca->removeDecorator('label');

        $mten_ds_marca = new Zend_Form_Element_Text('MTEN_DS_MARCA');
        $mten_ds_marca->setLabel('*Marca:')
                ->setAttrib('style', 'width: 490px; ')
                ->setRequired(true)
                ->setDescription('A lista será carregada após digitar no mínimo 2 caracteres.');

        $mten_cd_modelo = new Zend_Form_Element_Select('MTEN_CD_MODELO');
        $mten_cd_modelo->setLabel('*Modelo:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->addMultiOptions(array("" => "::Escolha uma Marca acima::"));

        $mten_nr_requisicao = new Zend_Form_Element_Text('MTEN_NR_REQUISICAO_MATERIAL');
        $mten_nr_requisicao->setRequired(false)
                ->setLabel('Número da Requisição:')
                ->setAttrib('style', 'width: 500px; ')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('Alnum', false, true)
                ->setAttrib('maxLength', 10)
                ->addValidator('StringLength', false, array(0, 10))
                ->setDescription('Número da requisição, se houver.');

        $validaNumeros = new Zend_Validate_Digits();
        $mten_qt_entrada_material = new Zend_Form_Element_Text('MTEN_QT_ENTRADA_MATERIAL');
        $mten_qt_entrada_material->setRequired(true)
                ->setLabel('*Quantidade de entrada:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
//                ->addValidator('NotEmpty')
                ->setAttrib('style', 'width: 500px; ')
                ->addValidator('Alnum', false, true)
                ->addValidator($validaNumeros)
                ->setAttrib('maxLength', 5)
                ->addValidator('StringLength', false, array(0, 5));


        $lhdw_ds_obs = new Zend_Form_Element_Text('LHDW_DS_OBSERVACAO');
        $lhdw_ds_obs->setLabel('*Observação da entrada:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->setDescription('Observação sobre a entrada do material.');

        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1_secao->setLabel('*TRF1/Seção:')
                ->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->addMultiOptions(array('' => ''));
        foreach ($secao as $v) {
            $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"] . '|' . $v["LOTA_COD_LOTACAO"] . '|' . $v["LOTA_TIPO_LOTACAO"] => $v["LOTA_DSC_LOTACAO"]));
        }

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('*Seção/Subseção:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->addMultiOptions(array('' => 'Primeiro escolha a TRF1/Seção'));

        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $this->addElements(array(
            $lhdw_ds_hardware_aux,
            $trf1_secao,
            $secao_subsecao,
            $lhdw_ds_obs,
            $mten_cd_marca,
            $mten_ds_marca,
            $mten_cd_modelo,
            $mten_qt_entrada_material,
            $mten_nr_requisicao,
            $mten_id_hardware,
            $mten_ds_hardware,
            $lhdw_ds_hardware,
            $submit, 
            $obrigatorio));
    }

}