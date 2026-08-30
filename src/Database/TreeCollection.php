<?php
namespace SimpleCMS\Framework\Database;

use Illuminate\Database\Eloquent\Collection;

class TreeCollection extends Collection
{
    /**
     * toNested converts a flat collection of nested set models to an set where
     * children is eager loaded. removeOrphans removes nodes that exist without
     * their parents.
     * @param bool $removeOrphans
     * @return Collection
     */
    public function toNested($removeOrphans = true)
    {
        $collection = $this->getDictionary();
        $nestedKeys = $this->initializeChildren($collection);

        $nestedKeys = $this->buildParentChildRelations($collection, $nestedKeys, $removeOrphans);

        return $this->removeNestedKeys($collection, $nestedKeys);
    }

    /**
     * Initialize children relation for each model.
     *
     * @param array $collection
     * @return array
     */
    protected function initializeChildren(&$collection)
    {
        foreach ($collection as $model) {
            $model->setRelation('children', new Collection);
        }
        return [];
    }

    /**
     * Build parent-child relations and collect nested keys.
     *
     * @param array $collection
     * @param array $nestedKeys
     * @param bool $removeOrphans
     * @return array
     */
    protected function buildParentChildRelations(&$collection, $nestedKeys, $removeOrphans)
    {
        foreach ($collection as $model) {
            $parentKey = $model->getParentId();
            if (empty($parentKey)) {
                continue;
            }

            if (array_key_exists($parentKey, $collection)) {
                $collection[$parentKey]->children[] = $model;
                $nestedKeys[] = $model->getKey();
            } elseif ($removeOrphans) {
                $nestedKeys[] = $model->getKey();
            }
        }
        return $nestedKeys;
    }

    /**
     * Remove nested keys from the collection.
     *
     * @param array $collection
     * @param array $nestedKeys
     * @return Collection
     */
    protected function removeNestedKeys(&$collection, $nestedKeys)
    {
        foreach ($nestedKeys as $key) {
            unset($collection[$key]);
        }

        return new Collection(/** @scrutinizer ignore-type */ $collection);
    }

    /**
     * listsNested gets an array with values of a given column. Values are indented according
     * to their depth.
     * @param  string $value  Array values
     * @param  string $key    Array keys
     * @param  string $indent Character to indent depth
     * @return array
     */
    public function listsNested($value, $key = null, $indent = '&nbsp;&nbsp;&nbsp;')
    {
        $rootItems = $this->toNested();
        return $this->buildNestedList($rootItems, $value, $key, $indent);
    }

    /**
     * Recursively build the nested list.
     *
     * @param Collection $items
     * @param string $value
     * @param string|null $key
     * @param string $indent
     * @param int $depth
     * @return array
     */
    protected function buildNestedList($items, $value, $key, $indent, $depth = 0)
    {
        $result = [];
        $indentString = str_repeat($indent, $depth);

        foreach ($items as $item) {
            $result = $this->addItemToList($result, $item, $value, $key, $indentString);

            $childItems = $item->getChildren();
            if ($childItems->count() > 0) {
                $result = $result + $this->buildNestedList($childItems, $value, $key, $indent, $depth + 1);
            }
        }

        return $result;
    }

    /**
     * Add an item to the nested list.
     *
     * @param array $result
     * @param $item
     * @param string $value
     * @param string|null $key
     * @param string $indentString
     * @return array
     */
    protected function addItemToList($result, $item, $value, $key, $indentString)
    {
        if ($key !== null) {
            $result[$item->{$key}] = $indentString . $item->{$value};
        } else {
            $result[] = $indentString . $item->{$value};
        }

        return $result;
    }
}
