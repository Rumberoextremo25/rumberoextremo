<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ally extends Model
{
    use HasFactory;

    protected $table = 'allies';

    protected $fillable = [
        'user_id',
        'company_name',
        'company_rif',
        'contact_person_name',
        'contact_email',
        'contact_phone',
        'contact_phone_alt',
        'company_address',
        'website_url',
        'discount',
        'notes',
        'registered_at',
        'status',
        'category_id',
        'sub_category_id',
        'business_type_id',
        'bank_name',
        'account_number',
        'account_type',
        'id_number',
        'account_holder_name',
        'description',
        'image_url',
        // Additional fields for payment system
        'commission_percentage',
        'default_payment_method',
        'bank_account',
        'bank',
        'account_type',
        'id_document',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'discount' => 'string',
        'commission_percentage' => 'decimal:2',
    ];

    // Possible statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    // Bank account types
    const ACCOUNT_CHECKING = 'checking';
    const ACCOUNT_SAVINGS = 'savings';

    // Payment methods
    const PAYMENT_TRANSFER = 'transfer';
    const PAYMENT_MOBILE = 'mobile_payment';
    const PAYMENT_CASH = 'cash';

    // --- Relationship Definitions ---

    /**
     * An ally belongs to a category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * An ally may belong to a subcategory (optional)
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    /**
     * An ally belongs to a specific business type
     */
    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class);
    }

    /**
     * An ally is associated with a system user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with sales
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relationship with payouts
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    // --- Scopes ---

    /**
     * Scope for active allies
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for allies with commission
     */
    public function scopeWithCommission($query)
    {
        return $query->where('commission_percentage', '>', 0);
    }

    /**
     * Scope for allies with valid bank account
     */
    public function scopeWithBankAccount($query)
    {
        return $query->whereNotNull('account_number')
                    ->whereNotNull('bank_name');
    }

    // --- Accessors ---

    /**
     * Get status in readable format
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SUSPENDED => 'Suspended',
            default => 'Unknown'
        };
    }

    /**
     * Get account type in readable format
     */
    public function getAccountTypeTextAttribute(): string
    {
        return match($this->account_type) {
            self::ACCOUNT_CHECKING => 'Checking',
            self::ACCOUNT_SAVINGS => 'Savings',
            default => 'Unknown'
        };
    }

    /**
     * Get payment method in readable format
     */
    public function getPaymentMethodTextAttribute(): string
    {
        return match($this->default_payment_method) {
            self::PAYMENT_TRANSFER => 'Transfer',
            self::PAYMENT_MOBILE => 'Mobile Payment',
            self::PAYMENT_CASH => 'Cash',
            default => 'Not defined'
        };
    }

    /**
     * Get full name for display
     */
    public function getFullNameAttribute(): string
    {
        return $this->company_name . ' - ' . $this->contact_person_name;
    }

    /**
     * Get complete banking information
     */
    public function getBankingInfoAttribute(): string
    {
        return $this->bank_name . ' - ' . $this->account_type . ' - ' . $this->account_number;
    }

    // --- Business Methods ---

    /**
     * Calculate commission amount for a given amount
     */
    public function calculateCommission(float $amount): float
    {
        return ($amount * $this->commission_percentage) / 100;
    }

    /**
     * Check if the ally is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the ally has bank account configured
     */
    public function hasBankAccount(): bool
    {
        return !empty($this->account_number) && !empty($this->bank_name);
    }

    /**
     * Check if the ally has commission configured
     */
    public function hasCommission(): bool
    {
        return $this->commission_percentage > 0;
    }

    /**
     * Get complete RIF (type + number)
     */
    public function getFullRifAttribute(): string
    {
        if (empty($this->company_rif)) {
            return 'Not defined';
        }

        return $this->company_rif;
    }
}