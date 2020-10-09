<?php
declare(strict_types = 1);

return [
    \BrainAppeal\CampusEventsConvert2News\Domain\Model\Convert2NewsConfiguration::class => [
        'tableName' => 'tx_campuseventsconnector_domain_model_convertconfiguration',
        'recordType'  => 1
    ],

    \BrainAppeal\CampusEventsConvert2News\Domain\Model\News::class => [
        'tableName' => 'tx_news_domain_model_news',
    ]
];