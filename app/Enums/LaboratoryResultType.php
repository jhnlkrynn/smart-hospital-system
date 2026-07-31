<?php

namespace App\Enums;

enum LaboratoryResultType: string
{
    case Numeric = 'numeric';
    case Text = 'text';
    case Qualitative = 'qualitative';
    case Boolean = 'boolean';
    case Structured = 'structured';
    case AttachmentOnly = 'attachment_only';

    public function label(): string
    {
        return match ($this) {
            self::Numeric => 'Numeric',
            self::Text => 'Text',
            self::Qualitative => 'Qualitative',
            self::Boolean => 'Boolean',
            self::Structured => 'Structured',
            self::AttachmentOnly => 'Attachment only',
        };
    }
}
