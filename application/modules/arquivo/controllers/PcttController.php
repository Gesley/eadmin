<?php

class Arquivo_PcttController extends Zend_Controller_Action {

    public function init() {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        /* Initialize action controller here */
        $this->view->titleBrowser = "e-Arquivo";
    }

    public function gridPrincipalAction(){
        
    }
    public function indexAction() {

        //Verificando se existe o metodo get do formulário  
        $id = Zend_Filter::filterStatic($this->_getParam('id'), 'int');

        //Criando um objeto formulario
        $form = new Arquivo_Form_Assunto();
        $this->view->titulo = "Cadastro de assunto principal";
        //Selcionando a tabela SAD_TB_ASSUNTO_PRINCIPAL
        $assunto_principal = new Arquivo_Model_DbTable_SadTbAqapAssuntoPrincipal();

        //Verificando se foi passado o id
        if ($id) {
            $data = $assunto_principal->find($id)->current()->toArray();
            $form->populate($data);
        }
        $this->view->form = $form;
    }

    public function assuntoPrincipalAction() {
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order = $this->_getParam('ordem', 'AQAP_CD_ASSUNTO_PRINCIPAL');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order . ' ' . $direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /* Ordenação */

        $table = new Arquivo_Model_DbTable_SadTbAqapAssuntoPrincipal();
        $tabela = new Arquivo_Model_DbTable_SadTbAqapAssuntoPrincipal();
        
        $tabelaUm = $tabela->fetchAll();
        $total = count($tabelaUm);
        if($total > 0){
            
            $mostarValor = $total+1;
        }else{
            $mostarValor = 1;
        }
        $this->view->tabela = $tabela->fetchAll();
        $this->view->tabelaPrincipal = $mostarValor;
        $select = $table->select()->order($order_aux);
//        if ($cod) {
//            $select = $table->select()->order($order_aux);
//        }
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::
        setDefaultViewPartial('pagination.phtml');

        $this->view->title = "PCTT - Assunto";
    }

    public function addPcttAction() {

//       $form = new Arquivo_Form_Assunto();
        $this->view->form = $form;
        $table = new Arquivo_Model_DbTable_SadTbAqapAssuntoPrincipal();
        if ($this->getRequest()->isPost()) {
            $data = $this->_getAllParams();
            unset($data['Enviar']);
            unset($data['id']);
           // if ($form->isValid($data)) {
            $existeCadastro = false;
                foreach ($table->fetchAll() as $tabela):
                    if ($data['AQAP_CD_ASSUNTO_PRINCIPAL'] == $tabela['AQAP_CD_ASSUNTO_PRINCIPAL'] or $data['AQAP_DS_ASSUNTO_PRINCIPAL'] == $tabela['AQAP_DS_ASSUNTO_PRINCIPAL']) {

                            echo "Ação já cadastrada!";
                            $existeCadastro = true;
                            break;
                    }
                endforeach;
                
                        if(!$existeCadastro){
                        $inserir = $table->createRow($data);
                        $inserir->save();
                        $this->_redirect('arquivo/pctt/assunto-principal/');
                        }
            }
       // }
    }

