<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommandesItems;
use App\Models\Items;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CommandeItemController extends Controller
{
    function getCommandeItems($idCommande)
    {
        $result1 = CommandesItems::where('id_commande','=', $idCommande)->get();
        $listItems = [];
        $listQuantity = [];
        $listQuantityItemsIds = [];
        $test = 0;
        $query = ' SELECT * FROM items WHERE id IN ( ';
        if(count($result1) > 0)
        {
            foreach($result1 as $iteminfo)
            {
                $listQuantity[] = $iteminfo->quantity;
                $listQuantityItemsIds[] = $iteminfo->id_item;
                if($test == 0)
                {
                    $test = 1;
                }
                else
                {
                    if($test == 1)
                    {
                        $query .= ' , ';
                    }
                }
                $query .= $iteminfo->id_item;
            }
            $query .= " ) " ;
            $result2 = DB::select(DB::raw($query));
            $index1 = 0;
            $index = 0;
            foreach($result2 as $item)
            {
                $index = 0;
                $listItems[] = $item;
                foreach($listQuantity as $itemQuantity)
                {
                    if($listQuantityItemsIds[$index] == $item->id)
                    {
                        $listItems[$index1]->quantity = $itemQuantity;
                        break;
                    }
                    $index ++;
                }
                $index1 ++;
            }
        }
        return response()->json($listItems);
    }
}
