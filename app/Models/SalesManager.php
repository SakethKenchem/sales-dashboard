<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesManager extends Model
{
    protected $fillable = ['name'];

    public function salesRecords()
    {
        return $this->hasMany(SalesRecord::class);
    }
}
