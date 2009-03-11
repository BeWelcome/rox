<?php


//------------------------------------------------------------------------------------
/**
 * This widget shows a list of notifications for a member.
 */
class NotifyMemberWidget extends ItemlistWithPagination
{
    private $_note;
    
    public function render() {
        parent::render(); 
    }
    
    
    //-----------------------------------------------------------------
    // getting the items
    
    protected function getAllItems()
    {
        $notes = $this->model->getMemberNotes();
        return $notes;
    }
    
    
    protected function showItems()
    {
        $words = new MOD_words();
        // don't need a table - a simple list is enough.
        $this->showItems_list();
        echo $words->flushBuffer();
        ?>
        <script type="text/javascript">
                    
	var DynamicExtensions = {
	    dynamicize: function(element){
	        element.dHref = element.href;
			element.dTarget = element.target;
			element.href = "#";
			element.target = "";
			element.dynamic = dynamicallyLoad.bindAsEventListener(element);
			Event.observe(element, 'click', element.dynamic);
			return element;
	    }
	}

	Element.addMethods(DynamicExtensions);

	function dynamicallyLoad(e) {
		element = Event.element(e);
		if (!element.dHref) element = element.parentNode;
		new Ajax.Updater(element.dTarget, element.dHref, {
			method: 'get',
			onComplete: function() {
				setLinks(element,element.dTarget);
				new Effect.Highlight(element.dTarget, {duration: 1});
				return false;
			}
		});
	}

	function setLinks(e, target) {
		(target) ? selector = '#'+target+' ' : selector = '';
		$$(selector+'a.dynamic').invoke('dynamicize');
	}

	Event.observe(window, 'load', setLinks);

        </script>
        
        <?php
    }
    
    protected function showListItem($item, $i_row)
    {
        $words = new MOD_words();
        extract(get_object_vars($item));
//        print_r($item);
        ?>
        <div class="floatbox"">
            <a target="notify-<?=$item->id?>" class="dynamic float_right" href="notify/<?=$item->id?>/check" alt="<?=$words->getSilent($item->WordCode,MOD_member::getUsername($item->IdRelMember))?>">
                <img src="images/icons/box-close.png">
            </a>
            <div class="float_right"><?=MOD_layoutbits::ago(strtotime($item->created))?></div>
            <?php if ($item->Link != '') { 
                echo '<a href="'.$item->Link.'" alt="'.$words->getSilent($item->WordCode,MOD_member::getUsername($item->IdRelMember)).'">';
            }
            ?>
                <?=$words->getSilent($item->WordCode,MOD_member::getUsername($item->IdRelMember))?>
            <?php if ($item->Link != '') { 
                echo '</a>';
            }
            ?>
        </div>
        <?php
    }
    
    
    protected function showBetweenListItems($prev_item, $item, $i_row)
    {
        echo '<hr />';
    }
    
    protected function showItems_list()
    {
        echo '
        <div class="itemlist">';
        // table rows with items
        $items = $this->getItems();
        if (!is_array($items)) {
            echo 'not an array.<br>';
            print_r($items);
        } else {
            $i_row = 0;
            if ($item = array_shift($items)) {
                echo '
                <div id="notify-'.$item->id.'" class="itemlist_element '.($i_row%2 ? 'odd' : 'even').'">';
                $this->showListItem($item, $i_row);
                
                echo '
                </div>';
                $i_row = 1;
                $prev_item = $item;
                foreach ($items as $item) {
                    echo '
                    <div id="notify-'.$item->id.'" class="itemlist_element '.($i_row%2 ? 'odd' : 'even').'">';
                    $this->showBetweenListItems($prev_item, $item, $i_row);
                    $this->showListItem($item, $i_row);
                    echo '
                    </div>';
                    ++$i_row;
                    $prev_item = $item;
                }
            }
        }
        echo '
        </div>';
    }

}


class NotifyAdminWidget extends ItemlistWithPagination
{
    public function render() {
        parent::render(); 
    }
    
    // pagination
    
    protected function hrefPage($i_page) {
        return 'notify/admin/'.$i_page;
    }
    
    
    //-----------------------------------------------------------------
    // getting the items
    
    protected function getAllItems()
    {
        $notes = $this->model->getNotes();
        return $notes;
    }
    
    protected function getItems()
    {
        $this->prepare();
        return $this->getItemsForPage($this->active_page);
    }
    
    
    //-----------------------------------------------------------------
    // table layout
    
    /**
     * Columns for notes table.
     * The $key of a column is used as a suffix for method tableCell_$key
     *
     * @return array table columns, as $name => Column title
     */
    protected function getTableColumns()
    {
        return array(
            'select' => '',
            // 'contact' => 'From/To',
            'id' => 'Id',
            'idmember' => 'IdMember',
            'idrelmember' => 'IdRelMember',
            'type' => 'Type',
            'link' => 'Link',
            'wordcode' => 'WordCode',
            'freetext' => 'FreeText',
            'checked' => 'Checked',
            'sendmail' => 'SendMail',
            'created' => 'Created',

        );
    }
    
    /**
     * Table cell in column 'select', for the given $note
     *
     * @param unknown_type $note
     */
    protected function tableCell_select($note)
    {
        //var_dump($note);
        ?>
        <input type="checkbox" name="note-mark[]" class="noteanchor" id="<?=$note->id?>" value="<?=$note->id?>" />
        <?php
    }

    protected function tableCell_id($note)
    {
        echo $note->id;
    }

    protected function tableCell_idmember($note)
    {
        echo $note->IdMember;
    }
    
    protected function tableCell_idrelmember($note)
    {
        ?>
        <?=$note->IdRelMember ?> (<a href="members/<?=MOD_member::getUsername($note->IdRelMember)?>"><?=MOD_member::getUsername($note->IdRelMember)?></a>)
        <?php
    }

    protected function tableCell_type($note)
    {
        echo $note->Type;
    }

    protected function tableCell_link($note)
    {
        ?>
        <a href="<?=$note->Link?>"><?=$note->Link?></a>
        <?php
    }

    protected function tableCell_wordcode($note)
    {
        echo $note->WordCode;
    }

    protected function tableCell_freetext($note)
    {
        echo $note->FreeText;
    }

    protected function tableCell_checked($note)
    {
        echo $note->Checked;
    }

    protected function tableCell_sendmail($note)
    {
        echo $note->SendMail;
    }

    protected function tableCell_created($note)
    {
        echo $note->created;
    }
    
}






?>
