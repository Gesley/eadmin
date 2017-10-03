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
 * @package Orcamento_Form_Permissao
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Permissao extends Orcamento_Form_Base {

    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init() {
        // Definições iniciais do formulário
        $this->retornaFormulario('permissao');

        $txtCodigo = new Zend_Form_Element_Text('PERM_ID_PERMISSAO_ACESSO');

        // Define opções o controle $txtCodigo
        $txtCodigo->setLabel('Código:');
        $txtCodigo->setAttrib('size', 5);
        $txtCodigo->setAttrib('maxlength', 1);
        $txtCodigo->addFilter('StringTrim');
        // $txtCodigo->addFilter ( 'Digits' );
        $txtCodigo->addValidator('Digits');

        // Cria o campo PERM_CD_MATRICULA
        $txtUsuario = new Zend_Form_Element_Text('PERM_CD_MATRICULA');

        // Define opções o controle $txtUsuario
        $txtUsuario->setLabel('Matricula do Usuário:');
        $txtUsuario->setAttrib('size', 8);
        $txtUsuario->setAttrib('style', 'width:450px;');
        $txtUsuario->setAttrib('maxlength', 45);
        $txtUsuario->addFilter('StringTrim');
        $txtUsuario->setRequired(true);

        // Cria o campo PERM_ID_PERFIL
        $facadePerm = new Orcamento_Facade_Permissao();
        $cboPerfil = new Zend_Form_Element_Select('PERM_DS_PERFIL');
        // Define opções o controle $cboPerfil
        $cboPerfil->setLabel('Perfil do Usuário:');
        $cboPerfil->addFilter('StripTags');
        $cboPerfil->addMultiOptions(array('' => 'Selecione'));
        $cboPerfil->addMultiOptions(
            array(
                '' => 'Selecione',
                'consulta' => 'Consulta',
                'desenvolvedor' => 'Desenvolvedor',
                'dipor' => 'Dipor',
                'diefi' => 'Diefi',
                'secretaria' => 'Secretaria',
                'secretaria_reserva' => 'Secretaria Reserva',
                'seccional' => 'Seccional',
                'planejamento' => 'Planejamento',
            )
        );
        $cboPerfil->setRequired(true);

        // Cria o campo PERM_CD_UNIDADE_GESTORA
        $facadeUg = new Orcamento_Facade_Ug();
        $cboUg = new Zend_Form_Element_Select('PERM_CD_UNIDADE_GESTORA');
        // Define opções o controle $cboUg
        $cboUg->setLabel('UG:');
        $cboUg->addFilter('StripTags');
        $cboUg->setAttrib('style', 'width:450px;');
        $cboUg->addMultiOptions(array('' => 'Selecione'));
        $cboUg->addMultiOptions($facadeUg->retornaComboUg());

        // Chekbox para todas ugs
        $checkUg = new Zend_Form_Element_Checkbox('CH_UG');
        $checkUg->setAttrib('style', 'float:left');
        $checkUg->setLabel('todas');

        $cboAcResp = new Zend_Form_Element_Text('AUTO_CP_RESPONSABILIDADE');
        $cboAcResp->setLabel('Pesquisa de Responsabilidade:');
        $cboAcResp->setAttrib('style', 'width:450px;');
        $cboAcResp->setRequired(false);

        $cboResp = new Zend_Form_Element_Textarea('PERM_DS_RESPONSABILIDADE');
        $cboResp->setLabel('Responsabilidade:');
        $cboResp->setAttrib('size', 5);
        $cboResp->setAttrib('style', 'width:450px;');
        $cboResp->setRequired(false);

        // Chekbox para todos os responsaveis
        $checkResp = new Zend_Form_Element_Checkbox('CH_RESP');
        $checkResp->setAttrib('style', 'float:left');
        $checkResp->setLabel('todas');

        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit('Enviar');

        // Define opções do controle $cmdEnviar
        $cmdEnviar->setLabel('Enviar');
        $cmdEnviar->setAttrib('type', 'submit');
        $cmdEnviar->setAttrib('class',
            Orcamento_Business_Dados::CLASSE_SALVAR);

        // Adiciona os controles no formulário
        $this->addElement($txtCodigo);
        $this->addElement($txtUsuario);
        $this->addElement($cboPerfil);
        $this->addElement($cboUg);
        $this->addElement($checkUg);
        $this->addElement($cboAcResp);
        $this->addElement($cboResp);
        $this->addElement($checkResp);
        $this->addElement($cmdEnviar);
    }

}