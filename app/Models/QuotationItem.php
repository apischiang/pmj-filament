<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuotationItem extends Model
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
        'quotation_uid',
        'product_name',
        'description',
        'quantity',
        'uom',
        'unit_price',
        'discount',
        'total_price',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (QuotationItem $item) {
            // Generate UID if not present
            if (empty($item->uid)) {
                $item->uid = Str::random(20);
            }
        });
        
        static::saving(function (QuotationItem $item) {
            // Calculate total price automatically
            if ($item->quantity && $item->unit_price) {
                $discountAmount = ($item->unit_price * ($item->discount ?? 0)) / 100;
                $priceAfterDiscount = $item->unit_price - $discountAmount;
                $item->total_price = $item->quantity * $priceAfterDiscount;
            }
        });
    }

    /**
     * Get the quotation that owns the item.
     */
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_uid', 'uid');
    }
}
