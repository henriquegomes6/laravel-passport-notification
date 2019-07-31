<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;

class UserController extends Controller
{
    /** @var ResponseFactory */
    private $responseFactory;

    public function __construct(
        User $user,
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    public function getMe(Request $request)
    {
        return $this->responseFactory->json($request->user(), 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:55',
        ]);

        $request->user()->name = $request->name;
        $request->user()->save();

        return $this->responseFactory->json(
            $request->user()
            , 200
        );
    }

    public function delete(Request $request)
    {
        $request->user()->delete();

        return $this->responseFactory->json(null, 200);
    }
}
