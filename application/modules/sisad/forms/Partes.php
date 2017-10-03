<?php
class Sisad_Form_Partes extends Zend_Form {

   public function init()
   {
        $this->setAction('save')
             ->setAttrib('enctype', 'multipart/form-data')
             ->setMethod('post')
             ->setName('cadastroPartes');
        
        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
         
        $papd_cd_matricula_interessado = new Zend_Form_Element_Text('PAPD_CD_MATRICULA_INTERESSADO');
        $papd_cd_matricula_interessado->setRequired(false)
                                   ->setLabel('Adicionar Pessoa TRF/Seções: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: TR22 ou Maria');

        $papd_id_pessoa = new Zend_Form_Element_Text('PAPD_ID_PESSOA_FISICA');
        $papd_id_pessoa->setRequired(false)
                                   ->setLabel('Adicionar Pessoa Externa: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
        
        $papd_id_pessoa_jur = new Zend_Form_Element_Text('PAPD_ID_PESSOA_JURIDICA');
        $papd_id_pessoa_jur->setRequired(false)
                                   ->setLabel('Adicionar Pessoa Jurídica: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
       
        $tipoParte = new Zend_Form_Element_Select('TIPO_PARTE');
        $tipoParte->setRequired(false)
                ->setLabel('Selecione o Tipo:')
                ->setOptions(array('style' => 'width:200px', 'class' => 'x-form-text'))
                ->addMultioptions(array("P" => "Pessoa TRF/Seções",
                                        "F" => "Pessoa Física Externa",
                                        "J" => "Pessoa Jurídica",
                                        "U" => "Unidade Administrativa"));
        
        $trf1Secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1Secao->setRequired(false)
                  ->setLabel('TRF1/Seções:')
                  ->addMultiOptions(array("0" => "Selecione"))
                  ->setOptions(array('style' => 'width:380px', 'class' => 'x-form-text'));
         
        foreach($secao as $s){
            $trf1Secao->addMultiOptions(array($s['SESB_SIGLA_SECAO_SUBSECAO']."|".$s['LOTA_COD_LOTACAO'] => $s['LOTA_DSC_LOTACAO']));
        }    
        
        $unidade = new Zend_Form_Element_Text('UNIDADE_PARTE');
        $unidade->setLabel('Adicionar Unidade Administrativa:')
                         ->setRequired(true)
                         ->setAttrib('style', 'width: 500px; ')
                        ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: SECIN');

        $replicaVista = new Zend_Form_Element_Checkbox('REPLICA_VISTAS');
        $replicaVista->setLabel('Replicar partes para vistas')
                     ->setCheckedValue('S')
                    ->setUncheckedValue('N');
        
        
        $this->addElements(array($papd_cd_matricula_interessado,
                                 $papd_id_pessoa, 
                                 $papd_id_pessoa_jur,
                                 $tipoParte,
                                 $trf1Secao,
                                 $unidade,
                                 $replicaVista
                                 ));

     }

}