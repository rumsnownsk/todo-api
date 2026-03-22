<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;
    public $timestamps = false;

    // Касты для преобразования при работе с моделью
    protected $casts = [
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];
    protected $fillable = ['name', 'slug'];

    // Связь с задачами
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'status_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $currentTimestamp = time();
            $model->created_at = $currentTimestamp;
            $model->updated_at = $currentTimestamp;
        });

        static::updating(function ($model) {
            $model->updated_at = time();
        });
    }

}
