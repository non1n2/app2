<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;

class AuthController extends Controller
{
    public function login(UserLoginRequest $request)
    {
        $data = $request->validated();
    
        $user = User::where('email', $data['email'])->first();
        if (!Hash::check($data['password'], $user->password)) {
            return $this->response(message: 'The provided credentials are incorrect.', code: 401);
        }
        $user->token = $user->createToken('barrer_token')->plainTextToken;
        return $this->response(UserResource::make($user));
    }
    public function register(UserRegisterRequest $request)
    {
        $data = $request->validated();
        // return $data;
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'userable_type' => Customer::class,
            'userable_id' => Customer::factory(1)->create()->first()->id,
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(10),
        ]);

        $user->token = $user->createToken('barrer_token')->plainTextToken;

        return $this->response(UserResource::make($user));
    }

 
}
