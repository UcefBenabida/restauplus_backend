<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

date_default_timezone_set('Africa/Casablanca');

class UserController extends Controller
{

    function getLastVisit($id)
    {   
        date_default_timezone_set('Africa/Casablanca');

        $user = User::find($id);
        return '{"lastvisit" : "'.$user->lastvisit.'" , "now" : "'.Carbon::now()->toDateTimeString().'"}' ;
    }

    function talkWithServer(Request $request)
    {
        $data = $request->all() ;

        $validator = Validator::make($data,[
            'role' => 'required',
        ]);

        $user = User::find($request->user()->id);
        $time = Carbon::now() ;

        $user->lastvisit = $time->toDateTimeString();
        $user->save();

        if($validator->fails())
        {
            return "you have to send role.";
        }

        if($user->blocked == 1 || $user->role != $data['role'])
        {
            return '{"blocked" : 1}' ;
        }
        else
        {
            return '{"blocked" : 0}' ;
        }
    }


    function createUser(Request $request)
    {
        $currentuser = $request->user();
        if(Gate::allows('isAdmin', $currentuser))
        {
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';

            $data = $request->all() ;

            $validator = Validator::make($data,[
                'first_name' => 'required',
                'last_name' => 'required',
                'role' => 'required',
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
                $data['lastvisit'] = date("Y-m-d h:m:s" ,time());
                User::create($data);

                return 'User created successfully.';
            }
        }
        else
        {
            return 'You are not allowed.';
        }

       
    }

    function deleteUser(Request $request)
    {
        $currentuser = $request->user();
        if(Gate::allows('isAdmin', $currentuser))
        {
            $user = User::find($request->id);
            $res = $user->delete();
            if($res)
            {
                return 'user deleted successfully.';
            }
            else
            {
                return 'delete fail';
            }
        }
        else
        {
            return 'You are not allowed.';
        }
       
    }

    function saveImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required', 
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5000',
        ]);

        if ($validator->passes()) {
            $imageName = "image_at_" . date("Y-m-d h:i:sa", time()).'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $user = User::find($request->id);
            $user->image = $imageName;
            $user->save();
            return "image uploaded successfully!";
        }
        else
        {
            return "we can't upload this image";  
        }
    }

    public function showImage($imagename)
    {
        $imagepath = public_path('images') . '/' . $imagename ;
        return response()->file($imagepath);
        
    }

    public function getImage($imagename){
       
        $imagepath = public_path('images') . '/' . $imagename ;
        $image = Storage::get($imagepath);
        return response($image, 200)->header('Content-Type', Storage::getMimeType($imagepath));
    }

    function blockUser(Request $request)
    {
        $currentuser = $request->user();
        if(Gate::allows('isAdmin', $currentuser))
        {
            $user = User::find($request->id);
            $user->blocked = true;
            $user->save();
            return response()->json('User blocked successfully.');
        }
        else
        {
            return 'You are not allowed.';  
        }

    }

    function unBlockUser(Request $request)
    {
        $currentuser = $request->user();
        if(Gate::allows('isAdmin', $currentuser))
        {
            $user = User::find($request->id);
            $user->blocked = false;
            $user->save();
            return response()->json('User unblocked successfully.');
        }
        else
        {
            return 'You are not allowed.';
        }
        
    }

    function getUser(Request $request)
    {
        return User::find($request->id);
    }

    function changeRole(Request $request)
    {
        $currentuser = $request->user();
        if(Gate::allows('isAdmin', $currentuser))
        {
            $user = User::find($request->id);
            $user->role = $request->role;
            $user->save();
            return 'role changed successfully.';
        }
        else
        {
            return 'You are not allowed.'; 
        }
        
    }

    

    function updateUser(Request $request)
    {

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';

        $data = $request->all();

        $validator = Validator::make($data,[
            'id' => 'required',
            'username' => 'unique:users',
            'email' => 'unique:users',
            'phone' => 'unique:users',
        ]);

        $user = User::find($request->id);

        if($validator->fails()){
            $messages = $validator->messages();
            if($messages->has('email') && $messages->first('email') == "The email has already been taken.")
            {
                if($request->email != null && $user->email != $request->email)
                {
                    return "The email has already been taken.";
                }
            }
            if($messages->has('phone') && $messages->first('phone') == "The phone has already been taken.")
            {
                if($request->phone != null && $user->phone != $request->phone)
                {
                    return "The phone has already been taken.";
                }
            }
        }


        if($request->email != null && $user->email != $request->email)
        {
            $user->email = $request->email;
        }

        if($request->first_name != null && $user->first_name != $request->first_name)
        {
            $user->first_name = $request->first_name;
        }
        if($request->last_name != null && $user->last_name != $request->last_name)
        {
            $user->last_name = $request->last_name;
        }
      
        if($request->username != null && $user->username != $request->username)
        {
            if($validator->fails() && $messages->has('username') && $messages->first('username') == "The username has already been taken.")
            {
                while($messages->has('username') && $messages->first('username') == "The username has already been taken.")
                {
                    $uniquementstr = substr(str_shuffle($permitted_chars), 0, 4);
                    $data['username'] =  $data['username'] . $uniquementstr;
                    $validator = Validator::make($data,[
                        'username' => 'unique:users',
                    ]);
                    $messages = $validator->messages();
                }
                $user->username = $data['username'];
            }
            else
            {
                $user->username = $request->username;
            }
        }
        if($request->age != null && $user->age != $request->age)
        {
            $user->age = $request->age;
        }
        if($request->address != null && $user->address != $request->address)
        {
            $user->address = $request->address;
        }
        if($request->phone != null && $user->phone != $request->phone)
        {
            $user->phone = $request->phone;
        }
        
        if($request->role != null && $user->role != $request->role)
        {
            $currentuser = $request->user();
            if(Gate::allows('isAdmin', $currentuser))
            {
                $user->role = $request->role;
            }
        }
        if($request->password != null && !Hash::check($request->password, $user->password))
        {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return 'User updated successfully.';
        
    }

    function getAllUsers(Request $request)
    {
        $currentuser = $request->user();
        if(Gate::allows('isAdmin', $currentuser))
        {
            return response()->json(User::all());
        }
        
    }
}
