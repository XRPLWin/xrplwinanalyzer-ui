<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
    public function front()
    {
      return view('frontpage');
    }

    public function account_index(string $account)
    {
      return view('account.index', compact('account'));
    }

    public function account_assets(string $account)
    {
      return view('account.assets', compact('account'));
    }


}
