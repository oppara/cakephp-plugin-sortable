# CakePHP plugin that mainly for [jQuery UI Sortable](https://jqueryui.com/sortable/)

[![Build Status](https://travis-ci.org/oppara/cakephp-plugin-sortable.svg?branch=master)](https://travis-ci.org/oppara/cakephp-plugin-sortable)
[![codecov](https://codecov.io/gh/oppara/cakephp-plugin-sortable/branch/master/graph/badge.svg)](https://codecov.io/gh/oppara/cakephp-plugin-sortable)

## Requirements

- CakePHP 5.0 or higher
- PHP 8.1 or higher

## Installation

```
composer require oppara/cakephp-plugin-sortable
```

## Enable plugin

Add the plugin to your application's bootstrap:

```php
// src/Application.php
public function bootstrap(): void
{
    parent::bootstrap();
    $this->addPlugin('Sortable');
}
```

## Examples

```sql
CREATE TABLE articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    created DATETIME,
    modified DATETIME
);
```

```php
// src/Model/Table/ArticlesTable.php
namespace App\Model\Table;

use Cake\ORM\Table;

class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('Sortable.Sortable', [
            'condition_fields' => ['user_id']
        ]);
    }

```

```php
//  src/Controller/ArticlesController.php
class ArticlesController extends AppController
{

    public function sort()
    {
        if (!$this->request->is('ajax')) {
            exit;
        }

        $data = json_encode($this->request->getData());
        $this->log(__METHOD__ . ' data:' . $data, LOG_DEBUG);

        $id = $this->request->getData('id');
        $new_order = $this->request->getData('display_order');
        $this->Sections->sort($id, $new_order);

        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['status' => 'OK']));
    }

```

```php
//  src/templates/Articles/index.php

echo $this->Html->css(['sort'], ['block' => true]);
echo $this->Html->script(['jquery-ui.min', 'sort'], ['block' => true]);
?>

<div class="row">
  <div class="col-md-12">
    <table cellpadding="0" cellspacing="0" class="table table-hover table-bordered sortable">
        <thead>
            <tr>
                <th class="col-md-8" scope="col">title</th>
                <th class="col-md-4" scope="col" class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
            <tr data-id="<?= $article->id ?>" data-url="<?= $this->Url->build(['controller' => 'Articles', 'action' => 'sort']) ?>">
                <td class="handle"><?= h($article->title) ?></td>
                <td class="actions">
                    <?= $this->Html->link('View', ['action' => 'view', $article->id], ['class' => 'btn btn-info btn-sm']) ?>
                    <?= $this->Html->link('Edit', ['action' => 'edit', $article->id], ['class' => 'btn btn-info btn-sm']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
  </div>
</div>
```

```javascript
// src/webroot/sort.js
$(function(){

  $('.sortable').sortable({
    items: 'tbody tr:not(.ui-sort-disabled)',
    axis: 'y',
    placeholder: 'ui-state-highlight',
    cursor: 'row-resize',
    handle: '.handle',
    opacity: 0.5,

    start: function(e, ui) {
      var tableWidth = $(this).width(),
        cells = ui.item.children('td'),
        widthForEachCell = tableWidth / cells.length + 'px';

      cells.css('width', widthForEachCell);
    },

    update: function(e, ui) {
      var item = ui.item,
        item_data = item.data();

      var params = {
        id: item_data.id,
        display_order: item.index()
      };

      $.ajax({
        type: 'POST',
        url: item_data.url,
        dataType: 'json',
        data: params,
        cache: false
      }).fail(function() {
        alert ('ERROR!');
      });
    }
  });

});
```
