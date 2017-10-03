<?php
class Soseg_Form_TrocarServico extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
     
        $idDocumento = new Zend_Form_Element_Hidden('SSOL_ID_DOCUMENTO');
        $idDocumento->setValue($decodeSolic["SSOL_ID_DOCUMENTO"])
                    ->removeDecorator('Label')
                    ->removeDecorator('HtmlTag');;
        
        $movi_id_movimentacao = new Zend_Form_Element_Hidden('MOVI_ID_MOVIMENTACAO');
        $movi_id_movimentacao->setValue($decodeSolic["MOFA_ID_MOVIMENTACAO"])
                             ->removeDecorator('Label')
                             ->removeDecorator('HtmlTag');
        
        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                            ->setLabel('Descrição da troca de serviço:')
                            ->setOptions(array('style' => 'width:500px'))
                            ->addValidator('StringLength', false, array(5, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter($Zend_Filter_HtmlEntities);
        
        $novoServico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $novoServico->setRequired(true)
                    ->setLabel('Novo serviço:')
                    ->addMultiOptions(array('' => '::SELECIONE::'));
        
        $ssol_sg_tipo_tombo = new Zend_Form_Element_Text('SSOL_SG_TIPO_TOMBO');
        $ssol_sg_tipo_tombo->setValue('T')
                           ->addFilter('StripTags')
                           ->addFilter('StringTrim')
                           ->addValidator('NotEmpty')
                           ->removeDecorator('Label')
                           ->removeDecorator('HtmlTag');
        
        $ssol_nr_tombo = new Zend_Form_Element_Text('SSOL_NR_TOMBO');
        $ssol_nr_tombo->setLabel('Nº do Tombo:')
                      ->addFilter('StripTags')
                      ->addFilter('StringTrim')
                      ->addValidator('NotEmpty');

        $de_mat = new Zend_Form_Element_Textarea('DE_MAT');
        $de_mat->setLabel('*Descrição do Tombo')
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty')
               ->setValue('Primeiro informe Nº do Tombo')
               ->setAttrib('disabled', 'disabled')
               ->setAttrib('style', 'width: 800px; height: 30px;')
               ->setAttrib('class', 'erroInputSelect');
        
        $sses_dt_inicio_video = new Zend_Form_Element_Text('SSES_DT_INICIO_VIDEO');
        $sses_dt_inicio_video->setRequired(false)
                        ->setLabel('*Data e hora de início da videoconferência:')
                        ->setAttrib('style', 'width: 120px;')
                        ->addValidator( new Zend_Validate_Date(array('format'=>'dd/MM/yyyyHH:mm:ss')))
                        ->setDescription('Fomato de data/hora deve ser dd/mm/yyyy hh:mm:ss');
        
        $docm_ds_hash_red = new Zend_Form_Element_File('DOCM_DS_HASH_RED');
        $docm_ds_hash_red->setLabel('Inserir Anexos:')
                         ->setRequired(false)
                        ->addValidator(new Zend_Validate_File_Extension(array('pdf')))
                        ->addValidator('Size', false, 52428800) // limit to 50m
                        ->setMaxFileSize(52428800)
                        ->addValidator('Count', false, array('min' => 0, 'max' => 1))
                        ->setDestination(APPLICATION_PATH . '/../temp')
                        ->setDescription('Somente serão aceitos arquivos com o formato PDF. Com tamanho máximo de 50 Megas.');

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($idDocumento,$movi_id_movimentacao,  $novoServico,$sses_dt_inicio_video, $mofa_ds_complemento,
                                 $ssol_nr_tombo, $de_mat, $docm_ds_hash_red ,$submit));
    }

}