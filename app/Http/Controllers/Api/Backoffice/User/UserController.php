<?php

namespace App\Http\Controllers\Api\Backoffice\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use \Auth;

class UserController extends Controller
{
    public function getUser(Request $request) {
        $validator = Validator::make($request->all(), [
	        'type' => 'required',
	        'limit' => 'required',
	        'offset' => 'required',
            'search' => 'nullable',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $query = User::
                    leftJoin(DB::raw('(SELECT user_id, AVG(score) as rating FROM user_ratings GROUP BY user_id) as ratings'), function ($join) {
                        $join->on('users.id', '=', 'ratings.user_id');
                    })
                    ->where('users.type', $valid['type']);
        if(isset($valid['search'])) {
            $search = $valid['search'];
            $query = $query->where(function($query) use ($search) {
                $query->where('users.name', 'ilike', '%'.$search.'%')
                    ->where('users.email', 'ilike', '%'.$search.'%');
            });
        }
        $query = $query->select(
                        'users.*',
                        DB::raw('ratings.rating as user_rating')
                    )
                    ->groupBy('users.id', 'ratings.rating')
                    ->orderBy('users.created_at', 'DESC');
        $count = $query->get()->count();
        $data = $query->limit($valid['limit'])
                    ->offset($valid['offset'])
                    ->get();
        $response = [
            'users' => $data,
            'count' => $count,
            'limit' => $valid['limit'],
            'offset' => $valid['offset'],
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }

    public function getUserDetail(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required|numeric',
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $user = User::find($valid['id']);
        if(!$user) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pengguna tidak ditemukan',
	        ], 404);
        }
        $response = [
            'user' => $user
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
    
    public function createUser(Request $request) {
        $validator = Validator::make($request->all(), [
	        'nama' => 'required|string',
	        'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required',
            
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $user = new User;
        $user->type = "Internal";
        $user->name = $valid['nama'];
        $user->email = $valid['email'];
        $user->password = Hash::make($valid['password']);
        $user->gender = $valid['jenis_kelamin'];
        $user->birth_date = $valid['tanggal_lahir'];
        $user->nik = "INTERNAL";
        $user->phone = "INTERNAL";
        $user->address_province = "INTERNAL";
        $user->address_city = "INTERNAL";
        $user->address_subdistrict = "INTERNAL";
        $user->address_village = "INTERNAL";
        $user->address_zipcode = "INTERNAL";
        $user->save();
        return response()->json(['success' => true, 'message' => 'Berhasil membuat akun']);
    }

    public function updateUser(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required|numeric',
	        'nama' => 'required|string',
	        'email' => 'required|string|unique:users,email,'.$request->id,
            'password' => 'nullable|string|confirmed',
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required',
            
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $user = User::find($valid['id']);
        if(!$user) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pengguna tidak ditemukan',
	        ], 404);
        }
        $user->name = $valid['nama'];
        $user->email = $valid['email'];
        $user->gender = $valid['jenis_kelamin'];
        $user->birth_date = $valid['tanggal_lahir'];
        if(isset($valid['password'])) {
            $user->password = Hash::make($valid['password']);
        }
        $user->save();
        return response()->json(['success' => true, 'message' => 'Berhasil mengubah akun']);
    }
    
    public function deleteUser(Request $request) {
        $validator = Validator::make($request->all(), [
	        'id' => 'required|numeric'
            
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $user = User::find($valid['id']);
        if(!$user) {
            return response()->json([
	            'success' => false,
	            'message' => 'Pengguna tidak ditemukan',
	        ], 404);
        }
        if($user->delete()) {
            return response()->json(['success' => true, 'message' => 'Berhasil menghapus akun']);
        }
        return response()->json(['success' => false, 'message' => 'Gagal menghapus akun, akun telah terelasi dengan berbagai data']);
    }
}
