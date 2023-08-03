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

class AuthController extends Controller
{
 /**
     * @OA\Info(
     *    title="Documentando mi api",
     *    version="1.0.0",
     * )
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     in="header",
     *     name="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT"
     * )
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
     *     description="Este endpoint se utiliza para obtener una lista de personas con sus usuarios y roles asociados que estén marcados como activos (estado=true).",
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
     * )
     */
    public function mostrar(){
      
        $datos = DB::table('provincias')
            ->join('cantones', 'provincias.id', '=', 'cantones.provincia_id')
            ->join('parroquias', 'parroquias.canton_id', '=', 'cantones.id')
            ->join(' recintoselectorales', 'recintoselectorales.parroquia_id', '=', 'parroquias.id')
            ->select('recintoselectorales.recinto','parroquias.parroquia', 'cantones.canton','provincias.provincia')->get();
            return response()->json(['data' => $datos], 200);
            
        
        

    }
     /**
     * @OA\Get(
     *     path="/api/auth/cantonesP",
     *     summary="Obtener lista de cantones por provincia",
     *     description="Este endpoint se utiliza para obtener una lista de cantones por provincia.",
     *     operationId="cantonesProvincia",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de cantones por provincia obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="canton_id", type="integer"),
     *                 @OA\Property(property="canton", type="string"),
     *                 @OA\Property(property="provincia", type="string")
     *             ))
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
     * )
     */
    public function cantonesProvincia(){
        $cantones = DB::table('cantones')
        ->join('provincias', 'provincias.id', '=', 'cantones.provincia_id')
        ->select('cantones.id as canton_id', 'cantones.canton', 'provincias.provincia')
        ->get();

    // Devolver la respuesta en formato JSON con los cantones y su provincia
    return response()->json(['data' => $cantones], 200);

   

}
 /**
     * @OA\Get(
     *     path="/api/auth/recintosE",
     *     summary="Obtener lista de recintos electorales por provincia y cantón",
     *     description="Este endpoint se utiliza para obtener una lista de recintos electorales por provincia y cantón.",
     *     operationId="recintosElectoralesPC",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de recintos electorales por provincia y cantón obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="recinto", type="string"),
     *                 @OA\Property(property="parroquia", type="string"),
     *                 @OA\Property(property="canton", type="string"),
     *                 @OA\Property(property="provincia", type="string")
     *             ))
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
     * )
     */
public function recintosElectoralesPC(){
      // Realizar la consulta utilizando Eloquent ORM para obtener los recintos electorales por provincia y cantón
      $recintos = DB::table('recintoselectorales')
      ->join('parroquias', 'parroquias.id', '=', 'recintoselectorales.parroquia_id')
      ->join('cantones', 'cantones.id', '=', 'parroquias.canton_id')
      ->join('provincias', 'provincias.id', '=', 'cantones.provincia_id')
      ->select('recintoselectorales.recinto', 'parroquias.parroquia', 'cantones.canton', 'provincias.provincia')
      ->get();

  // Devolver la respuesta en formato JSON con los recintos electorales por provincia y cantón
  return response()->json(['data' => $recintos], 200);

}
 /**
     * @OA\Put(
     *     path="/api/auth/update",
     *     summary="Actualizar un recinto electoral",
     *     description="Este endpoint se utiliza para actualizar un recinto electoral existente.",
     *     operationId="update",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del recinto electoral a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="recinto", type="string"),
     *             @OA\Property(property="estado", type="boolean"),
     *             @OA\Property(property="parroquia_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recinto electoral actualizado correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recinto electoral no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
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
     * )
     */
public function update(Request $request, $id)
{
    // Buscar el recinto electoral por su ID
    $recinto = RecintoElectoral::find($id);

    // Verificar si el recinto existe
    if (!$recinto) {
        return response()->json(['message' => 'Recinto Electoral no encontrado'], 404);
    }

    // Validar los datos enviados en la solicitud
    $request->validate([
        'recinto' => 'required|string|max:255',
        'estado' => 'required|boolean',
        'parroquia_id' => 'required|exists:parroquias,id'
    ]);

    // Actualizar los campos del recinto con los datos enviados
    $recinto->recinto = $request->input('recinto');
    $recinto->estado = $request->input('estado');
    $recinto->parroquia_id = $request->input('parroquia_id');

    // Guardar los cambios en la base de datos
    $recinto->save();

    // Devolver una respuesta de éxito
    return response()->json(['message' => 'Recinto Electoral actualizado correctamente'], 200);
}
/**
     * @OA\Delete(
     *     path="/api/auth/delete",
     *     summary="Eliminar parroquias por cantón",
     *     description="Este endpoint se utiliza para eliminar todas las parroquias asociadas a un cantón.",
     *     operationId="DeletePorCanton",
     *     @OA\Parameter(
     *         name="cantonId",
     *         in="path",
     *         description="ID del cantón del cual se eliminarán las parroquias",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parroquias eliminadas correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cantón no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         )
     * ),
     */
public function DeletePorCanton(Request $request, $cantonId)
{
    // Buscar el cantón por su ID
    $canton = Canton::find($cantonId);

    // Verificar si el cantón existe
    if (!$canton) {
        return response()->json(['message' => 'Cantón no encontrado'], 404);
    }

    // Obtener las parroquias asociadas al cantón
    $parroquias = $canton->parroquias;

    // Eliminar cada parroquia asociada al cantón
    foreach ($parroquias as $parroquia) {
        $parroquia->delete();
    }

    // Devolver una respuesta de éxito
    return response()->json(['message' => 'Parroquias eliminadas correctamente'], 200);
}
}

