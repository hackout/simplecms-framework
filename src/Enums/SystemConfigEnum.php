<?php

namespace SimpleCMS\Framework\Enums;

enum SystemConfigEnum: string
{

    /**
     * 单行文本
     */
    case Input = 'input';

    /**
     * 多行文本
     */
    case Textarea = 'textarea';

    /**
     * 富文本
     */
    case Editor = 'editor';

    /**
     * 文件
     */
    case File = 'file';

    /**
     * 图片
     */
    case Image = 'image';

    /**
     * 单选项
     */
    case Radio = 'radio';

    /**
     * 下拉选项
     */
    case Select = 'select';

    /**
     * 开关
     */
    case Switch = 'switch';

    /**
     * 多选项
     */
    case Checkbox = 'checkbox';

    /**
     * 列表
     */
    case List = 'list';

    public static function fromValue(string $value): self
    {
        return match ($value) {
            'textarea' => self::Textarea,
            'editor' => self::Editor,
            'file' => self::File,
            'image' => self::Image,
            'radio' => self::Radio,
            'select' => self::Select,
            'switch' => self::Switch ,
            'checkbox' => self::Checkbox,
            'list' => self::List ,
            default => self::Input
        };
    }

    public function isBool(): bool
    {
        return match ($this) {
            self::Switch => true,
            default => false
        };
    }

    public function isInt(): bool
    {
        return match ($this) {
            self::Radio => true,
            self::Select => true,
            default => false
        };
    }

    public function isArray(): bool
    {
        return match ($this) {
            self::list => true,
            self::checkbox => true,
            default => false
        };
    }

    public function isFile(): bool
    {
        return match ($this) {
            self::File => true,
            self::Image => true,
            default => false
        };
    }
    public function isString(): bool
    {
        return match ($this) {
            self::Switch => false,
            self::Radio => false,
            self::Select => false,
            self::list => false,
            self::checkbox => false,
            self::File => false,
            self::Image => false,
            default => true
        };
    }
}
