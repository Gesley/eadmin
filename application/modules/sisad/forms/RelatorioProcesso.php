<?php
class Sisad_Form_RelatorioProcesso extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
             ->setName('pesquisaProcessos');
        
        $rhCentralLot = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rhCentralLot->getSecoestrf1();
        
        $pctt = new Application_Model_DbTable_SadTbAqvpViaPctt();
        $arraypctt = $pctt->getPCTT();
        
        $SadTbOrgjOrgaoJulgador = new Application_Model_DbTable_SadTbOrgjOrgaoJulgador();
        $orgaos = $SadTbOrgjOrgaoJulgador->getOrgaosJulgadores();
        
        $trf1Secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1Secao->setRequired(true)
                  ->setLabel('TRF1/Seções:')
                  ->addMultiOptions(array("0" => "Selecione"))
                  ->setOptions(array('style' => 'width:500px', 'class' => 'x-form-text'));
         
        foreach($secao as $s){
            $trf1Secao->addMultiOptions(array($s['SESB_SIGLA_SECAO_SUBSECAO']."|".$s['LOTA_COD_LOTACAO'] => $s['LOTA_DSC_LOTACAO']));
        }    

        $secao_subsecao = new Zend_Form_Element_Select('SECAO_SUBSECAO');
        $secao_subsecao->setLabel('Seção/Subseção')
                        ->setAttrib('style', 'width: 500px; ')
                         ->setRequired(true)
                         ->addMultiOptions(array( '' => 'Primeiro escolha TRF1/Seções'));
                        
                        
       $unidade = new Zend_Form_Element_Text('DOCM_CD_LOTACAO_GERADORA');
       $unidade->setLabel('Unidade Administrativa:')
                         ->setRequired(false)
                         ->setAttrib('style', 'width: 500px; ');
               
       $data_inicio = new Zend_Form_Element_Text('DATA_INICIAL');
       $data_inicio->setLabel('Data Inicial:')
                             ->setRequired(false)
                             ->setAttribs(array( 'style' => 'width: 100px',
                                                 'class' => 'data'));
       
       $data_final = new Zend_Form_Element_Text('DATA_FINAL');
       $data_final->setLabel('Data Final:')
                  ->setRequired(false)
                  ->setAttribs(array( 'style' => 'width: 100px',
                                      'class' => 'data'));  
       
       $assunto = new Zend_Form_Element_Select('DOCM_ID_PCTT');
       $assunto->setRequired(false)
                      ->setValue("")
                      ->setLabel('Assunto:')
                      ->addFilter('StripTags')
                      ->setOptions(array('style' => 'width:500px'))
                      ->addFilter('StringTrim')
                      ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione um assunto da lista. Os assuntos são tabelados de acordo com o PCTT.')
                      ->addValidator('NotEmpty')
                      ->addMultiOptions(array('' => ''));
       
       foreach ($arraypctt as $arraypctt_p){
            $assunto->addMultiOptions(array($arraypctt_p["AQVP_ID_PCTT"] => $arraypctt_p["DESCRICAO_PCTT"]));
       }
       
       $qtde_dias = new Zend_Form_Element_Text('QTDE_DIAS');
       $qtde_dias->setLabel('Parados na Unidade a mais de: (dias)')
                  ->setRequired(true)
                  ->setAttribs(array( 'style' => 'width: 40px', 'maxlength' => 4))
                  ->setFilters(array('Int'));
       
       $orgao_julgador = new Zend_Form_Element_Select('ORGAO_JULGADOR');
       $orgao_julgador->setRequired(false)
                        ->setLabel('Órgão Julgador:')
                        ->setAttrib('style', 'width: 400px; ')
                        ->addMultiOptions(array( '0' => 'TODOS'));
                
       foreach ($orgaos as $o){
            $orgao_julgador->addMultiOptions(array($o["ORGJ_CD_ORGAO_JULGADOR"] => $o["ORGJ_NM_ORGAO_JULGADOR"]));
       }
       
       $ordenacao = new Zend_Form_Element_Select('ORDER');
       $ordenacao->setLabel('Ordenação:')
                        ->setAttrib('style', 'width: 120px; ')
                         ->setRequired(false)
                         ->addMultiOptions(array( 'ASC' => 'Crescente', 'DESC' => 'Decrescente'));
       
       $relator = new Zend_Form_Element_Text('RELATOR');
       $relator->setLabel('Relator:')
                         ->setRequired(false)
                         ->setAttrib('style', 'width: 500px;')
               ->setDescription('A lista com os nomes será carregada após digitar no mínimo três caracteres');
        
       $matriculaRelator = new Zend_Form_Element_Hidden('MATRICULA_RELATOR');
       $matriculaRelator->setRequired(false)
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
              
        
       $submit = new Zend_Form_Element_Submit('Pesquisar');
       
       $this->addElements(array($trf1Secao,
                                $secao_subsecao,
                                $unidade,
                                $assunto,
                                $orgao_julgador,
                                $relator,
                                $data_inicio,
                                $data_final,
                                $qtde_dias,
                                $ordenacao,
                                $matriculaRelator,
                                $submit));
    }
}