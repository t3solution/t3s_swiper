<?php

/**
 * Extension Manager/Repository config file for ext "t3sswiper".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 't3sSwiper',
    'description' => 'Swiper is the most modern free mobile touch slider with hardware accelerated transitions and amazing native behavior.',
    'category' => 'fe',
    'state' => 'beta',
    'author' => 'Helmut Hackbarth',
    'author_email' => 'typo3@t3solution.de',
    'author_company' => 'T3Solution',
    'version' => '0.0.4',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'content_blocks' => '0.6.0-0.6.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'T3S\\T3sSwiper\\' => 'Classes',
        ],
    ],
];
