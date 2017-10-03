<?php

/**
 * @category	TRF1
 * @package		Sisad_Form_Documento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de formulário para documentos
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Sisad_Form_Assinatura extends Zend_Form {

    public function init() {
        $userNs = new Zend_Session_Namespace('userNs');

        $radio_tipo_assinatura = new Zend_Form_Element_Radio('TIPO_ASSINATURA');
        $radio_tipo_assinatura->setRequired()
                ->setLabel('*Tipo de assinatura:')
                ->setMultiOptions(array('senha' => 'Assinatura por senha', 'certificado' => 'Assinatura por certificado digital'))
                ->setValue('senha');

        $usuario = new Zend_Form_Element_Text('USUARIO');
        $usuario->setLabel('Usuário logado:')
                ->setAttrib('readonly', 'readonly')
                ->setAttrib('style', 'width: 500px;')
                ->setValue($userNs->matricula . ' - ' . $userNs->nome);

        $senha = new Zend_Form_Element_Password('SENHA');
        $senha->setLabel('Digite a senha:')
                ->setDescription('O usuário será desconectado caso a senha não esteja correta.');

        $assinar = new Zend_Form_Element_Submit('BT_ASSINAR');
        $assinar->setLabel('Assinar');

        $this->addElements(array(
            $radio_tipo_assinatura
            , $usuario
            , $senha
            , $assinar
        ));
    }

}
