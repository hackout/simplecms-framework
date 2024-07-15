<?php
namespace SimpleCMS\Framework\Interfaces;

interface CanBePrecognitiveInterface
{
    /**
     * Filter the given array of rules into an array of rules that are included in precognitive headers.
     *
     * @param  array  $rules
     * @return array
     */
    public function filterPrecognitiveRules($rules);

    /**
     * Determine if the request is attempting to be precognitive.
     *
     * @return bool
     */
    public function isAttemptingPrecognition();

    /**
     * Determine if the request is precognitive.
     *
     * @return bool
     */
    public function isPrecognitive();
}