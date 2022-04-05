@extends('layouts.app')
@section('content')
  <form action="/search" aria-label="Search" role="search" method="GET">
    <center>
      <section class="d-flex" style="max-width:400px">
        <input class="form-control" name="q" aria-autocomplete="list" aria-label="Search query" autocomplete="off" autocorrect="off" placeholder="Search XRP address..." role="searchbox" spellcheck="false" enterkeyhint="search" type="text" dir="auto" data-focusable="true" value="" aria-expanded="true">
        <button type="submit" class="btn"><i class="fa fa-search" aria-hidden="true"></i></button>
      </section>
    </center>
  </form>
@endsection
