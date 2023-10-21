<?php

namespace Domain\Products\Enums;

enum Status: string
{
    case AVAILABLE = 'available';
    case UNAVAILABLE = 'unavailable';

    public function title(): string
    {
         return match($this) {
             self::AVAILABLE => __('доступен'),
             self::UNAVAILABLE => __('не доступен'),
         };
    }
}
