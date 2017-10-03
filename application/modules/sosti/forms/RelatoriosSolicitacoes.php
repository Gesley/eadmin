<?php
class Sosti_Form_RelatoriosSolicitacoes extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $rhCentralLot = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rhCentralLot->getSecoestrf1();
        $getLotacao = $rhCentralLot->getLotacao();
        
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
                             ->setRequired(true)
                             ->setAttribs(array( 'style' => 'width: 100px',
                                                 'class' => 'data'));
       
       $data_final = new Zend_Form_Element_Text('DATA_FINAL');
       $data_final->setLabel('Data Final:')
                  ->setRequired(true)
                  ->setAttribs(array( 'style' => 'width: 100px',
                                      'class' => 'data'));  
       
       $ordenacao = new Zend_Form_Element_Select('ORDER');
       $ordenacao->setLabel('Ordenação:')
                        ->setAttrib('style', 'width: 120px; ')
                         ->setRequired(false)
                         ->addMultiOptions(array( 'ASC' => 'Crescente', 'DESC' => 'Decrescente'));
       
       $exibicao = new Zend_Form_Element_Select('EXIBICAO');
       $exibicao->setLabel('Visualização do Relatório:')
                        ->setAttrib('style', 'width: 120px; ')
                         ->setRequired(false)
                         ->addMultiOptions(array( 'C' => 'Completo', 'S' => 'Simplificado', 'E' => 'Estatística'));
        
       $nr_tombo = new Zend_Form_Element_Text('NR_TOMBO');
       $nr_tombo->setLabel('Nº do tombo:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('style','width: 100px')
                ->addValidator('NotEmpty');
        
       $submit = new Zend_Form_Element_Submit('Pesquisar');
       
       $this->addElements(array($trf1Secao,
                                $secao_subsecao,
                                $unidade,
                                $data_inicio,
                                $data_final,
                                $ordenacao,
                                $exibicao,
                                $nr_tombo,
                                $submit));
    }
}