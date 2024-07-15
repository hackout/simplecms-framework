<?php
namespace SimpleCMS\Framework\Http\Requests;

use Illuminate\Http\Request;
use SimpleCMS\Framework\Interfaces\CanBePrecognitiveInterface;
use SimpleCMS\Framework\Interfaces\InteractsWithInputInterface;
use SimpleCMS\Framework\Interfaces\InteractsWithFlashDataInterface;
use SimpleCMS\Framework\Interfaces\InteractsWithContentTypesInterface;

/**
 * Framework based request
 * 
 * @method array validate(array $rules, ...$params)
 * @method array validateWithBag(string $errorBag, array $rules, ...$params)
 * @method bool hasValidSignature(bool $absolute = true)
 **/
class SimpleRequest extends Request implements CanBePrecognitiveInterface, InteractsWithFlashDataInterface, InteractsWithInputInterface, InteractsWithContentTypesInterface
{

}