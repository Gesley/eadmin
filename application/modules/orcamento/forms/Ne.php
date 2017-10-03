<?php

class Orcamento_Form_Ne extends Zend_Form {

    public function init () {
        $this->setName('frmNE')->setMethod('post')->setAttrib('id', 'frmNE')->setElementFilters(array('StripTags', 'StringTrim'));

        $txtNotaEmpenho = new Zend_Form_Element_Text('NOEM_CD_NOTA_EMPENHO');
        $txtNotaEmpenho->setLabel('Nota de empenho:')->setRequired(true)->setAttrib('size', '20')->setAttrib('maxlength', 12)->addFilter('Alnum')->addValidator('Alnum');

        $txtNotaEmpenhoReferencia = new Zend_Form_Element_Text('NOEM_CD_NE_REFERENCIA');
        $txtNotaEmpenhoReferencia->setLabel('Nota de empenho de referência:')->setAttribs(array('size' => '20', 'maxlength' => 12, 'readonly' => 'readonly'))->addFilter('Alnum')->addValidator('Alnum');

        $txtDespesa = new Zend_Form_Element_Text('NOEM_NR_DESPESA');
        $txtDespesa->setLabel('Despesa:')->addFilter('Digits')->addValidator('Digits')->setRequired(false)->setAttrib('size', '8');

        $txtUG = new Zend_Form_Element_Text('NOEM_CD_UG_OPERADOR');
        $txtUG->setLabel('UG operador:')->setRequired(true)->setAttribs(array('size' => '8', 'maxlength' => 12, 'readonly' => 'readonly'))->addFilter('Alnum')->addValidator('Alnum');

        $txtAno = new Zend_Form_Element_Text('NOEM_ANO');
        $txtAno->setLabel('Ano:')->setRequired(true)->setAttribs(array('size' => '8', 'maxlength' => 4, 'readonly' => 'readonly'))->addFilter('Alnum')->addValidator('Alnum');

        $txtDhNE = new Zend_Form_Element_Text('NOEM_DH_NE');
        $txtDhNE->setLabel('Data e hora do empenho:')->setRequired(true)->setAttribs(array('size' => '10', 'readonly' => 'readonly'));

        $txtDtEmissao = new Zend_Form_Element_Text('NOEM_DT_EMISSAO');
        $txtDtEmissao->setLabel('Emissão:')->setRequired(true)->setAttribs(array('size' => '10', 'readonly' => 'readonly'));

        $txtFonte = new Zend_Form_Element_Text('NOEM_CD_FONTE');
        $txtFonte->setLabel('Fonte:')->setAttribs(array('size' => '8', 'maxlength' => 3, 'readonly' => 'readonly'))->addFilter('Digits')->addValidator('Digits');

        $txtPtres = new Zend_Form_Element_Text('NOEM_CD_PT_RESUMIDO');
        $txtPtres->setLabel('PTRES:')->setAttribs(array('size' => '15', 'maxlength' => 6, 'readonly' => 'readonly'))->addFilter('Digits')->addValidator('Digits');

        $txtNatureza = new Zend_Form_Element_Text('NOEM_CD_ELEMENTO_DESPESA_SUB');
        $txtNatureza->setLabel('Natureza da despesa:')->setAttribs(array('size' => '10', 'maxlength' => 8, 'readonly' => 'readonly'))->addFilter('Digits')->addValidator('Digits');

        $txtProcesso = new Zend_Form_Element_Text('NOEM_NR_PROCESSO');
        $txtProcesso->setLabel('Processo:')->setAttribs(array('size' => '40', 'maxlength' => 20));

        $txtObservacao = new Zend_Form_Element_Textarea('NOEM_DS_OBSERVACAO');
        $txtObservacao->setLabel('Observação:')->setRequired(true)->setAttribs(array('readonly' => 'readonly'))->addFilter('StringTrim');

        $txtEvento = new Zend_Form_Element_Text('NOEM_CD_EVENTO');
        $txtEvento->setLabel('Evento:')->setRequired(true)->setAttribs(array('size' => '8', 'maxlength' => 6, 'readonly' => 'readonly'))->addFilter('Digits')->addValidator('Digits');

        $txtValorOriginal = new Zend_Form_Element_Text('NOEM_VL_NE');
        $txtValorOriginal->setLabel('Valor original da NE:')->setRequired(true)->setAttribs(array('size' => '8', 'maxlength' => 12, 'readonly' => 'readonly'));

        $txtValorAcertado = new Zend_Form_Element_Text('NOEM_VL_NE_ACERTADO');
        $txtValorAcertado->setLabel('Valor acertado:')->setRequired(true)->setAttribs(array('size' => '10', 'maxlength' => 20, 'readonly' => 'readonly'));

        /*
          $txtTipoNE = new Zend_Form_Element_Text ( 'NOEM_NU_TIPO_NE' );
          $txtTipoNE->setLabel ( 'Tipo NE:' )->setRequired ( true )->setAttribs ( array ('size' => '5', 'maxlength' => 1, 'readonly' => 'readonly' ) );

          $txtVinculacao = new Zend_Form_Element_Text ( 'NOEM_CD_VINCULACAO' );
          $txtVinculacao->setLabel ( 'Vinculação:' )->setRequired ( true )->setAttribs ( array ('size' => '8', 'maxlength' => 4, 'readonly' => 'readonly' ) )->addFilter ( 'Digits' )->addValidator ( 'Digits' );

          $txtCategoria = new Zend_Form_Element_Text ( 'NOEM_CD_CATEGORIA' );
          $txtCategoria->setLabel ( 'Categoria:' )->setRequired ( true )->setAttribs ( array ('size' => '8', 'maxlength' => 1, 'readonly' => 'readonly' ) )->addFilter ( 'Alnum' )->addValidator ( 'Alnum' );
         */

        $chkAcertoManual = new Zend_Form_Element_Checkbox('NOEM_IC_ACERTADO_MANUALMENTE');
        $chkAcertoManual->setLabel('Acertado manualmente?')->setRequired(true);

        // Botão submit
        $cmdSubmit = new Zend_Form_Element_Button('Salvar');
        $cmdSubmit->setLabel('Salvar')->setAttrib('type', 'submit')->setAttrib('class', 'ceo_salvar');

        $this->addElements(array($txtNotaEmpenho, $txtNotaEmpenhoReferencia, $txtDespesa, $txtUG, $txtAno, $txtDhNE, $txtDtEmissao, $txtFonte, $txtPtres, $txtNatureza, $txtProcesso, $txtObservacao, $txtEvento, $txtValorOriginal, $txtValorAcertado, /* $txtTipoNE, $txtVinculacao, $txtCategoria, */ $chkAcertoManual, $cmdSubmit));
    }

}
