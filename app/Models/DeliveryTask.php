<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One delivery task: an item, who gets it, where it goes, and which driver carries it.
 * created_at doubles as "sent at"; received_at is filled in when the driver confirms.
 */
class DeliveryTask extends Model
{
    /** The dispatcher sent the task; the driver has not confirmed it yet. */
    public const SENT = 'sent';

    /** The driver confirmed that they received the task. */
    public const RECEIVED = 'received';

    /** status and received_at are set by the app, never by a form, so they are not fillable. */
    protected $fillable = [
        'item_name',
        'item_quantity',
        'item_notes',
        'recipient_name',
        'destination',
        'zone',
        'driver_id',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function isReceived(): bool
    {
        return $this->status === self::RECEIVED;
    }
}
