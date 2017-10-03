<?php

class Sisad_Form_Juntada_ProcessoProcesso extends Zend_Form {

    public function init() {
        
    }

    /**
     * Monta formulário de adição.
     */
    public function add() {
        $this->setMethod('post');
        
        $sadTbDcprDocumentoProcesso = new Sisad_Form_Table_SadTbVipdVincProcDigital();
        $vipd_id_tp_vinculacao = $sadTbDcprDocumentoProcesso->getElement('VIPD_ID_TP_VINCULACAO');
        $vipd_id_tp_vinculacao->setName('TP_VINCULO');
        $services_Sisad_Tipo = new Services_Sisad_Tipo();
        $arrayTiposJuntada = $services_Sisad_Tipo->getTipoJuntada('processoaprocesso');

        foreach ($arrayTiposJuntada as $vinculo) {
            $vipd_id_tp_vinculacao->addMultiOptions(array($vinculo['id'] => $vinculo['nome']));
        }
        
        $sisad_Form_Table_SadTbMofaMoviFase = new Sisad_Form_Table_SadTbMofaMoviFase();
        $mofa_ds_complemento = $sisad_Form_Table_SadTbMofaMoviFase->getElement('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setLabel('Justificativa:')
                ->setRequired(true);

        $btnSalvar = new Zend_Form_Element_Button('Salvar');

        $this->addElements(array(
            $vipd_id_tp_vinculacao,
            $mofa_ds_complemento,
            $btnSalvar
        ));
    }

}