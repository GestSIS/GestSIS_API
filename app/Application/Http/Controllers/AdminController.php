<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;
use App\Domaine\API\AdminService;

class AdminController extends Controller
{
    protected $service;

    public function __construct(AdminService $service)
    {
        $this->service = $service;
    }

    public function sisContacts()
    {
        return response()->json(['data' => $this->service->sisContacts()]);
    }
}
