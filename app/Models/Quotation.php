<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quotation extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'uid';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_uid',
        'quotation_number',
        'date',
        'status',
        'total_amount',
        'terms_and_conditions',
        'created_by',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation) {
            // Generate UID if not present
            if (empty($quotation->uid)) {
                $quotation->uid = Str::random(20); // Or Str::uuid() if strictly UUID
            }
        });
    }

    /**
     * Get the customer that owns the quotation.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_uid', 'uid');
    }

    /**
     * Get the items for the quotation.
     */
    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_uid', 'uid');
    }
}
