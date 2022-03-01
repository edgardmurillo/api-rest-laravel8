<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
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

    public function detail() {
        $user = Auth::user();

        // Usuario sin inicio de sesion
        if ($user == null) {
            return response()->json(['message' => 'Solo pueden publicar usuarios activos. Inicie sesión, sino la tiene puede registrarse'],401);
        }

        $response['user'] = $user;
        return response()->json($response,200);
    }

    public function getPost($id) {
        $post = Post::find($id);

        if ($post == null) {
            return response()->json(['message' => 'Contenido no autorizado'],401);
        }

        return response()->json(json_encode($post));
    }

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
                'post_date' => 'required',
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
            $post->post_date = $request->input('post_date');
            $post->user_id = $user->id;
            $post->save();

            return response()->json(['message' => 'Publicación realizada'],200);

        } catch (Exception $e) {
            Log::channel('log-api')->error($e->getMessage());
            return response()->json(['error'=>$e->getMessage()], 400);
        }
    }

    public function listPost() {
        $posts = Post::orderBy('post_date', 'desc')->select('id','title','summary','post_date')->get();
        if ($posts->count() > 0) {
            return response()->json(json_encode($posts));
        } else {
            return response()->json(['message' => 'Sin registro actual'],200);
        }

    }

}
