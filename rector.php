<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withAttributesSets(symfony: true, doctrine: true);
