<?php
class Transporte_Form_Acidente extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $acid_id_acidente = new Zend_Form_Element_Hidden('ACID_ID_ACIDENTE');
        $acid_id_acidente->addFilter('Int')
                     ->removeDecorator('Label')
                     ->removeDecorator('HtmlTag');

        $acid_nr_proc_adm = new Zend_Form_Element_Text('ACID_NR_PROC_ADM');
        $acid_nr_proc_adm//->setRequired(true)
                     ->setLabel('Nr Processo Adm:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $acid_nr_boletim_ocorrencia = new Zend_Form_Element_Text('ACID_NR_BOLETIM_OCORRENCIA');
        $acid_nr_boletim_ocorrencia//->setRequired(true)
                     ->setLabel('Nr BO:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $acid_ds_fato = new Zend_Form_Element_Text('ACID_DS_FATO');
        $acid_ds_fato//->setRequired(true)
                     ->setLabel('Descrição Fato:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $acid_dh_fato = new Zend_Form_Element_Text('ACID_DH_FATO');
        $acid_dh_fato//->setRequired(true)
                     ->setLabel('Data-Hora Fato:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     ->addValidator('StringLength', false, array(5, 100));

        $acid_dt_comunicacao = new Zend_Form_Element_Text('ACID_DT_COMUNICACAO');
        $acid_dt_comunicacao//->setRequired(true)
                     ->setLabel('Data Comunicação:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $acid_id_motorista = new Zend_Form_Element_Text('ACID_ID_MOTORISTA');
        $acid_id_motorista//->setRequired(true)
                     ->setLabel('Motorista')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $acid_id_veiculo = new Zend_Form_Element_Text('ACID_ID_VEICULO');
        $acid_id_veiculo//->setRequired(true)
                     ->setLabel('Veículo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $acid_id_proc_fspr = new Zend_Form_Element_Text('ACID_ID_PROC_FSPR');
        $acid_id_proc_fspr//->setRequired(true)
                     ->setLabel('Processo')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $acid_cd_matr_user = new Zend_Form_Element_Text('ACID_CD_MATR_USER');
        $acid_cd_matr_user//->setRequired(true)
                     ->setLabel('Matrícula')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;

        $acid_dh_acidente = new Zend_Form_Element_Text('ACID_DH_ACIDENTE');
        $acid_dh_acidente//->setRequired(true)
                     ->setLabel('Data-Hora Acidente:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty')
                     ->addValidator('Alnum', false, true)
                     /*->addValidator('StringLength', false, array(5, 100))*/;


        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($acid_id_acidente, $acid_nr_proc_adm,
               $acid_nr_boletim_ocorrencia, $acid_ds_fato, $acid_dh_fato,
               $acid_dt_comunicacao, $acid_id_motorista, $acid_id_veiculo,
               $acid_id_proc_fspr, $acid_cd_matr_user, $acid_dh_acidente, $submit));

        //$this->setElementDecorators(array('Label','ViewHelper', 'Errors')); # sempre no final do form
    }
}