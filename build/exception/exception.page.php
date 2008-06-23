<?php


class ExceptionPage
{
    function render()
    {
        $e = $this->exception;
        echo '
        <style>
        * {
          font-family: arial;
        }
        .hoverme {
          position:relative;
        }
        .hoverme .tooltip {
          display:none;
        }
        .hoverme .tooltip {
          position:absolute;
          top:1em;
          left:0px;
          background:#FFFEEB;
          border:3px solid #aaa;
          padding:2px 6px;
          z-index:20;
        }
        .hoverme:hover {
          background:#eeeeff;
        }
        .hoverme:hover .tooltip {
          display:block;
        }
        .tracestep {
          padding:10px;
          border:1px solid #eaeaff;
          border-width: 1px 0 0 0;
          background:#f6f6ff;
        }
        .tracestep.odd {
          background:white;
        }
        .tracestep .tooltip {
          padding:10px;
          border:1px solid #ffee66;
          background:#fffeeb;
        }
        .tracestep .file {
          color:#aaa;
        }
        .tracestep .step {
          color:#aaa;
        }
        .tracestep pre {
          font-family:monospace;
        }
        </style>
        <h2>'.htmlentities($e->getMessage(), ENT_COMPAT, 'utf-8').'</h2>
        <p>'.get_class($e).'</p>
        <table>
        <tr><td>code: </td><td>'.$e->getCode().'</td></tr>
        <tr><td>message: </td><td>'.htmlentities($e->getMessage(), ENT_COMPAT, 'utf-8').'</td></tr>
        <tr><td>file: </td><td>'.$e->getFile().'</td></tr>
        <tr><td>line: </td><td>'.$e->getLine().'</td></tr>
        </table>
        <br>';
        foreach ($e->getInfo() as $i => $inf) {
            echo 'info['.$i.']: '.htmlentities($inf, ENT_COMPAT, 'utf-8').'<br>';
        }
        foreach ($e->getTrace() as $i => $step) {
            $this->showTraceStep($step, $i);
        }
        /*
        $stacktrace_widget = new StackTraceWidget;
        $stacktrace_widget->items = $e->getTrace();
        echo '<style> th {text-align:left;}';
        $stacktrace_widget->printCSS();
        echo '</style>';
        $stacktrace_widget->render();
        */
    }
    
    protected function showTraceStep($step, $i_step)
    {
        extract($step);
        echo '<div class="tracestep '.($i_step%2 ? 'odd' : 'even').'">';
        // echo '<div class="step">'.$i_step.'</div>';
        if (isset($class)) {
            echo $class.'::';
        }
        echo $function.'(';
        $showargs = array();
        if (isset($this->debug)) {
            foreach ($args as $i => $arg) {
                $showargs[] = '
                <span class="hoverme">
                <a href="x">'.
                (is_object($arg) ? (get_class($arg).' object') : (
                    is_numeric($arg) ? (gettype($arg).' '.$arg) : (
                        is_string($arg) ? ('string('.strlen($arg).') = "'.substr($arg, 0, 20).'"') : (
                            is_array($arg) ? ('array['.count($arg).']') : (
                                is_bool($arg) ? ($arg ? 'true' : 'false') : gettype($arg)
                            )
                        )
                    )
                )).'</a>
                <div class="tooltip"><pre>'.print_r($arg, true).'</pre></div>
                </span>';
            }
        } else {
            foreach ($args as $i => $arg) {
                $showargs[] =
                    is_object($arg) ? (get_class($arg).' object') : (
                        is_string($arg) ? ('string('.strlen($arg).')') : (
                            is_array($arg) ? ('array['.count($arg).']') : (
                                is_bool($arg) ? ($arg ? 'true' : 'false') : gettype($arg)
                            )
                        )
                    )
                ;
            }
        }
        echo implode(', ', $showargs);
        echo ')';
        if (isset($file)) {
            echo '<div class="file">'.$file.' (line '.$line.')</div>';
        }
        echo '</div>';
    }
}

/*
class StackTraceWidget extends ScrolltableWidget
{
    protected function getTableColumns()
    {
        return array(
            'file' => 'file',
            'line' => 'line',
            'function' => 'function',
            'class' => 'class',
            'type' => 'type',
            'args' => 'args',
        );
    }
    
    protected function tableCell($key, $trace_step) {
        if (isset($trace_step[$key])) {
            echo '<pre>'; print_r($trace_step[$key]); echo '</pre>';
        } else {
            echo '-';
        }
    }
    
    protected function tableCell_args($trace_step)
    {
        echo '<div style="overflow:auto; width:50em; height:6em;">';
        // print_r($trace_step['args']);
        
        echo '<table>';
        foreach ($trace_step['args'] as $i => $arg) {
            echo '<tr><th>arg['.$i.']</th><td><pre>';
            print_r($arg);
            echo '</pre></td></tr>';
        }
        echo '</table>';
        
        echo '</div>';
    }
}
*/

?>