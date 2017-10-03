<?php

class Tarefa_Form_Tarefa extends Zend_Form 
{

    public function init() 
    {
        $this->setAction('')
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAttrib('id', 'form')
            ->setMethod('post')
            ->setAttrib('class', 'formSave');
                
        $tipoTarefa = new Tarefa_Model_DataMapper_TipoTarefa();
        $atendentes = new Os_Model_DataMapper_Atendente();
        $statusTarefaSolicit = new Tarefa_Model_DataMapper_TarefaStatusSolicitacao();
        
        $this->setAction('')
             ->setMethod('post');
        
        $id = new Zend_Form_Element_Hidden('TARE_ID_TAREFA');
        $id->addFilter('Int')
           ->removeDecorator('Label')
           ->removeDecorator('HtmlTag');
        
        $perfilUsuario = new Zend_Form_Element_Hidden('PERFIL_USER');
        $perfilUsuario->removeDecorator('Label')
            ->removeDecorator('HtmlTag');
        
        $idMovimentacao = new Zend_Form_Element_Hidden('MOFA_ID_MOVIMENTACAO');
        $idMovimentacao->removeDecorator('Label')
            ->removeDecorator('HtmlTag');
        
        $idSaveTarefa = new Zend_Form_Element_Hidden('ID_SAVE_TAREFA');
        $idSaveTarefa->removeDecorator('Label')
            ->removeDecorator('HtmlTag');
        
        $tipo = new Zend_Form_Element_Select('TARE_ID_TIPO_TAREFA');
        $tipo->setLabel('Tipo de Tarefa:')
             ->addMultiOptions($tipoTarefa->fetchPairs());
        
        $descricaoTarefa = new Zend_Form_Element_Textarea('TARE_DS_TAREFA');
        $descricaoTarefa->setLabel('Descrição da Tarefa:')
            ->addFilter('StripTags')
            ->setDescription('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.')
            ->setAttrib('style', 'width: 600px; height: 30px;')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('StringLength', false, array(5, 500))
            ->addValidator('NotEmpty');
        
        $responsavelTarefa = new Zend_Form_Element_Select('TASO_CD_MATR_ATEND_TAREFA');
        $responsavelTarefa->setLabel('Responsável pela Tarefa:')
            ->addMultiOptions(array('' => ':: Selecione ::'))
            ->addMultiOptions($atendentes->fetchPairs(2));
        
        $aceitofabrica = new Zend_Form_Element_Radio('TASO_IC_ACEITE_ATENDENTE');
        $aceitofabrica->setLabel('Aceito pela fábrica:')
            ->setRequired(true)
            ->setMultiOptions(array('S'=>'Sim', 'N'=>'Não'))
            ->setValue('N');
        
        $status = new Zend_Form_Element_Select('TASO_IC_SITUACAO_NEGOCIACAO');
        $status->setLabel('Status:')
            ->addMultiOptions(array('' => ':: Selecione ::'))
            ->addMultiOptions($statusTarefaSolicit->getStatus());
        
        $descricaoAtendente = new Zend_Form_Element_Textarea('TASO_DS_JUSTIF_ATENDENTE');
        $descricaoAtendente->setLabel('Justificativa:')
            ->addFilter('StripTags')
            ->setDescription('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.')
            ->setAttrib('style', 'width: 600px; height: 30px;')
            ->addFilter('StringTrim')
//            ->setRequired(true)
            ->addValidator('StringLength', false, array(5, 500))
            ->addValidator('NotEmpty');
        
        $aceitoSolicitante = new Zend_Form_Element_Radio('TASO_IC_ACEITE_SOLICITANTE');
        $aceitoSolicitante->setLabel('De Acordo?')
            ->setRequired(true)
            ->setMultiOptions(array('S'=>'Sim', 'N'=>'Não'))
            ->setValue('N');
        
        $descricaoSolicitante = new Zend_Form_Element_Textarea('TASO_DS_JUSTIF_SOLICITANTE');
        $descricaoSolicitante->setLabel('Justificativa:')
            ->addFilter('StripTags')
            ->setDescription('ATENÇÃO: Os caracteres especiais tais como (ç,%,~,^, etc) são codificados por questões de segurança, por isso, os caracteres informados podem corresponder a uma quantidade real maior de caracteres.')
            ->setAttrib('style', 'width: 600px; height: 30px;')
            ->addFilter('StringTrim')
//            ->setRequired(true)
            ->addValidator('StringLength', false, array(5, 500))
            ->addValidator('NotEmpty');
        
        $anexosTarefa = new Zend_Form_Element_File('ANEXOS_TAREFA');
        $anexosTarefa->setLabel('Anexos')
                ->setRequired(false)
                ->setIsArray(true)
                ->addValidator('Size', false, array('max' => '52428800'))
                ->setMaxFileSize(52428800)
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setAttribs(array('class' => 'multi', 'maxlength' => 20, 'multiple' => true))
//                ->addValidator('File_Upload', true, array('messages'=>'YOUR MESSAGE HERE'))
//                ->addValidator(new App_Form_Validate_Anexos())
                ->setDescription('Até 20 Anexos. Soma dos arquivos até 50 Megas.');
        
        $anexosNegociacaoFabrica = new Zend_Form_Element_File('ANEXOS_NEGOCIACAO_FABRICA');
        $anexosNegociacaoFabrica->setLabel('Anexos')
                ->setRequired(false)
                ->setIsArray(true)
                ->addValidator('Size', false, array('max' => '52428800'))
                ->setMaxFileSize(52428800)
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setAttribs(array('class' => 'multi', 'maxlength' => 20, 'multiple' => true))
//                ->addValidator('File_Upload', true, array('messages'=>'YOUR MESSAGE HERE'))
//                ->addValidator(new App_Form_Validate_Anexos())
                ->setDescription('Até 20 Anexos. Soma dos arquivos até 50 Megas.');
        
        $anexosNegociacaoGestao = new Zend_Form_Element_File('ANEXOS_NEGOCIACAO_GESTAO');
        $anexosNegociacaoGestao->setLabel('Anexos')
                ->setRequired(false)
                ->setIsArray(true)
                ->addValidator('Size', false, array('max' => '52428800'))
                ->setMaxFileSize(52428800)
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setAttribs(array('class' => 'multi', 'maxlength' => 20, 'multiple' => true))
//                ->addValidator('File_Upload', true, array('messages'=>'YOUR MESSAGE HERE'))
//                ->addValidator(new App_Form_Validate_Anexos())
                ->setDescription('Até 20 Anexos. Soma dos arquivos até 50 Megas.');
        
        $salvar = new Zend_Form_Element_Submit('Salvar');
        $this->addElements(array(
            $id,
            $tipo,
            $idSaveTarefa,
            $descricaoTarefa,
            $idMovimentacao,
            $perfilUsuario,
            $anexosTarefa,
            $responsavelTarefa,
            $aceitofabrica,
            $status,
            $descricaoAtendente,
            $anexosNegociacaoFabrica,
            $aceitoSolicitante,
            $descricaoSolicitante,
            $anexosNegociacaoGestao,
            $salvar
        ));
    }

}
