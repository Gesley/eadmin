<?php
class Transporte_Form_Veiculo extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $veid_id_veiculo = new Zend_Form_Element_Hidden('VEID_ID_VEICULO');
        $veid_id_veiculo->addFilter('Int')
                        ->removeDecorator('Label')
                        ->removeDecorator('HtmlTag');

        $veic_cd_placa = new Zend_Form_Element_Text('VEIC_CD_PLACA');
        $veic_cd_placa//->setRequired(true)
                     ->setLabel('Placa:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_aa_fabricacao = new Zend_Form_Element_Text('VEIC_AA_FABRICACAO');
        $veic_aa_fabricacao//->setRequired(true)
                     ->setLabel('Ano Fabricação:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_aa_modelo = new Zend_Form_Element_Text('VEIC_AA_MODELO');
        $veic_aa_modelo//->setRequired(true)
                     ->setLabel('Ano Modelo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_id_cor = new Zend_Form_Element_Select('VEIC_ID_COR');
        $cores = new Application_Model_DbTable_TraTbCoveCorVeiculo();
        $veic_id_cor->setLabel('Cor:')
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->addValidator('NotEmpty')
                    ->addMultiOptions(array(':: Selecione ::'));
        foreach ($cores->getCorVeiculo() as $c) {
            $veic_id_cor->addMultiOptions(array($c["COVE_ID_COR"] => $c["COVE_NO_COR"]));
        }

        $veic_cd_marca = new Zend_Form_Element_Select('MARC_ID_MARCA');
        $marcas = new Application_Model_DbTable_OcsTbMarcMarca();
        $veic_cd_marca->setLabel('Marca:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty')
                      ->addMultiOptions(array(':: Selecione ::'));
        foreach ($marcas->getMarca() as $m) {
            $veic_cd_marca->addMultiOptions(array($m["MARC_ID_MARCA"] => $m["MARC_DS_MARCA"]));
        }

        $veic_cd_modelo = new Zend_Form_Element_Select('MODE_ID_MODELO');
        $modelos = new Application_Model_DbTable_OcsTbModeModelo();
        $veic_cd_modelo->setLabel('Modelo:')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty')
                       ->addMultiOptions(array(':: Selecione ::'));
        foreach ($modelos->getModeloVeiculo() as $m) {
            $veic_cd_modelo->addMultiOptions(array($m["MODE_ID_MODELO"] => $m["MODE_DS_MODELO"]));
        }

        $veic_nr_certificado = new Zend_Form_Element_Text('VEIC_NR_CERTIFICADO');
        $veic_nr_certificado//->setRequired(true)
                     ->setLabel('Nr Certificado:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_nr_renavam = new Zend_Form_Element_Text('VEIC_NR_RENAVAM');
        $veic_nr_renavam//->setRequired(true)
                     ->setLabel('Renavam:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_nr_motor = new Zend_Form_Element_Text('VEIC_NR_MOTOR');
        $veic_nr_motor//->setRequired(true)
                     ->setLabel('Nr Motor:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_cd_chassi = new Zend_Form_Element_Text('VEIC_CD_CHASSI');
        $veic_cd_chassi//->setRequired(true)
                     ->setLabel('Nr Chassi:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_id_especie = new Zend_Form_Element_Text('VEIC_ID_ESPECIE');
        $veic_id_especie//->setRequired(true)
                     ->setLabel('Espécie:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_id_grupo = new Zend_Form_Element_Text('VEIC_ID_GRUPO');
        $veic_id_grupo//->setRequired(true)
                     ->setLabel('Grupo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_ic_ar_condicionado = new Zend_Form_Element_Text('VEIC_IC_AR_CONDICIONADO');
        $veic_ic_ar_condicionado//->setRequired(true)
                     ->setLabel('Ar Condicionado:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_ic_direcao_hidraulica = new Zend_Form_Element_Text('VEIC_IC_DIRECAO_HIDRAULICA');
        $veic_ic_direcao_hidraulica//->setRequired(true)
                     ->setLabel('Direção Hidraulica:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_ic_vidro_eletrico = new Zend_Form_Element_Text('VEIC_IC_VIDRO_ELETRICO');
        $veic_ic_vidro_eletrico//->setRequired(true)
                     ->setLabel('Vidro Eletrico:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_ic_trava_eletrica = new Zend_Form_Element_Text('VEIC_IC_TRAVA_ELETRICA');
        $veic_ic_trava_eletrica//->setRequired(true)
                     ->setLabel('Trava Eletrica:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_qt_capacidade_veiculo = new Zend_Form_Element_Text('VEIC_QT_CAPACIDADE_VEICULO');
        $veic_qt_capacidade_veiculo//->setRequired(true)
                     ->setLabel('Capacidade Veiculo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_qt_capacidade_tanque = new Zend_Form_Element_Text('VEIC_QT_CAPACIDADE_TANQUE');
        $veic_qt_capacidade_tanque//->setRequired(true)
                     ->setLabel('Capacidade Tanque:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_nr_tombo = new Zend_Form_Element_Text('VEIC_NR_TOMBO');
        $veic_nr_tombo//->setRequired(true)
                     ->setLabel('Nr Tombo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_nr_tp_tombo = new Zend_Form_Element_Text('VEIC_NR_TP_TOMBO');
        $veic_nr_tp_tombo//->setRequired(true)
                     ->setLabel('Tipo Tombo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_ic_disponivel = new Zend_Form_Element_Text('VEIC_IC_DISPONIVEL');
        $veic_ic_disponivel//->setRequired(true)
                     ->setLabel('Disponivel?')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_dt_inclusao = new Zend_Form_Element_Text('VEIC_DT_INCLUSAO');
        $veic_dt_inclusao//->setRequired(true)
                     ->setLabel('Data Inclusão:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_sg_secao = new Zend_Form_Element_Text('VEIC_SG_SECAO');
        $veic_sg_secao//->setRequired(true)
                     ->setLabel('SG Seção:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_cd_lotacao = new Zend_Form_Element_Text('VEIC_CD_LOTACAO');
        $veic_cd_lotacao//->setRequired(true)
                     ->setLabel('Cód. Lotação:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_cd_secsubsec = new Zend_Form_Element_Text('VEIC_CD_SECSUBSEC');
        $veic_cd_secsubsec//->setRequired(true)
                     ->setLabel('SecSubsec:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_sg_secao_padrao = new Zend_Form_Element_Text('VEIC_SG_SECAO_PADRAO');
        $veic_sg_secao_padrao//->setRequired(true)
                     ->setLabel('SG Padrão:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true);

        $veic_cd_lotacao_padrao = new Zend_Form_Element_Text('VEIC_CD_LOTACAO_PADRAO');
        $veic_cd_lotacao_padrao->setLabel('Cód. Lotação Padrão:')
                               ->addFilter('StripTags')
                               ->addFilter('StringTrim')
                               ->addValidator('NotEmpty')
                               ->addValidator('Alnum', false, true);

//        $veic_cd_combustivel = new Zend_Form_Element_Text('VEIC_CD_COMBUSTIVEL');
//        $veic_cd_combustivel//->setRequired(true)
//                     ->setLabel('Combustível:')
//                     ->addFilter('StripTags')
//                     ->addFilter('StringTrim')
//                     ->addValidator('NotEmpty')
//                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;


        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($veid_id_veiculo, $veic_cd_placa, $veic_aa_fabricacao, $veic_aa_modelo,
            $veic_id_cor, $veic_cd_marca, $veic_cd_modelo,$veic_nr_certificado, $veic_nr_renavam,
            $veic_nr_motor, $veic_cd_chassi, $veic_id_especie, $veic_id_grupo,$veic_ic_ar_condicionado,
            $veic_ic_direcao_hidraulica, $veic_ic_vidro_eletrico, $veic_ic_trava_eletrica,
            $veic_qt_capacidade_veiculo, $veic_qt_capacidade_tanque, $veic_nr_tombo, $veic_nr_tp_tombo,
            $veic_ic_disponivel,$veic_dt_inclusao , $veic_sg_secao, $veic_cd_lotacao, $veic_cd_secsubsec,
            $veic_sg_secao_padrao,$veic_cd_lotacao_padrao,$submit));
    }
    
}