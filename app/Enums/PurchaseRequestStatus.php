<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum PurchaseRequestStatus: string implements HasLabel, HasColor
{
    case DRAFT = 'draft';
    case WAITING_APPROVAL = 'waiting_approval';
    case IN_REVIEW = 'in_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case NEED_REVISION = 'need_revision';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::WAITING_APPROVAL => 'Waiting Approval',
            self::IN_REVIEW => 'In Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::NEED_REVISION => 'Need Revision',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT => 'secondary',
            self::WAITING_APPROVAL => 'warning',
            self::IN_REVIEW => 'info',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::NEED_REVISION => 'primary',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    /**
     * Helper for Select options
     */
    public static function options(): array
    {
        return [
            self::DRAFT->value => self::DRAFT->getLabel(),
            self::WAITING_APPROVAL->value => self::WAITING_APPROVAL->getLabel(),
            self::IN_REVIEW->value => self::IN_REVIEW->getLabel(),
            self::APPROVED->value => self::APPROVED->getLabel(),
            self::REJECTED->value => self::REJECTED->getLabel(),
            self::NEED_REVISION->value => self::NEED_REVISION->getLabel(),
            self::COMPLETED->value => self::COMPLETED->getLabel(),
            self::CANCELLED->value => self::CANCELLED->getLabel(),
        ];
    }
}
