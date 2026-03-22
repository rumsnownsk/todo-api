<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;
    public $timestamps = false;

    // Касты для преобразования при работе с моделью
    protected $casts = [
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];

    protected $fillable = ['title', 'description', 'status_id', 'user_id'];

    /**
     * Устанавливаем Unix‑timestamp перед сохранением
     */
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

}
