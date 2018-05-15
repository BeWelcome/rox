<?php

class AdminTreasurerBasePage extends AdminBasePage
{
    protected $campaign = false;

    protected function getSubmenuItems()
    {
        $words = $this->getWords();

        $items = [];
        $items[] = ['add', 'admin/treasurer/add', $words->get('AdminTreasurerAddDonation')];
        if ($this->campaign)
        {
            $items[] = ['stop', 'admin/treasurer/campaign/stop', $words->get('AdminTreasurerStopCampaign')];
        } else {
            $items[] = ['start', 'admin/treasurer/campaign/start', $words->get('AdminTreasurerStartCampaign')];
        }
        return $items;
    }
}
