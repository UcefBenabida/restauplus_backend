<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Commande;
use App\Models\CommandesItems;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

date_default_timezone_set('Africa/Casablanca');


class CommandeController extends Controller
{
    function getCommande($id)
    {
         return response()->json(Commande::find($id));
    }

    function createCommande(Request $request)
    {
    
      
        $validator = Validator::make($request->all(),[
            'id_server' => 'required',
            'items' => 'required',
            'table' => 'required',
            'total' => 'required',
        ]);
        $data = $request->all();
        $data['order_time'] = substr(Carbon::now()->toDateTimeString(), 11)  ;
        $data['date'] = substr(Carbon::now()->toDateTimeString(), 0, 10) ;

        if($validator->fails()){
            return 'Commande creation failed.';
        }
        else
        {
            $id_commande = Commande::create($data);
            foreach(json_decode($request->items) as $item)
            {
                CommandesItems::create(['id_item' => $item->id_item, 'quantity' => $item->quantity, 'id_commande' => $id_commande->id]);
            }
            return 'Commande created successfully.';
        }

       
    }


    function deleteWaitingCommande(Request $request)
    {
        
        $commande = Commande::find($request->id);
        if($commande->state == 'en attente')
        {
            $res = $commande->delete();
            if($res)
            {
                return 'Commande deleted successfully.';
            }
            else
            {
                return 'delete fail';
            }
        }
        else
        {
            return 'you can\'t delete this commande';
        }
       
    }

    
    function setCommandeOnPreparing(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_cook' => 'required',
            'id' => 'required',
        ]);
        if($validator->fails()){
            return 'updating Commande state failed.';
        }
        else
        {
            $commande = Commande::find($request->id);
            if($commande->state == 'en attente')
            {
                $commande->state = 'en préparation';
                $commande->id_cook = $request->id_cook;
                $commande->save();
                return 'Commande state updated successfully.';
            }
            else
            {
                return 'The commande is always on preparing.';

            }

        }
    }

    function setCommandePrepared(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
        ]);
        if($validator->fails()){
            return 'updating Commande state failed.';
        }
        else
        {
            $commande = Commande::find($request->id);
            if($commande->state == "en préparation")
            {
                $commande->state = "préparée";
                $commande->prepared_time = date("H:m:s" ,time());
                $commande->save();
                return 'Commande state updated successfully.';
            }
            else
            {
                return 'The commande is always prepared.';
            }

        }
    }

    function setCommandeDelivered(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
        ]);


        if($validator->fails()){
            return 'updating Commande state failed.';
        }
        else
        {
            $commande = Commande::find($request->id);
            if($commande->state == "préparée")
            {
                date_default_timezone_set('Africa/Casablanca');
                $commande->state = "livrée";
                $commande->delivered_time = date("H:m:s" ,time());
                $commande->save();
                return 'Commande state updated successfully.';
            }
            else
            {
                return 'The commande is always delivered.';
            }

        }
    }

    function setCommandePayed(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
        ]);
        if($validator->fails()){
            return 'updating Commande state failed.';
        }
        else
        {
            $commande = Commande::find($request->id);
            if($commande->state == "livrée")
            {
                $commande->state = "payée";
                $commande->payed_time = date("H:m:s" ,time());
                $commande->save();
                return 'Commande seted payed successfully.';
            }
            else
            {
                return 'The commande is always payed.';
            }
        }
    }

    function updateCommandeState(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
            'state' => 'required',
        ]);
        if($validator->fails()){
            return 'Commande updating state failed.';
        }
        else
        {
            $commnade = Commande::find($request->id);
            if($commande->state != $request->state)
            {
                $commande->state = $request->state ;
                $commande->save();
                return 'Commande state updated successfully.';
            }
            else
            {
                return 'You have sent the seam state.';
            }

        }
    }


               
    function getNotPreparedYetCommandes(Request $request)
    {
        $query = 'SELECT * FROM commandes WHERE ';
        $test = 0;
        $commandes_ids = json_decode($request->commandes_ids);
        if(count($commandes_ids) > 0)
        {
            $changed_state_commandes_query = '';
            $new_waiting_or_on_preparing_commandes_query = '';
            $out_of_waiting_or_on_preparing_commandes_query = '';
            foreach($commandes_ids as $item_ids)
            {
                if($test == 0)
                {
                    $new_waiting_or_on_preparing_commandes_query .= ' (id NOT IN (';
                    $out_of_waiting_or_on_preparing_commandes_query .= ' or (id IN (';
                    $test = 1;
                }
                else
                {
                    $out_of_waiting_or_on_preparing_commandes_query .= ' ,' ;
                    $new_waiting_or_on_preparing_commandes_query .= ' ,';
                }
                $new_waiting_or_on_preparing_commandes_query .= $item_ids->id;
                $out_of_waiting_or_on_preparing_commandes_query .= $item_ids->id;
                $changed_state_commandes_query .= ' or ( ( id = ' . $item_ids->id . ' ) and ( state != "' . $item_ids->state . '" ) and ( id_server = ' . $request->id_server . ' ) ) ';
            }
            $new_waiting_or_on_preparing_commandes_query .= ' )  and  ( state = "en préparation" or state = "en attente"  ) and ( id_server = ' . $request->id_server . ' ) )  ';
            $out_of_waiting_or_on_preparing_commandes_query .= ' ) and  ( state != "en préparation" and state != "en attente") and ( id_server = ' . $request->id_server . ' ) ) ';
            $query .= $new_waiting_or_on_preparing_commandes_query ;
            $query .= $changed_state_commandes_query ;
            $query .= $out_of_waiting_or_on_preparing_commandes_query ;
            $query .= '  ';
            $result = DB::select( DB::raw($query) );
            return response()->json($result);
        }
        else
        {
            return response()->json(DB::select(DB::raw(' SELECT * FROM commandes WHERE id_server = '.$request->id_server.' AND ( state = "en attente" OR state = "en préparation" ) ORDER BY order_time ')));
        }
    }


    function getNotDeliveredYetCommandes(Request $request)
    {
        $query = 'SELECT * FROM commandes WHERE ( id_server = ' . $request->id_server . ' ) AND ( ( id IN ';
        $test = 0;
        $commandes_ids = json_decode($request->commandes_ids);
        if(count($commandes_ids) > 0)
        {
            $ids = ' ( ' ;
            foreach($commandes_ids as $item_ids)
            {
                if($test == 0)
                {
                    $test = 1;
                }
                else
                {
                    $ids .= ' , ' ;
                }
                $ids .= $item_ids->id;
            }
            $ids .= ' ) ' ;
            $query .= $ids ;
            $query .= ' AND  ( state != "préparée" ) )';
            $query .= ' OR ( id NOT IN '.$ids.' AND  ( state = "préparée" ) ) ) ';
            $result = DB::select( DB::raw($query) );
            return response()->json($result);
        }
        else
        {
            return response()->json(DB::select(DB::raw(' SELECT * FROM commandes WHERE id_server = '.$request->id_server.' AND state = "préparée" ORDER BY order_time ')));
        }
        
    }

    function getWaitingPreparingCommandes(Request $request)
    {
        $query = 'SELECT * FROM commandes WHERE ';
        $test = 0;
        $commandes_ids = json_decode($request->commandes_ids);
        if(count($commandes_ids) > 0)
        {
            $subquery = '';
            foreach($commandes_ids as $item_ids)
            {
                if($test == 0)
                {
                    $query .= ' ( ( id NOT IN (';
                    $subquery .= ' or ( ( id IN (';
                    $test = 1;
                }
                else
                {
                    $subquery .= ' ,' ;
                    $query .= ' ,';
                }
                $query .= $item_ids->id;
                $subquery .= $item_ids->id;
            }
            $query .= ' ) ) and  state = "en attente"  ) ';
            $subquery .= ' ) ) and state != "en attente" ) ';
            $query .= $subquery ;
            $result = DB::select( DB::raw($query) );
            return response()->json($result);
        }
        else
        {
            return response()->json(Commande::where('state', '=', "en attente")->orderBy('order_time')->get());
        }
    }

    function getOnPreparingCommandes(Request $request)
    {
        $query = 'SELECT * FROM commandes WHERE ';
        $test = 0;
        $subquery = '';
        $commandes_ids = json_decode($request->commandes_ids);
        if(count($commandes_ids) > 0)
        {
            $query .= ' ( ( id NOT IN ( ';
            $subquery .= ' or ( ( id IN ( ';
            foreach($commandes_ids as $item_ids)
            {
                if($test == 0)
                {
                    $test = 1;
                }
                else
                {
                    $query .= ' ,';
                    $subquery .= ' ,';
                }
                $query .= $item_ids->id;
                $subquery .= $item_ids->id;
            }
            $subquery .= ' ) ) and ( state != "en préparation" ) and  ( id_cook = ' . $request->id_cook . ' )  ) ';
            $query .= ' ) ) and  ( state = "en préparation"  ) and  ( id_cook = ' . $request->id_cook . ' )  ) ';
            $query .= $subquery ;
            $result = DB::select( DB::raw($query) );
            return response()->json($result);
        }
        else
        {
            return response()->json(DB::table('commandes')->where('state', "en préparation")->where('id_cook', $request->id_cook)->orderBy('prepared_time')->get());
        }
    }

    function getNotPayedYetCommandes($id_server)
    {
        $result = Commande::where('state' , "=", "livrée")->where('id_server' , "=", $id_server)->get();
        return response()->json($result);
    }


}
