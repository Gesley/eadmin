<?php

/**
 * Contém formularios da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Form
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Disponibiliza o formulário para entrada de dados sobre inciso.
 *
 * @category Orcamento
 * @package Orcamento_Form_Ptres
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Inciso extends Orcamento_Form_Base {

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {
        // Definições iniciais do formulário
        $this->retornaFormulario('inciso');
        
        // Cria o campo oculto INCI_ID_INCISO
        $campoId = new Zend_Form_Element_Hidden('INCI_ID_INCISO');

        // Cria o campo INCI_VL_INCISO
        $campoValor = new Zend_Form_Element_Text('INCI_VL_INCISO');
        $campoValor->setLabel('Inciso números romanos:')
                ->setAttrib('size', '6')
                ->setAttrib('maxlength', 6)
                ->addFilter('StringTrim')
                ->addValidator('Alnum')
                ->setRequired(true);

        // Cria o campo INCI_DS_INCISO
        $campoDescricao = new Zend_Form_Element_Text('INCI_DS_INCISO');
        $campoDescricao->setLabel('Descrição inciso:')
                ->setAttrib('size', 40)
                ->setAttrib('maxlength', 400)
                ->addFilter('StringTrim')
                ->setRequired(true);

        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit('Enviar');
        $cmdEnviar->setLabel('Incluir')
                ->setAttrib('type', 'submit')
                ->setAttrib('class', Orcamento_Business_Dados::CLASSE_SALVAR);

        // Adiciona os controles no formulário
        $this->addElement($campoId)
                ->addElement($campoValor)
                ->addElement($campoDescricao)
                ->addElement($cmdEnviar);
    }
}