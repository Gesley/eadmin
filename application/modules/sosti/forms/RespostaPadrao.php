<?php

class Sosti_Form_RespostaPadrao extends Sosti_Form_SosTbRepdRespostaPadrao {

    private $_idGrupo;
    private $_idGrupoValidacao;
    private $_idResposta;

    public function get_idGrupoValidacao() {
        return $this->_idGrupoValidacao;
    }

    public function set_idGrupoValidacao($_idGrupoValidacao) {
        $this->_idGrupoValidacao = $_idGrupoValidacao;
    }

    public function get_idGrupo() {
        return $this->_idGrupo;
    }

    public function set_idGrupo($_idGrupo) {
        $this->_idGrupo = $_idGrupo;
    }

    public function get_idResposta() {
        return $this->_idResposta;
    }

    public function set_idResposta($_idResposta) {
        $this->_idResposta = $_idResposta;
    }

    /**
     * Função para configuração do fomulário, adicionando os campos necessários para a action ADD
     * @author Daniel Rodrigues
     */
    public function add() {

        //INSTANCIA DA REGRA DE NEGOCIO DO GRUPO DE SERVICO
        $services_sosti_tiposervico = new Services_Sosti_TipoServico();
        $array_tipo_ser = $services_sosti_tiposervico->getTipoServicoByGrupo($this->get_idGrupo());

        //Configurar campos
        foreach ($array_tipo_ser as $tipo_ser) {
            $this->REPD_ID_SERVICO->addMultiOptions(
                    array($tipo_ser['SETP_ID_SERVICO'] => $tipo_ser['SETP_DS_SERVICO'])
            );
        }

        $this->REPD_ID_SERVICO->setRequired(false);
    }

    /**
     * Função para configuração do fomulário, adicionando os campos necessários para a action Edit
     * @author Daniel Rodrigues
     */
    public function edit() {

        //  $this->REPD_ID_SERVICO->setValue($this->get_idGrupo());
        //CRIANDO CAMPOS PARA VALIDAÇÃO DE ALTERAÇÃO DAS RESPOSTAS PADRÕES
        $id_grupo_validacao = NEW Zend_Form_Element_Hidden('REPD_ID_GRUPO_VALIDACAO');
        $id_grupo_validacao->setValue($this->get_idGrupoValidacao())->removeDecorator('label')->setRequired(true);

        $matricula = new Zend_Form_Element_Hidden('REPD_CD_MATRICULA_CADASTRO');
        $matricula->setRequired(true)->removeDecorator('label');

        //REMOVENDO A OPÇÃO DO USUÁRIO EDITAR A CONFIDENCIALIDADE DA RESPOSTA
        $this->removeElement('REPD_IC_CONFIDENCIALIDADE');

        //ADICIONANDO NOVOS ELEMENTOS AO FORMULÁRIO
        $this->addElements(array($id_grupo_validacao, $matricula));
    }

    /**
     * Função para configuração do fomulário, adicionando os campos necessários para a action Delete
     * @author Daniel Rodrigues
     */
    public function delete() {

        //CAPTURANDO ELEMENTOS PARA CONSTRUIR O FORMULÁRIO DE EXCLUSÃO
        $id_resposta = $this->REPD_ID_RESPOSTA_PADRAO->setRequired(true);
        $id_grupo = $this->REPD_ID_GRUPO->setValue($this->get_idGrupo());
        //GRUPO PARA VALIDACAO E REDIRECIONAMENTO DAS PÁGINAS CRUD DA RESPOSTA PADRAO
        $id_grupo_validacao = NEW Zend_Form_Element_Hidden('REPD_ID_GRUPO_VALIDACAO');
        $id_grupo_validacao->setValue($this->get_idGrupo())->removeDecorator('label')->setRequired(true);
        //ADICIONANDO A MATRICULA DO CADASTRO
        $matricula = new Zend_Form_Element_Hidden('REPD_CD_MATRICULA_CADASTRO');
        $matricula->setRequired(true)->removeDecorator('label');
        //ALTERANDO A LABEL DO SUBMIT DO FORMULARIO
        $submit = $this->Salvar->setName('Excluir');
        //LIMPA OS ELEMENTOS DESNECESSÁRIOS
        $this->clearElements();
        //ADICIONA OS ELEMENTOS DO NOVO FORMULÁRIO
        $this->addElements(array($id_resposta, $id_grupo, $id_grupo_validacao, $matricula, $submit));
    }

