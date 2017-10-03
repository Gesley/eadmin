<?php
class Sosti_Form_Dashboard extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        $opcao = new Zend_Form_Element_Radio('OPCAO');
        $opcao->setLabel('Contabilizar:')
                ->setRequired(true)
                ->setMultiOptions(array('total_chamados_com_rechamados'=>'Todos os chamados (contabiliza reencaminhamento)', 'total_primeiro_chamado'=>'Primeiro chamado (contabiliza somente o primeiro encaminhamento)', /*'unidade'=>'Unidade Responsável'*/));
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico(); 
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getTodasCaixas();
        
        
        
        $sgrs_id_grupo = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
        $sgrs_id_grupo->setRequired(true)
                      ->setLabel('Caixa de Atendimento:');
        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => substr($SgrsGrupoServico_p["CXEN_DS_CAIXA_ENTRADA"], 9)));
        endforeach;

       $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
       $data_inicial
                    ->setRequired(true)
                    ->setValue('')
                    ->setLabel('Data inicial:');
       
       $data_final = new Zend_Form_Element_Text('DATA_FINAL');
       $data_final
                    ->setRequired(true)
                    ->setValue('')
                    ->setLabel('Data final:');

        $submit = new Zend_Form_Element_Submit('Gerar');

        $this->addElements(array($opcao, $sgrs_id_grupo, $data_inicial,$data_final, $submit));
    }

}