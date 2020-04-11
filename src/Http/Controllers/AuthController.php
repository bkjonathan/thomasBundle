<?php


namespace Thomas\Bundle\Http\Controllers;


use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Thomas\Bundle\Models\Admin;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ];

        //Add Validation Rules for User Type
        if ($request->has('isAdmin')) {
            $validate['email'] = ['required', 'string', 'email', 'max:255', 'unique:admins'];
        } else if ($request->has('create_token')) {
            $validate['device_name'] = ['required', 'string'];
        } else {
            $validate['email'] = ['required', 'string', 'email', 'max:255', 'unique:users'];
        };

        //Validate the Request
        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $data['password'] = bcrypt($data['password']);

        //Create User
        if ($request->has('isAdmin')) {
            $user = Admin::create($data);

            $user_data['user'] = $user;
            $user_data['isAdmin'] = true;
            if ($request->has('create_token')) {
                $token_count = $user->tokens()->get()->count();
                if (config('thomas.register_count') >= $token_count) {
                    $user_data['token'] = $user->createToken($request->device_name)->plainTextToken;
                } else {
                    return response(['errors' => 'Your Account Count Full. Your are already at ' . $token_count . ' Device.'], 422);
                }
            }
        } else {
            $user = User::create($data);
            $user_data['user'] = $user;

            if ($request->has('create_token')) {
                $token_count = $user->tokens()->get()->count();
                if (config('thomas.register_count') >= $token_count) {
                    $user_data['token'] = $user->createToken($request->device_name)->plainTextToken;
                } else {
                    return response(['errors' => 'Your Account Count Full. Your are already at ' . $token_count . ' Device.'], 422);
                }
            }
        }
//        $token = $user->createToken('token-name');

//        return $token->plainTextToken;

        return response($user_data, 200);

    }


    public function login(Request $request)
    {
        $validate = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];

        //Validate the Request
        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

//        $data['password'] = bcrypt($data['password']);


        //Check for User Type
        if ($request->has('isAdmin')) {
            $user = Admin::where('email', $data['email'])->first();
            $user_data = [
                'user' => $user,
                'token' => $user->createToken('vueapp')->plainTextToken,
                'isAdmin' => true
            ];
        } else {
            $user = User::where('email', $data['email'])->first();
            if ($user) {
                $user_data = [
                    'user' => $user,
                    'token' => $user->createToken('vueapp')->plainTextToken,
                ];
            }

        };


        if (!$user || !Hash::check($request->password, $user->password)) {
            return response(['errors' => 'The provided credentials are incorrect.'], 422);
        }

        return response($user_data, 200);

    }

}
