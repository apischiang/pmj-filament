<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Customer extends Model
{
    // Custom timestamp column names
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'edited_at';

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
        'name',
        'company_name',
        'address',
        'npwp',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            // Generate UID if not present
            if (empty($customer->uid)) {
                $customer->uid = Str::random(20);
            }
        });

        // Removed static::saving validation to avoid conflict with Filament's validation
        // Filament already handles validation based on the form schema.
    }

    /**
     * Get the transactions for the customer.
     * Assuming a Transaction model exists or will exist.
     */
    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }
}