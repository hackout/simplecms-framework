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
        foreach ($collection as $key => $model) {
            $model->setRelation('children', new Collection);
        }

        $nestedKeys = [];
        foreach ($collection as $key => $model) {
            if (!$parentKey = $model->getParentId()) {
                continue;
            }

            if (array_key_exists($parentKey, $collection)) {
                $collection[$parentKey]->children[] = $model;
                $nestedKeys[] = $model->getKey();
            }
            elseif ($removeOrphans) {
                $nestedKeys[] = $model->getKey();
            }
        }

        foreach ($nestedKeys as $key) {
            unset($collection[$key]);
        }

        return new Collection($collection);
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
        $buildCollection = function ($items, $depth = 0) use (&$buildCollection, $value, $key, $indent) {
            $result = [];

            $indentString = str_repeat($indent, $depth);

            foreach ($items as $item) {
                if ($key !== null) {
                    $result[$item->{$key}] = $indentString . $item->{$value};
                }
                else {
                    $result[] = $indentString . $item->{$value};
                }

                $childItems = $item->getChildren();
                if ($childItems->count() > 0) {
                    $result = $result + $buildCollection($childItems, $depth + 1);
                }
            }

            return $result;
        };

        $rootItems = $this->toNested();
        return $buildCollection($rootItems);
    }
}