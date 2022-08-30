<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class CategoriesController extends Controller
{

    function getCategory(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
        ]);
        if($validator->fails()){
            return 'error in post body';
        }
        else
        {
            return response()->json(Categories::find($request->id));
        }
    }

    function createCategory(Request $request)
    {
      

        $validator = Validator::make($request->all(),[
            'name' => ['required','unique:categories'],
        ]);

        if($validator->fails()){
            $messages = $validator->messages();
            if($messages->has('name') && $messages->first('name') == "The name has already been taken.")
            {
                return "The name has already been taken.";
            }
            else
            {
                
                return response()->json($messages);       
                  
            }
           
        }
        else
        {
            Categories::create($request->all());

            return 'Category created successfully.';
        }

       
    }

    function updateCategory(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
            'name' => 'unique:categories',
        ]);

        if($validator->fails()){
            $messages = $validator->messages();
        }

        $category = Categories::find($request->id);

        if($category->name != $request->name)
        {
            if($validator->fails() && $messages->has('name') && $messages->first('name') == "The name has already been taken.")
            {
                return "The name has already been taken.";
            }
            else
            {
                $category->name = $request->name;
            }
        }
        
        $category->save();

        return 'Category updated successfully.';
        
    }


    function deleteCategory(Request $request)
    {
        $category = Categories::find($request->id);
        $res = $category->delete();
        if($res)
        {
            DB::table('items')->where('id_category', '=', $request->id)->delete();
            return 'Category deleted successfully.';
        }
        else
        {
            return 'delete fail';
        }
    }


    function getAllCategories()
    {
        return response()->json(Categories::all());
    }
}
