<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'admins';


    protected $guarded = ['id', 'created_at', 'updated_at'];


    public function userable()
    {
        return $this->morphOne(User::class, 'userable');
    }
}
