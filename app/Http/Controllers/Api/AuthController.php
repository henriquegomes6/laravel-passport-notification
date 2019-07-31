<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyEmail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
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
                'message' => 'token_invalid',
            ], 403);
        }

        $this->user->email_verified_at = now();

        $this->user->save();
        return $this->responseFactory->json(null, 200);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email'       => 'required|email',
            'password'    => 'required',
            'remember_me' => 'boolean',
        ]);

        if (!Auth::attempt($loginData)) {
            return $this->responseFactory->json([
                'message' => 'Invalid data',
            ], 401);
        }

        $user = $request->user();

        $tokenResult = $user->createToken('token');
        $token       = $tokenResult->token;

        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        return $this->responseFactory->json([
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
        ]);
    }
}
