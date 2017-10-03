<?php

class Orcamento_Form_Nc extends Zend_Form {

    public function init() {
        $this->setName('frmNC')->setMethod('post')->setAttrib('id', 'frmNC')->setElementFilters(array('StripTags', 'StringTrim'));

        $txtCdNc = new Zend_Form_Element_Text('NOCR_CD_NOTA_CREDITO');
        $txtCdNc->setLabel('Nota de crédito:')->setRequired(false)->setAttrib('size', '20')->setAttrib('maxlength', 12);

        $txtDespesa = new Zend_Form_Element_Text('NOCR_NR_DESPESA');
        $txtDespesa->setLabel('Despesa:')->addFilter('Digits')->addValidator('Digits')->setRequired(false);

        $txtDespesaReserva = new Zend_Form_Element_Text('NOCR_NR_DESPESA_RESERVA');
        $txtDespesaReserva->setLabel('Despesa reserva:')->addFilter('Digits')->addValidator('Digits')->setRequired(false);

        // Tipo Nota Credito
        $tbTipoNc = new Trf1_Orcamento_Negocio_Tiponc ();
        $cboTpNc = new Zend_Form_Element_Select('NOCR_CD_TIPO_NC');
        $cboTpNc->setLabel('Tipo de nota de crédito:')->setRequired(false)->addMultiOptions($tbTipoNc->retornaCombo());

        $txtUgOpe = new Zend_Form_Element_Text('NOCR_CD_UG_OPERADOR');
        $txtUgOpe->setLabel('UG Operador:')->setRequired(false)->setAttribs(array('size' => '10', 'maxlength' => 6, 'readonly' => 'readonly'))->addFilter('Digits')->addValidator('Digits');

        $txtUgFav = new Zend_Form_Element_Text('NOCR_CD_UG_FAVORECIDO');
        $txtUgFav->setLabel('UG Favorecido:')->setRequired(false)->setAttribs(array('size' => '10', 'maxlength' => 6, 'readonly' => 'readonly'))->addFilter('Digits')->addValidator('Digits');

        $txtAno = new Zend_Form_Element_Text('NOCR_ANO');
        $txtAno->setLabel('Ano:')->setRequired(true)->setAttribs(array('size' => '8', 'maxlength' => 4, 'readonly' => 'readonly'))->addFilter('Alnum')->addValidator('Alnum');

        $txtCdFonte = new Zend_Form_Element_Text('NOCR_CD_FONTE');
        $txtCdFonte->setLabel('Fonte:')->setRequired(false)->setAttribs(array('size' => '10', 'maxlength' => 3, 'readonly' => 'readonly'))->addFilter('Digits')->addValidator('Digits');

        $txtPtres = new Zend_Form_Element_Text('NOCR_CD_PT_RESUMIDO');
        $txtPtres->setLabel('PTRES:')->setRequired(false)->setAttribs(array('size', '10', 'maxlength' => 6, 'readonly' => 'readonly'))->addFilter('Digits')->addValidator('Digits');

        $txtCDVinc = new Zend_Form_Element_Text('NOCR_CD_VINCULACAO');
        $txtCDVinc->setLabel('Vinculação:')->setRequired(true)->setAttribs(array('size' => '10', 'maxlength' => 6, 'readonly' => 'readonly'))->addFilter('Alnum')->addValidator('Alnum');

        $txtElDesp = new Zend_Form_Element_Text('NOCR_CD_ELEMENTO_DESPESA_SUB');
        $txtElDesp->setLabel('Natureza da despesa:')->setRequired(true)->setAttribs(array('size', '10', 'maxlength' => 8, 'readonly' => 'readonly'))->addFilter('Digits')->addValidator('Digits');

        $txtDhNc = new Zend_Form_Element_Text('NOCR_DH_NC');
        $txtDhNc->setLabel('Data e hora:')->setLabel('Data:')->setRequired(false)->setAttribs(array('size' => '10', 'readonly' => 'readonly'));

        $txtDtEmissao = new Zend_Form_Element_Text('NOCR_DT_EMISSAO');
        $txtDtEmissao->setLabel('Emissão:')->setRequired(false)->setAttrib('size', '10')->setAttribs(array('readonly' => 'readonly'));

        $txtCdCate = new Zend_Form_Element_Text('NOCR_CD_CATEGORIA');
        $txtCdCate->setLabel('Categoria:')->setRequired(false)->setAttribs(array('size' => '10', 'maxlength' => 1, 'readonly' => 'readonly'))->addFilter('Alnum')->addValidator('Alnum');

        $txtDsObserv = new Zend_Form_Element_Textarea('NOCR_DS_OBSERVACAO');
        $txtDsObserv->setLabel('Observação:')->setRequired(true)->setAttribs(array('size' => '20', 'maxlength' => 234, 'readonly' => 'readonly'))->addFilter('StringTrim');

        $txtVlNc = new Zend_Form_Element_Text('NOCR_VL_NC');
        $txtVlNc->setLabel('Valor original da NC:')->setRequired(true)->setAttribs(array('maxlength' => 20, 'size' => '10', 'readonly' => 'readonly'));

        $txtVlNcAc = new Zend_Form_Element_Text('NOCR_VL_NC_ACERTADO');
        $txtVlNcAc->setLabel('Valor acertado:')->setRequired(true)->setAttribs(array('maxlength' => 20, 'size' => '10', 'readonly' => 'readonly'));

        $chkAcertoManual = new Zend_Form_Element_Checkbox('NOCR_IC_ACERTADO_MANUALMENTE');
        $chkAcertoManual->setLabel('Acertado manualmente?')->setRequired(true);

        // Botão submit
        $cmdSubmit = new Zend_Form_Element_Button('Salvar');
        $cmdSubmit->setLabel('Salvar')->setAttrib('type', 'submit')->setAttrib('class', 'ceo_salvar');

        $this->addElements(array($txtCdNc, $cboTpNc, $txtDespesa, $txtDespesaReserva, $txtUgOpe, $txtUgFav, $txtAno, $txtCdFonte, $txtPtres, $txtElDesp, $txtDhNc, $txtDtEmissao, $txtDsObserv, $txtVlNc, $txtVlNcAc, $chkAcertoManual, $cmdSubmit));
    }

}
