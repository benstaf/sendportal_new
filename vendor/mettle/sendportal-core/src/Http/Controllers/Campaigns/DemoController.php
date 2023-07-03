<?php



namespace Sendportal\Base\Http\Controllers;

use Sendportal\Base\Http\Controllers\Controller;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

  

  

class DemoController extends Controller

{

    /**

     * Write code on Method

     *

     * @return response()

     */

    public function index(Request $request)

    {

        $routes = Route::getRoutes();

  

        return view('routesList', compact('routes'));

    }

}