    public function editAction() {

        $this->view->title = "Editar Assunto";
        $table = new Arquivo_Model_DbTable_SadTbAqapAssuntoPrincipal();
        $verificar = $table->fetchAll();
        $form = new Arquivo_Form_Assunto();
        $formSecundario = new Arquivo_Form_Secundario();
        $this->view->formSecundario = $formSecundario;
        $form->setAction('edit');
        $id = Zend_Filter::FilterStatic($this->_getParam('AQAP_ID_ASSUNTO_PRINCIPAL'), 'int');

        if ($this->getRequest()->isGet()) {
            $data = $this->_getAllParams();
            
                    try {
                        //$id = $data['AQAP_ID_ASSUNTO_PRINCIPAL'];
                        $row = $table->find($id)->current();
                        $row = $row->setFromArray($data);
                        $row->save();
                        $msg_to_user = "Assunto alterado com Sucesso";
                        $this->_helper->flashMessenger(array(
                            'message' => $msg_to_user, 'status' => 'success'));
                    } catch (Zend_Exception $error_string) {
                        $msg_to_user = "Não é possível alterar o assunto";
                        $this->_helper->flashMessenger(array(
                            'message' => $msg_to_user, 'status' => 'notice'));
                    
                }
            
            $this->_redirect('arquivo/pctt/assunto-principal');
        }

        $this->view->form = $form;
        $row = $table->fetchRow(array(
            'AQAP_CD_ASSUNTO_PRINCIPAL = ?' => $id));
        if ($row) {
            $data = $row->toArray();
            $form->populate($data);
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * /////////////////////Crud de assunto secundário /////////////////////////
     *//////////////////////////////////////////////////////////////////////////
    //GRID DO ASSUNTO SECUNDÁRIO //

    public function gridAction() {
        $form = new Arquivo_Form_Pctt();
        $id = $this->_getParam('AQAS_CD_ASSUNTO_SECUNDARIO');
        $tableSec = new Arquivo_Model_DbTable_SadTbAqasAssuntoSecundario();
        $tabelaSEcundaria = new Arquivo_Model_DataMapper_Pctt();
        $mostarQuantidadeCodigoSecundario = $tabelaSEcundaria->getCountCodigoSecundario($id);
        $motarValorCodigo = new Arquivo_Model_DataMapper_Pctt();
        $secFetch = $motarValorCodigo->getCountSecundario($id);
        foreach ($secFetch as $contador):
          if($mostarQuantidadeCodigoSecundario[0]['RESULTADO'] > 0){
              $inputValue = "value=".$contador['TOTAL']."";
           $this->view->contSec = $inputValue;
              
            }else{
              $inputValue = "value='1'";
              $this->view->contSec = $inputValue;
                
            }
        endforeach;
        $mostrar_por_id = $tableSec->fetchAll("AQAS_CD_ASSUNTO_PRINCIPAL= " .$id );
        $ultimoValor = new Arquivo_Model_DataMapper_Pctt();
        $enviar = $ultimoValor->getMaxIdAqas();
        $this->view->tableSec = $mostrar_por_id ;
        $this->view->titulo = "Lista de Assunto Secundário";
        $this->view->id = $id;
        $this->view->ultimoValor = $enviar;
        
        $aqap_cd_assunto_principal = $this->_getParam('AQAP_CD_ASSUNTO_PRINCIPAL');

        $form->getElement('AQAP_CD_ASSUNTO_PRINCIPAL')
                ->setValue($aqap_cd_assunto_principal);

        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order = $this->_getParam('ordem', 'AQAS_CD_ASSUNTO_SECUNDARIO');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order . ' ' . $direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /* Ordenação */
        if ($aqap_cd_assunto_principal) {
            $select = $tableSec->select()
                    ->where('AQAS_CD_ASSUNTO_PRINCIPAL = '. $id)
                    ->order($order_aux);
        } else {
            $select = $tableSec->select()
                    ->order($order_aux);
        }
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::
        setDefaultViewPartial('pagination.phtml');
    }

    public function listAssuntoSecundarioAction() {
          $this->view->title = "PCTT - Assunto Secundário";
        //Criando um objeto formulario
        $form = new Arquivo_Form_Pctt();
        $form->setAttrib('onChange', 'buscar_secundario()');
        $this->view->form = $form;
    }

    public function addAssuntoSecundarioAction() {
        //Pegando o id vindo do GET
        $id = Zend_Filter::filterStatic($this->_getParam(
                                'AQAS_CD_ASSUNTO_PRINCIPAL'), 'int');
        $table = new Arquivo_Model_DbTable_SadTbAqasAssuntoSecundario();
        
        // Recuperando os valores do auto_increment do id
        $dataMapper = new Arquivo_Model_DataMapper_Pctt();
        $idSecun = $dataMapper->getMaxIdAqas();

        //Criando um objeto formulario
        $form = new Arquivo_Form_Secundario();
        $form->addElement('hidden', 'AQAS_CD_ASSUNTO_PRINCIPAL', array('value' => $id));
        $form->addElement('hidden', 'AQAS_ID_ASSUNTO_SECUNDARIO', array('value' => $idSecun['AQAS_ID_ASSUNTO_SECUNDARIO']));

        // Enviando o formulario para view
        $this->view->form = $form;
        //Setando o tabela secundaria
        //Setando a tabela principal
        $tablePrim = new Arquivo_Model_DbTable_SadTbAqapAssuntoPrincipal();
        /* Selecionando os registros do banco de acordo com 
          /*AQAP_CD_ASSUNTO_PRINCIPAL
         */
        $where = array(
            'AQAP_CD_ASSUNTO_PRINCIPAL = ' . $id);
        $select = $tablePrim->fetchAll($where);
        $this->view->tablePrim = $select;
        // Metodos do input post
        if ($this->getRequest()->isGet()) {
            $data = $this->_getAllParams();
            $where = 'AQAS_CD_ASSUNTO_PRINCIPAL='
                    .$data['AQAS_CD_ASSUNTO_PRINCIPAL']
                    .'AND AQAS_CD_ASSUNTO_SECUNDARIO='
                    .$data['AQAS_CD_ASSUNTO_SECUNDARIO'];
        $contar = count($table->fetchRow($where));
            unset($data['Enviar']);
            unset($data['id']);
           // if ($form->isValid($data)) {
               
                    if ($contar > 0) {
                        
                          echo "Ação já cadastrada!";
                          exit();
                    }
                        $inserir = $table->createRow($data);
                        $inserir->save();
                        echo "Dados cadastrados";
                        $this->_redirect('arquivo/pctt/list-assunto-secundario/');
                        
            }
       // }
    }

    public function editAssuntoSecundarioAction() {
        //Titulo para a view
        $this->view->title = "Editar Assunto Secundario";
        //Selecionando o banco Assunto secundário
        $table = new Arquivo_Model_DbTable_SadTbAqasAssuntoSecundario();
        //Recuparando os metodos  GETs
        if ($this->getRequest()->isGet()) {
            $id = $this->_getParam('AQAS_ID_ASSUNTO_SECUNDARIO');
            //selecionando o id para atulizar
            $data = $this->_getAllParams();
            try {
                //Selecionando os campos no banco com parametro GET
                $row = $table->find($id)->current();
                //Criando uma row com os campos de acordo com o GET 
                $salvar = $row->setFromArray($data);
                $salvar->save();
                $msg_to_user = "Assunto alterado com Sucesso";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'success'));
                //Retornando os campos get para as view
                $dados = $table->find($id)->current();
                return $dados;
            } catch (Zend_Exception $error_string) {
                $msg_to_user = "Não é possível alterar o assunto";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'notice'));
                $this->_redirect('/arquivo/pctt/list-assunto-secundario/id/' . $id);
            }
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * /////////////////////////// Crud Classes ////////////////////////////////
     *//////////////////////////////////////////////////////////////////////////
    // GRID CLASSE //

    public function gridClasseAction() {

        //Criando um objeto formulario
        $form = new Arquivo_Form_ClasseSecundaria();
        $form->setAttrib('onChange', 'chamaClasse()');
        $this->view->titulo = "Listar Classes";

        if ($this->getRequest()->isGet()) {
            //Recuperando o GET
            $aqap_cd_assunto_principal = $this->_getParam('AQAP_CD_ASSUNTO_PRINCIPAL');

                $assuntosSecundarios = new Arquivo_Model_DataMapper_Pctt();

                foreach ($assuntosSecundarios->getAssuntoSecundario(
                        $aqap_cd_assunto_principal
                ) as $assunto):
                    $form->getElement('AQAS_CD_ASSUNTO_SECUNDARIO')
                            ->addMultiOptions(array($assunto["AQAS_CD_ASSUNTO_SECUNDARIO"]
                                => strtoupper(
                                        $assunto['AQAS_CD_ASSUNTO_SECUNDARIO'] . ' - ' .
                                        $assunto["AQAS_DS_ASSUNTO_SECUNDARIO"])));
                endforeach;
            

            $aqas_cd_assunto_secundario = $this->_getParam('AQAS_CD_ASSUNTO_SECUNDARIO');

            $form->getElement('AQAS_CD_ASSUNTO_SECUNDARIO')
                    ->setValue($aqas_cd_assunto_secundario);

            /* paginação */
            $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

            /* Ordenação das paginas */
            $order = $this->_getParam('ordem', 'AQCL_ID_CLASSE');
            $direction = $this->_getParam('direcao', 'ASC');
            $order_aux = $order . ' ' . $direction;
            ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
            /* Ordenação */

            $table = new Arquivo_Model_DbTable_SadTbAqclClasse();

            if ($aqas_cd_assunto_secundario) {
                $select = $table->select()
                        ->where('AQCL_ID_AQAS = ?', $aqas_cd_assunto_secundario)
                        ->order($order_aux);
            } else {
                $select = $table->select()
                        ->order($order_aux);
            }

            $paginator = Zend_Paginator::factory($select);
            $paginator->setCurrentPageNumber($page)
                    ->setItemCountPerPage(15);
            $this->view->ordem = $order;
            $this->view->direcao = $direction;
            $this->view->data = $paginator;
            Zend_View_Helper_PaginationControl::
            setDefaultViewPartial('pagination.phtml');
            $this->view->form = $form;
        }
    }

