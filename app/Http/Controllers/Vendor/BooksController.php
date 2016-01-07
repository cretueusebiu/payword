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

        foreach ($request->commits as $commit) {
            $commit = new Commit($commit);

            if (! $commit->verify()) {
                return response()->json('Invalid commit signature.', 422);
            }

            $commits[] = $commit;
        }

        // $success = \App\Payword\Broker::blockMoney($commit->getCertificate());

        $client = new HttpClient(['base_uri' => 'http://broker.payword.app/api/']);

        $response = $client->request('POST', 'block_money', [
            'form_params' => [
                'certificate' => $commit->getCertificate()->toString()
            ]
        ]);

        $success = json_decode((string) $response->getBody());

        if (! $success) {
            return response()->json('You don\'t enough money in your account.', 422);
        }

        $firstPage = $book->pages()->first();

        foreach ($commits as $commit) {
            CommitModel::create([
                'commit' => $commit->toString(),
                'last_payword' => $commit->getFirstPayword(),
                'user_identity' => $commit->getCertificate()->getUserIdentity(),
                'page_id' => $firstPage->id,
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
        $commits = CommitModel::findByUserIdentity($userIdentity);

        $paywordVerified = false;

        foreach ($commits as $model) {
            $commit = new Commit($model->commit);

            // h(cn) = cn-1
            if (sha1($payword) === $model->last_payword) {
                $paywordVerified = true;
                break;
            }
        }

        if (! $paywordVerified) {
            return response()->json('Invalid payword.', 422);
        }

        $pageId = $commits->first()->page_id;

        if (! $currentPage = $book->page($pageId)) {
            return response()->json('Page not found.', 404);
        }

        if (! $nextPage = $book->nextPage($pageId)) {
            return response()->json('There are no more pages.', 404);
        }

        foreach ($commits as $model) {
            $model->last_payword = $payword;
            $model->page_id = $nextPage->id;
            $model->save();
        }

        return $currentPage;
    }
}
