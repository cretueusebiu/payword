<?php

namespace App\Http\Controllers\Vendor;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BooksController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Book::with('price')->get();
    }

    /**
     * @param  \App\Models\Book $book
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $book = Book::with('prices')->find($id);

        return $book;

        return $book->load('prices');
    }
}
