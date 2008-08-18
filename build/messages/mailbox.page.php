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
    
    protected function messageActions()
    {
        require_once 'templates/actions.php';
    }
        
    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();
        
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MessagesController', 'mailBoxCallback');
        
        $this->mailboxDescription();
        $formstart = '<form name="msgform" id="msgform" action="'.$page_url.'" method="post">';
        $formstart .= $callback_tag;
        $formend = '</form>';
        $widget = $this->getMailboxWidget();
        if ($widget->needsPagination()) {
            echo $formstart;
            $widget->showPagination();
            echo '<br style="clear:both">';
            $widget->render();
            $this->messageActions($actionurl);
            $widget->showPagination();
            echo $formend;
            echo '<br style="clear:both">';
        } else {
            echo $formstart;
            $widget->render();
            $this->messageActions($actionurl);
            echo $formend;
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
            
            var hold_inner = 0;
            var inputs = $$('.full .select input');
            for (i = 0; i < inputs.length; i++) {
                inputs[i].onmouseover = function() {
                    hold = 0;
                    hold_inner = 1;
                }
                inputs[i].onmouseout = function() {
                    hold_inner = 0;
                }
            }
            
            var select = $$('.full .select');
            for (i = 0; i < select.length; i++) {
                select[i].onclick = function() {
                    hold = 1;
                    e = this.select('input');
                    if (hold_inner != 1) {
                        if (e[0].checked == false) {
                            e[0].checked = true;
                        } else {
                            e[0].checked = false;
                        }
                    }
                    if (e[0].checked == true) {
                        new Effect.Highlight(this.parentNode, { startcolor: '#ffffff', endcolor: '#ffff99', restorecolor: '#ffff99' });
                    } else {
                        new Effect.Highlight(this.parentNode, { startcolor: '#ffff99', endcolor: '#ffffff', restorecolor: '#ffffff' });
                    }
                    //hold = 0; 
                }
                select[i].onmouseout = function() {
                    hold = 0;
                    hold_inner = 0;
                }
            }
            
            var rows = $$('.full tr tr');
            for (i = 0; i < rows.length; i++) {
                rows[i].onclick = function() {
                    if (hold == 0) {
                        msg = this.select('input.msganchor');
                        if (msg[0]) {
                            window.location.href = http_baseuri+'messages/'+msg[0].identify();
                            return false;
                        }
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
