<?php
class Sosti_Form_RelatoriosHelpdesk extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $userNamespace = new Zend_Session_Namespace('userNs'); 
       
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico(); 
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getTodasCaixas();
        
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(true)
                      ->setLabel('Grupo de Serviço:');
        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => substr($SgrsGrupoServico_p["CXEN_DS_CAIXA_ENTRADA"], 9)));
        endforeach;        

        $sser_id_servico = new Zend_Form_Element_Select('SNAT_CD_NIVEL');
        $sser_id_servico->setRequired(true)
                        ->setLabel('Nível de Serviço:')
                        ->setAttrib('style', 'width: 650PX;');
       
       $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
       $data_inicial->setLabel('Data inicial:');
       
       $data_final = new Zend_Form_Element_Text('DATA_FINAL');
       $data_final->setLabel('Data final:');
       
       $data_inicial_cadastro = new Zend_Form_Element_Text('DATA_INICIAL_CADASTRO');
       $data_inicial_cadastro->setLabel('Data inicial cadastro:');
       
       $data_final_cadastro = new Zend_Form_Element_Text('DATA_FINAL_CADASTRO');
       $data_final_cadastro->setLabel('Data final cadastro:');
       
       $data_inicial_encaminhamento = new Zend_Form_Element_Text('DATA_INICIAL_ENCAMINHAMENTO');
       $data_inicial_encaminhamento->setLabel('Data inicial encaminhamento:');
       
       $data_final_encaminhamento = new Zend_Form_Element_Text('DATA_FINAL_ENCAMINHAMENTO');
       $data_final_encaminhamento->setLabel('Data final encaminhamento:');
       
       $avaliacao = new Zend_Form_Element_Radio('AVALIACAO');
       $avaliacao->setLabel('Avaliação:')
                 ->addMultiOption("1014", "Avaliadas Positivamente")
                 ->addMultiOption("9999", "Não Avaliadas")
                 ->addMultiOption("1019", "Recusadas")
                 ->addMultiOption("1000", "Baixadas")->setAttrib("checked", "checked")
                 ->setSeparator('<br />');

       $submit = new Zend_Form_Element_Submit('Pesquisar');
       $submit->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all');
       
       $this->addElements(array($sgrs_id_grupo,
                                $sser_id_servico,
                                $data_inicial,
                                $data_final,
                                $data_inicial_cadastro,
                                $data_final_cadastro,
                                $data_inicial_encaminhamento,
                                $data_final_encaminhamento,           
                                $avaliacao,
                                $submit));
    }
}