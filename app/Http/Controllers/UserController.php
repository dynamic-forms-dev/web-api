<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use App\User;
use App\Models\Form;
use App\Models\FieldDescriptor as Descriptor;
use App\Models\OptionType as OptionType;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('oauth');
        $this->middleware('admin');
    }


    private function insertUser($role,$request){
        if ( ! ($request->has('email') and $request->has('password')) ){

            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'No email or password provided'
            ], 401);
        }

        if (User::alreadyExists($request->input('email'))){
            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'That user exists already'
            ], 401);
        }

        $user = new User;
        if ($request->has('name')) $user->name = $request->input('name');
        $user->password   = $request->input('password');
        $user->email      = $request->input('email');
        $user->membership = $role;
        $user->save();

        return response()->json([
                'success' => true,
                'message' => 'Success']);
    }
    
    /**
     * Insertar un usuario de tipo empleado en el sistema
     * solo se requiere ser adminstrador.
     *
     * @return \Illuminate\Http\Response
     */
    public function newEmployee(Request $request)
    {
        if (User::alreadyExists($request->input('email'))){
            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'That user exists already'
            ], 401);
        }

        return $this->insertUser("employee",$request);
    }
    
    /**
     * Insertar un usuario de tipo manager en el sistema
     * Se requiere ser president
     *
     * @return \Illuminate\Http\Response
     */
    public function newManager(Request $request)
    {
        
        if ( !User::isPresident($request->input('user_id')) )
            return response()->json([
                'error'   => "401",
                'success' => false,
                'message' => 'You must be a president user to do that'
            ], 401);
        
        return $this->insertUser("manager",$request);
    }

    /**
     * Listar todos los usuarios del sistema
     *
     * @return \Illuminate\Http\Response
     */
    public function listAll(Request $request)
    {
        return response()->json(User::all());
    }
    
    /**
     * Listar todos los usuarios del sistema por rol
     *
     * @param rol: tipo del rol a buscar
     * @return \Illuminate\Http\Response
     */
    public function listWithRole(Request $request)
    {
        if ( !$request->has('role') ){

            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'No role found'
            ], 401);
        }
        
        return response()->json(User::getAllWithRole($request->input('role')));
    }

    /**
     * Listar todos los usuarios del sistema por rol
     *
     * @param rol: tipo del rol a buscar
     * @return \Illuminate\Http\Response
     */
    // public function myUsers(Request $request)
    // {
    //     return response()->json("hola");
    // }
}
