<?php



namespace Sendportal\Base\Http\Controllers;

use Sendportal\Base\Http\Controllers\Controller;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;



class SpecController extends Controller

{

    /**

     * Write code on Method

     *

     * @return response()

     */

    public function create_special_camp(Request $request)

    {

     	$routes = Route::getRoutes();



        return view('sendportal::demotest.routesList', compact('routes'));

    }

}




