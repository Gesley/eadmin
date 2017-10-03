<?php
class Sisad_Form_AddComponente extends Zend_Form
{
    public function init()
    {
        $this/*->setAction('savecomponente')*/
             ->setAttrib('enctype', 'multipart/form-data')
             ->setMethod('post')
             /*->setName('addComponente')*/;

        $grdv_ds_grupo_divulgacao = new Zend_Form_Element_Text('GRDV_DS_GRUPO_DIVULGACAO');
        $grdv_ds_grupo_divulgacao->setRequired(true)
                ->setLabel('Nome do Grupo de Divulgação:')
                ->setAttrib('disabled', 'disabled')
                ->setAttrib('style', 'text-transform: uppercase; width: 540px;');
             
        
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
         
        $comp_cd_matricula_trf = new Zend_Form_Element_Text('COMP_CD_MATRICULA_TRF');
        $comp_cd_matricula_trf->setRequired(false)
                                   ->setLabel('Adicionar Pessoa TRF/Seções: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: TR22 ou Maria');

        $comp_id_pessoa_fisica = new Zend_Form_Element_Text('COMP_ID_PESSOA_FISICA');
        $comp_id_pessoa_fisica->setRequired(false)
                                   ->setLabel('Adicionar Pessoa Externa: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
        
        $comp_id_pessoa_juridica = new Zend_Form_Element_Text('COMP_ID_PESSOA_JURIDICA');
        $comp_id_pessoa_juridica->setRequired(false)
                                   ->setLabel('Adicionar Pessoa Jurídica: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
       
        $tipoPessoa = new Zend_Form_Element_Select('TIPO_PESSOA');
        $tipoPessoa->setRequired(false)
                ->setLabel('Selecione o Tipo:')
                ->setOptions(array('style' => 'width:200px', 'class' => 'x-form-text'))
                ->addMultioptions(array("P" => "Pessoa TRF/Seções",
                                        /*"F" => "Pessoa Física Externa",
                                        "J" => "Pessoa Jurídica",*/
                                        "U" => "Unidade Administrativa"));
        
        $trf1Secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1Secao->setRequired(false)
                  ->setLabel('TRF1/Seções:')
                  ->addMultiOptions(array("0" => "Selecione"))
                  ->setOptions(array('style' => 'width:380px', 'class' => 'x-form-text'));
         
        foreach($secao as $s){
            $trf1Secao->addMultiOptions(array($s['SESB_SIGLA_SECAO_SUBSECAO']."|".$s['LOTA_COD_LOTACAO'] => $s['LOTA_DSC_LOTACAO']));
        }    
        
        $unidade = new Zend_Form_Element_Text('UNIDADE_PESSOA');
        $unidade->setLabel('Adicionar Unidade Administrativa:')
                         ->setRequired(true)
                         ->setAttrib('style', 'width: 500px; ')
                        ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: SECIN');
        
        $grdv_id_grupo_divulgacao = new Zend_Form_Element_Hidden('GRDV_ID_GRUPO_DIVULGACAO');
        $grdv_id_grupo_divulgacao->setRequired(false)
                          ->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');

        $submit = new Zend_Form_Element_Submit('Salvar');
      
        $this->addElements(array($grdv_ds_grupo_divulgacao,
                                 $comp_cd_matricula_trf,
                                 $comp_id_pessoa_fisica, 
                                 $comp_id_pessoa_juridica,
                                 $tipoPessoa,
                                 $trf1Secao,
                                 $unidade,
                                 $grdv_id_grupo_divulgacao,
                                 $submit
                                 ));

     }

}