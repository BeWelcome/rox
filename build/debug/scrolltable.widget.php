<?php


class ScrolltableWidget extends ItemlistWidget
{
    function printCSS()
    {
        echo '
        .scrolling_table_widget {
          position:relative;
        }
        .scrolling_table_widget .scrollbox {
          max-height:300px;
          overflow:auto;
          border:1px solid #ddd;
          // color:grey;
          background:#fffeeb;
          padding:3px;
          text-align:left;
          float:left;
        }
        .scrolling_table_widget .clearfloats {
          clear:both;
        }
        .scrolling_table_widget table {
          border-collapse:collapse;
        }
        .scrolling_table_widget table th,
        .scrolling_table_widget table td {
          border:1px solid #ddd;
          border-width:1px 0px;
          padding:0 16px;
        }
        .scrolling_table_widget table tr.even {
          background:#fffff4;
        }
        .scrolling_table_widget select {
          width:140px;
        }
        .scrolling_table_widget select .select_none {
          color:grey;
          background:#eee;
        }
        ';
    }
    
    function render() {
        echo '
        <div class="scrolling_table_widget">
        <div class="scrollbox">';
        parent::render();
        echo '
        </div>
        <div class="clearfloats"></div>
        </div>';
    }
    
    
}


?>