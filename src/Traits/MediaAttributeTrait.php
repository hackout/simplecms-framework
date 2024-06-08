<?php
namespace SimpleCMS\Framework\Traits;


use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Downloaders\DefaultDownloader;
use Spatie\MediaLibrary\MediaCollections\Events\CollectionHasBeenClearedEvent;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidUrl;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeUpdated;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\Conversions\ImageGenerators;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection as ModelMediaCollection;
use Spatie\MediaLibrary\MediaCollections\FileAdderFactory;

/**
 * 重装InteractsWithMedia
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * 说明:
 * 模块class中引用:
 *
 *   use \SimpleCMS\Framework\Traits\MediaAttributeTrait;
 *
 * @see Spatie\MediaLibrary\InteractsWithMedia
 *
 */
trait MediaAttributeTrait
{

    /** @var Conversion[] */
    public array $mediaConversions = [];

    /** @var MediaCollection[] */
    public array $mediaCollections = [];

    protected bool $deletePreservingMedia = false;

    protected array $unAttachedMediaLibraryItems = [];

    public static function bootInteractsWithMedia()
    {
        static::deleting(function (HasMedia $model) {
            if ($model->shouldDeletePreservingMedia()) {
                return;
            }

            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->media()->cursor()->each(fn(Media $media) => $media->delete());
        });
    }

    public function media(): MorphMany
    {
        return $this->morphMany($this->getMediaModel(), 'model');
    }

    /**
     * Add a file to the media library.
     */
    public function addMedia(string|UploadedFile $file): FileAdder
    {
        return app(FileAdderFactory::class)->create($this, $file);
    }

    public function addMediaFromRequest(string $key): FileAdder
    {
        return app(FileAdderFactory::class)->createFromRequest($this, $key);
    }

    /**
     * Add a file from the given disk.
     */
    public function addMediaFromDisk(string $key, ?string $disk = null): FileAdder
    {
        return app(FileAdderFactory::class)->createFromDisk($this, $key, $disk ?: config('filesystems.default'));
    }


    /**
     * Add multiple files from a request by keys.
     *
     * @param  string[]  $keys
     * @return \Spatie\MediaLibrary\MediaCollections\FileAdder[]
     */
    public function addMultipleMediaFromRequest(array $keys): Collection
    {
        return app(FileAdderFactory::class)->createMultipleFromRequest($this, $keys);
    }

    /**
     * Add all files from a request.
     *
     * @return \Spatie\MediaLibrary\MediaCollections\FileAdder[]
     */
    public function addAllMediaFromRequest(): Collection
    {
        return app(FileAdderFactory::class)->createAllFromRequest($this);
    }

    /**
     * Add a remote file to the media library.
     *
     *
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded
     */
    public function addMediaFromUrl(string $url, array|string ...$allowedMimeTypes): FileAdder
    {
        if (!Str::startsWith($url, ['http://', 'https://'])) {
            throw InvalidUrl::doesNotStartWithProtocol($url);
        }

        $downloader = config('media-library.media_downloader', DefaultDownloader::class);
        $temporaryFile = (new $downloader())->getTempFile($url);
        $this->guardAgainstInvalidMimeType($temporaryFile, $allowedMimeTypes);

        $filename = basename(parse_url($url, PHP_URL_PATH));
        $filename = urldecode($filename);

        if ($filename === '') {
            $filename = 'file';
        }

        if (!Str::contains($filename, '.')) {
            $mediaExtension = explode('/', mime_content_type($temporaryFile));
            $filename = "{$filename}.{$mediaExtension[1]}";
        }

        return app(FileAdderFactory::class)
            ->create($this, $temporaryFile)
            ->usingName(pathinfo($filename, PATHINFO_FILENAME))
            ->usingFileName($filename);
    }

