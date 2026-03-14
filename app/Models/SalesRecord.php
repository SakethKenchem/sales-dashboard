<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesRecord extends Model
{
    protected $guarded = [];

    public function region() { return $this->belongsTo(Region::class); }
    public function salesManager() { return $this->belongsTo(SalesManager::class); }
    public function vendor() { return $this->belongsTo(Vendor::class); }
}
