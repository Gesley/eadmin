<?php

class Sosti_Form_Asso extends Zend_Form {

    public function init() {
        $this->setAction('save')
                ->setMethod('post');

        $acompanhar = new Zend_Form_Element_Checkbox('ACOMPANHAR');
        $acompanhar->setLabel('')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttribs(array('style' => 'float: left;'))
                ->setRequired(false)
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $asso_id_movimentacao = new Zend_Form_Element_Text('ASSO_ID_MOVIMENTACAO');
        $asso_id_movimentacao->setLabel('Id da Movimentação')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        $asso_id_atendimento_sistemas = new Zend_Form_Element_Text('ASSO_ID_ATENDIMENTO_SISTEMAS');
        $asso_id_atendimento_sistemas->setLabel('Id Atendimento Sistemas')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        $asso_ic_atendimento_emergencia = new Zend_Form_Element_Checkbox('ASSO_IC_ATENDIMENTO_EMERGENCIA');
        $asso_ic_atendimento_emergencia->setLabel('Emergência')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttribs(array('style' => 'float: left;'))
                ->setRequired(false)
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $asso_ic_solucao_problema = new Zend_Form_Element_Checkbox('ASSO_IC_SOLUCAO_PROBLEMA');
        $asso_ic_solucao_problema->setLabel('Problema')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttribs(array('style' => 'float: left;'))
                ->setRequired(false)
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        $asso_ic_solucao_causa_problema = new Zend_Form_Element_Checkbox('ASSO_IC_SOLUCAO_CAUSA_PROBLEMA');
        $asso_ic_solucao_causa_problema->setLabel('Ic Solucao Causa Problema')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');

        /* Checkbox para demandas emergenciais */
        $emergencial = new Zend_Form_Element_Checkbox('EMERGENCIAL');
        $emergencial->setLabel('Atendimento Emergencial')
                ->setRequired(false)
                ->setCheckedValue('S')
                ->setUncheckedValue('N');

        /* Radio button para escolha do tipo de atendimento: CAUSA ou PROBLEMA */
        $causa_problema = new Zend_Form_Element_Radio('CAUSA_PROBLEMA');
        $causa_problema->setLabel('*Tipo:')
                ->setValue(2)
                ->setRequired(false)
                ->addMultiOptions(array(
                                        '2' => 'PROBLEMA',
                                        '1' => 'CAUSA',
                                        ));
        
        /* Campo destinado a inclusao de solicitações (problemas) relacionados com a causa */
        $solicitacoes_problemas = new Zend_Form_Element_Textarea('SOLIC_PROBLEMAS');
        $solicitacoes_problemas->setLabel('Digite os números das solicitações relacionadas:')
                               ->setDescription('Separe-os usando a vírgula.');
        
        $this->addElements(
                array(
                    $asso_ic_atendimento_emergencia,
                    $asso_ic_solucao_problema,
                    $asso_id_movimentacao,
                    $asso_id_atendimento_sistemas,
                    $asso_ic_solucao_causa_problema,
                    $emergencial,
                    $causa_problema,
                    $solicitacoes_problemas
        ));
    }

}