    /**
     * Add a file to the media library that contains the given string.
     *
     * @param string string
     */
    public function addMediaFromString(string $text): FileAdder
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'media-library');

        file_put_contents($tmpFile, $text);

        $file = app(FileAdderFactory::class)
            ->create($this, $tmpFile)
            ->usingFileName('text.txt');

        return $file;
    }

    /**
     * Add a base64 encoded file to the media library.
     *
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded
     * @throws InvalidBase64Data
     */
    public function addMediaFromBase64(string $base64data, array|string ...$allowedMimeTypes): FileAdder
    {
        // strip out data uri scheme information (see RFC 2397)
        if (str_contains($base64data, ';base64')) {
            [$_, $base64data] = explode(';', $base64data);
            [$_, $base64data] = explode(',', $base64data);
        }

        // strict mode filters for non-base64 alphabet characters
        $binaryData = base64_decode($base64data, true);

        if ($binaryData === false) {
            throw InvalidBase64Data::create();
        }

        // decoding and then reEncoding should not change the data
        if (base64_encode($binaryData) !== $base64data) {
            throw InvalidBase64Data::create();
        }

        // temporarily store the decoded data on the filesystem to be able to pass it to the fileAdder
        $tmpFile = tempnam(sys_get_temp_dir(), 'media-library');
        file_put_contents($tmpFile, $binaryData);

        $this->guardAgainstInvalidMimeType($tmpFile, $allowedMimeTypes);

        $file = app(FileAdderFactory::class)->create($this, $tmpFile);

        return $file;
    }

    /**
     * Add a file to the media library from a stream.
     */
    public function addMediaFromStream($stream): FileAdder
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'media-library');

        file_put_contents($tmpFile, $stream);

        $file = app(FileAdderFactory::class)
            ->create($this, $tmpFile)
            ->usingFileName('text.txt');

        return $file;
    }

    /**
     * Copy a file to the media library.
     */
    public function copyMedia(string|UploadedFile $file): FileAdder
    {
        return $this->addMedia($file)->preservingOriginal();
    }

    /*
     * Determine if there is media in the given collection.
     */
    public function hasMedia(string $collectionName = 'default', array $filters = []): bool
    {
        return count($this->getMedia($collectionName, $filters)) ? true : false;
    }

    /**
     * Get media collection by its collectionName.
     */
    public function getMedia(string $collectionName = 'default', array|callable $filters = []): ModelMediaCollection
    {
        return $this->getMediaRepository()
            ->getCollection($this, $collectionName, $filters)
            ->collectionName($collectionName);
    }

    public function getMediaRepository(): MediaRepository
    {
        return app(MediaRepository::class);
    }

    public function getMediaModel(): string
    {
        return config('media-library.media_model');
    }

    public function getFirstMedia(string $collectionName = 'default', $filters = []): ?Media
    {
        $media = $this->getMedia($collectionName, $filters);

        return $media->first();
    }

    /*
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     * If no profile is given, return the source's url.
     */
    public function getFirstMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        $media = $this->getFirstMedia($collectionName);

        if (!$media) {
            return $this->getFallbackMediaUrl($collectionName, $conversionName) ?: '';
        }

        if ($conversionName !== '' && !$media->hasGeneratedConversion($conversionName)) {
            return $media->getUrl();
        }

        return $media->getUrl($conversionName);
    }

    /*
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     *
     * If no profile is given, return the source's url.
     */
    public function getFirstTemporaryUrl(
        DateTimeInterface $expiration,
        string $collectionName = 'default',
        string $conversionName = ''
    ): string {
        $media = $this->getFirstMedia($collectionName);

        if (!$media) {
            return $this->getFallbackMediaUrl($collectionName, $conversionName) ?: '';
        }

        if ($conversionName !== '' && !$media->hasGeneratedConversion($conversionName)) {
            return $media->getTemporaryUrl($expiration);
        }

        return $media->getTemporaryUrl($expiration, $conversionName);
    }

    public function getRegisteredMediaCollections(): Collection
    {
        $this->registerMediaCollections();

        return collect($this->mediaCollections);
    }

    public function getMediaCollection(string $collectionName = 'default'): ?MediaCollection
    {
        $this->registerMediaCollections();

        return collect($this->mediaCollections)
            ->first(fn(MediaCollection $collection) => $collection->name === $collectionName);
    }

    public function getFallbackMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        $fallbackUrls = optional($this->getMediaCollection($collectionName))->fallbackUrls;

        if (in_array($conversionName, ['', 'default'], true)) {
            return $fallbackUrls['default'] ?? '';
        }

        return $fallbackUrls[$conversionName] ?? $fallbackUrls['default'] ?? '';
    }

    public function getFallbackMediaPath(string $collectionName = 'default', string $conversionName = ''): string
    {
        $fallbackPaths = optional($this->getMediaCollection($collectionName))->fallbackPaths;

        if (in_array($conversionName, ['', 'default'], true)) {
            return $fallbackPaths['default'] ?? '';
        }

        return $fallbackPaths[$conversionName] ?? $fallbackPaths['default'] ?? '';
    }

    /*
     * Get the url of the image for the given conversionName
     * for first media for the given collectionName.
     * If no profile is given, return the source's url.
     */
    public function getFirstMediaPath(string $collectionName = 'default', string $conversionName = ''): string
    {
        $media = $this->getFirstMedia($collectionName);

        if (!$media) {
            return $this->getFallbackMediaPath($collectionName, $conversionName) ?: '';
        }

        if ($conversionName !== '' && !$media->hasGeneratedConversion($conversionName)) {
            return $media->getPath();
        }

        return $media->getPath($conversionName);
    }

    /*
     * Update a media collection by deleting and inserting again with new values.
     */
    public function updateMedia(array $newMediaArray, string $collectionName = 'default'): Collection
    {
        $this->removeMediaItemsNotPresentInArray($newMediaArray, $collectionName);

        $mediaClass = $this->getMediaModel();
        $mediaInstance = new $mediaClass();
        $keyName = $mediaInstance->getKeyName();

        return collect($newMediaArray)
            ->map(function (array $newMediaItem) use ($collectionName, $mediaClass, $keyName) {
                static $orderColumn = 1;

                $currentMedia = $mediaClass::findOrFail($newMediaItem[$keyName]);

                if ($currentMedia->collection_name !== $collectionName) {
                    throw MediaCannotBeUpdated::doesNotBelongToCollection($collectionName, $currentMedia);
                }

                if (array_key_exists('name', $newMediaItem)) {
                    $currentMedia->name = $newMediaItem['name'];
                }

                if (array_key_exists('custom_properties', $newMediaItem)) {
                    $currentMedia->custom_properties = $newMediaItem['custom_properties'];
                }

                $currentMedia->order_column = $orderColumn++;

                $currentMedia->save();

                return $currentMedia;
            });
    }

    protected function removeMediaItemsNotPresentInArray(array $newMediaArray, string $collectionName = 'default'): void
    {
        $this
            ->getMedia($collectionName)
            ->reject(
                fn(Media $currentMediaItem) => in_array(
                    $currentMediaItem->getKey(),
                    array_column($newMediaArray, $currentMediaItem->getKeyName()),
                )
            )
            ->each(fn(Media $media) => $media->delete());

        if ($this->mediaIsPreloaded()) {
            unset($this->media);
        }
    }

    public function clearMediaCollection(string $collectionName = 'default'): HasMedia
    {
        $this
            ->getMedia($collectionName)
            ->each(fn(Media $media) => $media->delete());

        event(new CollectionHasBeenClearedEvent($this, $collectionName));

        if ($this->mediaIsPreloaded()) {
            unset($this->media);
        }

        return $this;
    }

    public function clearMediaCollectionExcept(
        string $collectionName = 'default',
        array|Collection|Media $excludedMedia = []
    ): HasMedia {
        if ($excludedMedia instanceof Media) {
            $excludedMedia = collect()->push($excludedMedia);
        }

        $excludedMedia = collect($excludedMedia);

        if ($excludedMedia->isEmpty()) {
            return $this->clearMediaCollection($collectionName);
        }

        $this
            ->getMedia($collectionName)
            ->reject(fn(Media $media) => $excludedMedia->where($media->getKeyName(), $media->getKey())->count())
            ->each(fn(Media $media) => $media->delete());

        if ($this->mediaIsPreloaded()) {
            unset($this->media);
        }

        if ($this->getMedia($collectionName)->isEmpty()) {
            event(new CollectionHasBeenClearedEvent($this, $collectionName));
        }

        return $this;
    }

    /**
     * Delete the associated media with the given id.
     * You may also pass a media object.
     *
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted
     */
    public function deleteMedia(int|string|Media $mediaId): void
    {
        if ($mediaId instanceof Media) {
            $mediaId = $mediaId->getKey();
        }

        $media = $this->media->find($mediaId);

        if (!$media) {
            throw MediaCannotBeDeleted::doesNotBelongToModel($mediaId, $this);
        }

        $media->delete();
    }

    public function addMediaConversion(string $name): Conversion
    {
        $conversion = Conversion::create($name);

        $this->mediaConversions[] = $conversion;

        return $conversion;
    }

    public function addMediaCollection(string $name): MediaCollection
    {
        $mediaCollection = MediaCollection::create($name);

        $this->mediaCollections[$name] = $mediaCollection;

        return $mediaCollection;
    }

    public function deletePreservingMedia(): bool
    {
        $this->deletePreservingMedia = true;

        return $this->delete();
    }

    public function shouldDeletePreservingMedia(): bool
    {
        return $this->deletePreservingMedia ?? false;
    }

    protected function mediaIsPreloaded(): bool
    {
        return $this->relationLoaded('media');
    }

    public function loadMedia(string $collectionName): Collection
    {
        $collection = $this->exists
            ? $this->loadMissing('media')->media
            : collect($this->unAttachedMediaLibraryItems)->pluck('media');

        $collection = new ModelMediaCollection($collection);

        return $collection
            ->filter(fn(Media $mediaItem) => $collectionName !== '*' ? $mediaItem->collection_name === $collectionName : true)
            ->sortBy('order_column')
            ->values();
    }

    public function prepareToAttachMedia(Media $media, FileAdder $fileAdder): void
    {
        $this->unAttachedMediaLibraryItems[] = compact('media', 'fileAdder');
    }

    public function processUnattachedMedia(callable $callable): void
    {
        foreach ($this->unAttachedMediaLibraryItems as $item) {
            $callable($item['media'], $item['fileAdder']);
        }

        $this->unAttachedMediaLibraryItems = [];
    }

    protected function guardAgainstInvalidMimeType(string $file, ...$allowedMimeTypes)
    {
        $allowedMimeTypes = Arr::flatten($allowedMimeTypes);

        if (empty($allowedMimeTypes)) {
            return;
        }

        $validation = Validator::make(
            ['file' => new File($file)],
            ['file' => 'mimetypes:' . implode(',', $allowedMimeTypes)]
        );

        if ($validation->fails()) {
            throw MimeTypeNotAllowed::create($file, $allowedMimeTypes);
        }
    }

    /**
     * 获取既定规格附件
     *
     * 数组格式:
     *       name: 文件名
     *       url:  外部地址
     *       uuid: UUID
     *     poster: 缩率图
     * 
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $collectionName
     * @param  array  $filters
     * @return array<string,string>
     */
    public function getMediaArray(string $collectionName = 'default', array|callable $filters = []): array
    {
        return $this->getMedia($collectionName, $filters)->map(function (Media $item) {
            return [
                'name' => $item->file_name,
                'url' => url($item->getUrl()),
                'uuid' => $item->uuid,
                'poster' => $item->getUrl('shrinkage')
            ];
        })->toArray();
    }

    /**
     * 获取单条既定规格附件
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $collectionName
     * @param  array  $filters
     * @return array
     */
    public function getFirstMediaArray(string $collectionName = 'default', array|callable $filters = []): array
    {
        $medias = $this->getMediaArray($collectionName, $filters);
        if (!$medias)
            return [];
        return head($medias);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (app(ImageGenerators\Image::class)->supportedMimeTypes()->contains($media->mime_type)) {
            $this->addMediaConversion('shrinkage')
                ->width(128)
                ->height(128);
        }
        if (app(ImageGenerators\Video::class)->supportedMimeTypes()->contains($media->mime_type)) {
            $this->addMediaConversion('shrinkage')
                ->width(128)
                ->height(128)
                ->extractVideoFrameAtSecond(1)
                ->performOnCollections('videos');
        }
        if (app(ImageGenerators\Pdf::class)->supportedMimeTypes()->contains($media->mime_type)) {
            $this->addMediaConversion('shrinkage')
                ->width(128)
                ->height(128)
                ->pdfPageNumber(1);
        }
    }

    public function registerMediaCollections(): void
    {
    }

    public function registerAllMediaConversions(?Media $media = null): void
    {
        $this->registerMediaCollections();

        collect($this->mediaCollections)->each(function (MediaCollection $mediaCollection) use ($media) {
            $actualMediaConversions = $this->mediaConversions;

            $this->mediaConversions = [];

            ($mediaCollection->mediaConversionRegistrations)($media);

            $preparedMediaConversions = collect($this->mediaConversions)
                ->each(fn(Conversion $conversion) => $conversion->performOnCollections($mediaCollection->name))
                ->values()
                ->toArray();

            $this->mediaConversions = [...$actualMediaConversions, ...$preparedMediaConversions];
        });

        $this->registerMediaConversions($media);
    }

    /**
     * 获取一对一附件key
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public function getHasOneMedia():array
    {
        if(property_exists($this,'hasOneMedia'))
        {
            return $this->hasOneMedia;
        }
        return [];
    }

    public function __sleep(): array
    {
        // do not serialize properties from the trait
        return collect(parent::__sleep())
            ->reject(
                fn($key) => in_array(
                    $key,
                    [
                        'mediaConversions',
                        'mediaCollections',
                        'unAttachedMediaLibraryItems',
                        'deletePreservingMedia',
                    ]
                )
            )->toArray();
    }
}