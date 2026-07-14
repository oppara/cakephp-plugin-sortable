<?php
declare(strict_types=1);

return [
    'articles' => [
        'columns' => [
            'id' => ['type' => 'integer', 'null' => false],
            'company_id' => ['type' => 'integer', 'null' => false],
            'author_id' => ['type' => 'integer', 'null' => false],
            'title' => ['type' => 'string', 'length' => 255, 'null' => true],
            'position' => ['type' => 'integer', 'default' => 0, 'null' => false],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'sections' => [
        'columns' => [
            'id' => ['type' => 'integer', 'null' => false],
            'name' => ['type' => 'string', 'length' => 255, 'null' => true],
            'display_order' => ['type' => 'integer', 'default' => 0, 'null' => false],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
];
