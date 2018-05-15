<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminFlagsBasePage extends AdminBasePage
{
    protected $sidebar = array(
        'AdminFlagsAssign' => 'admin/flags/assign',
        'AdminFlagsOverview' => 'admin/flags/overview',
        'AdminFlagsListMembers' => 'admin/flags/list/members',
        'AdminFlagsListFlags' => 'admin/flags/list/flags',
        'AdminFlagsCreate' => 'admin/flags/create',
    );

    protected $current = false;
    protected $flags = false;
    protected $create = false;

    public function __construct() {
        parent::__construct(new AdminFlagsModel());
        $member = $this->model->getLoggedInMember();
        $flags = $member->getOldFlags();
        $scope = $flags['Flags']['Scope'];
        $this->create = stripos($scope, '"create"') !== false;
        $this->create |= stripos($scope, '"all"') !== false;
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . " &raquo; <a href='admin/flags'>{$this->words->get('AdminFlags')}</a>";
    }

    /**
     * @param string $current current item in the side bar
     */
    protected function setCurrent($current) {
        $this->current = $current;
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/admin.css?2';
        return $stylesheets;
    }

    protected function getSubmenuItems()
    {
        $items = [];
        foreach($this->sidebar as $key => $item) {
            if (!$this->create && $key == 'AdminRightsCreate') continue;
            $items[] = [
                $key,
                $item,
                $this->words->get($key),
            ];
        }
        return $items;
    }

    protected function getSubmenuActiveItem()
    {
        return $this->current;
    }

    protected function flagsSelect($flags, $current, $disabled = false)
    {
        $select = '<select class="form-control" id="flagid" name="flagid"' . ($disabled ? ' disabled="disabled"' : '') . '>';
        $select .= '<option value="0"></option>';
        foreach ($flags as $flag) {
            $select .= '<option value="' . $flag->id . '"';
            if ($current == $flag->id) {
                $select .= ' selected="selected"';
            }
            $select .= '>' . $this->getWords()->getSilent('AdminFlags' . $flag->Name) . '</option>';
        }
        $select .= '</select>' . $this->getWords()->FlushBuffer();
        return $select;
    }

    function levelSelect($current, $disabled = false, $showEmpty = true)
    {
        $select = '<select class="form-control" id="level" name="level"' . ($disabled ? ' disabled="disabled"' : '') . '>';
        if ($showEmpty) {
            $select .= '<option value="0"></option>';
        }
        for ($i = 1; $i <= 10; $i++) {
            $select .= '<option value="' . $i . '"';
            if ($i == $current) {
                $select .= ' selected="selected"';
            }
            $select .= '>' . $i . '</option>';
        }
        $select .= '</select>';
        return $select;
    }
}
