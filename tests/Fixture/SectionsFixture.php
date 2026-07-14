<?php
namespace Sortable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SectionsFixture extends TestFixture
{
    public string $table = 'sections';

    public array $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'length' => 255, 'null' => true],
        'display_order' => ['type' => 'integer', 'default' => 0],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    public array $records = [];
}
