<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\People;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function index()
    {
      $people = People::orderBy('created_at', 'DESC')->get();
      return $people;
    }
}
