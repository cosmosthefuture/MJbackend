<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\CheckOtpRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\RequestOtpRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\VerifyPhoneNumberRequest;
use Illuminate\Http\Request;
use App\Http\Services\User\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tzsk\Otp\Facades\Otp;

class UserController extends ApiController
{
    private $user_service;

    public function __construct(UserService $user_service)
    {
        $this->user_service = $user_service;
    }

    public function verifyPhoneNumber(VerifyPhoneNumberRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $request->validated();

            $phone_number = $validated['phone_number'];

            Otp::forget($phone_number);
            $otp = Otp::generate($phone_number);

            // send otp code to sms later

            return $this->successResponse([], 200, "OTP code sent to your phone number"); // only in dev stage
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function check_otp(CheckOtpRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $request->validated();

            // if (!Otp::check($validated['otp'], $validated['phone_number'])) {
            //     return $this->errorResponse('Invalid OTP.', 400);
            // }

            if ($validated['otp'] !== "000000") {
                return $this->errorResponse('Invalid OTP.', 400);
            }
            return $this->successResponse([], 200, 'OTP verified successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function request_new_otp(RequestOtpRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $request->validated();
            $phone_number = $validated['phone_number'];

            Otp::forget($phone_number);
            $otp = Otp::generate($phone_number);

            // send otp code to sms later

            return $this->successResponse([], 200, "OTP code sent to your phone number"); // only in dev stage
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function register(RegisterRequest $request)
    {
        try {

            // if (!Otp::check($request->otp, $request->phone_number)) {
            //     return $this->errorResponse('Invalid OTP.', 400);
            // }

            // if ($request->otp !== "000000") {
            //     return $this->errorResponse('Invalid OTP.', 400);
            // }

            $validator = Validator::make($request->all(), $request->rules());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated_data = $request->validated();

            $user = $this->user_service->register($validated_data);

            $accessToken = $this->user_service->generateAccessToken($user);
            // $refreshToken = $this->user_service->generateRefreshToken($user);

            if (isset($validated_data['fcm_token'])) {
                $this->user_service->storeFcmToken($user, $validated_data['fcm_token']);
            }

            return $this->successResponse([
                'token_type' => 'Bearer',
                'access_token' => $accessToken,
                // 'refresh_token' => $refreshToken
                'user' => $user
            ], 200, 'Account created.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $request->validated();
            $user = $this->user_service->whereFirst('phone_number', $validated['phone_number']);
            if (!$user) {
                return $this->errorResponse("Phone number does not exist.", 401);
            }
            if (!Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse("Wrong Password. Try again!", 401);
            }

            $accessToken = $this->user_service->generateAccessToken($user);
            // $refreshToken = $this->user_service->generateRefreshToken($admin);

            if (isset($validated['fcm_token'])) {
                $this->user_service->storeFcmToken($user, $validated['fcm_token']);
            }

            return $this->successResponse([
                'token_type' => 'bearer',
                'accessToken' => $accessToken,
                // 'refreshToken' => $refreshToken,
                'user' => $user
            ], 200, 'User account Logged in Successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function logout()
    {
        try {
            $this->user_service->logout();
            return $this->successResponse([], 200, 'Successfully logged out');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getWalletBalance()
    {
        try {
            $user = auth('api-user')->user();
            $balance = $user->balance;
            return $this->successResponse(['wallet-balance' => $balance], 200, "wallet balance");
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $request->validated();
            $user = auth('api-user')->user();

            if (!Hash::check($validated['current_password'], $user->password)) {
                return $this->errorResponse("Current Password Is Wrong. Try again!", 409);
            }

            $this->user_service->changePassword($validated);

            return $this->successResponse([], 200, 'Password Changed Successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $request->validated();
            return $this->errorResponse('Cannot reset password for now!', 500);
            // if (!Otp::check($validated['otp'], $validated['phone_number'])) {
            //     return $this->errorResponse('Invalid OTP.', 400);
            // }

            // if ($validated['otp'] !== "000000") {
            //     return $this->errorResponse('Invalid OTP.', 400);
            // }
            $accessToken = $this->user_service->resetPassword($validated);
            $user = $this->user_service->whereFirst('phone_number', $validated['phone_number']);
            return $this->successResponse([
                'token_type' => 'bearer',
                'accessToken' => $accessToken,
                // 'refreshToken' => $refreshToken,
                'user' => $user
            ], 200, 'reset password successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}