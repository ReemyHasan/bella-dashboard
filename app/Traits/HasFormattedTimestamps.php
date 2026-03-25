<?php

namespace App\Traits;

trait HasFormattedTimestamps
{
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at
            ? showDateTime($this->created_at)
            : null;
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at
            ? showDateTime($this->updated_at)
            : null;
    }
}
