<?php

class Sosti_Form_LabCheckList extends Zend_Form {

    public function init() {

        $this->setAction('')
                ->setMethod('post')
                ->setAttrib('id','form-checklist')
                ->setName('checklist');

        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setRequired(false)
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');
        $controller = new Zend_Form_Element_Hidden('controller');
        $controller->setRequired(false)
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        
        $lfse_id_documento = new Zend_Form_Element_Hidden('LFSE_ID_DOCUMENTO');
        $lfse_id_documento->setLabel('Solicitação N°:')
                ->setAttrib('style', 'width: 500px; ')
                ->removeDecorator('label')
                ->setAttrib('size', 35);
        
        $docm_nr_documento = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO');
        $docm_nr_documento->setLabel('Solicitação N°:')
                ->setAttrib('style', 'width: 500px; ')
                ->removeDecorator('label')
                ->setAttrib('size', 35);

        $lfse_ds_servico_executado = new Zend_Form_Element_Text('LFSE_DS_SERVICO_EXECUTADO');
        $lfse_ds_servico_executado->setRequired(true)
                ->setLabel('*Serviço Executado:')
                ->setAttrib('style', 'width: 500px; ')
                ->setAttrib('data-tipo', 'obrigatorio')
                ->setAttrib('class', 'campoObrig')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('StringLength', false, array(5, 200))
                ->setAttrib('size', 60);

        $TOMBO = new Zend_Form_Element_Text('SSOL_NR_TOMBO_PESQUISA');
        $TOMBO->setLabel('*Tombo:')
                ->setRequired(true)
                ->setAttrib('data-tipo', 'obrigatorio')
                ->setAttrib('class', 'campoObrig')
                ->setOptions(array('style' => 'width: 50px'));
        
        $NR_TOMBO = new Zend_Form_Element_Hidden('SSOL_NR_TOMBO');
        $NR_TOMBO->setLabel('Tombo:')
                ->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->removeDecorator('label')
                ->setOptions(array('style' => 'width: 50px'));

        $TIPO_TOMBO = new Zend_Form_Element_Text('TI_TOMBO');
        $TIPO_TOMBO->setLabel('Tipo de tombo:')
                ->setAttrib('disabled', 'disabled')
                ->setOptions(array('style' => 'width: 50px'));

        $nome_rede = new Zend_Form_Element_Text('LFSE_NO_COMPUTADOR');
        $nome_rede->setAttrib('style', 'width: 500px; ')
                ->setLabel('Nome na Rede:')
                ->setAttrib('maxlength', 60)
                ->addValidator('StringLength', false, array(5, 150))
                ->setAttrib('size', 50);

        $lfse_dt_entrada = new Zend_Form_Element_Hidden('MOFA_DH_FASE');
        $lfse_dt_entrada->setLabel('Data de Entrada:')
                ->setAttrib('readonly', 'readonly')
                ->setAttrib('style', 'width: 500px; ')
                ->setRequired(true)
                ->removeDecorator('Label');

        /* ------------------- BACKUP --------------------- */
        
        $bkp_tombo_backup_pesquisa = new Zend_Form_Element_Text('LBKP_NR_TOMBO_PESQUISA');
        $bkp_tombo_backup_pesquisa->setLabel('Tombo do Backup:')
                ->setAttrib('style', 'width: 50px; ')
                ->setDescription('Informe um tombo de backup para empréstimo, se necessário.'); 
        
        $bkp_tombo_backup = new Zend_Form_Element_Hidden('LBKP_NR_TOMBO');
        $bkp_tombo_backup->setLabel('Tombo do Backup:')
                ->removeDecorator('label');
        
        /* -------------- FICHA DE SERVIÇO ---------------------- */

        $objTipoUsuario = new Application_Model_DbTable_SosTbLtpTipoUsuario();
        $tipos = $objTipoUsuario->gettipoUsuariosLaboratorio();
        $lfse_id_tp_usuario = new Zend_Form_Element_Select('LFSE_ID_TP_USUARIO');
        $lfse_id_tp_usuario->setLabel('*Tipo de Usuário:')
                ->setRequired(true)
                ->setAttrib('data-tipo', 'obrigatorio')
                ->setAttrib('class', 'campoObrig')
                ->setAttrib('style', 'width: 500px;')
                ->addMultiOptions(array(''=>''));
        foreach ($tipos as $t) {
            $lfse_id_tp_usuario->addMultiOptions(array($t["LTPU_ID_TP_USUARIO"] => $t["LTPU_DS_TP_USUARIO"]));
        }
        
        $lfse_ic_backup = new Zend_Form_Element_Checkbox('LFSE_IC_BACKUP');
        $lfse_ic_backup->setLabel('Backup dos Dados')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left')
                ->setValue('Teste');

        $lfse_ic_formatacao = new Zend_Form_Element_Checkbox('LFSE_IC_FORMATACAO');
        $lfse_ic_formatacao->setLabel('Formatação da máquina')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left')
                ->setValue('Teste');

        $lfse_ic_exclusao_arqtemp = new Zend_Form_Element_Checkbox('LFSE_IC_EXCLUSAO_ARQTEMP');
        $lfse_ic_exclusao_arqtemp->setLabel('Exclusão de Arquivos Temporários')
                ->setValue('Teste')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left');

        $lfse_ic_exclusao_profile = new Zend_Form_Element_Checkbox('LFSE_IC_EXCLUSAO_PROFILE');
        $lfse_ic_exclusao_profile->setLabel('Exclusão de Profile')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left')
                ->setValue('Teste');

        $lfse_ic_winupdate = new Zend_Form_Element_Checkbox('LFSE_IC_WINUPDATE');
        $lfse_ic_winupdate->setLabel('Execução do Windows Update')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left')
                ->setValue('Teste');

        $lfse_ic_desfragmentacao = new Zend_Form_Element_Checkbox('LFSE_IC_DESFRAGMENTACAO');
        $lfse_ic_desfragmentacao->setLabel('Desfragmentação')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left')
                ->setValue('Teste');

        $lfse_ic_scandisk = new Zend_Form_Element_Checkbox('LFSE_IC_SCANDISK');
        $lfse_ic_scandisk->setLabel('Execução do Scandisk')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left')
                ->setValue('Teste');

        $lfse_ic_manutencao_externa = new Zend_Form_Element_Checkbox('LFSE_IC_MANUTENCAO_EXTERNA');
        $lfse_ic_manutencao_externa->setLabel('Manutenção Externa')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left')
                ->setValue('Teste');

        $lfse_ic_garantia = new Zend_Form_Element_Checkbox('LFSE_IC_GARANTIA');
        $lfse_ic_garantia->setLabel('Garantia')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
                ->setAttrib('style', 'float:left')
                ->setValue('Teste');

        $Motivo = new Zend_Form_Element_Text('LFSE_DS_MOTIVO_MANUTENCAO');
        $Motivo->setLabel('*Motivo da manutenção:')
                ->setRequired(true)
                ->setAttrib('data-tipo', 'obrigatorio')
                ->setAttrib('class', 'campoObrig')
                ->setAttrib('style', 'width: 500px; ')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('size', 150)
                ->setAttrib('maxlength', 100)
                ->addValidator('StringLength', false, array(5, 200));

        $submit = new Zend_Form_Element_Submit('Salvar');

        $DOC_ID = new Zend_Form_Element_Hidden('DOC_ID');
        $DOC_ID->setRequired(false)
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');
        
        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $this->addElements(
                array(
                    $acao,
                    $controller,
                    $DOC_ID,
                    $lfse_id_documento,
                    $docm_nr_documento,
                    $lfse_dt_entrada,
                    $lfse_ds_servico_executado,
                    $lfse_id_tp_usuario,
                    $TOMBO,
                    $NR_TOMBO,
                    $TIPO_TOMBO,
                    $bkp_tombo_backup_pesquisa,
                    $bkp_tombo_backup,
                    $nome_rede,
                    $Motivo,
                    $lfse_ic_backup,
                    $lfse_ic_formatacao,
                    $lfse_ic_exclusao_arqtemp,
                    $lfse_ic_exclusao_profile,
                    $lfse_ic_winupdate,
                    $lfse_ic_desfragmentacao,
                    $lfse_ic_scandisk,
                    $lfse_ic_manutencao_externa,
                    $lfse_ic_garantia,
                    $submit,
                    $obrigatorio)
        );
    }

}
