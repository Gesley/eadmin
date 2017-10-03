<?php
class Sisad_Form_FormularioCadTreinamento extends Zend_Form
{
    public function init()
    {
        $this->setAction('save')
             ->setMethod('post');

       $userNamespace = new Zend_Session_Namespace('userNs'); 
       
        $ctre_cod_id = new Zend_Form_Element_Hidden('CTRE_COD_ID');
        $ctre_cod_id->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $ctre_cod_curso = new Zend_Form_Element_Hidden('CTRE_COD_CURSO');
        $ctre_cod_curso->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

       $usuario = new Zend_Form_Element_Text('USUARIO');
       $usuario//->setRequired(true)
                               ->setLabel('Solicitante:')
                               ->addFilters(array ('StripTags', 'StringTrim' ))
                               ->setOptions(array('style' => 'width:500px'))
                               ->setValue($userNamespace->matricula.' - '.$userNamespace->nome)
                               ->addValidator('NotEmpty')
                               ->setAttrib('readonly', 'readonly');

        $ctre_desc_curso = new Zend_Form_Element_Text('CTRE_DESC_CURSO');
        $ctre_desc_curso->setRequired(true)
                        ->setLabel('*Nome do Curso:')
                        ->addFilters(array ('StripTags', 'StringTrim' ))
                        ->setAttrib('style', 'width: 600px; ')
                        ->addValidator('NotEmpty');
 
        
        $ctre_desc_instituicao = new Zend_Form_Element_Text('CTRE_DESC_INSTITUICAO');
        $ctre_desc_instituicao->setRequired(true)
                        ->setLabel('*Instituição Promotora:')
                        ->addFilters(array ('StripTags', 'StringTrim' ))
                        ->setAttrib('style', 'width: 500px; ')
                        ->addValidator('NotEmpty');
        
        $ctre_dat_inicio = new Zend_Form_Element_Text('CTRE_DAT_INICIO');
        $ctre_dat_inicio->setRequired(true)
                        ->setLabel('*Data de Início:')
                        ->addFilters(array ('StripTags', 'StringTrim' ))
                        ->setAttribs(array('style' => 'width: 80px;', 'class' => 'datepicker'))
                        ->addValidator('NotEmpty');
        
        $ctre_dat_fim = new Zend_Form_Element_Text('CTRE_DAT_FIM');
        $ctre_dat_fim->setRequired(true)
                        ->setLabel('*Data de Término:')
                        ->addFilters(array ('StripTags', 'StringTrim' ))
                        ->setAttribs(array('style' => 'width: 80px;', 'class' =>  'datepicker'))
                        ->addValidator('NotEmpty');
        
        $ctre_carga_horaria = new Zend_Form_Element_Text('CTRE_CARGA_HORARIA');
        $ctre_carga_horaria->setRequired(true)
                        ->setLabel('*Carga Horária:')
                        ->addFilter('StripTags')
                        ->setAttribs( array('style' => 'width: 40px;', 'title' => 'Digite somente números'))
                        ->addFilters(array('StringTrim', 'Digits'))
                        ->addValidator( 'NotEmpty');
        
        $ctre_tipo_certificado = new Zend_Form_Element_Select('CTRE_TIPO_CERTIFICADO');
        $ctre_tipo_certificado->setRequired(true)
                      ->setLabel('*Tipo de Curso:')
                      ->addFilters(array ('StripTags', 'StringTrim' ))
                      ->addMultiOptions(
                    array('A' => 'AÇÃO DE TREINAMENTO',
                          'P' => 'PÓS-GRADUAÇÃO',
                          'M' => 'MESTRADO',
                          'D' => 'DOUTORADO'
                          ));
        
        $ctre_telefone = new Zend_Form_Element_Text('CTRE_TELEFONE');
        $ctre_telefone->setRequired(false)
                        ->setLabel('Telefone Instituição:')
                        ->setAttribs( array('style' => 'width: 100px;', 'title' => 'Digite somente números', 'maxlength' => '12'))
                        ->addFilters(array('StringTrim', 'Digits', 'StripTags'));
        
        $ctre_email = new Zend_Form_Element_Text('CTRE_EMAIL');
        $ctre_email->setRequired(false)
                        ->setLabel('Email Instituição :')
                        ->setAttribs( array('style' => 'width: 350px;'))
                        ->addFilters(array('StringTrim', 'StripTags'));
        
        $docm_ds_hash_red = new Zend_Form_Element_File('DOCM_DS_HASH_RED');
        $docm_ds_hash_red->setLabel('*Inserir Documento:')
                ->setRequired(true)
//                ->addValidator(new Zend_Validate_File_Extension(array('pdf')))
                ->addValidator('Size', false, 10240000) // limit to 1m
                ->setMaxFileSize(10240000)
                ->addValidator('Count', false, array('min' => 0, 'max' => 1))
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setDescription('Somente serão aceitos arquivos com o tamanho máximo de 50 Megas.');
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($usuario, 
                                $ctre_tipo_certificado,
                                $ctre_desc_curso, 
                                $ctre_dat_inicio,
                                $ctre_dat_fim,
                                $ctre_carga_horaria,
                                $ctre_desc_instituicao,
                                $ctre_cod_curso,
                                $ctre_telefone,
                                $ctre_email,
                                $docm_ds_hash_red,
                                $submit));
    }
}