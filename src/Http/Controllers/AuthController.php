<?php


namespace Thomas\Bundle\Http\Controllers;


use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Thomas\Bundle\Models\Admin;
use Thomas\Bundle\Models\ThomasAccessCode;

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
            $validate['email'] = ['required', 'string', 'email', 'max:255', 'unique:users'];
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
                $user_data['token'] = $user->createToken($request->device_name)->plainTextToken;
//                $token_count = $user->tokens()->get()->count();
//                if (config('thomas.register_count') >= $token_count) {
//                    $user_data['token'] = $user->createToken($request->device_name)->plainTextToken;
//                } else {
//                    return response(['errors' => 'Your Account Count Full. Your are already at ' . $token_count . ' Device.'], 422);
//                }
            }
        } else {
            $user = User::create($data);
            $user_data['user'] = $user;

            if ($request->has('create_token')) {
                $user_data['token'] = $user->createToken($request->device_name)->plainTextToken;
            }
        }
//        $token = $user->createToken('token-name');

//        return $token->plainTextToken;

        return response($user_data, 200);

    }


    public function login(Request $request)
    {
        //Validate Array
        $validate = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];

        //Validate the Request
        $validator = Validator::make($request->all(), $validate);

        //Validate Error return
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        //Check for User Type
        $user_data = [];
        if ($request->has('isAdmin')) {
            $user = Admin::where('email', $data['email'])->first();
//            Auth::guard('admin')->login($user);
            $user_data['isAdmin'] = true;
        } else {
            $user = User::where('email', $data['email'])->first();
        };


        if (!$user || !Hash::check($request->password, $user->password)) {
            return response(['errors' => 'The provided credentials are incorrect.'], 422);
        }

        //Set Return Data
        $user_data['user'] = $user;

        //Check if Login Count set in config
        if (config('thomas.login_count') && !$request->has('access_code') && !$request->has('isAdmin')) {
            if ($this->CheckLoginCount($user)) {
                return response(['errors' => 'Sorry! Your Device Limit is over.Please contact the service provider Or Logout account from other devices or browser.'], 422);
            };
        }

        //Check if Login with Access Code
        if ($request->has('access_code')) {
            $code = ThomasAccessCode::where('code', $request->access_code)->first();
            if (!$code || !$code->status) {
                return response(['errors' => 'Access Code is invalid.'], 200);
            }
            $device_count = $code->devices()->count();
            $limit_count = $code->limit;
            if ($limit_count <= $device_count) {
                return response(['errors' => 'Sorry! Your Device Limit is over.Please contact the service provider Or Logout account from other devices or browser.'], 200);

            }

            $user_data['token']=$user->createToken($request->device_name)->plainTextToken;
            $token_id=$user->tokens()->whereName($request->device_name)->first()->id;

            if ($code->user_id === null){
                $code->user_id = $user->id;
                $code->save();
            }

            $code->devices()->create([
                'token'=>$user_data['token'],
                'token_id'=>$token_id,
                'device'=>$request->device_name
            ]);

        }else{
            $user_data['token'] = $user->createToken('ThomasV1')->plainTextToken;
        }

        return response($user_data, 200);

    }

    private function CheckLoginCount($user)
    {
        $set_count = config('thomas.login_count');
        $db_count = $user->tokens()->get()->count();
        if ($set_count <= $db_count) {
            return true;
        }
        return false;
    }

}
