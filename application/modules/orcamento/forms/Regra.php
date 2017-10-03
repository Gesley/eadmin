<?php

/**
 * Contém formuçarios da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Form
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza o formulário para entrada de dados sobre esfera.
 *
 * @category Orcamento
 * @package Orcamento_Form_Regra
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Regra extends Orcamento_Form_Base {

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init () {
        // Definições iniciais do formulário
        $this->retornaFormulario('regra');

        // Id regra
        $codigo = new Zend_Form_Element_Text('RGEX_ID_REGRA_EXERCICIO');
        $codigo->setLabel('Codigo:');
        $codigo->setAttrib('size', '10');
        $codigo->setAttrib('style', ' background: #CDCDCD; font-weight: bold; text-align: center;"');

        // Ano Exercicio
        $tbExercicio = new Orcamento_Business_Negocio_Exercicio ();
        $txtAno = new Zend_Form_Element_Select('RGEX_AA_ANO');
        $txtAno->setLabel('Definir Exercicio Orçamentário:');
        $txtAno->addFilter('StripTags')
            ->addMultiOptions(array(
                '' => 'Definir Exercício Orçamentário:')
            )->addMultiOptions(
            $tbExercicio->retornaCombo()
        )->setRequired(true);

        // Cria o campo de descrição da regra
        $txtDescricao = new Zend_Form_Element_Text('RGEX_DS_REGRA_EXERCICIO');

        // Define opções o controle $txtDescricao
        $txtDescricao->setLabel('Descrição da Regra de Ajuste:');
        $txtDescricao->setAttrib('size', 40);
        $txtDescricao->setAttrib('maxlength', 45);
        $txtDescricao->addFilter('StringTrim');
        $txtDescricao->setRequired(true);


        // Cria o campo de percentual de ajuste
        $intPercent = new Zend_Form_Element_Text('RGEX_VL_PERCENTUAL');

        // Definições de percentual
        $intPercent->setLabel('Percentual de Ajuste:');
        $intPercent->setAttrib('size', '10');
        $intPercent->setAttrib('maxlength', '10');
        $intPercent->setRequired(true);

        // Campo de incidencia da regra.
        $reicregra = new Zend_Form_Element_Select('RGEX_DS_INCIDENCIA_REGRA');
        $reicregra->setLabel('Campo de incidência da regra:');
        $reicregra->addFilter('StripTags')
            ->addMultiOptions(array(
                'Composição da base' => 'Composição da base',
                'Reajuste do exercício' => 'Reajuste do exercício',
                'Ajuste ao limite' => 'Ajuste ao limite',
                )
            )->setRequired(true);

        // PTRes - Programa de Trabalho Resumido
        $txtPTRes = new Zend_Form_Element_Text('DESP_CD_PT_RESUMIDO');
        $txtPTRes->setLabel('PTRES:')->setAttribs(
            array('size' => '90', 'maxlength' => 20))->setDescription(
            'A lista será carregada após digitar 3 caracteres.');

        // Elemento de Despesa
        $txtElemento = new Zend_Form_Element_Text(
            'DESP_CD_ELEMENTO_DESPESA_SUB');
        $txtElemento->setLabel('Natureza da despesa:')->setAttribs(
            array('size' => '70', 'maxlength' => 20))->setDescription(
            'A lista será carregada após digitar 3 caracteres.');

        // Tipo de Despesa
        $tbTipoDespesa = new Trf1_Orcamento_Negocio_Tipodespesa ();
        $cboTipoDespesa = new Zend_Form_Element_Select('DESP_CD_TIPO_DESPESA');
        $cboTipoDespesa->setLabel('Caráter da despesa:')
            ->addFilter('StripTags')
            ->addMultiOptions(array(
                " " => 'Selecione o caráter da despesa')
            )->addMultiOptions(
            $tbTipoDespesa->retornaCombo()
        );

        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit('Enviar');

        // Define opções do controle $cmdEnviar
        $cmdEnviar->setLabel('Enviar');
        $cmdEnviar->setAttrib('type', 'submit');
        $cmdEnviar->setAttrib('class', Orcamento_Business_Dados::CLASSE_SALVAR);

        // Adiciona os controles no formulário

        $this->addElement($codigo);
        $this->addElement($txtAno);
        $this->addElement($txtDescricao);
        $this->addElement($intPercent);
        $this->addElement($reicregra);
        $this->addElement($txtPTRes);
        $this->addElement($txtElemento);
        $this->addElement($cboTipoDespesa);
        $this->addElement($cmdEnviar);
    }

}
