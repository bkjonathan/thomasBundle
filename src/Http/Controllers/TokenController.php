<?php


namespace Thomas\Bundle\Http\Controllers;



use App\User;
use Thomas\Bundle\Models\Admin;
use Thomas\Bundle\MyTrait\ThomasHelper;

class TokenController
{
    use ThomasHelper;

    public function index(){
        if (request('isAdmin')){
            $admin =new Admin();
            $user= $this->ThomasApi($admin);
        }else{
            $user =new User();
            $user= $this->ThomasApi($user);
        }

        return response($user,200);
    }

    public function store(){}

    public function show($id){
        $user=request('isAdmin') ? new Admin(): new User();
//        $model_name=request('userType') === 'admin' ? "Thomas\\Bundle\Models\\Admin": 'App\\User';

        $user_data=$user->where('id',$id)->first();
        $data['user']=$user_data;
        $data['tokens']=$user_data->tokens()->select('id','name','last_used_at')->get();
//        $data['tokens_counts']=$data['user']->tokens->count();
        return $data;
    }
    public function update(){}
    public function destroy(){}
}
