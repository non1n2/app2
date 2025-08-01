<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    protected function response(mixed $data = [], string $message = 'success', int $code = 200)
    {
        return response()->json(['status' => true, 'message' => $message, 'data' => $data], $code);  
    }


    protected function saveImage($image)
    {
        $path = $image->store('images', 'public');
        return Storage::url($path);
    }
}
