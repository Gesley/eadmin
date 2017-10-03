<?php

class Sosti_Form_TrocarSolicitante extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
            ->setMethod('post');

        $rhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rhCentralLotacao->getSecoestrf1();
        $options = array('' => '');
        foreach ($secao as $v) {
            $options[$v["SESB_SIGLA_SECAO_SUBSECAO"] . '|' . $v["LOTA_COD_LOTACAO"] . '|' . $v["LOTA_TIPO_LOTACAO"]] = $v["LOTA_DSC_LOTACAO"];
        }

        $this->addElement('select', 'TRF1_SECAO', array(
                'label' => 'TRF1/Seções',
                'multiOptions' => $options,
                'attribs' => array('style' => 'width: 500px; ')
            )
        );

        $this->addElement('select', 'SECAO_SUBSECAO', array(
            'label' => 'Seção/Subseção',
            'attribs' => array(
                'style' => 'width: 500px;',
                'disabled' => 'disabled'
            ),
            'multiOptions' => array('' => 'Primeiro escolha a TRF1/Seção')
        ));

        $this->addElement('text', 'DOCM_CD_LOTACAO_GERADORA', array(
            'label' => 'Unidade Administrativa',
            'attribs' => array(
                'style' => 'width: 500px;',
                'disabled' => true
            ),
            'placeholder' => 'Primeiro escolha a TRF1/Seção'
        ));

        $this->addElement('text', 'DOCM_CD_MATRICULA_CADASTRO', array(
            'label' => 'Solicitante',
            'required' => true,
            'attribs' => array(
                'style' => 'width: 500px;'
            )
        ));

        $this->addElement('submit', 'Listar', array(
            'attribs' => array('class' => 'ui-button')
        ));
    }

    public function isValid($data)
    {
        $this->populate($data);
        if (!empty($data["TRF1_SECAO"])) {
            $secao = explode('|', $data["TRF1_SECAO"]);
            $RhCentralLotacao = new Application_Model_DbTable_RhCentralLotacao();
            $Lotacao_array = $RhCentralLotacao->getSubSecoes($secao[0], $secao[1]);
            foreach ($Lotacao_array as $lotacao) {
                $res[$lotacao["LOTA_SIGLA_SECAO"] . '|' . $lotacao['LOTA_COD_LOTACAO'] . '|' . $lotacao["LOTA_TIPO_LOTACAO"]] = $lotacao["LOTA_SIGLA_LOTACAO"] . ' - ' . $lotacao["LOTA_DSC_LOTACAO"] . ' - ' . $lotacao["LOTA_COD_LOTACAO"] . ' - ' . $lotacao["LOTA_SIGLA_SECAO"] . ' - ' . $lotacao["LOTA_LOTA_COD_LOTACAO_PAI"];
            }
            $this->SECAO_SUBSECAO->addMultiOptions($res);
            $this->SECAO_SUBSECAO->setValue($data['SECAO_SUBSECAO']);
            $this->SECAO_SUBSECAO->setAttrib('disabled', null);
            return parent::isValid($this->getValues());
        }
        else{
            return parent::isValid($data);
        }
    }

}