    //Listando o conteúdo da grid principal
    public function gridListClasseTabelaAction() {

        $id = Zend_Filter::FilterStatic($this->_getParam(
                                'AQAS_CD_ASSUNTO_SECUNDARIO'), 'int');
        $form = new Arquivo_Form_Classe();
        $tableClasse = new Arquivo_Model_DbTable_SadTbAqclClasse();
        $contadorClasses = new Arquivo_Model_DataMapper_Pctt();
        $contadorCodigoClasse = $contadorClasses->getCountCodigoClasse($id);
        $classeFetch = $contadorClasses->getCountClasse($id) ;
        $contClasse = $classeFetch;
         if($contadorCodigoClasse[0]['RESULTADO'] > 0){
              $inputValue = "value=".$contClasse[0]['TOTALCLASSE']."";
              $this->view->contClasse = $inputValue ;
              
            }else{
              $inputValue = "value='1'";
              $this->view->contClasse = $inputValue;
                
            }

        $classe = $tableClasse->fetchAll("AQCL_ID_AQAS =" . $id);
        $this->view->titulo = "Lista de Assunto Secundário";
        $this->view->data = $classe;
        $this->view->id = $id;
        $litaUltimoIdClasse = new Arquivo_Model_DataMapper_Pctt();
        $valorId = $litaUltimoIdClasse->getMaxIdClasse();
        foreach ($valorId as $listaId):
          $this->view->listaId = $listaId;
        endforeach;
    }

    public function listClasseAction() {
      $this->view->title = "PCTT - Classes";
        //Criando um objeto formulario
        $form = new Arquivo_Form_Classe();
        $this->view->titulo = "Listar Classes";
        $this->view->form = $form;

        $table = new Arquivo_Model_DbTable_SadTbAqclClasse();
        $this->view->data = $table->fetchAll();
    }

    public function addClasseAction() {

        $idSec = Zend_Filter::filterStatic($this->_getParam(
                                'AQCL_ID_AQAS'
                        ), 'int');
        $tableSec = new Arquivo_Model_DbTable_SadTbAqasAssuntoSecundario();
        /* Selecionando os registros do banco de acordo com 
          /*AQCL_ID_AQAS
         */
        $where = array(
            'AQAS_CD_ASSUNTO_SECUNDARIO = ?' => $idSec);
       $select =  $tableSec->fetchAll($where);
        $this->view->tableSec = $select; 
        //Pegando o id vindo do GET
        $id = Zend_Filter::filterStatic($this->_getParam(
                                'AQCL_CD_CLASSE'), 'int');
        $table = new Arquivo_Model_DbTable_SadTbAqclClasse();
        $this->view->tabela = $table;
        // Recuperando os valores do auto_increment do id
        $dataMapper = new Arquivo_Model_DataMapper_Pctt();
        $idclasse = $dataMapper->getMaxIdClasse();

        //Criando um objeto formulario
        $form = new Arquivo_Form_AddClasse();
        $form->addElement('hidden', 'AQCL_ID_CLASSE', array(
            'value' => $idclasse['AQCL_ID_CLASSE']
        ));
        $form->addElement('hidden', 'AQCL_ID_AQAS', array('value' => $idSec));
        // Enviando o formulario para view
        $this->view->form = $form;

        // Metodos do input post
        if ($this->getRequest()->isGet()) {

            $data = $this->_getAllParams();
            //Verificando se os dados são válidos
            //if ($form->isValid($data)) {
               $where = 'AQCL_ID_AQAS='
                    .$data['AQCL_ID_AQAS']
                    .'AND AQCL_CD_CLASSE='
                    .$data['AQCL_CD_CLASSE'];
               $contar = count($table->fetchRow($where));
               unset($data['Enviar']);
               unset($data['id']);
           // if ($form->isValid($data)) {
               
                    if ($contar > 0) {
                        
                          echo "Ação já cadastrada!";
                          exit();
                    }
                //Excluindo o campo submit
                unset($data['Enviar']);
                //Criando uma linha para inserir no banco
                $inserir = $table->createRow($data);
                // Salva a linha criada para o banco
                $inserir->save();
                
           // }
        }
    }

