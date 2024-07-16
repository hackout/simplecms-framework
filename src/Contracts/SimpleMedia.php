<?php
namespace SimpleCMS\Framework\Contracts;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @method void prepareToAttachMedia(Media $media, FileAdder $fileAdder)
 *
 * @property bool $registerMediaConversionsUsingModelInstance
 * @property ?\Spatie\MediaLibrary\MediaCollections\MediaCollection $mediaCollections
 * 
 * @use \Illuminate\Database\Eloquent\Model
 * @abstract \Illuminate\Database\Eloquent\Model
 */
interface SimpleMedia
{
    public function media(): MorphMany;

    public function addMedia(string|UploadedFile $file): FileAdder;

    public function copyMedia(string|UploadedFile $file): FileAdder;

    public function hasMedia(string $collectionName = ''): bool;

    public function getMedia(string $collectionName = 'default', array|callable $filters = []): Collection;

    public function clearMediaCollection(string $collectionName = 'default'): HasMedia;

    public function clearMediaCollectionExcept(string $collectionName = 'default', array|Collection $excludedMedia = []): HasMedia;

    public function shouldDeletePreservingMedia(): bool;

    public function loadMedia(string $collectionName);

    public function addMediaConversion(string $name): Conversion;

    public function registerMediaConversions(?Media $media = null): void;

    public function registerMediaCollections(): void;

    public function registerAllMediaConversions(): void;

    public function getMediaModel(): string;

    public function getHasOneMedia(): array;
}
