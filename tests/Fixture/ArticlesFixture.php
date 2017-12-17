<?php
namespace Sortable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public $table = 'articles';

    public $fields = [
        'id' => ['type' => 'integer'],
        'company_id' => ['type' => 'integer'],
        'author_id' => ['type' => 'integer'],
        'title' => ['type' => 'string', 'length' => 255, 'null' => true],
        'position' => ['type' => 'integer', 'default' => 0],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    public $records = [];
}
