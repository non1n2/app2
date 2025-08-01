<?php

namespace App\Http\Controllers\API;

use App\Models\Qr;
use Illuminate\Http\Request;
use App\Events\QrEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreQrRequest;
use App\Http\Requests\UpdateQrRequest;

class QrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // if (!auth()->check()) {
        //     // You might want to handle this differently, e.g., throw an exception
        //     // or return an unauthenticated response, depending on your API's security.
        //     return $this->response(message: 'Unauthenticated.', code: 401);
        // }
        if (!Auth::attempt(['email' => 'ahmadghafeer@gmail.com', 'password' => 'password'])) { // Provide PLAIN TEXT password
            return $this->response(message: 'The provided credentials are incorrect.', code: 401);
        }

        $Qrs = Qr::paginate(10);
        

        if (request()->expectsJson()) {
            return $this->response(data: $Qrs);
        }

        return view('index', compact('Qrs'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQrRequest $request)
    {
         if (!Auth::attempt(['email' => 'ahmadghafeer@gmail.com', 'password' => 'password'])) { // Provide PLAIN TEXT password
            return $this->response(message: 'The provided credentials are incorrect.', code: 401);
        }
        $data = $request->validated();
        try {
            $Qr = Qr::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                logger()->info('Authenticated ID: ' . Auth()->id());
                return $this->response(message: 'Qr already exists', code: 409);
            }
            return $this->response(message: 'Failed to store Qr', code: 400);
        }
        
        event(new QrEvent($Qr, Auth()->id()));
        return $this->response(message: 'Qr received successfully!',code: 200);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQrRequest $request,string $value)
    {
        if (!Auth::attempt(['email' => 'ahmadghafeer@gmail.com', 'password' => 'password'])) { // Provide PLAIN TEXT password
            return $this->response(message: 'The provided credentials are incorrect.', code: 401);
        }
        $data = $request->validated();
        $Qr = Qr::where('value', $value)->first();
        logger()->info('Qr found in update is: ' . $Qr);
        
        try {
            $Qr->update($data);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                logger()->info('Authenticated ID: ' . Auth()->id());
                logger()->info('Qr doesnt exsist on update, attempting store');
                return $this->store($data);
            }
            return $this->response(message: 'Failed to update Qr', code: 400);
        }
        $Qr = Qr::where('value', $value)->first();
        event(new QrEvent($Qr, Auth()->id(),$isUpdate = true));
        return $this->response(message: 'Qr updated successfully!',code: 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Qr $Qr)
    {
        //
    }
}