    /**
     * Função para configuração do fomulário, adicionando os campos necessários para a escolha da resposta padrão
     * @author Daniel Rodrigues
     */
    public function escolheResposta() {

        //FILTRO PARA CAMPO DE TEXTO, ELIMINANDO AS ASPAS SIMPLES
        $Zend_Filter_PregReplace = new Zend_Filter_PregReplace();
        $Zend_Filter_PregReplace->setMatchPattern("'");
        $Zend_Filter_PregReplace->setReplacement("''");
        
        //CAPTURANDO ELEMENTOS PARA CONSTRUIR O FORMULÁRIO DE BUSCA E ESCOLHA
        $nome = $this->REPD_NM_RESPOSTA_PADRAO->setRequired(false);
        $descricao = new Zend_Form_Element_Text('REPD_DS_RESPOSTA_PADRAO');
        $descricao->setLabel('Descrição')->addFilter($Zend_Filter_PregReplace);

        //FAZENDO TRATAMENTO JSON PARA INCLUIR UM ARRAY DE IDS DOS GRUPOS NO CAMPO HIDDEN
        $grupos = $this->get_idGrupo();
        $id_grupo = $this->REPD_ID_GRUPO->setValue(Zend_Json::encode($grupos))->removeDecorator('label');

        //COLOCANDO O SUBMIT COMO BUTTOMPARA FAZER A REQUISIÇÃO VIA AJAX
        $submit = new Zend_Form_Element_Button('Buscar');
        $submit->setAttrib('id', 'Buscar');

        //INSTANCIA DA REGRA DE NEGOCIO DO GRUPO DE SERVICO
        $tipo_servico = $this->REPD_ID_SERVICO;
        $services_sosti_tiposervico = new Services_Sosti_TipoServico();
        $array_tipo_ser = $services_sosti_tiposervico->getTipoServicoByGrupos($this->get_idGrupo());
        //CONFIGURAR CAMPOS
        foreach ($array_tipo_ser as $tipo_ser) {
            $tipo_servico->addMultiOptions(
                    array($tipo_ser['SETP_ID_SERVICO'] => $tipo_ser['SETP_DS_SERVICO'])
            );
        }

        $this->REPD_ID_SERVICO->setRequired(false);
        //LIMPA OS ELEMENTOS DESNECESSÁRIOS
        $this->clearElements();

        //ADICIONA OS ELEMENTOS DO NOVO FORMULÁRIO
        $this->addElements(array($nome, $descricao, $id_grupo, $tipo_servico, $submit));
    }
    
    /**
     * Função que configura o formulário do filtro das respostas padrões do sistem
     * @author Daniel Rodrigues
     */
    public function filtroResposta(){
        
        //FILTRO PARA CAMPO DE TEXTO, ELIMINANDO AS ASPAS SIMPLES
        $Zend_Filter_PregReplace = new Zend_Filter_PregReplace();
        $Zend_Filter_PregReplace->setMatchPattern("/'/");
        $Zend_Filter_PregReplace->setReplacement("''");
        
        //CAPTURANDO ELEMENTOS PARA CONSTRUIR O FORMULÁRIO DE BUSCA E ESCOLHA
        $nome = $this->REPD_NM_RESPOSTA_PADRAO->setRequired(false);
        $descricao = new Zend_Form_Element_Text('REPD_DS_RESPOSTA_PADRAO');
        $descricao->setLabel('Descrição')
                ->setRequired(false)
                ->addFilter($Zend_Filter_PregReplace)
                ->addFilter('StripTags');

        //FAZENDO TRATAMENTO JSON PARA INCLUIR UM ARRAY DE IDS DOS GRUPOS NO CAMPO HIDDEN
        $grupos = $this->get_idGrupo();
        $id_grupo = $this->REPD_ID_GRUPO->setValue($grupos)->removeDecorator('label');

        //COLOCANDO O SUBMIT 
        $submit = $this->Salvar->setLabel('Filtrar');
        $submit->setAttrib('id', 'Buscar');

        //INSTANCIA DA REGRA DE NEGOCIO DO GRUPO DE SERVICO
        $tipo_servico = $this->REPD_ID_SERVICO;
        $services_sosti_tiposervico = new Services_Sosti_TipoServico();
        $array_tipo_ser = $services_sosti_tiposervico->getTipoServicoByGrupos($this->get_idGrupo());
        //CONFIGURAR CAMPOS
        $tipo_servico->addMultiOptions(array("0" => ""));
        foreach ($array_tipo_ser as $tipo_ser) {
            $tipo_servico->addMultiOptions(
                    array($tipo_ser['SETP_ID_SERVICO'] => $tipo_ser['SETP_DS_SERVICO'])
            );
        }

        $this->REPD_ID_SERVICO->setRequired(false);
        //LIMPA OS ELEMENTOS DESNECESSÁRIOS
        $this->clearElements();

        //ADICIONA OS ELEMENTOS DO NOVO FORMULÁRIO
        $this->addElements(array($nome, $descricao, $id_grupo, $tipo_servico, $submit));
        
    }

}

?>
