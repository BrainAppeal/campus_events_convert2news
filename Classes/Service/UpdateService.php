<?php

namespace BrainAppeal\CampusEventsConvert2News\Service;

use BrainAppeal\CampusEventsConnector\Service\UpdateService as BaseUpdateService;


class UpdateService extends BaseUpdateService
{
    /** @var array $tables */
    protected $tables = [
        'tx_news_domain_model_news',
    ];
}
