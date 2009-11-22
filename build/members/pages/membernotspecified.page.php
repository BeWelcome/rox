<?php


class MembersMembernotspecifiedPage extends RoxPageView
{
    protected function teaserHeadline()
    {
        echo '
        Members - No username specified.';
    }
    
    protected function leftSidebar()
    {
        echo '
        unclickable black';
    }
    
    protected function column_col3()
    {
        echo '
        <p>We have so many members!!!<br> Which one do you want?</p>
        <ul>
        <li><a href="">The dangerous rabbit.</a></li>
        <li><a href="">The old lady.</a></li>
        <li><a href="">The bus driver.</a></li>
        <li><a href="">Santa Claus.</a></li>
        </ul>';
    }
}
