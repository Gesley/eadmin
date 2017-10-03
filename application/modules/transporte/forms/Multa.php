<?php
class Transporte_Form_Multa extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $mult_id_multa = new Zend_Form_Element_Hidden('MULT_ID_MULTA');
        $mult_id_multa->addFilter('Int')
                     ->removeDecorator('Label')
                     ->removeDecorator('HtmlTag');

        $mult_nr_proc_adm = new Zend_Form_Element_Text('MULT_NR_PROC_ADM');
        $mult_nr_proc_adm//->setRequired(true)
                     ->setLabel('Nr Processo Adm:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_nr_multa = new Zend_Form_Element_Text('MULT_NR_MULTA');
        $mult_nr_multa//->setRequired(true)
                     ->setLabel('Nr Multa:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_ds_local_multa = new Zend_Form_Element_Text('MULT_DS_LOCAL_MULTA');
        $mult_ds_local_multa//->setRequired(true)
                     ->setLabel('Local Multa:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_dh_multa = new Zend_Form_Element_Text('MULT_DH_MULTA');
        $mult_dh_multa//->setRequired(true)
                     ->setLabel('Data-Hora Multa:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(5, 100));

        $mult_vl_integral_multa = new Zend_Form_Element_Text('MULT_VL_INTEGRAL_MULTA');
        $mult_vl_integral_multa//->setRequired(true)
                     ->setLabel('Valor Integral:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_vl_desconto_multa = new Zend_Form_Element_Text('MULT_VL_DESCONTO_MULTA');
        $mult_vl_desconto_multa//->setRequired(true)
                     ->setLabel('Valor Desconto:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_dt_vencimento = new Zend_Form_Element_Text('MULT_DT_VENCIMENTO');
        $mult_dt_vencimento//->setRequired(true)
                     ->setLabel('Data Vencimento:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_dt_pagamento = new Zend_Form_Element_Text('MULT_DT_PAGAMENTO');
        $mult_dt_pagamento//->setRequired(true)
                     ->setLabel('Data Pagamento:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_id_motorista = new Zend_Form_Element_Text('MULT_ID_MOTORISTA');
        $mult_id_motorista//->setRequired(true)
                     ->setLabel('Motorista:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_id_veiculo = new Zend_Form_Element_Text('MULT_ID_VEICULO');
        $mult_id_veiculo//->setRequired(true)
                     ->setLabel('Veiculo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_ic_pg_motorista = new Zend_Form_Element_Text('MULT_IC_PG_MOTORISTA');
        $mult_ic_pg_motorista//->setRequired(true)
                     ->setLabel('IC PG:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_cd_matr_cadastrante = new Zend_Form_Element_Text('MULT_CD_MATR_CADASTRANTE');
        $mult_cd_matr_cadastrante//->setRequired(true)
                     ->setLabel('Cadastrado por:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_id_proc_fspr = new Zend_Form_Element_Text('MULT_ID_PROC_FSPR');
        $mult_id_proc_fspr//->setRequired(true)
                     ->setLabel('Processo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_dh_cadastramento = new Zend_Form_Element_Text('MULT_DH_CADASTRAMENTO');
        $mult_dh_cadastramento//->setRequired(true)
                     ->setLabel('Data-Hora Cadastro:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $mult_ds_observacao = new Zend_Form_Element_Text('MULT_DS_OBSERVACAO');
        $mult_ds_observacao//->setRequired(true)
                     ->setLabel('Observação:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;


        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($mult_id_multa, $mult_nr_proc_adm, $mult_nr_multa,
               $mult_ds_local_multa, $mult_dh_multa, $mult_vl_integral_multa,$mult_vl_desconto_multa,
               $mult_dt_vencimento,$mult_dt_pagamento, $mult_id_motorista, $mult_id_veiculo,
               $mult_ic_pg_motorista, $mult_cd_matr_cadastrante,$mult_id_proc_fspr,
               $mult_dh_cadastramento,$mult_ds_observacao, $submit));

        //$this->setElementDecorators(array('Label','ViewHelper', 'Errors')); # sempre no final do form
    }
}