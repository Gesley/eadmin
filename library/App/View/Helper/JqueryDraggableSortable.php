<?php
/**
 * Realiza a ordenação da table com Drag in Drop.
 * 
 * @autor <marcelo.caixeta@trf1.jus.br>
 */
class App_View_Helper_JqueryDraggableSortable extends Zend_View_Helper_Abstract
{
    public $view;
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
 
    /**
     * Tabela a ser ordenada.
     *
     * @param  string $tableId
     * @return void
     */
    public function JqueryDraggableSortable($tableId)
    { ?>
        <script type="text/javascript">
            $(function() {
                $("#<?php echo $tableId; ?>").sortable({
                  revert: true
                });
                $("#draggable").draggable({
                  connectToSortable: "#<?php echo $tableId; ?>",
                  helper: "clone",
                  revert: "invalid"
                });
                $("tbody, tr").disableSelection();
            });
        </script>
    <?php }
} ?>