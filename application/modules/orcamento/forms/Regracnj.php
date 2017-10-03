<?php
/**
 * Contém formuçarios da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Form
 *
 * @author Sandro Maceno <smaceno@stefanini.com>
 */

/**
 * Disponibiliza o formulário para entrada de dados sobre esfera.
 *
 * @category Orcamento
 * @package Orcamento_Form_Regra
 * @author Sandro Maceno <smaceno@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Regracnj extends Orcamento_Form_Base {
    
    const FINANCEIRO = '1';

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function init () {
        // Definições iniciais do formulário
        $form = $this->retornaFormulario('regracnj');
        
        $parametro = Zend_Controller_Front::
                getInstance()->getRequest()->getParams();
        $tabela = $parametro['REGC_IC_TB_IMPACTO'];
        
        // Id regra
        $codigo = new Zend_Form_Element_Text('REGC_ID_REGRA');
        $codigo->setLabel('Codigo:');
        $codigo->setAttrib('size', '10');
        $codigo->setAttrib('style', ' background: #CDCDCD; font-weight: bold; text-align: center;"');

        // Cria o campo REGC_AA_REGRA
        $txtAno = new Zend_Form_Element_Text ( 'REGC_AA_REGRA' );
        
        // Define opções o controle $txtAno
        $txtAno->setLabel ( 'Ano:' );
        $txtAno->setAttrib ( 'size', 7 );
        $txtAno->setAttrib ( 'maxlength', 4);
        $txtAno->addFilter ( 'StringTrim' );
        $txtAno->setValue(date('Y'));
        $txtAno->setRequired ( true );
        
        // Tipo de Despesa
        $cboTipoDespesa = new Zend_Form_Element_Select('REGC_IC_TB_IMPACTO');
        $cboTipoDespesa->setLabel('Tabela:')
            ->addFilter('StripTags')
            ->setRequired ( true )
            ->addMultiOptions(array(
                 '' => 'Selecione',
                    '1' => 'Financeiro',
                    '3' => 'Liquidado',
                    '11' => 'Restos a Pagar'                
                )
            );
 
        // Natureza da despesa inicio
        $txtElemento = new Zend_Form_Element_Text(
            'REGC_VL_NATUREZA_DESP_INICIAL');
        $txtElemento->setLabel('Natureza da despesa inicio:')->setAttribs(
            array('size' => '70', 'maxlength' => 20))->setDescription(
            'A lista será carregada após digitar 3 caracteres.');
        if($tabela !== self::FINANCEIRO){
            $txtElemento->setRequired ( true );
        }
        
        // Natureza da despesa final
        $txtElementoFinal = new Zend_Form_Element_Text(
          'REGC_VL_NATUREZA_DESP_FINAL');
        $txtElementoFinal->setLabel('Natureza da despesa final:')->setAttribs(
            array('size' => '70', 'maxlength' => 20))->setDescription(
            'A lista será carregada após digitar 3 caracteres.');
        if($tabela !== self::FINANCEIRO){
            $txtElementoFinal->setRequired ( true );
        }
        
        // Cria o campo REGC_AA_REGRA
        $txtCategoria = new Zend_Form_Element_Text ( 'REGC_IC_CATEGORIA' );
        
        // Define opções o controle $txtAno
        $txtCategoria->setLabel ( 'Categoria:' );
        $txtCategoria->setAttrib ( 'size', 3 );
        $txtCategoria->setAttrib ( 'maxlength', 1);
        $txtCategoria->addFilter ( 'StringTrim' );
        if($tabela === self::FINANCEIRO){
            $txtCategoria->setRequired ( true );
        }
        
        // Cria o campo ALIN_ID_INCISO
        $cboTipoInciso = new Zend_Form_Element_Select ( 'REGC_ID_INCISO' );
        
        // Dados da fonte
        $tbTipoInc = new Orcamento_Business_Negocio_Inciso ();

        $arrayInciso = $tbTipoInc->retornaComboComposta();
        $arrayInciso[0] = 'Selecione';
        ksort($arrayInciso);
        
        // Define opções o controle $cboTipoNC
        $cboTipoInciso->setLabel ( 'Inciso:' );
        $cboTipoInciso->addMultiOptions ( $arrayInciso );
        $cboTipoInciso->setRequired ( true );
        $cboTipoInciso->setAttribs(
                array(
                    'style' => 'width:400px;'
                ));
        
        // Cria o campo REGC_ID_ALINEA
        $cboTipoAlinea = new Zend_Form_Element_Select ( 'REGC_ID_ALINEA' );
        $cboTipoAlinea->setRegisterInArrayValidator(false);
        
        // Define opções o controle $cboTipoNC
        $cboTipoAlinea->setLabel ( 'Alínea:' );
        $cboTipoAlinea->setRequired ( true );
        $cboTipoAlinea->setAttribs(
                array(
                    'style' => 'width:400px;'
                ));
            
        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit('Enviar');

        // Define opções do controle $cmdEnviar
        $cmdEnviar->setLabel('Enviar');
        $cmdEnviar->setAttrib('type', 'submit');
        $cmdEnviar->setAttrib('class', Orcamento_Business_Dados::CLASSE_SALVAR);

        // Adiciona os controles no formulário

        $this->addElement($codigo);
        $this->addElement($txtAno);
        $this->addElement($txtElemento);
        $this->addElement($txtElementoFinal);
        $this->addElement($cboTipoDespesa);
        $this->addElement($txtCategoria);
        $this->addElement($cboTipoInciso);
        $this->addElement($cboTipoAlinea);
        $this->addElement($cmdEnviar);
        
        
    }

}