    public function editClasseAction() {

        $this->view->title = "Editar Classe";
        //Selecionando o banco Assunto secundário
        $table = new Arquivo_Model_DbTable_SadTbAqclClasse();
        //Recuparando os metodos  GETs
        if ($this->getRequest()->isGet()) {
            //selecionando o id para atulizar
            $id = Zend_Filter::filterStatic($this->_getParam('AQCL_ID_CLASSE'), 'int');
            $data = $this->_getAllParams();

            try {
                //Selecionando os campos no banco com parametro GET
                $row = $table->find($id)->current();
                //Criando uma row com os campos de acordo com o GET 
                $salvar = $row->setFromArray($data);
                $salvar->save();
                $msg_to_user = "Classe alterada com sucesso";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'success'));
                //Retornando os campos get para as view
                $dados = $table->find($id)->current();
                return $dados;
            } catch (Zend_Exception $error_string) {
                $msg_to_user = "Não é possível alterar o assunto";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'notice'));
            }
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * //////////////////////////Crud das subClasses////////////////////////////
     *//////////////////////////////////////////////////////////////////////////

    public function listSubClassesAction() {
        
        $this->view->title = "PCTT - Subclasses";
        
        $form = new Arquivo_Form_Pctt();
        $form->setAttrib('onChange', 'assuntoClasse()');
        $formClasse = new Arquivo_Form_Classe();
        $assuntosSecundario = new Arquivo_Model_DbTable_SadTbAqapAssuntoPrincipal();
        $this->view->form = $form;
    }

    public function showClasseAction() {

        $id = Zend_Filter::filterStatic($value, $classBaseName);
    }

      public function listSubSecundarioAction() {
        //Criando um objeto formulario
        $form = new Arquivo_Form_ClasseSecundaria();
        $form->setAttrib('onChange', 'Classe()');
        
        if ($this->getRequest()->isGet()) {
            //Recuperando o GET
            $aqap_cd_assunto_principal = $this->_getParam('AQAP_CD_ASSUNTO_PRINCIPAL');
            $codigoPirncipal = $aqap_cd_assunto_principal;
                $assuntosSecundarios = new Arquivo_Model_DataMapper_Pctt();

                foreach ($assuntosSecundarios->getAssuntoSecundario(
                        $aqap_cd_assunto_principal
                ) as $assunto):
                    $form->getElement('AQAS_CD_ASSUNTO_SECUNDARIO')
                            ->addMultiOptions(array($assunto["AQAS_CD_ASSUNTO_SECUNDARIO"]
                                => strtoupper(
                                        $codigoPirncipal . '.'.
                                        $assunto['AQAS_CD_ASSUNTO_SECUNDARIO'] . ' - ' .
                                        $assunto["AQAS_DS_ASSUNTO_SECUNDARIO"])));
                endforeach;
        }   $form->addElement('hidden', 'AQAP_CD_ASSUNTO_PRINCIPAL', array('value' => $codigoPirncipal));
            $this->view->form = $form;
    }
    
        public function listSubClasseAction() {

        $form = new Arquivo_Form_ListarClasse();
        $form->setAttrib('onchange', 'buscarSubClasse()');

        if ($this->getRequest()->isGet()) {
            //Recuperando o GET
            $idPrincipal = $this->_getParam('AQAP_CD_ASSUNTO_PRINCIPAL');
            $codPrincipal   = $idPrincipal;
            $classe = $this->_getParam('AQAS_CD_ASSUNTO_SECUNDARIO');
            $codigoSecundario = $classe;
            if ($classe) {

                $classes = new Arquivo_Model_DataMapper_Pctt();
                $pctt = $classes->getCodClasse($classe);
//                zend_debug::dump($pctt); die;
                $classe = $classes->getClasse($classe);
                //echo '<pre>';print_r($pctt);die;
                foreach ($classe as $assunto):
                    $form->getElement('AQCL_CD_CLASSE')
                            ->addMultiOptions(array($assunto["AQCL_ID_CLASSE"]
                                => strtoupper(
                                        $codPrincipal. '.' .
                                        $codigoSecundario. '-' .
                                        $assunto['AQCL_CD_CLASSE'] . ' -' .
                                        $assunto["AQCL_DS_CLASSE"])));
                endforeach;
            }
            $form->addElement('hidden','AQAP_CD_ASSUNTO_PRINCIPAL',array('value' => $codPrincipal));
            $form->addElement('hidden','AQAS_CD_ASSUNTO_SECUNDARIO',array('value' => $codigoSecundario));
            $this->view->form = $form;
        }
    }
    
    public function gridSubClasseAction() {
        // Recuperando o id via get
        $id = $this->_getParam('AQAS_CD_ASSUNTO_SECUNDARIO');

        //Criando um objeto formulario
        $formSecundario = new Arquivo_Form_ListSecundario();
        $formSecundario->setAttrib('onchange', 'assuntoSubClasse()');
        // Chamando o data mapper 
        $table = new Arquivo_Model_DataMapper_Pctt();
        $listarId = $table->getMaxIdSubClasse();
        foreach ($listarId as $listar):
        $this->view->listarId = $listar;
        endforeach;
        $tableClass = new Arquivo_Model_DbTable_SadTbAqclClasse();
        $classe = $tableClass->fetchAll('AQCL_ID_AQAS = ' . $id);
        $this->view->classe = $classe;
        // criando um loop para selecionar os campos da tabela e inserir no
        // select
        foreach ($table->getClasse($id) as $assunto):
            $formSecundario->getElement('AQCL_CD_CLASSE')
                    ->addMultiOptions(array(
                        $assunto["AQCL_ID_CLASSE"]
                        => strtoupper(
                                $assunto['AQCL_CD_CLASSE'] . ' - ' .
                                $assunto["AQCL_DS_CLASSE"])));
        endforeach;

        $this->view->formClasse = $formSecundario;
    }

    public function listSubMenuTabelaAction() {

        //Peganto o id com o metodo get
        $id = Zend_Filter::filterStatic($this->_getParam(
                                'AQCL_ID_AQAS'), 'int'
        );
        $codigoAlternativo = $this->_getAllParams();
        $codigoAlternativoId = $codigoAlternativo; 
        $tabela = new Arquivo_Model_DataMapper_Pctt();
        $contarCodigoSubClass = $tabela->getCountCodigoSubClasse($id);
        $litarId = $tabela->getMaxIdSubClasse();
        foreach ($litarId as $listarIdSub):
            $this->view->listarId = $listarIdSub;
        endforeach;
        $codigoClasse = new Arquivo_Model_DataMapper_Pctt();
        $codigoClasseId = $codigoClasse->getClasseCodigo($id);
        $selec = $tabela->getSubClass($id);
        $tableSub = new Arquivo_Model_DbTable_SadTbAqscSubclasse();
        $subClass =$codigoClasse->getCountSubclasseCodigo($id);
        if($contarCodigoSubClass[0]['RESULTADO'] > 0 ){
          $inputValue = "value=".$subClass[0]['TOTALSUBCLASSE']."";
              $this->view->contClasse = $inputValue ;
              
            }else{
              $inputValue = "value='1'";
              $this->view->contClasse = $inputValue;
                
            }
        $this->view->id = $id;
        $this->view->data = $selec;
        $this->view->codigoAlternativoId = $codigoAlternativoId;
        $this->view->codigoClasseId = $codigoClasseId; 
    }

    // Adicionando um registro no banco das subclasses

    public function addSubClassAction() {

        $idClass = Zend_Filter::filterStatic($this->_getParam(
                                'AQSC_ID_SUBCLASSE'
                        ), 'int');
        $tableClasse = new Arquivo_Model_DbTable_SadTbAqclClasse();

        /* Selecionando os registros do banco de acordo com 
          /*AQCL_ID_AQAS
         */
        $where = array(
            'AQCL_ID_CLASSE = ?' => $idClass);
        $select = $tableClasse->fetchAll($where);
        $this->view->select = $select;
        //Pegando o id vindo do GET
        $id = Zend_Filter::filterStatic($this->_getParam(
                                'AQCL_CD_CLASSE'), 'int');
        $table = new Arquivo_Model_DbTable_SadTbAqscSubclasse();
        $this->view->tabela = $table;
        // Recuperando os valores do auto_increment do id
        $dataMapper = new Arquivo_Model_DataMapper_Pctt();
        $idclasse = $dataMapper->getMaxIdSubClasse();

        //Criando um objeto formulario
        $form = new Arquivo_Form_AddSubClasse();
        $form->addElement('hidden', 'AQSC_ID_SUBCLASSE', array(
            'value' => $idclasse['AQSC_ID_SUBCLASSE']
        ));
        $form->addElement('hidden', 'AQSC_ID_AQCL', array(
            'value' => $idClass
        ));
        // Enviando o formulario para view
        $this->view->form = $form;

        // Metodos do input post
        if ($this->getRequest()->isGet()) {

            $data = $this->_getAllParams();
            // Salva a linha criada para o banco
            //Verificando se os dados são válidos
               $where = 'AQSC_CD_SUBCLASSE='. $data['AQSC_CD_SUBCLASSE']
                          .'AND AQSC_ID_AQCL='. $data['AQSC_ID_AQCL'];
            $contar = count($table->fetchRow($where));
            unset($data['Enviar']);
            // if ($form->isValid($data)) {
                    if ($contar > 0) {
                          echo "Ação já cadastrada!";
                          exit();
                    }
                //Excluindo o campo submit
                unset($data['Enviar']);
                //Criando uma linha para inserir no banco
                $inserir = $table->createRow($data);
                $inserir->save();
        }
    }

    public function editSubClasseAction() {
        $form = new Arquivo_Form_AddSubClasse();
        $this->view->title = "Editar SubClasse";
        $this->view->form = $form;
        //Selecionando o banco Assunto secundário
        $table = new Arquivo_Model_DbTable_SadTbAqscSubclasse();
        //Recuparando os metodos  GETs
        if ($this->getRequest()->isGet()) {
            //selecionando o id para atulizar
            $id = Zend_Filter::filterStatic($this->_getParam('AQSC_ID_SUBCLASSE'), 'int');
            $data = $this->_getAllParams();
            try {
                //Selecionando os campos no banco com parametro GET
                $row = $table->find($id)->current();
                //Criando uma row com os campos de acordo com o GET 
                $salvar = $row->setFromArray($data);
                $salvar->save();
                $msg_to_user = "SubClasse alterada com sucesso";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'success'));
                //Retornando os campos get para as view
                $dados = $table->find($id)->current();
                return $dados;
            } catch (Zend_Exception $error_string) {
                $msg_to_user = "Não é possível alterar o assunto";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'notice'));
            }
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * //////////////////////////Crud das Cadasttro de Vias/////////////////////
     *//////////////////////////////////////////////////////////////////////////

    public function listCasdastroViasAction() {
        
          $this->view->title = "PCTT - Vias";
        /* paginação */
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');

        /* Ordenação das paginas */
        $order = $this->_getParam('ordem', 'AQVI_CD_VIA');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order . ' ' . $direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /* Ordenação */

        $table = new Arquivo_Model_DbTable_SadTbAqaviVia();
        $ContarVia = count($table->fetchAll());
        $tableCont = new Arquivo_Model_DataMapper_Pctt();
        $contVias  = $tableCont->getCountVia(); 
          if($ContarVia > 0 ){
          $inputValue = "value=".$contVias[0]['TOTALVIAS']."";
              $this->view->contVias = $inputValue ;
              
            }else{
              $inputValue = "value='1'";
              $this->view->contVias = $inputValue;
                
            }
        $select = $table->select()->order($order_aux);
        //        if ($cod) {
        //            $select = $table->select()->order($order_aux);
        //        }
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::
        setDefaultViewPartial('pagination.phtml');

    }

    public function addCadastroViasAction() {
        $table = new Arquivo_Model_DbTable_SadTbAqaviVia();
            if ($this->getRequest()->isGet()) {
                $data = $this->_getAllParams();
                // Salva a linha criada para o banco
                //Verificando se os dados são válidos
                $count = new Arquivo_Model_DataMapper_Pctt();
                $contar = $count->getSecectAtividade($data['AQVI_CD_VIA']);
                unset($data['Enviar']);
                $existeCadastro = false;
                if ($contar['CONTAR'] > 0) {
                    echo "Ação já cadastrada!";
                    $existeCadastro = true;
                    exit();
                }
                //Excluindo o campo submit
                //unset($data['Enviar']);
                //Criando uma linha para inserir no banco
                if(!$existeCadastro){
                $inserir = $table->createRow($data);
                $inserir->save();
                }
            }
    }

    public function editCadastroViasAction() {
         $form = new Arquivo_Form_AddVias();
        $this->view->title = "Editar SubClasse";
        $this->view->form = $form;
        //Selecionando o banco Assunto secundário
        $table = new Arquivo_Model_DbTable_SadTbAqaviVia();
        //Recuparando os metodos  GETs
        if ($this->getRequest()->isGet()) {
            //selecionando o id para atulizar
            $id = $this->_getParam('AQVI_ID_VIA');
            $data = $this->_getAllParams();
            try {
                //Selecionando os campos no banco com parametro GET
                $row = $table->find($id)->current();
                //Criando uma row com os campos de acordo com o GET 
                $salvar = $row->setFromArray($data);
                $salvar->save();
                $msg_to_user = "Via alterada com sucesso";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'success'));
                //Retornando os campos get para as view
                $dados = $table->find($id)->current();
                return $dados;
            } catch (Zend_Exception $error_string) {
                $msg_to_user = "Não é possível alterar o assunto";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'notice'));
            }
        }
        
    }

    /* /////////////////////////////////////////////////////////////////////////
     * /////////////////////////Crud das Cadasttro de atividades////////////////
     *//////////////////////////////////////////////////////////////////////////

    public function listCasdastroAtividadesAction() {
        $this->view->title = "PCTT - Atividades e Vias";
        $data = new Arquivo_Model_DbTable_SadTbAqatAtividade();
        $this->view->data = $data;
        $formPrincipal = new Arquivo_Form_Pctt();
        $formPrincipal->setAttrib('onChange', 'buscarAtivadeSecundario()');
        $this->view->formPrincipal = $formPrincipal;
    }

    public function listAtividadeSecundarioAction() {
        //Criando um objeto formulario
        $form = new Arquivo_Form_ClasseSecundaria();
        $form->setAttrib('onChange', 'buscarAtividadeClasse()');
        if ($this->getRequest()->isGet()) {
            //Recuperando o GET
            $aqap_cd_assunto_principal = $this->_getParam('AQAP_CD_ASSUNTO_PRINCIPAL');

                $assuntosSecundarios = new Arquivo_Model_DataMapper_Pctt();

                foreach ($assuntosSecundarios->getAssuntoSecundario(
                        $aqap_cd_assunto_principal
                ) as $assunto):
                    $form->getElement('AQAS_CD_ASSUNTO_SECUNDARIO')
                            ->addMultiOptions(array($assunto["AQAS_CD_ASSUNTO_SECUNDARIO"]
                                => strtoupper(
                                        $aqap_cd_assunto_principal. '.'.
                                        $assunto['AQAS_CD_ASSUNTO_SECUNDARIO'] . ' - ' .
                                        $assunto["AQAS_DS_ASSUNTO_SECUNDARIO"])));
                endforeach;
            $form->addElement('hidden', 'AQAP_CD_ASSUNTO_PRINCIPAL', array('value' => $aqap_cd_assunto_principal));
            $this->view->form = $form;
        }
    }

    public function listAtividadeClasseAction() {

        $form = new Arquivo_Form_ListarClasse();
        $form->setAttrib('onchange', 'buscarAtividadeSubClasse()');

        if ($this->getRequest()->isGet()) {
            //Recuperando o GET
            $idPrincipal = $this->_getParam('AQAP_CD_ASSUNTO_PRINCIPAL');
            $codPrincipal   = $idPrincipal;
            $classe = $this->_getParam('AQAS_CD_ASSUNTO_SECUNDARIO');
            $codigoSecundario = $classe;
            if ($classe) {

                $classes = new Arquivo_Model_DataMapper_Pctt();
                $pctt = $classes->getCodClasse($classe);
//                zend_debug::dump($pctt); die;
                $classe = $classes->getClasse($classe);
                //echo '<pre>';print_r($pctt);die;
                foreach ($classe as $assunto):
                    $form->getElement('AQCL_CD_CLASSE')
                            ->addMultiOptions(array($assunto["AQCL_ID_CLASSE"]
                                => strtoupper(
                                        $codPrincipal. '.' .
                                        $codigoSecundario. '-' .
                                        $assunto['AQCL_CD_CLASSE'] . ' -' .
                                        $assunto["AQCL_DS_CLASSE"])));
                endforeach;
            }
            $form->addElement('hidden','AQAP_CD_ASSUNTO_PRINCIPAL',array('value' => $codPrincipal));
            $form->addElement('hidden','AQAS_CD_ASSUNTO_SECUNDARIO',array('value' => $codigoSecundario));
            $this->view->form = $form;
        }
    }

    public function listAtividadeSubClasseAction() {

        $form = new Arquivo_Form_SelectSubClasse();
        $form->setAttrib('onchange', 'chamaTabelaAtividade()');

        if ($this->getRequest()->isGet()) {
            //Recuperando o GET
            $idPrincipal = $this->_getParam('AQAP_CD_ASSUNTO_PRINCIPAL');
            $codPrincipal   = $idPrincipal;
            $codSecundario   = $this->_getParam('AQAS_CD_ASSUNTO_SECUNDARIO');
            $subClasse = $this->_getParam('AQCL_CD_CLASSE');
            $codigoClasse = new Arquivo_Model_DataMapper_Pctt();
            $idClasse = $codigoClasse->getClasseCodigo($subClasse);
            
            if ($subClasse) {

                $subClasses = new Arquivo_Model_DataMapper_Pctt();
                 $idSubClasse = $subClasses->getSubClass($subClasse);
                foreach ($idSubClasse as $assunto):
                    $form->getElement('AQSC_CD_SUBCLASSE')
                            ->addMultiOptions(array($assunto["AQSC_ID_SUBCLASSE"]
                                => strtoupper(
                                        $idPrincipal. '.'.
                                        $codSecundario.'.'.
                                        $idClasse[0]["AQCL_CD_CLASSE"]. '.'.
                                        $assunto['AQSC_CD_SUBCLASSE'] . ' - ' .
                                        $assunto["AQSC_DS_SUBCLASSE"])));
                endforeach;
            }
            $form->addElement('hidden','AQAP_CD_ASSUNTO_PRINCIPAL',array('value' => $codPrincipal));
            $form->addElement('hidden','AQAS_CD_ASSUNTO_SECUNDARIO',array('value' => $codigoSecundario));
            $this->view->form = $form;
            
        }
    }
    
    
    public function listAtividadeTabelaAction(){
            
        $id = $this->_getParam('AQSC_CD_SUBCLASSE');
        // Chamando o data mapper
        $codigoClasse = new Arquivo_Model_DataMapper_Pctt(); 
        $form = new Arquivo_Form_AddAtividades();
        $tableAtividade = new Arquivo_Model_DbTable_SadTbAqatAtividade();
        $atividadeFetch = $codigoClasse->getContaAtividade($id);
        // inserindo valores automatico no campo código
          if($atividadeFetch[0]['TOTALATIVIDADE'] > 0 ){
              $inputValue = "value=".$atividadeFetch[0]['TOTALATIVIDADE']."";
              $this->view->contAtividades = $inputValue ;
              
            }else{
              $inputValue = "value='1'";
              $this->view->contAtividades = $inputValue ;
                
            }
        $codigoAlternativo = $this->_getAllParams();
        $codigoAlternativoId = $codigoAlternativo;
        $codigoAlternativoId1 = $this->_getParam('AQSC_CD_SUBCLASSE');
        
        // Pegando o codigo da classe
        $idCodigoClasse = $this->_getParam('AQCL_CD_CLASSE');
        // Chamando a função para gerar o codigo da classe 
        $idClasse = $codigoClasse->getClasseCodigo($idCodigoClasse);
        // Chamando o cidogo da subclasse
        $codigoSubClasse = new Arquivo_Model_DataMapper_Pctt();
        $codigoClasseId = $codigoSubClasse->getSubclasseCodigo($codigoAlternativoId1);
        
        $idSub = $id;
        $this->view->idSub = $idSub;
        $Atividade = $tableAtividade->fetchAll("AQAT_ID_AQSC = " . $id);
        $this->view->DESCRICAO = array();
//        foreach ($Atividade as $AtividadeDescricao):
//            $valor = $AtividadeDescricao['AQAT_DS_ATIVIDADE'];
//            $this->view->data['DESCRICAO'] = $valor;
//        endforeach;
        
        $dataMapper = new Arquivo_Model_DataMapper_Pctt();
        $listaId = $dataMapper->getMaxIdAtividade();
        foreach ($listaId as $listarId):
        $this->view->codigoClasse = $idClasse;
        endforeach;
        $this->view->titulo = "Lista de Atividades";
        $this->view->data = $Atividade;
        $this->view->codigoAlternativoId = $codigoAlternativoId;
        $this->view->codigoClasseId = $codigoClasseId;
        $this->view->id = $id;
        $this->view->listaId = $listaId['AQAT_ID_ATIVIDADE'];
    
    }


    public function addCadastroAtividadesAction() {

        $idSubClass = Zend_Filter::filterStatic($this->_getParam(
                                'AQSC_CD_SUBCLASSE'
                        ), 'int');
        $tableSubClasse = new Arquivo_Model_DbTable_SadTbAqscSubclasse();

        /* Selecionando os registros do banco de acordo com 
          /*AQCL_ID_AQAS
         */
        $where = array(
            'AQSC_ID_SUBCLASSE = ?' => $idSubClass);
        $select = $tableSubClasse->fetchAll($where);
        $this->view->select = $select;
        //Pegando o id vindo do GET
        $id = Zend_Filter::filterStatic($this->_getParam(
                                'AQSC_CD_SUBCLASSE'), 'int');
        $table = new Arquivo_Model_DbTable_SadTbAqatAtividade();
        $this->view->tabela = $table;
        // Recuperando os valores do auto_increment do id
        $dataMapper = new Arquivo_Model_DataMapper_Pctt();
        $idClasse = $dataMapper->getMaxIdAtividade();
        //Criando um objeto formulario
        $form = new Arquivo_Form_AddAtividades();
        $form->addElement('hidden', 'AQAT_ID_ATIVIDADE', array(
            'value' => $idClasse['AQAT_ID_ATIVIDADE']
        ));
        $form->addElement('hidden', 'AQAT_ID_AQSC', array(
            'value' => $id
        ));
        // Enviando o formulario para view
        $this->view->form = $form;

        // Metodos do input get do ajax
        if ($this->getRequest()->isGet()) {

            $data = $this->_getAllParams();
            // Salva a linha criada para o banco
            //Verificando se os dados são válidos
               $where = 'AQAT_ID_AQSC='
                    .$data['AQAT_ID_AQSC']
                    .'AND AQAT_CD_ATIVIDADE='
                    .$data['AQAT_CD_ATIVIDADE'];
               $contar = count($table->fetchRow($where));
               unset($data['Enviar']);
               unset($data['id']);
           // if ($form->isValid($data)) {
               
                    if ($contar > 0) {
                        
                          echo "Ação já cadastrada!";
                          exit();
                    }
                
                //Excluindo o campo submit
                unset($data['Enviar']);
                //Criando uma linha para inserir no banco
                $inserir = $table->createRow($data);
                $inserir->save();
                $msg_to_user = "Dados inseridos com sucesso!";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user,
                    'status' => 'success'));
                $this->_helper->_redirector('list-casdastro-atividades', 'pctt', 'arquivo');
            
        }
    }

    public function editAvitidadesAction() {

        //Selecionando o banco Assunto secundário
        $table = new Arquivo_Model_DbTable_SadTbAqatAtividade();
        //Recuparando os metodos  GETs
        if ($this->getRequest()->isGet()) {
            //selecionando o id para atulizar
            $id = Zend_Filter::filterStatic($this->_getParam('AQAT_ID_ATIVIDADE'), 'int');
            $data = $this->_getAllParams();
            try {
                //Selecionando os campos no banco com parametro GET
                $row = $table->find($id)->current();
                //Criando uma row com os campos de acordo com o GET 
                $salvar = $row->setFromArray($data);
                $salvar->save();
                $msg_to_user = "Atividade alterada com sucesso";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'success'));
                //Retornando os campos get para as view
                $dados = $table->find($id)->current();
                return $dados;
            } catch (Zend_Exception $error_string) {
                $msg_to_user = "Não é possível alterar o assunto";
                $this->_helper->flashMessenger(array(
                    'message' => $msg_to_user, 'status' => 'notice'));
            }
        }
    }
