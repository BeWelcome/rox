<?php


/**
 * this class allows to have a local page object,
 * and replaces the global PVars::getObj('page').
 * Unlike the PVars::getObj('page'), this one can render itself!
 *
 * alias of PageWithParameterizedRoxLayout
 *
 * inject the parameters using functionality defined in ObjectWithInjection
 */
class RoxGenericPage extends PageWithParameterizedRoxLayout
{
    protected function getColumnNames() {
        return array('col1', 'col3');
    }

    protected function submenu()
    {
        echo <<<SUBMENU
<div class="col-md-3 offcanvas-collapse mb-2">
    <div class="w-100 p-1 text-right d-md-none">
        <button type="button" class="btn btn-sm" aria-label="Close" data-toggle="offcanvas">
            <i class="fa fa-lg fa-times white" aria-hidden="true"></i>
        </button>
    </div>
    {$this->get('newBar')}
</div>
SUBMENU;
    }
}

