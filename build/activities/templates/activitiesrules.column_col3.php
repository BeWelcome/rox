<div class="row">
    <div class="col-12">
        <h1><?= $this->getWords()->getSilent('activities.rules.headline'); ?></h1>
        <p><?= $this->getWords()->getSilent('activities.rules.tool'); ?></p>
        <p><?= $this->getWords()->getSilent('activities.rules.description'); ?></p>
        <p><?= $this->getWords()->getSilent('activities.rules.handling'); ?></p>
        <h3><?= $this->getWords()->getSilent('activities.rules.types'); ?></h3>
        <ul>
            <li><?= $this->getWords()->getSilent('activities.rules.type.category'); ?></li>
            <li><?= $this->getWords()->getSilent('activities.rules.type.variants'); ?></li>
        </ul>
        <h3><?= $this->getWords()->getSilent('activities.rules.duration'); ?></h3>
        <ul>
            <li><?= $this->getWords()->getSilent('activities.rules.duration.short'); ?></li>
            <li><?= $this->getWords()->getSilent('activities.rules.duration.volatile'); ?></li>
            <li><?= $this->getWords()->getSilent('activities.rules.duration.recurring'); ?></li>
        </ul>
        <h3><?= $this->getWords()->getSilent('activities.rules.noncommercial'); ?></h3>
        <ul>
            <li><?= $this->getWords()->getSilent('activities.rules.noncommercial.community'); ?></li>
            <li><?= $this->getWords()->getSilent('activities.rules.noncommercial.bewelcome'); ?></li>
            <li><?= $this->getWords()->getSilent('activities.rules.noncommercial.promotion'); ?></li>
            <li><?= $this->getWords()->getSilent('activities.rules.noncommercial.expenses'); ?></li>
        </ul>
        <h3><?= $this->getWords()->getSilent('activities.rules.reporting'); ?></h3>
        <ul>
            <li><?= $this->getWords()->getSilent('activities.rules.reporting.process'); ?></li>
            <li><?= $this->getWords()->getSilent('activities.rules.reporting.removal'); ?></li>
        </ul>
    </div>
</div>
