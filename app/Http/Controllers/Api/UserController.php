<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    /** @var User */
    private $user;
    /** @var ResponseFactory */
    private $responseFactory;

    public function __construct(
        User $user,
        ResponseFactory $responseFactory
    ) {
        $this->user            = $user;
        $this->responseFactory = $responseFactory;
    }

    public function getMe(Request $request)
    {
        return $this->responseFactory->json(
            $this->findMe($request->user()->id),
            200
        );
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:55',
        ]);

        $user       = $this->findMe($request->user()->id);
        $user->name = $request->name;
        $user->save();

        return $this->responseFactory->json(
            $user
            , 200
        );
    }

    public function delete(Request $request)
    {
        $user = $this->findMe($request->user()->id);

        $user->delete();

        return $this->responseFactory->json(null, 200);
    }

    private function findMe(int $id)
    {
        $user = $this->user->find($id);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        return $user;
    }
}
