<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromptCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'sort_order',
    ];

    public function approvedPrompts(): HasMany
    {
        return $this->hasMany(ApprovedPrompt::class, 'category_id');
    }

    public function activePrompts(): HasMany
    {
        return $this->hasMany(ApprovedPrompt::class, 'category_id')->where('is_active', true);
    }
}
