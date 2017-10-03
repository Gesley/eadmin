<?php

class Sisad_Form_Table_SadTbConfConfidencialidade extends Zend_Form {

    public function init() {
        /*
         * Todos os campos do banco
         * com suas respectivas regras
         */

        $conf_id_confidencialidade = new Zend_Form_Element_Text('CONF_ID_CONFIDENCIALIDADE');
        $conf_id_confidencialidade->setRequired(true)
                ->addValidator('StringLength', false, array(1, 2));

        $conf_ds_confidencialidade = new Zend_Form_Element_Text('CONF_DS_CONFIDENCIALIDADE');
        $conf_ds_confidencialidade->setRequired(true)
                ->addValidator('StringLength', false, array(1, 45));

        $this->addElements(array(
            $conf_id_confidencialidade
            , $conf_ds_confidencialidade
        ));
    }

}