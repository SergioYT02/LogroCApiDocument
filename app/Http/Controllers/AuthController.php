<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\User;

use App\Models\Canton;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RecintoElectoral;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
/**
 * @OA\Info(
 *    title="API Prueba",
 *    version="1.0.0",
 * ),
 *   @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       in="header",
 *       name="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *    ),
 */
/**
 * @OA\Schema(
 *     schema="CreateUserRequest",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="password", type="string"),
 * )
 */
/**
 * @OA\Schema(
 *     schema="LoginUserRequest",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="password", type="string"),
 * )
 */

class AuthController extends Controller
{
     /**
 *  @OA\Post(
 *     path="/api/auth/register",
 *     summary="Crear un nuevo usuario",
 *     description="Este endpoint se utiliza para crear un nuevo usuario junto con su información de persona asociada en la aplicación.",
 *     operationId="createUser",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/CreateUserRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuario creado exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User Created Successfully"),
 *             @OA\Property(property="token", type="string", example="API TOKEN")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Campos vacíos o inválidos",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Existen campos vacios"),
 *             @OA\Property(property="errors", type="object")
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
/**
 * @OA\Post(
 *     path="/api/auth/login",
 *     summary="Iniciar sesión de usuario",
 *     description="Este endpoint se utiliza para permitir a un usuario iniciar sesión en la aplicación.",
 *     operationId="loginUser",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/LoginUserRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuario ha iniciado sesión exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User Logged In Successfully"),
 *             @OA\Property(property="token", type="string", example="API TOKEN")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Error de validación o Email y contraseña no coinciden con nuestros registros",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="errors", type="object")
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
 *     path="/api/auth/lista/provincias",
 *     summary="Obtener lista de provincias",
 *     description="Este endpoint se utiliza para obtener una lista de provincias con información adicional de cantones, parroquias y recintos electorales.",
 *     operationId="listaProvincias",
 *     @OA\Response(
 *         response=200,
 *         description="Lista de provincias obtenida exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="Listado", type="array", @OA\Items(
 *                 @OA\Property(property="provincia", type="string"),
 *                 @OA\Property(property="canton", type="string"),
 *                 @OA\Property(property="parroquia", type="string"),
 *                 @OA\Property(property="recinto", type="string")
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

public function listaProvincias()
{
    $provincias_Info = DB::table('provincias')
    ->join('cantones', 'provincias.id', '=', 'cantones.provincia_id')
    ->join('parroquias', 'cantones.id', '=', 'parroquias.canton_id')
    ->join('recintoselectorales', 'parroquias.id', '=', 'recintoselectorales.parroquia_id')
    ->select('provincias.provincia', 'cantones.canton', 'parroquias.parroquia', 'recintoselectorales.recinto')
    ->where('provincias.estado',true)
    ->get();
    return response()->json([
        "Listado"=> $provincias_Info,
    ]);


}
/**
 * @OA\Get(
 *     path="/api/auth/lista/cantones",
 *     summary="Obtener lista de cantones y provincias",
 *     description="Este endpoint se utiliza para obtener una lista de cantones y provincias disponibles en la aplicación.",
 *     operationId="Lista_cantones_provincias",
 *     @OA\Response(
 *         response=200,
 *         description="Lista de cantones y provincias obtenida exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="Listado", type="array", @OA\Items(
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
public function Lista_cantones_provincias(){
    $provincias_Cantones = DB::table('provincias')
    ->join('cantones', 'provincias.id', '=', 'cantones.provincia_id')
    ->select( 'cantones.canton','provincias.provincia')
    ->where('cantones.estado',true)
    ->get();
    return response()->json([
        "Listado"=> $provincias_Cantones,
    ]);

}

/**
 * @OA\Get(
 *     path="/api/auth/lista/recintos",
 *     summary="Obtener lista de recintos electorales",
 *     description="Este endpoint se utiliza para obtener una lista de recintos electorales disponibles en la aplicación.",
 *     operationId="Lista_recintos",
 *     @OA\Response(
 *         response=200,
 *         description="Lista de recintos electorales obtenida exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="Listado", type="array", @OA\Items(
 *                 @OA\Property(property="recinto", type="string"),
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
public function Lista_recintos(){
    $provincias_Cantones_Recintos = DB::table('provincias')
    ->join('cantones', 'provincias.id', '=', 'cantones.provincia_id')
    ->join('parroquias', 'cantones.id', '=', 'parroquias.canton_id')
    ->join('recintoselectorales', 'parroquias.id', '=', 'recintoselectorales.parroquia_id')
    ->select('recintoselectorales.recinto', 'cantones.canton','provincias.provincia' )
    ->where('recintoselectorales.estado',true)
    ->get();
    return response()->json([
        "Listado"=> $provincias_Cantones_Recintos,
    ]);
}


 public function updateRecintosElectorales(Request $request, $id){
 // Validar los datos enviados desde el formulario
 $request->validate([
    'recinto' => 'required|string',
    'parroquia_id' => 'required', 
]);

// Buscar el registro electoral por su ID
$recinto = RecintoElectoral::find($id);

if ($recinto->estado==false) {
    return response()->json(['message' => 'Registro electoral no encontrado'], 404);
}

$recinto->update([
    'recinto' => $request->input('recinto'),
    'parroquia_id' => $request->input('parroquia_id'),

]);
  


return response()->json(['message' => 'Registro electoral actualizado correctamente']);


 }
 public function DeleteP(Request $request, $id)
{
    // Buscar el cantón por su ID
    $canton = Canton::find($id);

    // Verificar si el cantón existe
    if ($canton->estado==false) {
        return response()->json(['message' => 'Cantón no encontrado'], 404);
    }

    // Obtener las parroquias asociadas al cantón
    $parroquias = $canton->parroquias;

    // Eliminar cada parroquia asociada al cantón
    foreach ($parroquias as $parroquia) {
        $parroquias->estado=false;
        $parroquias->save();
        
    }

    // Devolver una respuesta de éxito
    return response()->json(['message' => 'Parroquias eliminadas correctamente'],200);
}
}