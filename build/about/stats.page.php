<?php


class AboutStatisticsPage extends AboutBasePage
{
    protected function getPageTitle() {
        return 'About BeWelcome: Statistics';
    }
    
    protected function getCurrentSubpage() {
        return 'stats';
    }
    
    protected function teaserContent() {
        $title = 'BW Statistics';
        require TEMPLATE_DIR.'apps/rox/teaser_simple.php';
    }
    
    protected function column_col3() {
        $countryrank = $this->getModel()->getMembersPerCountry();
        $loginrank = $this->getModel()->getLastLoginRank();
        $loginrankgrouped = $this->getModel()->getLastLoginRankGrouped();        
        $statsall = $this->getModel()->getStatsLogAll();
        $statslast = $this->getModel()->getStatsLog2Month();
        
        require TEMPLATE_DIR.'apps/rox/stats.php';
    }
}


?>