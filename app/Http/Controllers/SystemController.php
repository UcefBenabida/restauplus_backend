<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;



class SystemController extends Controller
{

    private $securityCode = "Nova7i1212" ;

    function securityCodeTest(Request $request)
    {
        $data = $request->all() ;

        $validator = Validator::make($data,[
            'security_code' => 'required',
        ]);

        if($validator->fails())
        {
            return "no security code.";
        }

        if($data['security_code'] == $this->securityCode)
        {
            return "security code is correct" ;
        }
        else
        {
            return "security code is not correct" ;
        }
    }

    function ifTokenIsValid()
    {
        if(auth('sanctum')->check())
        {
            return "token is valid.";
        }
        else
        {
            return "token is not valid.";
        }
    }

    function ifSystemHasAdmin()
    {

        $user = User::where("role", "admin")->get();
        if($user != "[]")
        {
            return "system has admin" ;
        }
        else
        {
            return "system doesn t have admin" ;
        }
    }

    function createAdmin(Request $request)
    {

        if($this->ifSystemHasAdmin() != "system has admin" && isset($request->security_code) && $request->security_code == $this->securityCode)
        {
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';

            $data = $request->all() ;

            $validator = Validator::make($data,[
                'first_name' => 'required',
                'last_name' => 'required',
                'username' => ['required','unique:users'],
                'email' => ['required','unique:users'],
                'phone' => 'unique:users',
                'password' => 'required',

            ]);

            if($validator->fails()){
                $messages = $validator->messages();
                if($messages->has('email') && $messages->first('email') == "The email has already been taken.")
                {
                    return "The email has already been taken.";
                }
                else
                {
                    if($messages->has('phone') && $messages->first('phone') == "The phone has already been taken.")
                    {
                        return "The phone has already been taken.";
                    }
                    if($messages->has('username') && $messages->first('username') == "The username has already been taken.")
                    {
                        while($messages->has('username') && $messages->first('username') == "The username has already been taken.")
                        {
                            $uniquementstr = substr(str_shuffle($permitted_chars), 0, 4);
                            $data['username'] =  $data['username'] . $uniquementstr;
                            $validator = Validator::make($data,[
                                'first_name' => 'required',
                                'last_name' => 'required',
                                'role' => 'required',
                                'username' => ['required','unique:users'],
                                'email' => ['required','unique:users'],
                                'password' => 'required',
                            ]);
                            $messages = $validator->messages();
                        }
                        if(!$validator->fails())
                        {
                            $data['password'] = Hash::make($data['password']);
                            $data['role'] = "admin" ;
                            $data['lastvisit'] = date("Y-m-d h:m:s" ,time());

                            User::create($data);
                            return 'User created successfully.';
                        }
                        else
                        {
                            return response()->json($messages);       
                        }

                    }

                    return response()->json($messages);       

                }
            
            }
            else
            {
                $data['password']  = Hash::make($data['password']);
                $data['role'] = "admin" ;
                $data['lastvisit'] = date("Y-m-d h:m:s" ,time());

                User::create($data);

                return 'User created successfully.';
            }
        }
        else
        {
            return "no you can\'t" ;
        }
        
    }
}
