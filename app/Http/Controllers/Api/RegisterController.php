<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\RegisterResponse;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    /**
     * Handle a registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validated = $this->validateRegistrationData($request);

            $user = $this->createUser($validated);

            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('User registered successfully', ['user_id' => $user->id]);

            return $this->successResponse($user, $token);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Registration validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate registration data
     *
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateRegistrationData(Request $request)
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'home_address' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:50',
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|it|org|net|edu|gov)$/'
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'specialization_id' => ['required', 'exists:specializations,id'],
        ]);
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     * @throws \Exception
     */
    private function createUser(array $data)
    {
        Log::info('Attempting to create user', array_diff_key($data, ['password' => '']));

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'home_address' => $data['home_address'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!$user) {
            throw new \Exception('Failed to create user');
        }

        $user->specializations()->attach($data['specialization_id']);

        $user->load('specializations');

        return $user;
    }

    /**
     * Return success response
     *
     * @param User $user
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    private function successResponse(User $user, string $token)
    {
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }
}
