<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard.auth.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DashboardLoginRequest $request)
    {
        $data = $request->afterValidation();
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return redirect()->route('product.index');
        } else {
            return redirect()->back()->with(['error' => 'The provided credentials are incorrect.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
