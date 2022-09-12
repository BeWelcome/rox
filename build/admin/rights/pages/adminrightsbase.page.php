<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */
class AdminRightsBasePage extends AdminBasePage
{
    protected $sidebar = array(
        'AdminRightsAssign' => 'admin/rights',
        'AdminRightsOverview' => 'admin/rights/overview',
        'AdminRightsListMembers' => 'admin/rights/list/members',
        'AdminRightsListRights' => 'admin/rights/list/rights',
        'AdminRightsCreate' => 'admin/rights/create',
    );

    protected $current = false;
    protected $rights = false;
    protected $create = false;

    public function __construct() {
        parent::__construct(new AdminRightsModel());
        $member = $this->model->getLoggedInMember();
        $rights = $member->getOldRights();
        $scope = $rights['Rights']['Scope'];
        $this->create = stripos($scope, '"create"') !== false;
        $this->create |= stripos($scope, '"all"') !== false;
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . " &raquo; <a href='admin/rights'>{$this->words->get('AdminRights')}</a>";
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

    protected function rightsSelect($rights, $current, $disabled = false)
    {
        $select = '<select id="rightid" name="rightid"' . ($disabled ? ' disabled="disabled"' : '') . ' class="o-input">';
        $select .= '<option value="0"></option>';
        foreach ($rights as $right) {
            $select .= '<option value="' . $right->id . '"';
            if ($current == $right->id) {
                $select .= ' selected="selected"';
            }
            $select .= '>' . $right->Name . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    function levelSelect($current, $disabled = false, $showEmpty = true)
    {
        $select = '<select id="level" name="level"' . ($disabled ? ' disabled="disabled"' : '') . ' class="o-input select2">';
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
