<?php

class Sosti_Form_RelatoriosSolicitacoesPorServico extends Zend_Form
{

    public function init() {
        $this->setAction('')
                ->setMethod('post');

        $rh_central = new Application_Model_DbTable_RhCentralLotacao();
        $secao = $rh_central->getSecoestrf1();
        $trf1_secao = new Zend_Form_Element_Select('TRF1_SECAO');
        $trf1_secao->setLabel('*TRF1/Seções')
                ->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->addMultiOptions(array('' => 'Escolha TRF1/Seções'));
        foreach ($secao as $v) {
            $trf1_secao->addMultiOptions(array($v["SESB_SIGLA_SECAO_SUBSECAO"] . '|' . $v["LOTA_COD_LOTACAO"] . '|' . $v["LOTA_TIPO_LOTACAO"] => $v["LOTA_DSC_LOTACAO"]));
        }

        $unidade = new Zend_Form_Element_Select('LOTA_COD_LOTACAO');
        $unidade->setLabel('*Caixa de Atendimento:')
                ->setRequired(true)
                ->setAttrib('style', 'width: 500px; ')
                ->addMultiOptions(array('' => 'Primeiro escolha o TRF1/Seções'));

        $data_inicio = new Zend_Form_Element_Text('DATA_INICIAL');
        $data_inicio->setLabel('*Período Inicial:')
                ->setRequired(true)
                ->setAttribs(array('style' => 'width: 100px',
                    'class' => 'data'));

        $data_final = new Zend_Form_Element_Text('DATA_FINAL');
        $data_final->setLabel('*Período Final:')
                ->setRequired(true)
                ->setAttribs(array('style' => 'width: 100px',
                    'class' => 'data'));

        $ctss_nm_categoria_servico = new Zend_Form_Element_Select('CTSS_NM_CATEGORIA_SERVICO');
        $ctss_nm_categoria_servico->setLabel('*Categoria de Serviço')
                ->setAttrib('style', 'width: 500px;')
                ->addMultiOptions(array('' => 'Primeiro escolha a caixa de atendimento'));

        $status = new Zend_Form_Element_Select('STATUS');
        $status->setRequired(false)
                ->addMultiOptions(array("" => "", "1" => 'Baixado', "2" => 'Aberto'))
                ->setLabel('Status:')
                ->setAttrib('style', 'width: 500px;');

        $agrupamento = new Zend_Form_Element_Radio('AGRUPAMENTO');
        $agrupamento->setLabel('*Agrupamento:')
                ->setRequired(true)
                ->setMultiOptions(array('1' => 'Mensal', '2' => 'Anual'));

        $nomeCategoria = new Zend_Form_Element_Hidden('NOM_CATEGORIA');
        $nomeCategoria->setRequired(false)
                ->setAttrib('id', 'nome-categoria');

        $submit = new Zend_Form_Element_Submit('Pesquisar');

        $this->addElements(array(
            $trf1_secao,
            $unidade,
//            $ctss_nm_categoria_servico,
            $status,
            $agrupamento,
            $data_inicio,
            $data_final,
            $nomeCategoria,
            $submit));
    }

}