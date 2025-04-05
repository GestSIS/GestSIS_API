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

    public function sisParams()
    {
        return response()->json(['data' => $this->service->sisParams()]);
    }

    public function sisLocalites()
    {
        return response()->json(['data' => $this->service->sisLocalites()]);
    }
}
