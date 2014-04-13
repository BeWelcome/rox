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

    private $current = false;
    protected $rights = false;

    public function __construct($model = false) {
        parent::__construct($model);
        if ($model) {
            $this->rights = $model->getRights();
        }
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . " &raquo; <a href='admin/rights'>{$this->words->get('AdminRights')}</a>";
    }

    /**
     * includes the sidebar
     *
     * @todo: Find a better way to include the template
     */
    public function leftSidebar() {
        require getcwd() . '/../build/admin/rights/templates/adminrights.leftsidebar.php';
    }

    /**
     * @param $current current item in the side bar
     */
    public function setCurrent($current) {
        $this->current = $current;
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        // $stylesheets[] = 'styles/css/minimal/screen/custom/tooltipster.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/admin.css';
        return $stylesheets;
    }

    protected function rightsSelect($rights, $current, $disabled = false)
    {
        $select = '<select id="right" name="right"' . ($disabled ? ' disabled="disabled"' : '') . '>';
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

    function levelSelect($current, $disabled = false)
    {
        $select = '<select id="level" name="level"' . ($disabled ? ' disabled="disabled"' : '') . '>';
        $select .= '<option value="0"></option>';
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