/*/////////////////////////////////////////////////////////////////////////////
 * /////////////////////////////////Atividades e vias//////////////////////////
 * ////////////////////////////////////////////////////////////////////////////
 */
    
 
    public function listCadastroAtividadesViasAction() {
        $this->view->title = "PCTT - Atividades e Vias";
        $id = Zend_Filter::filterStatic($this->_getParam('ID_ATIVIDADE'), 'int');
        $table = new Arquivo_Model_DataMapper_Pctt();
        $tabela = $table->getViasAtividades($id);
        $descricao = $table->getSelectDescAtividade($id);
        $addAtividades = $this->_getAllParams();
        $this->view->addAvitidades = $addAtividades;
        $this->view->data = $tabela;
        $this->view->id = $id;
        $this->view->descricao = $descricao;
        
               
    }
    
    public function buscaAtividadesViasAction(){
        
        $this->view->title = "PCTT - Consulta de Atividades e Vias";
         $form = new Arquivo_Form_Buscar();
         $this->view->buscar = $form;
        
        }
        
     public function gridBuscaAtividadesAction(){
             $id = $this->_getParam('codigo');
             //Zend_Debug::Dump($id);exit;
        if ($id <> '' OR $id <> NULL) {
            $table = new Arquivo_Model_DataMapper_Pctt();
            $tabela = $table->getBuscaAtividades($id);
            $this->view->data = $tabela;
     }
     }
    
    public function addAtividadesViasAction(){
        
        $table = new Arquivo_Model_DbTable_SadTbAqvpViaPctt();
        $tableVias = new Arquivo_Model_DbTable_SadTbAqaviVia();
        $tableDestino = new Arquivo_Model_DbTable_SadTbAqapDestino();
        $tableCorrente = new Arquivo_Model_DbTable_SadTbAqapCorrente();
        $idAtividadesVias = new Arquivo_Model_DataMapper_Pctt();
        $id = $idAtividadesVias->getMaxIdAtividadesVias();
        foreach ($id as $idVias):
            $idVias['AQVP_ID_PCTT'];
        endforeach;
       
        //Valores para inserir no campo codigo
        if($this->getRequest()->isGet()){
        $codigo  = $this->_getAllParams();
        $codigo0  = $codigo['AQVP_ID_AQAT'];
        $codigo1  = $codigo['AQAP_CD_ASSUNTO_PRINCIPAL'].'.';
        $codigo2  = $codigo['AQAS_CD_ASSUNTO_SECUNDARIO'];
        $codigo3  = $codigo['AQCL_CD_CLASSE'];
        $codigo4  = $codigo['AQSC_CD_SUBCLASSE'].'.';
        $codigo5  = $codigo['AQAT_CD_ATIVIDADE'];
        $idCodigo = $codigo1. $codigo2. $codigo3. $codigo4.$codigo5.'-';
        }
        $codigo6 = $codigo['CODIGO_DESTINO'];
     
        // Enviando para as views
        $this->view->tableVias = $tableVias->fetchAll('AQVI_CD_VIA IS NOT NULL' ,'AQVI_CD_VIA ASC');
        $this->view->tableDestino = $tableDestino->fetchAll('AQDE_CD_DESTINO IS NOT NULL','AQDE_CD_DESTINO ASC');
        $this->view->tableCorrente = $tableCorrente->fetchAll('AQTE_CD_TEMPORALIDADE IS NOT NULL','AQTE_CD_TEMPORALIDADE ASC');
        $this->view->codigos = $idCodigo;
        $this->view->codigo0 = $codigo0;
        $this->view->ativVias = $idVias;
        
        // Transformando o campo vindo do get em array
        
         
             $data = $this->_getAllParams();
             $table = new Arquivo_Model_DbTable_SadTbAqvpViaPctt();
             $codigoPcttBanco = $table->fetchAll("AQVP_ID_AQAT = " . $data['AQVP_ID_AQAT']);
             $aqvp_cd_pctt  = new Arquivo_Model_DataMapper_Pctt();
             $codigoPctt = $aqvp_cd_pctt->getPCTTCodigo($data['AQVP_ID_AQAT']);
             //$contar = count($table->fetchRow('AQVP_CD_PCTT = \'' .$data['AQVP_CD_PCTT']. '\''));
             //if($contar > 0 ){
                 foreach ($codigoPcttBanco as $exibirCodigoPctt):
                   $exibirCodigoPctt['AQVP_CD_PCTT'];
                   $arrayPctt = explode('-',$exibirCodigoPctt['AQVP_CD_PCTT']);
                  // zend_debug::dump($arrayPctt[1]); 
                 endforeach;
                 
                   if($arrayPctt[1] <> ''){
                      $quantidadeVia = $aqvp_cd_pctt->getSelectQTAtividade($arrayPctt[1]);
                      $ultimo_valor_Via = $quantidadeVia[0]['AQVI_QT_VIA'];
                      $tableVias = new Arquivo_Model_DbTable_SadTbAqaviVia();
                      $SelecionarVia = $tableVias->fetchAll('AQVI_QT_VIA = ' .$ultimo_valor_Via);
                      foreach ($SelecionarVia as $SelecionarViaCd):
                      $this->view->SelecionarVia = $SelecionarViaCd['AQVI_CD_VIA'];
                      $this->view->ultimoValorVia = $ultimo_valor_Via;
                       endforeach;
                      //zend_debug::dump($SelecionarVia);
                   }else{
                        $this->view->SelecionarVia = 'A';
                   }
        // }
        
    }
    
    public function saveAtividadeAction(){
        //$this->_helper->viewRenderer->setNoRender(true);
        //$this->_helper->layout->disableLayout();
          $table = new Arquivo_Model_DbTable_SadTbAqvpViaPctt();
          $cont  = new Arquivo_Model_DataMapper_Pctt();
           //Inserindo os dados no banco
        if ($this->getRequest()->isGet()) {
            $data = $this->_getAllParams();
            $contar = count($table->fetchRow('AQVP_CD_PCTT = \'' .$data['AQVP_CD_PCTT']. '\''));
            //Verificando se existe o registro antes de inserir
            //$contar = $cont->getCountTemporalidade($data['AQVP_CD_PCTT'], $data['AQVP_ID_AQAT']);
            if ($contar > 0) {
                  echo 'Ação já cadastrada!';
                  die();
            }
            else{
            $inserir = $table->createRow($data);
            $inserir->save();
            }
        }
        $this->view->contar = $contar;
    }
    
    public function mostrarDestinoAction(){
        
          if($this->getRequest()->isGet()){
            
          $data = $this->_getAllParams();
          $table = new Arquivo_Model_DbTable_SadTbAqapDestino();
          $destino = $table->fetchAll('AQDE_CD_DESTINO='.$data['CODIGO_DESTINO']);
          $this->view->destino = $destino;
          }
          
        
    }
    
      public function mostrarCorrenteAction(){
        
          if($this->getRequest()->isGet()){
            
          $data = $this->_getAllParams();
          $table = new Arquivo_Model_DbTable_SadTbAqapCorrente();
          $corrente = $table->fetchAll('AQTE_CD_TEMPORALIDADE= '.$data['CODIGO_DESTINO']);
          $this->view->destino = $corrente;
          }
          
        
    }
    
      public function mostrarIntermediarioAction(){
        
          if($this->getRequest()->isGet()){
            
          $data = $this->_getAllParams();
          $table = new Arquivo_Model_DbTable_SadTbAqapCorrente();
          $intermediario = $table->fetchAll('AQTE_CD_TEMPORALIDADE='.$data['CODIGO_DESTINO']);
          $this->view->intermediario = $intermediario;
          }
          
          
        
    }
    public function editAtividadesViasAction(){
        
    }
}
