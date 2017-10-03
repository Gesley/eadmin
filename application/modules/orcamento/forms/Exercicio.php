<?php

class Orcamento_Form_Exercicio extends Zend_Form
{

    public function init ()
    {
        $this->setName ( 'frmExercicio' )->setMethod ( 'post' )->setAttrib ( 'id', 'frmExercicio' )->setElementFilters ( array ( 'StripTags', 'StringTrim' ) );

        // TODO: ver modificação / inclusão de decorators
        $txtNrDespesa = new Zend_Form_Element_Hidden ( 'ANOE_CD_MATRICULA_INCLUSAO' );

// Definição do valor do ano exercicio        
        $negocio = new Orcamento_Business_Negocio_Exercicio();
        $anoEexercicio = $negocio->retornaProximoAnoExercicio ();
        if( is_null ( $anoEexercicio[ "ANOE_AA_ANO" ] ) ) {
            $anoEexercicio[ "ANOE_AA_ANO" ] = date ( 'Y' );
        }

// Define opções para o campo ANOE_NR_ANO
        $txtAno = new Zend_Form_Element_Text ( 'ANOE_AA_ANO' );

        $txtAno->setLabel ( 'Ano:' );
        $txtAno->setAttrib ( 'size', '6' );
        $txtAno->setAttrib ( 'maxlength', '4' );
        $txtAno->setValue ( $anoEexercicio[ 'ANOE_AA_ANO' ] );
        $txtAno->setAttrib ( 'readonly', 'readonly' );
        $txtAno->setRequired ( true );

// Define opções para campo ANOE_DS_OBSERVACAO
        $txtDescricao = new Zend_Form_Element_Text ( 'ANOE_DS_OBSERVACAO' );

        $txtDescricao->setLabel ( 'Descrição:' );
        $txtDescricao->setAttrib ( 'size', 40 );
        $txtDescricao->getDecorator ( 'label' )->setOption ( 'requiredSuffix', '  ' );
        $txtDescricao->setAttrib ( 'maxlength', 45 );
        $txtDescricao->addFilter ( 'StringTrim' );
        $txtDescricao->setRequired ( true );


// Define opções para campo ANOE_DS_OBSERVACAO
        $intMatricula = new Orcamento_Business_Negocio_Base ();
        $txtCdLotacao = new Zend_Form_Element_Hidden ( 'ANOE_CD_MATRICULA_INCLUSAO' );
        $txtCdLotacao->setValue ( $intMatricula->retornaMatricula () );

// Define opções para o campo status
        $faseExercicio = new Orcamento_Facade_Exercicio();

        $cboStatus = new Zend_Form_Element_Select ( 'FANE_ID_FASE_EXERCICIO' );
        $cboStatus->setLabel ( 'Status:' );
        $cboStatus->addFilter ( 'StripTags' );
        $cboStatus->addMultiOptions ( $faseExercicio->retornaComboPerfil () );

// Finaliza com o submit
        $cmdEnviar = new Zend_Form_Element_Submit ( 'Enviar' );

        $cmdEnviar->setLabel ( 'Enviar' );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', Orcamento_Business_Dados::CLASSE_SALVAR );


// Adiciona os controles no formulário
        $this->addElement ( $txtAno );
        $this->addElement ( $txtDescricao );
        $this->addElement ( $txtCdLotacao );
        $this->addElement ( $cboStatus );
        $this->addElement ( $cmdEnviar );
    }

}
