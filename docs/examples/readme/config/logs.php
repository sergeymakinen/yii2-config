<?php

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        'main' => [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning'],
        ],
    ],
];
