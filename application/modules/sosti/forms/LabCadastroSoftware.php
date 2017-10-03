<?php

class Sosti_Form_LabcadastroSoftware extends Zend_Form {

    public function init() {
        $marcas = new Application_Model_DbTable_OcsTbMarcMarca();
        $lstMarcas = $marcas->getMarca();

        $softlst = new Application_Model_DbTable_SosTbLtpsTipoSoftware();
        $tipos_software = $softlst->getTipoSoftware($order);
        
        $sostbltpu = new Application_Model_DbTable_SosTbLtpuTipoUsuario();
        $tipo_usuario = $sostbltpu->getTipoUsuario();

        $this->setAction('')
                ->setMethod('post')
                ->setName('CadastroSoftware');

        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('CadastroSoftware');

        $lsfw_id_software = new Zend_Form_Element_Text('LSFW_ID_SOFTWARE');
        $lsfw_id_software->setLabel('Código:')
                ->setOptions(array('style' => 'width: 50px', 'readonly' => 'readonly'));

        $lsfw_ds_software = new Zend_Form_Element_Text('LSFW_DS_SOFTWARE');
        $lsfw_ds_software->setRequired(true)
                ->setLabel('*Descrição:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
//                ->addValidator('NotEmpty')
//                ->addValidator('Alnum', false, true)
                ->setAttrib('maxlength', 200)
                ->setAttrib('style', 'width: 500px; ')
                ->setAttrib('StringLength', false, array(5, 200));

        $lsfw_id_tp_software = new Zend_Form_Element_Select('LSFW_ID_TP_SOFTWARE');
        $lsfw_id_tp_software->setLabel('*Tipo do Software:')
                ->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->addMultiOption('', '::Selecione::');
        foreach ($tipos_software as $v) {
            $lsfw_id_tp_software->addMultiOptions(array($v["LTPS_ID_TP_SOFTWARE"] => $v["LTPS_DS_TP_SOFTWARE"]));
        }

        $lsfw_id_marca = new Zend_Form_Element_Select('LSFW_ID_MARCA');
        $lsfw_id_marca->setLabel('*Marca:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->addMultiOption('', '::Selecione::');
        foreach ($lstMarcas as $v) {
            $lsfw_id_marca->addMultiOptions(array($v["MARC_ID_MARCA"] => $v["MARC_DS_MARCA"]));
        }

        $lsfw_id_modelo = new Zend_Form_Element_Select('LSFW_ID_MODELO');
        $lsfw_id_modelo->setLabel('*Modelo:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true);
        $lsfw_id_modelo->addMultiOptions(array(' ' => '::Selecione Marca Primeiro::'));
        foreach ($lstMarcas as $v) {
            $lsfw_id_modelo->addMultiOptions(array($v["CO_MODELO"] => $v["DE_MODELO"]));
        }

        $lsfw_dt_aquisicao = new Zend_Form_Element_Text('LSFW_DT_AQUISICAO');
        $lsfw_dt_aquisicao->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setLabel('*Data de Aquisição:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('maxlength', 10)
                ->addValidator('StringLength', false, array(10, 10));

        $lsfw_dt_validade_licenca = new Zend_Form_Element_Text('LSFW_DT_VALIDADE_LICENCA');
        $lsfw_dt_validade_licenca->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setLabel('*Validade da Licença:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('maxlength', 10)
                ->addValidator('StringLength', false, array(10, 10));

        $lsfw_qt_adquirida = new Zend_Form_Element_Text('LSFW_QT_ADQUIRIDA');
        $lsfw_qt_adquirida->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->setLabel('*Quantidade adquirida:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
//                ->addValidator('NotEmpty')
                ->setAttrib('maxLength', 5)
                ->addValidator('StringLength', false, array(0, 5))
                ->addValidator('Alnum', false, true);

        $lsfw_ic_software_livre = new Zend_Form_Element_Select('LSFW_IC_SOFTWARE_LIVRE');
        $lsfw_ic_software_livre->setLabel('*Software Livre:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->addMultiOption('', '::Selecione::')
                ->addMultiOptions(array('S' => 'Sim', 'N' => 'Não'));

        $lsfw_nr_processo_compra = new Zend_Form_Element_Text('LSFW_NR_PROCESSO_COMPRA');
        $lsfw_nr_processo_compra->setLabel('Número do Processo:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_cd_tipo_doc_contrato = new Zend_Form_Element_Text('LSFW_CD_TIPO_DOC_CONTRATO');
        $lsfw_cd_tipo_doc_contrato->setLabel('Tipo do Documento do Contrato:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_nr_aditamento_contrato = new Zend_Form_Element_Text('LSFW_NR_ADITAMENTO_CONTRATO');
        $lsfw_nr_aditamento_contrato->setLabel('Aditamento do Contrato:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_nr_contrato = new Zend_Form_Element_Text('LSFW_NR_CONTRATO');
        $lsfw_nr_contrato->setLabel('Número do Contrato:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_nr_processo_contrato = new Zend_Form_Element_Text('LSFW_NR_PROCESSO_CONTRATO');
        $lsfw_nr_processo_contrato->setLabel('Número do Processo do Contrato:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_nr_termo = new Zend_Form_Element_Text('LSFW_NR_TERMO');
        $lsfw_nr_termo->setLabel('Número do Termo:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_aa_termo = new Zend_Form_Element_Text('LSFW_AA_TERMO');
        $lsfw_aa_termo->setLabel('AA do Termo:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_cd_tipo_termo = new Zend_Form_Element_Text('LSFW_CD_TIPO_TERMO');
        $lsfw_cd_tipo_termo->setLabel('Tipo do Termo:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_nr_tombo = new Zend_Form_Element_Text('LSFW_NR_TOMBO');
        $lsfw_nr_tombo->setLabel('Número do Tombo:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_sg_tombo = new Zend_Form_Element_Text('LSFW_SG_TOMBO');
        $lsfw_sg_tombo->setLabel('Tipo do Tombo:')
                ->setAttrib('style', 'width: 500px; ');

        $lsfw_id_tp_usuario = new Zend_Form_Element_Select('LSFW_ID_TP_USUARIO');
        $lsfw_id_tp_usuario->setLabel('Tipo de Usuário:')
                ->setAttrib('style', 'width: 500px; ')
                ->addMultiOption('', '::Selecione::');
        foreach ($tipo_usuario as $t) {
            $lsfw_id_tp_usuario->addMultiOptions(array($t["LTPU_ID_TP_USUARIO"] =>  $t["LTPU_DS_TP_USUARIO"]));
        }

        $lsfw_nr_doc_origem = new Zend_Form_Element_Text('LSFW_NR_DOC_ORIGEM');
        $lsfw_nr_doc_origem->setAttrib('style', 'width: 500px; ')
                ->setLabel('Documento de Origem:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('Alnum', false, true)
                ->setAttrib('maxlength', 200)
                ->addValidator('StringLength', false, array(5, 200));

        $lsfw_nr_nota_fiscal = new Zend_Form_Element_Text('LSFW_NR_NOTA_FISCAL');
        $lsfw_nr_nota_fiscal->setLabel('Número da Nota Fiscal:')
                ->setAttrib('style', 'width: 500px; ')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('Alnum', false, true)
                ->setAttrib('maxlength', 200)
                ->addValidator('StringLength', false, array(5, 200));

        $lsfw_ic_aprovacao_instalacao = new Zend_Form_Element_Select('LSFW_IC_APROVACAO_INSTALACAO');
        $lsfw_ic_aprovacao_instalacao->setLabel('*Aprovação de Instalação:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->addMultiOption('', '::Selecione::')
                ->addMultiOptions(array('S' => 'Sim', 'N' => 'Não'));

        $lsfw_ic_perpetuidade_licenca = new Zend_Form_Element_Select('LSFW_IC_PERPETUIDADE_LICENCA');
        $lsfw_ic_perpetuidade_licenca->setLabel('*Perpetuidade de Licença:')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->addMultiOption('', '::Selecione::')
                ->addMultiOptions(array('S' => 'Sim', 'N' => 'Não'));


        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');

        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');


        $this->addElements(array(
            $acao,
            $lsfw_id_software,
            $lsfw_ds_software,
            $lsfw_id_tp_software,
            $lsfw_id_marca,
            $lsfw_id_modelo,
            $lsfw_dt_aquisicao,
            $lsfw_dt_validade_licenca,
            $lsfw_qt_adquirida,
            $lsfw_nr_processo_compra,
            $lsfw_ic_software_livre,
            $lsfw_cd_tipo_doc_contrato,
            $lsfw_nr_aditamento_contrato,
            $lsfw_nr_contrato,
            $lsfw_nr_processo_contrato,
            $lsfw_nr_termo,
            $lsfw_aa_termo,
            $lsfw_cd_tipo_termo,
            $lsfw_nr_tombo,
            $lsfw_sg_tombo,
            $lsfw_id_tp_usuario,
            $lsfw_nr_doc_origem,
            $lsfw_nr_nota_fiscal,
            $lsfw_ic_aprovacao_instalacao,
            $lsfw_ic_perpetuidade_licenca,
            $submit,
            $obrigatorio));
    }

}