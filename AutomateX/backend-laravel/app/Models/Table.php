<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Table extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function garment()
    {
        return $this->belongsTo(Garment::class);
    }

    public function parts()
    {
        return $this->hasManyThrough(Part::class, PartTable::class);
    }

}
