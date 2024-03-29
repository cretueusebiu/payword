<?php

namespace App\Http\Controllers\Vendor;

use App\Models\Book;
use App\Payword\Commit;
use App\Models\Commit as CommitModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client as HttpClient;

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
    public function show(Book $book)
    {
        return $book->load('prices');
    }

    /**
     * @param  \App\Models\Book $book
     * @return \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function verifyCommits(Book $book, Request $request)
    {
        $this->validate($request, ['commits' => 'required|min:1']);

        $commits = [];

        $cbook = null;

        foreach ($request->commits as $commit) {
            $commit = new Commit($commit);

            if (CommitModel::where('serial_number', $commit->getCertificate()->getSerialNumber())->exists()) {
                return response()->json('Commit certificate already used.', 422);
            }

            if (! $commit->verify()) {
                return response()->json('Invalid commit signature.', 422);
            }

            if (! $cbook) {
                $cbook = Book::findOrFail($commit->getBookId());

                if ($commit->getCertificate()->getCreditLimit() != $cbook->getPrice()) {
                    return response()->json('Invalid book id.', 422);
                }

                if (CommitModel::findByUserIdentity($commit->getCertificate()->getUserIdentity(), $book->id)->count()) {
                    return response()->json('Book already open.', 422);
                }
            }


            $commits[] = $commit;
        }

        $success = \App\Payword\Broker::blockMoney($commit->getCertificate());

        // $client = new HttpClient(['base_uri' => 'http://broker.payword.app/api/']);

        // $response = $client->request('POST', 'block_money', [
        //     'form_params' => [
        //         'certificate' => $commit->getCertificate()->toString()
        //     ]
        // ]);

        // $success = json_decode((string) $response->getBody());

        if (! $success) {
            return response()->json('You don\'t enough money in your account.', 422);
        }

        $firstPage = $book->pages()->first();

        foreach ($commits as $commit) {
            CommitModel::create([
                'commit' => $commit->toString(),
                'last_payword' => $commit->getFirstPayword(),
                'user_identity' => $commit->getCertificate()->getUserIdentity(),
                'serial_number' => $commit->getCertificate()->getSerialNumber(),
                'page_id' => $firstPage->id,
                'book_id' => $book->id,
            ]);
        }

        return response()->json(['page_price' => $firstPage->price]);
    }

    /**
     * @param  \App\Models\Book $book
     * @return \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function verifyPayword(Book $book, Request $request)
    {
        $payword = $request->payword; // cn
        $userIdentity = $request->userIdentity;
        $commits = CommitModel::findByUserIdentity($userIdentity, $book->id);

        $pageId = $commits->first()->page_id;
        $nextPage = $book->nextPage($pageId);
        $paywordVerified = false;

        foreach ($commits as $model) {
            $commit = new Commit($model->commit);

            if ($commit->getBookId() != $book->id) {
                return response()->json('Invalid book id.', 422);
            }

            // h(cn) = cn-1
            if (sha1($payword) === $model->last_payword) {
                $paywordVerified = true;
                $model->last_payword = $payword;
                $model->last_payword_pos += 1;
                break;
            }
        }

        if (! $paywordVerified) {
            return response()->json('Invalid payword.', 422);
        }

        if (! $currentPage = $book->page($pageId)) {
            return response()->json('Page not found.', 404);
        }

        foreach ($commits as $model) {
            $model->page_id = $nextPage ? $nextPage->id : null;
            $model->save();
        }

        return ['page' => $currentPage, 'next_page' => $nextPage ? $nextPage->price : null];
    }
}
