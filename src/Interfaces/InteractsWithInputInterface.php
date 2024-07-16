<?php
namespace SimpleCMS\Framework\Interfaces;

interface InteractsWithInputInterface
{
    /**
     * Retrieve a server variable from the request.
     *
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    public function server($key = null, $default = null);

    /**
     * Determine if a header is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasHeader($key);

    /**
     * Retrieve a header from the request.
     *
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    public function header($key = null, $default = null);

    /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
     */
    public function bearerToken();

    /**
     * Determine if the request contains a given input item key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($key);

    /**
     * Determine if the request contains a given input item key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($key);

    /**
     * Determine if the request contains any of the given inputs.
     *
     * @param  string|array  $keys
     * @return bool
     */
    public function hasAny($keys);

    /**
     * Apply the callback if the request contains the given input item key.
     *
     * @param  string  $key
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return $this|mixed
     */
    public function whenHas($key, callable $callback, ?callable $default = null);

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function filled($key);

    /**
     * Determine if the request contains an empty value for an input item.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function isNotFilled($key);

    /**
     * Determine if the request contains a non-empty value for any of the given inputs.
     *
     * @param  string|array  $keys
     * @return bool
     */
    public function anyFilled($keys);

    /**
     * Apply the callback if the request contains a non-empty value for the given input item key.
     *
     * @param  string  $key
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return $this|mixed
     */
    public function whenFilled($key, callable $callback, ?callable $default = null);

    /**
     * Determine if the request is missing a given input item key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function missing($key);

    /**
     * Apply the callback if the request is missing the given input item key.
     *
     * @param  string  $key
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return $this|mixed
     */
    public function whenMissing($key, callable $callback, ?callable $default = null);

    /**
     * Determine if the given input key is an empty string for "filled".
     *
     * @param  string  $key
     * @return bool
     */
    protected function isEmptyString($key);

    /**
     * Get the keys for all of the input and files.
     *
     * @return array
     */
    public function keys();

    /**
     * Get all of the input and files for the request.
     *
     * @param  array|mixed|null  $keys
     * @return array
     */
    public function all($keys = null);

    /**
     * Retrieve an input item from the request.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function input($key = null, $default = null);

    /**
     * Retrieve input from the request as a Stringable instance.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return \Illuminate\Support\Stringable
     */
    public function str($key, $default = null);

    /**
     * Retrieve input from the request as a Stringable instance.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return \Illuminate\Support\Stringable
     */
    public function string($key, $default = null);

    /**
     * Retrieve input as a boolean value.
     *
     * Returns true when value is "1", "true", "on", and "yes". Otherwise, returns false.
     *
     * @param  string|null  $key
     * @param  bool  $default
     * @return bool
     */
    public function boolean($key = null, $default = false);

    /**
     * Retrieve input as an integer value.
     *
     * @param  string  $key
     * @param  int  $default
     * @return int
     */
    public function integer($key, $default = 0);

    /**
     * Retrieve input as a float value.
     *
     * @param  string  $key
     * @param  float  $default
     * @return float
     */
    public function float($key, $default = 0.0);

    /**
     * Retrieve input from the request as a Carbon instance.
     *
     * @param  string  $key
     * @param  string|null  $format
     * @param  string|null  $tz
     * @return \Illuminate\Support\Carbon|null
     *
     * @throws \Carbon\Exceptions\InvalidFormatException
     */
    public function date($key, $format = null, $tz = null);

    /**
     * Retrieve input from the request as an enum.
     *
     *
     * @param  string  $key
     * @param  class-string  $enumClass
     * @return mixed
     */
    public function enum($key, $enumClass);

    /**
     * Retrieve input from the request as a collection.
     *
     * @param  array|string|null  $key
     * @return \Illuminate\Support\Collection
     */
    public function collect($key = null);

    /**
     * Get a subset containing the provided keys with values from the input data.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function only($keys);

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function except($keys);

    /**
     * Retrieve a query string item from the request.
     *
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    public function query($key = null, $default = null);

    /**
     * Retrieve a request payload item from the request.
     *
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    public function post($key = null, $default = null);

    /**
     * Determine if a cookie is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasCookie($key);

    /**
     * Retrieve a cookie from the request.
     *
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    public function cookie($key = null, $default = null);

    /**
     * Get an array of all of the files on the request.
     *
     * @return array
     */
    public function allFiles();

    /**
     * Convert the given array of Symfony UploadedFiles to custom Laravel UploadedFiles.
     *
     * @param  array  $files
     * @return array
     */
    protected function convertUploadedFiles(array $files);

    /**
     * Determine if the uploaded data contains a file.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasFile($key);

    /**
     * Check that the given file is a valid file instance.
     *
     * @param  mixed  $file
     * @return bool
     */
    protected function isValidFile($file);

    /**
     * Retrieve a file from the request.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return \Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|array|null
     */
    public function file($key = null, $default = null);
    /**
     * Retrieve a parameter item from a given source.
     *
     * @param  string  $source
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    protected function retrieveItem($source, $key, $default);

    /**
     * Dump the items.
     *
     * @param  mixed  $keys
     * @return $this
     */
    public function dump($keys = []);
}