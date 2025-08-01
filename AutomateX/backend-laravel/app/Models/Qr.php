<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Qr extends Model
{
    use HasFactory;

    protected $table = 'Qrs';

    protected $guarded = ['id', 'created_at', 'updated_at'];



}
