<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Validator;


class RestaurantController extends Controller
{
    function getRestaurant()
    {
        return Restaurant::first();
    }

    function createRestaurant(Request $request)
    {
        $data = $request->all() ;

        $validator = Validator::make($data,[
            'title' => ['required', 'unique:restaurants'],
        ]);

        if($validator->fails())
        {
            return "you have to send unique title.";
        }

        $result = Restaurant::create($data) ;

        return "Restaurant created successfully." ;

    }

    function updateRestaurant(Request $request)
    {
        $restau = Restaurant::first(); 

        if($request->title != $restau->title)
        {
            $restau->title = $request->title ;
        }

        if($request->address != $restau->address)
        {
            $restau->address = $request->address ;
        }

        if($request->phone != $restau->phone)
        {
            $restau->phone = $request->phone ;
        }

        if($request->facebook != $restau->facebook)
        {
            $restau->facebook = $request->facebook ;
        }

        if($request->youtube != $restau->youtube)
        {
            $restau->youtube = $request->youtube ;
        }

        if($request->instagram != $restau->instagram)
        {
            $restau->instagram = $request->instagram ;
        }

        if($request->siteweb != $restau->siteweb)
        {
            $restau->siteweb = $request->siteweb ;
        }
        $restau->save();
        return "Restaurant updated successfully." ;
    }

    function saveImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5000',
        ]);

        if ($validator->passes()) {
            $imageName = "image_at_" . date("Y-m-d h:i:sa", time()).'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $restau = Restaurant::first();
            $restau->image = $imageName;
            $restau->save();
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
}
