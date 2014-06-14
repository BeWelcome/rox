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
     * includes the sidebar
     *
     * @todo: Find a better way to include the template
     */
    public function leftSidebar() {
        require getcwd() . '/../build/admin/flags/templates/adminflags.leftsidebar.php';
    }

    /**
     * @param $current current item in the side bar
     */
    protected function setCurrent($current) {
        $this->current = $current;
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/admin.css?1';
        return $stylesheets;
    }

    protected function flagsSelect($flags, $current, $disabled = false)
    {
        $select = '<select id="flagid" name="flagid"' . ($disabled ? ' disabled="disabled"' : '') . '>';
        $select .= '<option value="0"></option>';
        foreach ($flags as $flag) {
            $select .= '<option value="' . $flag->id . '"';
            if ($current == $flag->id) {
                $select .= ' selected="selected"';
            }
            $select .= '>' . $flag->Name . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    function levelSelect($current, $disabled = false, $showEmpty = true)
    {
        $select = '<select id="level" name="level"' . ($disabled ? ' disabled="disabled"' : '') . '>';
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
