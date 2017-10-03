<?php
class Sosti_Form_IntervaloData extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                
                ->setName('IntervaloData');
        

       $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
       $data_inicial
                    ->setRequired(true)
                    ->setValue('')
                    ->setLabel('Data inicial:')
                    ->addValidator( new Zend_Validate_Date(array('format'=>'dd/MM/yyyyHH:mm:ss')))
                    ->setDescription('Fomato de data/hora deve ser dd/mm/yyyy hh:mm:ss');
       
       $data_final = new Zend_Form_Element_Text('DATA_FINAL');
       $data_final
                    ->setRequired(true)
                    ->setValue('')
                    ->setLabel('Data final:')
                    ->addValidator( new Zend_Validate_Date(array('format'=>'dd/MM/yyyyHH:mm:ss')))
                    ->setDescription('Fomato de data/hora deve ser dd/mm/yyyy hh:mm:ss');
                    

        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico(); 
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixasGrupoServicoPorLotacao( 'TR', 2 );
        //$SgrsGrupoServico = $SadTbCxgsGrupoServico->getCaixaAtendimentoUsuGrupoServicoPorLotacao( 'AC' , 176 );
        
        /**
         *  Retira o grupo de Gestão para as Seções
         *  Retira o grupo Desenvolvimento.
         *  Retira o grupo Gestão da infra.
         */
        if($userNs->siglasecao != 'TR'){
            $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 118);
        }
//        $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 119);
        $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 120);
        $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 121);
        
        /** Habilita a caixa quando for avaliação automática */ 
        $locate = explode('/', $_SERVER['REQUEST_URI']);
        $end = end($locate);
        if ($end != "avaliacaoautomatica") {
            $SgrsGrupoServico = App_UtilArray::retiraposicaoarray2dby($SgrsGrupoServico, "SGRS_ID_GRUPO", 2);
        }
        
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setLabel('*Grupo de Serviço:');
        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'))
                        ->setDescription('Selecione o Grupo de Serviço');
        
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => $SgrsGrupoServico_p["SGRS_DS_GRUPO"]));
        endforeach;
        
        
	$sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setLabel('Serviço:')
                        ->addMultiOptions(array('' => ''))
                        ->setAttrib('style', 'width: 250PX;')
                        ->setDescription('Selecione o Serviço');        
                    
        $this->addElements(array($data_inicial,$data_final,$sgrs_id_grupo,$sser_id_servico));
    }

}
