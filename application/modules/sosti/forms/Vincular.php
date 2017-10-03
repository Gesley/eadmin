<?php
class Sosti_Form_Vincular extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $mofa_id_movimentacao = new Zend_Form_Element_Hidden('MOFA_ID_MOVIMENTACAO');
        $mofa_id_movimentacao->addFilter('Int')
                             ->removeDecorator('Label')
                             ->removeDecorator('HtmlTag');
        
        $docm_id_documento = new Zend_Form_Element_Hidden('DOCM_ID_DOCUMENTO');
        $docm_id_documento->setRequired(false)
                           ->removeDecorator('Label')
                           ->removeDecorator('HtmlTag');
        
        $docm_nr_documento = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO');
        $docm_nr_documento->setRequired(false)
                           ->removeDecorator('Label')
                           ->removeDecorator('HtmlTag');
               
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                            ->setLabel('Descrição da Vinculação:')
                            ->setOptions(array('style' => 'width:500px'))
                            ->addValidator('StringLength', false, array(5, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));
        
        $docm_ds_hash_red = new Zend_Form_Element_File('DOCM_DS_HASH_RED');
        $docm_ds_hash_red->setLabel('Inserir Anexos:')
                                    ->setRequired(false)
                                    ->addValidator(new Zend_Validate_File_Extension(array('pdf')))
                                    ->addValidator('Size', false, 52428800) // limit to 50m
                                    ->setMaxFileSize(52428800)
                                    ->addValidator('Count', false, array('min' => 0, 'max' => 1))
                                    ->setDestination(APPLICATION_PATH . '/../temp')
                                    ->setDescription('Somente serão aceitos arquivos com o formato PDF. Com tamanho máximo de 50 Megas.');
		$redir_controller = new Zend_Form_Element_Hidden('redir_controller');
        $redir_controller->setRequired(false)
                           ->removeDecorator('Label')
                           ->removeDecorator('HtmlTag');
        $redir_action = new Zend_Form_Element_Hidden('redir_action');
        $redir_action->setRequired(false)
                           ->removeDecorator('Label')
                           ->removeDecorator('HtmlTag');
        $submit = new Zend_Form_Element_Submit('Salvar');

        $submit = new Zend_Form_Element_Submit('Salvar');
        $this->addElements(array($mofa_id_movimentacao, $docm_id_documento, $docm_nr_documento, 
                                 $mofa_ds_complemento, $docm_ds_hash_red, $submit, $redir_action,$redir_controller));
    }

}