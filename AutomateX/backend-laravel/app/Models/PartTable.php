<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PartTable extends Pivot
{
    protected $table = 'part_table';


    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
