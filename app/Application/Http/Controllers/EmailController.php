<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;
use App\Domaine\API\EmailService;

class EmailController extends Controller
{
    protected $service;

    public function __construct(EmailService $service)
    {
        $this->service = $service;
    }
    
    public function validateEmail(Request $request)
    {
        // Check for multiple sis
        $email = $request->input('email');
        return response()->json(['data' => $this->service->checkEmail($email)]);
    }
}
