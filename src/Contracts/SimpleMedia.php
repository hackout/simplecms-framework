<?php
namespace SimpleCMS\Framework\Contracts;

use Spatie\MediaLibrary\HasMedia;

/**
 * @see Spatie\MediaLibrary\HasMedia
 * 
 * @use \Illuminate\Database\Eloquent\Model
 * @abstract \Illuminate\Database\Eloquent\Model
 */
interface SimpleMedia extends HasMedia
{

    public function getHasOneMedia(): array;
}
