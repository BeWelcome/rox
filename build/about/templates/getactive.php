<?php
/*
Copyright (c) 2007 BeVolunteer
This file is part of BW Rox.
BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330,
Boston, MA 02111-1307, USA.
*/
$words = new MOD_words();
?>
<div class="col-12 px-0">
    <h2><?php echo $words->get('GetActive') ?></h2>
    <p><?php echo $words->get('GetActiveIntro') ?></p>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0">
<i class="fa fa-code" aria-hidden="true"></i> <?php echo $words->get('GetActiveDevTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeDevTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveDevContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveDevText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-flask"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActiveTestingTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeTestingTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveTestingContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveTestingText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-question-circle-o"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActiveSupportTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeSupportTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveSupportContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveSupportText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-paint-brush"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActiveDesignTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeDesignTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveDesignContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveDesignText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-handshake-o"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActiveNMBWTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeNMBWTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveNMBWContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveNMBWText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-language"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActiveTranslateTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeTranslateTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveTranslateContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveTranslateText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-bullhorn"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActivePRTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomePRTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActivePRContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActivePRText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-user-circle"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActiveModTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeModTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveModContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveModText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-map-marker-alt"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActiveLocalTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeLocalTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveLocalContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveLocalText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-money"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActiveDonationTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeDonationTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveDonationContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveDonationText') ?></p>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 mb-3 pl-0">
    <div class="card p-3 h-100">
        <div class="card-body">
            <h3 class="card-title m-0"><i class="fa fa-heart"
                                      aria-hidden="true"></i> <?php echo $words->get('GetActiveBVTitle') ?></h3>
            <p class="text-muted"><?php echo $words->get('HelpBeWelcomeBVTags') . " <br> " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveBVContact') ?></p>
            <p class="card-text"><?php echo $words->get('GetActiveBVText') ?></p>

        </div>
    </div>
</div>
