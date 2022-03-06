<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{

    /**
     * @OA\Get(
     *      path="/api/users/{id}",
     *      tags={"Usuario"},
     *      summary="Detalle de usuario",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Id de usuario",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     **/
    public function getUser() {
        $user = Auth::user();

        // Usuario sin inicio de sesion
        if ($user == null) {
            return response()->json(['message' => 'Solo pueden publicar usuarios activos. Inicie sesión, sino la tiene puede registrarse'],401);
        }

        $data = [
            'success'=>true,
            'messages'=>$user
        ];

        return response()->json($data);
    }

    /**
     * @OA\Get(
     *      path="/api/login",
     *      tags={"Usuario"},
     *      summary="Inicio de sesión",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          description="Ingresar la cuenta de correo para iniciar sesión",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          description="Ingresar la contraseña",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     **/
    public function login(Request $request) {
        if (Auth::attempt(['email' => $request->input('email'),'password' => $request->input('password')])) {

            $user = Auth::user();
            $response['token'] = $user->createToken('MiAplicacion');
            $response['name'] = $user->name;

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'Credenciales invalidas'],401);
        }

    }

    /**
     * @OA\Post(
     *      path="/api/register",
     *      tags={"Usuario"},
     *      summary="Registro de usuario",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="c_password",
     *                      type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
    *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     **/
    public function register(Request $request) {

        $validator = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'c_password' => 'required|same:password'
            ]);

        // Campos requeridos
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        $response['token'] = $user->createToken('MiAplicacion');
        $response['name'] = $user->name;

        return response()->json($response,200);

    }

    /**
     * @OA\Get(
     *      path="/api/posts",
     *      tags={"Publicaciones"},
     *      summary="Lista de publicaciones",
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     **/
    public function listPost() {
        $posts = Post::orderBy('post_date', 'desc')->select('id','title','summary','post_date')->get();
        if ($posts->count() > 0) {
            $data = [
                'success'=>true,
                'messages'=>$posts
            ];
            return response()->json($data);
        } else {
            return response()->json(['message' => 'Sin registro actual'],200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/posts/{id}",
     *      tags={"Publicaciones"},
     *      summary="Detalle de publicación",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Id de usuario",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     **/
    public function getPost($id) {
        $post = Post::find($id);

        $data = [
            'success'=>true,
            'messages'=>$post
        ];

        if ($post == null) {
            return response()->json(['message' => 'Contenido no autorizado'],401);
        }

        return response()->json($data);
    }

    /**
     * @OA\Post(
     *      path="/api/posts",
     *      tags={"Publicaciones"},
     *      summary="Agregar publicación",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="summary",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="content",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="post_date",
     *                      type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     **/
    public function setPost(Request $request) {
        $user = Auth::user();

        // Usuario sin inicio de sesion
        if ($user == null) {
            return response()->json(['message' => 'Solo pueden publicar usuarios activos. Inicie sesión, sino la tiene puede registrarse'],401);
        }

        $validator = Validator::make($request->all(),
            [
                'title' => 'required',
                'summary' => 'required',
                'content' => 'required',
                'post_date' => 'required:date',
            ]);

        // Campos requeridos
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }

        try {

            $post = new Post();
            $post->title = $request->input('title');
            $post->summary = $request->input('summary');
            $post->content = $request->input('content');
            $post->post_date = Carbon::parse($request->input('post_date'))->format('Y-m-d');
            $post->user_id = $user->id;
            $post->save();

            return response()->json(['message' => 'Publicación realizada'],200);

        } catch (Exception $e) {
            Log::channel('log-api')->error($e->getMessage());
            return response()->json(['error'=>$e->getMessage()], 400);
        }
    }

}
