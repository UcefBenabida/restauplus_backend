<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Items;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;



class ItemsController extends Controller
{

    public function getItemsByIds(Request $request)
    {
        $data = $request->all() ;
        $validator = Validator::make($data,[
            'items_ids' => 'required',
        ]);
        if($validator->fails()){
            return "you did not send items ids ";
        }
        $items = json_decode($request->items_ids);
        $itemsids = " ( " ;
        $test = 0 ;
        if(count($items) > 0)
        {
            foreach($items as $item)
            {
                if($test == 0)
                {
                    $test = 1 ;
                }
                else
                {
                    if($test == 1)
                    {
                        $itemsids .= " , " ;
                    }
                }
                $itemsids .= $item->id ;
            }
        }
        $itemsids .= " ) " ;
        $query = 'SELECT * FROM items WHERE id IN ' . $itemsids ;
        return response()->json(DB::select(DB::raw($query)));
        
    }

      public function showImage($imagename)
    {
        $imagepath = public_path('images') . '/' . $imagename ;
        return response()->file($imagepath);
       

        
    }

    function generateQrCode()
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $uniquementstr = substr(str_shuffle($permitted_chars), 0, 20);
        $data['qrcode'] = $uniquementstr;
        $validator = Validator::make($data,[
            'qrcode' => 'unique:items',
        ]);

        if($validator->fails()){
           
            while($validator->fails())
            {
                $uniquementstr = substr(str_shuffle($permitted_chars), 0, 20);
                $data['qrcode'] =  $uniquementstr;
                $validator = Validator::make($data,[
                    'qrcode' => 'unique:items',
                ]);
            }
            return $uniquementstr;

        }
        else
        {
            return $uniquementstr;
        }


    }

    function validateQrCode(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'qrcode' => 'unique:items',
        ]);

        if($validator->fails())
        {
            return 'false'; 
        }
        else
        {
            return 'true';
        }
    }

    function getItemByQrCode(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'qrcode' => 'required',
        ]);

        if($validator->fails())
        {
            return 'geting faild'; 
        }
        else
        {
            return  Items::where('qrcode',$request->qrcode)->get();
        }
       
    }

    function getItemById(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
        ]);

        if($validator->fails())
        {
            return 'geting faild'; 
        }
        else
        {
            return  Items::find($request->id);
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
            $item = Items::find($request->id);
            $item->image = $imageName;
            $item->save();
            return "image uploaded successfully!";
        }
        else
        {
            return "we can't upload this image";  
        }
    }

    function deleteItem(Request $request)
    {
        $item = Items::find($request->id);
        $res = $item->delete();
        if($res)
        {
            return 'item deleted successfully.';
        }
        else
        {
            return 'delete fail';
        }
    }
    
    function createItem(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_category' => 'required',
            'name' => 'required',
            'price' => 'required',
            'qrcode' => 'required',

        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        Items::create(array_merge($request->all(), ['image' => 'null']));

        return 'Item created successfully.';
    }

    function updateItem(Request $request)
    {

        $data = $request->all();

        $validator = Validator::make($data,[
            'id' => 'required',
            'qrcode' => 'unique:items',
        ]);

        if($validator->fails()){
            $messages = $validator->messages();
        }

        $item = Items::find($request->id);

        if($item->qrcode != $request->qrcode)
        {
            if($validator->fails() && $messages->has('qrcode') && $messages->first('qrcode') == "The qrcode has already been taken.")
            {
                return "The qrcode has already been taken.";
            }
            else
            {
                $item->qrcode = $request->qrcode;
            }
        }

        if($item->id_category != $request->id_category)
        {
            $item->id_category = $request->id_category;
        }
        if($item->name != $request->name)
        {
            $item->name = $request->name;
        }
        if($item->price != $request->price)
        {
            $item->price = $request->price;
        }

        $item->save();

        return 'Item updated successfully.';
        
    }

    function getAllItems()
    {
        return response()->json(Items::all());
    }
}
