<?php

/**
 * @category            Services
 * @package		Services_Sisad_RelatorioProcesso
 * @copyright           Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Dayane Oliveira Freire
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial            Tutorial abaixo
 * 
 * TRF1, Classe de serviços para os Relatórios de Processos
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Services_Sisad_RelatorioProcesso {

    private $_negocio = null;
    
    public function __construct ()
    {
        $this->_negocio = new Trf1_Sisad_Negocio_RelatorioProcesso();
    }
    
    public function retornaRelatores($nome){
        $retorno = $this->_negocio->retornaRelatores($nome);
        return $retorno;
    }
    
    public function getForm(){
        return new Sisad_Form_RelatorioProcesso();
    }
    
    public function getFormApensadosAnexados(){
        $form = $this->getForm();
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    
    public function getFormArquivadosUnidade(){
        $form = $this->getForm();
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    public function getFormAutuadosAssunto(){
        $form = $this->getForm();
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    
    public function getFormAutuadosUnidade(){
        $form = $this->getForm();
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    
    public function getFormDistribuidosRedistribuidos(){
        $form = $this->getForm();
        $form->removeElement('TRF1_SECAO');
        $form->removeElement('SECAO_SUBSECAO');
        $form->removeElement('DOCM_CD_LOTACAO_GERADORA');
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    
    public function getFormEncaminhadosUnidade(){
        $form = $this->getForm();
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    
     public function getFormOrgaoJulgador(){
        $form = $this->getForm();
        $form->removeElement('TRF1_SECAO');
        $form->removeElement('SECAO_SUBSECAO');
        $form->removeElement('DOCM_CD_LOTACAO_GERADORA');
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    
    public function getFormNaUnidade(){
        $form = $this->getForm();
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    
    public function getFormParadoNaUnidade(){
        $form = $this->getForm();
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('DATA_INICIAL');
        $form->removeElement('DATA_FINAL');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    
    public function getFormRelator(){
        $form = $this->getForm();
        $form->removeElement('TRF1_SECAO');
        $form->removeElement('SECAO_SUBSECAO');
        $form->removeElement('DOCM_CD_LOTACAO_GERADORA');
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('ORDER');
        return $form;
    }
    
    public function getFormSigilosos(){
        $form = $this->getForm();
        $form->removeElement('DOCM_CD_LOTACAO_GERADORA');
        $form->removeElement('DOCM_ID_PCTT');
        $form->removeElement('QTDE_DIAS');
        $form->removeElement('ORGAO_JULGADOR');
        $form->removeElement('RELATOR');
        $form->removeElement('MATRICULA_RELATOR');
        $form->removeElement('ORDER');
        return $form;
    }
    
    public function retornaApensadosAnexados ($dados, $params)
    {
        $retorno = $this->_negocio->retornaApensadosAnexados($dados, $params);
        return $retorno;
    }
    
    public function retornaArquivadosUnidade ($dados, $params)
    {
        $retorno = $this->_negocio->retornaArquivadosUnidade($dados, $params);
        return $retorno;
    }
    
    public function retornaAutuadosUnidade ($dados, $params)
    {
        $retorno = $this->_negocio->retornaAutuadosUnidadeAssunto($dados, $params);
        return $retorno;
    }
    
    public function retornaAutuadosAssunto($dados, $params)
    {
        $retorno = $this->_negocio->retornaAutuadosUnidadeAssunto($dados, $params);
        return $retorno;
    }
    
    public function retornaProcessosDistribuidosRedistribuidos ($dados)
    {
        $retorno = $this->_negocio->retornaProcessosDistribuidosRedistribuidos($dados);
        return $retorno;
    }
    
    public function retornaEncaminhadosUnidade ($dados, $params)
    {
        $retorno = $this->_negocio->retornaEncaminhadosUnidade($dados, $params);
        return $retorno;
    }
    
    public function retornaProcessosNaUnidade($dados)
    {
        $retorno = $this->_negocio->retornaProcessosNaUnidade($dados);
        return $retorno;
    }
    
    public function retornaProcessosOrgaoJulgador($dados)
    {
        $retorno = $this->_negocio->retornaProcessosOrgaoJulgador($dados);
        return $retorno;
    }
    
    public function retornaProcessosParadosNaUnidade($dados)
    {
        $retorno = $this->_negocio->retornaProcessosParadosNaUnidade($dados);
        return $retorno;
    }
    
    public function retornaProcessosPorRelator($dados)
    {
        $retorno = $this->_negocio->retornaProcessosPorRelator($dados);
        return $retorno;
    }
    
    public function retornaProcessosSigilosos($dados)
    {
        $retorno = $this->_negocio->retornaProcessosSigilosos($dados);
        return $retorno;
    }
    
    public function retornaSomatorioUnidade($arrResultado)
    {
        $retorno = $this->_negocio->somatorioPorUnidade($arrResultado);
        return $retorno;
    }
    
    public function retornaCabecalho( $dados ){
        
        $cabecalho = array();
        
        if( isset($dados['DOCM_CD_LOTACAO_GERADORA']) && !empty( $dados['DOCM_CD_LOTACAO_GERADORA']) ){
                $descricaoUnidade = explode(" - ", $dados['DOCM_CD_LOTACAO_GERADORA']);
                $cabecalho['unidade']  = $descricaoUnidade[1];
        }else if( isset($dados['SECAO_SUBSECAO']) && !empty($dados['SECAO_SUBSECAO']) ){
            $lot = explode("|", $dados['SECAO_SUBSECAO']);
            $rhCentalLot = new Application_Model_DbTable_RhCentralLotacao();
            $descricaoUnidade = $rhCentalLot->getDescricaoLotacao($lot[0], $lot[1]);
            $cabecalho['unidade'] = $descricaoUnidade[0]['DESCRICAO'];
        }
        
        if( isset($dados['DATA_INICIAL']) && !empty($dados['DATA_INICIAL']) ){
            $cabecalho['data_inicial'] = $dados['DATA_INICIAL'];
        }
        
        if( isset($dados['DATA_FINAL']) && !empty($dados['DATA_FINAL']) ){
            $cabecalho['data_final'] = $dados['DATA_FINAL'];
        }
        
        if( isset($dados['DOCM_ID_PCTT'])) {
            $mapperPctt = new Arquivo_Model_DataMapper_Pctt();
            $assunto = $mapperPctt->getPCTTbyId($dados['DOCM_ID_PCTT']);
            $cabecalho['assunto'] = $assunto["AQAT_DS_ATIVIDADE"]. " - ".$assunto["AQVP_CD_PCTT"];
        }
        
        if ( isset($dados['QTDE_DIAS']) ){
            $cabecalho['quantidade_dias'] = $dados['QTDE_DIAS'];
        }
        
        if ( isset($dados['ORGAO_JULGADOR']) && $dados['ORGAO_JULGADOR'] != "0" ){
            $SadTbOrgjOrgaoJulgador = new Application_Model_DbTable_SadTbOrgjOrgaoJulgador();
            $nomeOrgao = $SadTbOrgjOrgaoJulgador->find($dados['ORGAO_JULGADOR'])->toArray();
            $cabecalho['orgao_julgador'] = $nomeOrgao[0]['ORGJ_NM_ORGAO_JULGADOR'];
        }
        
        if ( isset($dados['RELATOR']) ){
            $rel = explode(" - ", $dados['RELATOR']);
            $cabecalho['relator'] = $rel[0];
        }
        
        return $cabecalho;
        
    }
    
}