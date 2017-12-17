<?php
namespace Sortable\Model\Behavior;

use ArrayObject;
use Cake\Database\Expression\QueryExpression;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;

/**
 * SortableBehavior
 */
class SortableBehavior extends Behavior
{

    /**
     * Default configuration.
     *
     * - field Field name for display order
     * - condition_fields List of field names used for WHERE clause
     * @var array
     */
    protected $_defaultConfig = [
        'field' => 'display_order',
        'condition_fields' => [],
    ];

    /**
     * Set the new entity's display order before it is saved
     *
     * @param \Cake\Event\Event $event The beforeSave event that was fired
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved
     * @param \ArrayObject $options the options passed to the save method
     * @return void
     */
    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        if (!$entity->isNew()) {
            return;
        }

        $table = $this->getTable();
        $conditions = $this->_makeConditions($entity);
        $count = $table->find('all', compact('conditions'))->count();

        $field = $this->getConfig('field');

        $entity->set($field, $count + 1);
    }

    /**
     * Decrease the display order after the entity is deleted
     *
     * @param \Cake\Event\Event $event The afterDelete event that was fired
     * @param \Cake\ORM\Entity $entity The entity that was deleted
     * @param \ArrayObject $options the options passed to the delete method
     * @return void
     */
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        $field = $this->getConfig('field');
        $position = $entity->{$field};

        $expression = new QueryExpression($this->_makeExpression('-'));
        $conditions = $this->_makeConditions($entity);
        $conditions[] = [$field . ' >' => $position];

        $table = $this->getTable();
        $table->updateAll([$expression], $conditions);
    }

    /**
     * Change the display order
     *
     * @param int $id primary key
     * @param int $new_order new display order
     * @return \Cake\ORM\Entity
     */
    public function sort($id, $new_order)
    {
        $table = $this->getTable();
        $entity = $table->get($id);

        $field = $this->getConfig('field');
        $current_order = $entity->{$field};
        $entity->{$field} = $new_order;

        $conn = $table->getConnection();
        $conn->begin();

        if ($new_order > $current_order) {
            $this->_up($new_order, $current_order, $entity);
        } else {
            $this->_down($new_order, $current_order, $entity);
        }
        $table->save($entity);

        $conn->commit();

        return $entity;
    }

    /**
     * Increase the display order
     *
     * @param int $new_order new display order
     * @param int $current_order current display order
     * @param \Cake\ORM\Entity $entity an entity
     * @return void
     */
    protected function _up($new_order, $current_order, $entity)
    {
        $field = $this->getConfig('field');

        $expression = new QueryExpression($this->_makeExpression('-'));
        $conditions = $this->_makeConditions($entity);
        $conditions[] = [
            $field . ' >' => $current_order,
            $field . ' <=' => $new_order,
        ];

        $table = $this->getTable();
        $table->updateAll([$expression], $conditions);
    }

    /**
     * Decrease the display order
     *
     * @param int $new_order new display order
     * @param int $current_order current display order
     * @param \Cake\ORM\Entity $entity an entity
     * @return void
     */
    protected function _down($new_order, $current_order, $entity)
    {
        $field = $this->getConfig('field');

        $expression = new QueryExpression($this->_makeExpression('+'));
        $conditions = $this->_makeConditions($entity);
        $conditions[] = [
            $field . ' >=' => $new_order,
            $field . ' <' => $current_order,
        ];

        $table = $this->getTable();
        $table->updateAll([$expression], $conditions);
    }

    /**
     * Make array for WHERE clause
     *
     * @param \Cake\ORM\Entity $entity an entity
     * @return array
     */
    protected function _makeConditions($entity)
    {
        $conditions = [];
        $fields = $this->getConfig('condition_fields');
        foreach ($fields as $field) {
            $conditions[] = [$field => $entity->{$field}];
        }

        return $conditions;
    }

    /**
     * Make string for QueryExpression
     *
     * @param string $operator (+|-)
     * @return string
     */
    protected function _makeExpression($operator)
    {
        $fmt = '%1$s = %1$s %2$s 1';
        $field = $this->getConfig('field');

        return sprintf($fmt, $field, $operator);
    }
}
