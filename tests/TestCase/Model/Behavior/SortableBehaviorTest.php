<?php
namespace Sortable\Test\TestCase\Model\Behavior;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Sortable\Model\Behavior\SortableBehavior;

/**
 * Sortable\Model\Behavior\SortableBehavior Test Case
 */
class SortableBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.sortable.articles',
        'plugin.sortable.sections',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->entity = $this->getMockBuilder('Cake\ORM\Entity')->getMock();
        $this->table = $this->getMockBuilder('Cake\ORM\Table')->getMock();
        $this->behavior = new SortableBehavior($this->table, []);

        $this->Sections = TableRegistry::get('Sortable.Sections');
        $this->Sections->addBehavior('Sortable.Sortable');

        $this->Articles = TableRegistry::get('Sortable.Articles');
        $this->Articles->addBehavior('Sortable.Sortable', [
            'field' => 'position',
            'condition_fields' => ['company_id', 'author_id']
        ]);
    }

    public function testDefaultConfig()
    {
        $this->assertSame('display_order', $this->behavior->getConfig('field'));
        $this->assertSame([], $this->behavior->getConfig('condition_fields'));
    }

    public function testSetConfig()
    {
        $config = [
            'field' => 'foo',
            'condition_fields' => [ 'bar', 'baz' ],
        ];
        $behavior = new SortableBehavior($this->table, $config);
        $this->assertSame($config['field'], $behavior->getConfig('field'));
        $this->assertSame($config['condition_fields'], $behavior->getConfig('condition_fields'));
    }

    // @codingStandardsIgnoreStart
    public function testBeforeSaveOnCreate()
    {
        $this->saveSections();

        $query = $this->Sections->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 2, 3, 4, 5, 6];
        $expected_orders = [1, 2, 3, 4, 5, 6];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->display_order, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testBeforeSaveOnUpdate()
    {
        $this->saveSections();

        $entity = $this->Sections->get(2);
        $entity->name = 'foobar';
        $this->Sections->save($entity);

        $query = $this->Sections->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 2, 3, 4, 5, 6];
        $expected_orders = [1, 2, 3, 4, 5, 6];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->display_order, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testAfterDelete()
    {
        $this->saveSections();

        $entity = $this->Sections->get(2);
        $this->Sections->delete($entity);
        $entity = $this->Sections->get(4);
        $this->Sections->delete($entity);

        $query = $this->Sections->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 3, 5, 6];
        $expected_orders = [1, 2, 3, 4];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->display_order, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testUpward()
    {
        $this->saveSections();

        // id = 1 to 3rd
        $this->Sections->sort(1, 3);

        $query = $this->Sections->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 2, 3, 4, 5, 6];
        $expected_orders = [3, 1, 2, 4, 5, 6];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->display_order, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testDownward()
    {
        $this->saveSections();

        // id = 4 to 2nd
        $this->Sections->sort(4, 2);

        $query = $this->Sections->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 2, 3, 4, 5, 6];
        $expected_orders = [1, 3, 4, 2, 5, 6];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->display_order, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testBeforeSaveOnCreateWithCustomConfig()
    {
        $this->saveArticles();

        $query = $this->Articles->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        $expected_orders = [1, 1, 2, 1, 2, 3, 1, 1, 1, 2, 3];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->position, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testBeforeSaveOnUpdateWithCustomConfig()
    {
        $this->saveArticles();

        $entity = $this->Articles->get(3);
        $entity->title = 'foobar';
        $this->Articles->save($entity);

        $query = $this->Articles->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        $expected_orders = [1, 1, 2, 1, 2, 3, 1, 1, 1, 2, 3];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->position, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testBeforeSaveOnUpdateWithDirtyContidionField()
    {
        $this->saveArticles();

        $entity = $this->Articles->get(10);
        $entity->company_id = 2;
        $entity->author_id = 2;
        $this->Articles->save($entity);

        $query = $this->Articles->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;

        // company_id       1, 2, 2, 2, 2, 2, 3, 3, 3, 2, 3
        // author_id        1, 2, 2, 3, 3, 3, 4, 5, 6, 2, 6
        $expected_ids    = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        $expected_orders = [1, 1, 2, 1, 2, 3, 1, 1, 1, 3, 2];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->position, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testAfterDeleteOnCreateWithCustomConfig()
    {
        $this->saveArticles();

        $entity = $this->Articles->get(2);
        $this->Articles->delete($entity);
        $entity = $this->Articles->get(10);
        $this->Articles->delete($entity);

        $query = $this->Articles->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 3, 4, 5, 6, 7, 8, 9, 11];
        $expected_orders = [1, 1, 1, 2, 3, 1, 1, 1, 2];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->position, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testUpwardWithCustomConfig()
    {
        $this->saveArticles();

        // id = 2 to 2nd
        $this->Articles->sort(2, 2);
        // id = 9 to 3rd
        $this->Articles->sort(9, 3);

        $query = $this->Articles->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        $expected_orders = [1, 2, 1, 1, 2, 3, 1, 1, 3, 1, 2];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->position, 'id=' . $row->id);
            $idx++;
        }
    }

    public function testDownwardWithCustomConfig()
    {
        $this->saveArticles();

        // id = 2 to 1st
        $this->Articles->sort(3, 1);
        // id = 6 to 2nd
        $this->Articles->sort(6, 2);
        // id = 11 to 1st
        $this->Articles->sort(11, 1);

        $query = $this->Articles->find('all', ['order' => ['id' => 'ASC']]);
        $idx = 0;
        $expected_ids    = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        $expected_orders = [1, 2, 1, 1, 3, 2, 1, 1, 2, 3, 1];
        foreach ($query as $row) {
            $this->assertSame($expected_ids[$idx], $row->id);
            $this->assertSame($expected_orders[$idx], $row->position, 'id=' . $row->id);
            $idx++;
        }
    }

    // @codingStandardsIgnoreEnd

    protected function saveSections()
    {
        $data = [
            'foo',
            'bar',
            'baz',
            'hoge',
            'huga',
        ];
        foreach ($data as $name) {
            $entity = $this->Sections->newEntity();
            $entity->name = $name;
            $this->Sections->save($entity);
        }
    }

    protected function saveArticles()
    {
        $data = [
            ['id' => 1, 'company_id' => 1, 'author_id' => 1, 'title' => 'foo'],
            ['id' => 2, 'company_id' => 2, 'author_id' => 2, 'title' => 'bar1'],
            ['id' => 3, 'company_id' => 2, 'author_id' => 2, 'title' => 'bar2'],
            ['id' => 4, 'company_id' => 2, 'author_id' => 3, 'title' => 'baz1'],
            ['id' => 5, 'company_id' => 2, 'author_id' => 3, 'title' => 'baz2'],
            ['id' => 6, 'company_id' => 2, 'author_id' => 3, 'title' => 'baz3'],
            ['id' => 7, 'company_id' => 3, 'author_id' => 4, 'title' => 'hoge'],
            ['id' => 8, 'company_id' => 3, 'author_id' => 5, 'title' => 'huga'],
            ['id' => 9, 'company_id' => 3, 'author_id' => 6, 'title' => 'hage1'],
            ['id' => 10, 'company_id' => 3, 'author_id' => 6, 'title' => 'hage2'],
            ['id' => 11, 'company_id' => 3, 'author_id' => 6, 'title' => 'hage3'],
        ];
        foreach ($data as $values) {
            $entity = $this->Articles->newEntity();
            foreach ($values as $name => $val) {
                $entity->{$name} = $val;
            }
            $this->Articles->save($entity);
        }
    }
}
