<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EnterpriseScopeTrait;

class LaundryService extends Model
{
    use EnterpriseScopeTrait;

    protected $fillable = ['enterprise_id', 'name', 'category', 'description', 'price', 'turnaround_hours', 'status', 'display_order'];
    
    protected $casts = ['price' => 'decimal:2', 'turnaround_hours' => 'integer', 'display_order' => 'integer'];

    public function enterprise() { return $this->belongsTo(Enterprise::class); }
    public function scopeAvailable($query) { return $query->where('status', 'available'); }
    public function getFormattedPriceAttribute() { return number_format($this->price, 0, ',', ' ') . ' FCFA'; }
}
