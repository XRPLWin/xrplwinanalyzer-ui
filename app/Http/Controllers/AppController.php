<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
    public function front()
    {
      return view('frontpage');
    }

    public function search(Request $request)
    {
      $q = $request->input('q');
      if(empty($q))
        return redirect()->route('front');

      return redirect()->route('account.index',['account' => $q]);
    }

    public function account_index(string $account)
    {
      return view('account.index', compact('account'));
    }

    public function account_tokens(string $account)
    {
      return view('account.tokens', compact('account'));
    }


}
