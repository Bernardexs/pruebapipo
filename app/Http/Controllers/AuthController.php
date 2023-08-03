<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Persona;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Canton;
use App\Models\Parroquia;
use App\Models\Provincia;
use App\Models\RecintoElectoral;
use OpenApi\Annotations as OA;


use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(
 *    title="Documentando mi api",
 *    version="1.0.0",
 * ),
 *   @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       in="header",
 *       name="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *    ),
 */
/**
 * 
 * @OA\Schema(
 *     schema="PersonaWithUserAndRole",
 *     
 *     @OA\Property(property="recinto", type="string"),
 *     @OA\Property(property="parroquia", type="string"),
 *     @OA\Property(property="canton", type="string")
 * )
 */

class AuthController extends Controller
{
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Existen campos vacios',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    /**
    * @OA\Get(
        *     path="/api/auth/mostrar",
        *     summary="Obtener lista de personas con usuarios y roles asociados",
        *     description="Este endpoint se utiliza para obtener una lista de personas con sus usuarios y roles asociados que estén marcados como activos (esato=true).",
        *     operationId="mostrar",
        *     @OA\Response(
        *         response=200,
        *         description="Lista de personas, usuarios y roles obtenida exitosamente",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="personas", type="array", @OA\Items(ref="#/components/schemas/PersonaWithUserAndRole"))
        *         )
        *     ),
        *     @OA\Response(
        *         response=500,
        *         description="Error del servidor",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="status", type="boolean", example=false),
        *             @OA\Property(property="message", type="string")
        *         )
        *     )
        * )
      */
    public function mostrar(){
      
        $datos = DB::table('provincias')
            ->join('cantones', 'provincias.id', '=', 'cantones.provincia_id')
            ->join('parroquias', 'parroquias.canton_id', '=', 'cantones.id')
            ->join(' recintoselectorales', 'recintoselectorales.parroquia_id', '=', 'parroquias.id')
            ->select('recintoselectorales.recinto','parroquias.parroquia', 'cantones.canton','provincias.provincia')->get();
            return response()->json(['data' => $datos], 200);
            
        
        

    }
    public function cantonesProvincia(){
        $cantones = DB::table('cantones')
        ->join('provincias', 'provincias.id', '=', 'cantones.provincia_id')
        ->select('cantones.id as canton_id', 'cantones.canton', 'provincias.provincia')
        ->get();

    // Devolver la respuesta en formato JSON con los cantones y su provincia
    return response()->json(['data' => $cantones], 200);

   

}
}
