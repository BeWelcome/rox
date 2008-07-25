<?php


class MessagesPageWithMailbox extends MessagesBasePage
{
    protected function getMailboxWidget()
    {
        $widget = $this->layoutkit->createWidget($this->getMailboxWidgetClassname());
        $widget->delegate = $this;  // delegate
        $widget->delegate_prefix = 'mailbox';
        $widget->model = $this->getModel();
        $widget->items_per_page = 7;
        $widget->active_page = $this->active_page;
        $widget->visible_range = 2;
        return $widget;
    }
        
    protected function column_col3()
    {
        $this->mailboxDescription();
        
        $widget = $this->getMailboxWidget();
        if ($widget->needsPagination()) {
            $widget->showPagination();
            echo '<br style="clear:both">';
            $widget->render();
            $widget->showPagination();
            echo '<br style="clear:both">';
        } else {
            $widget->render();
        }
    }
    
    protected function footer() {
        parent::footer();
        ?>
        <script type="text/javascript">
        //<![cdata[
        var hold = 0;
        var msg = '';

        function clickablerows() {
            if (!document.getElementsByTagName || !document.createTextNode) return;
            
            var links = $$('.full a');
            for (i = 0; i < links.length; i++) {
                links[i].onmouseover = function() { hold = 1; }
                links[i].onmouseout = function() { hold = 0; }
            }
            
            var rows = $$('.full tr tr');
            for (i = 0; i < rows.length; i++) {
                rows[i].onclick = function() {
                    if (hold == 0) {
                        this.highlight();
                        msg = this.select('a.msganchor');
                        window.location.href='messages/'+msg[0].identify();
                        return false;
                    }
                }
            }
            
        }
        
        clickablerows();
        //]]>
        </script>
        <?php
    }    
}



?>