<?php

class Sosti_Form_LabCadastroHardware extends Zend_Form {

    public function init() {

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();

        $this->setAction('')
                ->setMethod('post')
                ->setName('CadastroHardware');

        $lhdw_id_hardware = new Zend_Form_Element_Hidden('LHDW_ID_HARDWARE');
        $lhdw_id_hardware->removeDecorator('label');

        $lhdw_ds_hardware = new Zend_Form_Element_Text('LHDW_DS_HARDWARE');
        $lhdw_ds_hardware->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setLabel('*Descrição:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setDescription('Pode-se usar a descrição pré-estabelecida ou informar uma nova.')
                ->addValidator('StringLength', false, array(5, 500));

        $lhdw_cd_material = new Zend_Form_Element_Text('LHDW_CD_MATERIAL');
        $lhdw_cd_material->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setLabel('*Código do Material:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
//                ->addValidator('NotEmpty')
                ->addValidator('Alnum', false, true)
                ->setDescription('O campo será carregado após digitar 5 caracteres. Pesquise pelo código ou nome do material e selecione uma opção da lista.')
                ->addValidator('StringLength', false, array(5, 500));

        $lhdw_cd_marca = new Zend_Form_Element_Hidden('LHDW_CD_MARCA');
        $lhdw_cd_marca->removeDecorator('label');

        $lhdw_ds_marca = new Zend_Form_Element_Text('LHDW_DS_MARCA');
        $lhdw_ds_marca->setLabel('*Marca:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->setDescription('A lista será carregada após digitar no mínimo 2 caracteres. Selecione uma opção da lista.');

        $lhdw_cd_modelo = new Zend_Form_Element_Select('LHDW_CD_MODELO');
        $lhdw_cd_modelo->setLabel('*Modelo:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->addMultiOptions(array("" => "::Escolha uma Marca acima::"));
        
        $tpUsuario = new Application_Model_DbTable_SosTbLtpuTipoUsuario();
        $rowstpUsuario = $tpUsuario->fetchAll();
        $lhdw_id_tipo_usuario = new Zend_Form_Element_Select('LHDW_ID_TP_USUARIO');
        $lhdw_id_tipo_usuario->setLabel('Tipo de Usuário:')
                ->setAttrib('style', 'width: 500px; ')
                ->addMultiOption('', ':: Selecione ::');
        foreach ($rowstpUsuario as $v) {
            $lhdw_id_tipo_usuario->addMultiOptions(array($v["LTPU_ID_TP_USUARIO"] => $v["LTPU_DS_TP_USUARIO"]));
        }
        //::1 

        $lhdw_nr_processo = new Zend_Form_Element_Text('LHDW_NR_PROCESSO');
        $lhdw_nr_processo->setRequired(false)
                ->setAttrib('style', 'width: 500px; ')
                ->setLabel('Número Processo:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('Alnum', false, true)
                ->addValidator('StringLength', false, array(5, 200))
                ->setDescription('Número do processo associado ao hardware ou e-Sosti.');
        $mten_nr_requisicao = new Zend_Form_Element_Text('MTEN_NR_REQUISICAO_MATERIAL');
        $mten_nr_requisicao->setRequired(false)
                ->setLabel('Número da Requisição:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('Alnum', false, true)
                ->addValidator('StringLength', false, array(0, 10))
                ->setDescription('Número da requisição, se houver.');

        $lfhw_qt_material_almox = new Zend_Form_Element_Text('MTEN_QT_ENTRADA_MATERIAL');
        $lfhw_qt_material_almox->setRequired(false)
                ->setLabel('Saldo Atual:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('style', 'width: 50px; ')
                ->addValidator('Alnum', false, true)
                ->addValidator('StringLength', false, array(0, 5));


        $lhdw_ds_obs = new Zend_Form_Element_Text('LHDW_DS_OBSERVACAO');
        $lhdw_ds_obs->setLabel('Observação:')
                ->setAttrib('style', 'width: 500px; ')
                ->setDescription('Observação sobre o Hardware sendo cadastrado.');

        $lhdw_nr_serie = new Zend_Form_Element_Text('LHDW_NR_SERIE');
        $lhdw_nr_serie->setLabel('Número de Série:')
                ->addValidator('StringLength', false, array(0, 10))
                ->setAttrib('style', 'width: 500px; ');


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
                ->setAttrib('style', 'display: none');

        $this->addElements(array(
            $lhdw_id_hardware,
            $trf1_secao,
            $secao_subsecao,
            $lhdw_cd_material,
            $lhdw_ds_hardware,
            $lhdw_cd_marca,
            $lhdw_ds_marca,
            $lhdw_cd_modelo,
            $lhdw_id_tipo_usuario,
            $lhdw_ds_obs,
            $lhdw_nr_serie,
            $lhdw_nr_processo,
            $submit, $obrigatorio));
    }

}