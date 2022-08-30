<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;


date_default_timezone_set('Africa/Casablanca');

class StatisticsController extends Controller
{

    function getNowDate()
    {
        return substr(Carbon::now()->toDateTimeString(), 0, 10) ;
    }

    function getCommnadesDates()
    {
        $result = DB::select(DB::raw(" SELECT DISTINCT date FROM commandes  ORDER BY date DESC LIMIT 30 "));
        return $result;
    }

    function getNumberOfUsers()
    {
        $result = DB::select(DB::raw(" SELECT count(*) AS number, role FROM users GROUP BY role "));
        return $result;
    }

    function getNumberOfCommandes($date)
    {
    
        $result = DB::select(DB::raw(' SELECT state , count(*) AS number,sum(total) AS total FROM commandes WHERE commandes.date = "'. $date .'" GROUP BY state '));
        return $result;
    }

    function getUsersOfTheDay(Request $request)
    {

        $serversdata = json_decode($request->servers) ;
        $cooksdata = json_decode($request->cooks) ;
        $date = $request->date;

        $cooks = (object)[];
        $servers = (object)[];

        $toSendServers = [];
        $toSendCooks = [];

        $test = false ;

        $i = 0;

        if(count($serversdata) > 0)
        { 
            $query = ' SELECT users.id, count(commandes.id) AS number,sum(commandes.total) AS total  FROM users INNER JOIN commandes ON  commandes.id_server = users.id WHERE  commandes.date = "'. $date .'"  GROUP BY users.id ' ;
            $servers = DB::select(DB::raw($query));
            foreach($servers as $server)
            {
                $i =0;
                $toSendServers[$i] = (object)[] ;
                $toSendServers[$i]->id = $server->id ;
                $toSendServers[$i]->number = $server->number ;
                $toSendServers[$i]->total = $server->total ;
                $i++;
            }
        }
        else
        {
            $toSendServers = DB::select(DB::raw(' SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.age, users.address, users.phone, users.role , users.image , users.blocked, count(commandes.id) AS number,sum(commandes.total) AS total  FROM users INNER JOIN commandes ON  commandes.id_server = users.id WHERE commandes.date = "'. $date .'"  GROUP BY users.id, users.first_name, users.last_name, users.username, users.email, users.age, users.address, users.phone, users.role, users.image , users.blocked '));
        }

        if(count($cooksdata) > 0)
        {
            $query = ' SELECT users.id, count(commandes.id) AS number  FROM users INNER JOIN commandes ON  commandes.id_cook = users.id WHERE commandes.date = "'. $date .'" and state != "en attente" GROUP BY users.id  ' ;
            $cooks = DB::select(DB::raw($query));

            foreach($cooks as $cook)
            {
                $i =0;
                $toSendCooks[$i] = (object)[] ;
                $toSendCooks[$i]->id = $cook->id ;
                $toSendCooks[$i]->number = $cook->number ;
                $i++;
            }
        }
        else
        {
            $toSendCooks = DB::select(DB::raw(' SELECT  users.id, users.first_name, users.last_name, users.username, users.email, users.age, users.address, users.phone, users.role , users.image , users.blocked , count(commandes.id) AS number FROM users INNER JOIN commandes ON  commandes.id_cook = users.id  WHERE commandes.date = "'. $date .'" and state != "en attente" GROUP BY users.id, users.first_name, users.last_name, users.username, users.email, users.age, users.address, users.phone, users.role , users.image , users.blocked  '));
        }
        
        $result = (object)[]; 

        $result->servers = $toSendServers ; 
        $result->cooks = $toSendCooks ;
        
        return $result;
    }
}
