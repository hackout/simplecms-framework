<?php
namespace SimpleCMS\Framework\Exceptions;

use Exception;
use Illuminate\Support\Facades\Event;

class SimpleException extends Exception
{
    protected $message = '自定义异常消息';
    protected $code = 500;

    protected $status = 500;

    public function __construct($message = null, $code = 0)
    {
        if (!is_null($message)) {
            $this->message = $message;
        }

        if (!empty($code)) {
            $this->status = $code;
        }

        parent::__construct($this->message, $this->code);
    }

    public function report()
    {
        // 将异常信息记录到日志或其他地方
    }

    public function render($request)
    {
        Event::dispatch('simplecms.backend.request', [$request, false]);
        return response()->json([
            'code' => $this->getCode(),
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}