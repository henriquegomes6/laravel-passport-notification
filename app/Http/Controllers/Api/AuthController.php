<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyEmail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /** @var User */
    private $user;
    /** @var ResponseFactory */
    private $responseFactory;
    /** @var Carbon */
    private $carbon;

    public function __construct(
        User $user,
        ResponseFactory $responseFactory,
        Carbon $carbon
    ) {
        $this->user            = $user;
        $this->responseFactory = $responseFactory;
        $this->carbon          = $carbon;
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|max:55',
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);
        $data['password']         = bcrypt($request->password);
        $data['activation_token'] = str_random(60);
        $this->user               = $this->user->create($data);

        $this->user->notify(new VerifyEmail($this->user));

        return $this->responseFactory->json(null, 200);
    }

    public function verifyToken(string $token)
    {
        $this->user = $this->user->where('activation_token', $token)->first();
        if (!$this->user) {
            return $this->responseFactory->json([
                'message' => 'invalid token',
            ], 403);
        }

        $this->user->email_verified_at = $this->carbon->now();

        $this->user->save();
        return $this->responseFactory->json(null, 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $auth = Auth::attempt([
            'email'    => $request['email'],
            'password' => $request['password'],
        ]);

        if (!$auth) {
            return $this->responseFactory->json([
                'message' => 'Invalid data',
            ], 401);
        }

        $user = Auth::user();
        if (!$user->email_verified_at) {
            return $this->responseFactory->json([
                'message' => 'check your emaill',
            ], 401);
        }

        $tokenResult = $user->createToken('token');
        $token       = $tokenResult->token;

        $token->expires_at = $this->carbon->now()->addWeeks(1);

        $token->save();

        return $this->responseFactory->json([
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $this->carbon->parse($tokenResult->token->expires_at)->toDateTimeString(),
        ]);
    }
}
