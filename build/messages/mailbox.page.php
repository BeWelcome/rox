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
        $actionurl = '';
        $formstart = '<form name="msgform" id="msgform" action="'.$actionurl.'" method="post">';
        $formstart .= '<div class="NotDisplayed">
                        Well, even without scripts this should work... 
                        <input type="radio" name="noscriptaction" value="delmsg" /> delete&nbsp;&nbsp;
                        <input type="radio" name="noscriptaction" value="isspam" /> mark as spam
                        <input type="submit" id="submit" value="process messages or whatever" />
                       </div>
                       ';
        $formstart .= '<div class="Display" style="display:none" onload="this.show()">
                        <input type="radio" name="noscriptaction" value="delmsg" /> delete&nbsp;&nbsp;
                        <input type="radio" name="noscriptaction" value="isspam" /> mark as spam
                        <input type="submit" id="submit" value="process messages or whatever" />
                       </div>
                       ';
        $formend = '</form>';
        
        $widget = $this->getMailboxWidget();
        if ($widget->needsPagination()) {
            echo $formstart;
            $widget->showPagination();
            echo '<br style="clear:both">';
            $widget->render();
            echo $formend;
            $widget->showPagination();
            echo '<br style="clear:both">';
        } else {
            echo $formstart;
            $widget->render();
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
                            window.location.href='messages/'+msg[0].identify();
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