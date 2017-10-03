<?php

class Sisad_Form_Juntada_DocumentoProcesso extends Zend_Form {

    public function init() {
        
    }

    /**
     * Monta formulário de adição.
     */
    public function add() {
        $this->setMethod('post');
        
        $sadTbDcprDocumentoProcesso = new Sisad_Form_Table_SadTbDcprDocumentoProcesso();
        $dcpr_id_tp_vinculacao = $sadTbDcprDocumentoProcesso->getElement('DCPR_ID_TP_VINCULACAO');
        $dcpr_id_tp_vinculacao->setName('TP_VINCULO');
        $services_Sisad_Tipo = new Services_Sisad_Tipo();
        $arrayTiposJuntada = $services_Sisad_Tipo->getTipoJuntada('documentoaprocesso');

        foreach ($arrayTiposJuntada as $vinculo) {
            $dcpr_id_tp_vinculacao->addMultiOptions(array($vinculo['id'] => $vinculo['nome']));
        }

        /*
         * Oculta o campo $dcpr_id_tp_vinculacao até que seja concluido o esquema de juntada
         * ---------------------------------------------------------------------
         */
        $dcpr_id_tp_vinculacao->removeDecorator('label');
        $dcpr_id_tp_vinculacao->setValue(1);
        $dcpr_id_tp_vinculacao->setAttrib('style', 'display: none');
        /*
         * ---------------------------------------------------------------------
         */
        
        $sisad_Form_Table_SadTbMofaMoviFase = new Sisad_Form_Table_SadTbMofaMoviFase();
        $mofa_ds_complemento = $sisad_Form_Table_SadTbMofaMoviFase->getElement('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setLabel('Justificativa:')
                ->setRequired(true);

        $btnSalvar = new Zend_Form_Element_Button('Salvar');

        $this->addElements(array(
            $dcpr_id_tp_vinculacao,
            $mofa_ds_complemento,
            $btnSalvar
        ));
    }

}