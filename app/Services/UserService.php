<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserService{
    public function getAllUser(Request $request){
        $users = User::query()->with('departments', 'role');

        if($request->has('search')){
            $users->where('name', 'like', "%{$request->search}%");
        }

        if($request->has('role')){
            $users->whereHas('role', function($q) use ($request){
                 $q->where('name', $request->role); // Assuming role search by name, or use role_id
            });
        }

        if($request->has('department_id')){
            $users->whereHas('departments', function($q) use ($request){
                $q->where('departments.id', $request->department_id);
            });
        }

        if($request->has('status')){
            $users->where('status', $request->status);
        }

        if($request->has('sort_by')){
            $users->orderBy($request->sort_by, $request->sort_order);
        }

        return $users->paginate($request->per_page);
    }

    public function createUser(array $data){
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'pin' => $data['pin'] ?? null,
            // 'role_id' handled separately if in user_roles pivot, or if column exists. 
            // Assuming role logic is handled or column exists based on StoreUserRequest? 
            // Wait, roles() is M-M in User model. Need to sync roles too if input provided.
        ]);

        if(isset($data['department_id'])){
            $user->departments()->sync([$data['department_id']]);
        }
        
        if(isset($data['role_id'])){
            $user->roles()->sync([$data['role_id']]);
        }

        return $user->load('departments', 'roles');
    }

    public function updateUser(int $id, array $data){
        $user = User::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        
        if(isset($data['password'])){
             $updateData['password'] = Hash::make($data['password']);
        }
        
        if(isset($data['pin'])){
            $updateData['pin'] = $data['pin'];
        }

        $user->update($updateData);

        if(isset($data['department_id'])){
            $user->departments()->sync([$data['department_id']]);
        }

        if(isset($data['role_id'])){
             $user->roles()->sync([$data['role_id']]);
        }

        return $user->load('departments', 'roles');
    }

    public function deleteUser(int $id){
        $user = User::findOrFail($id);
        $user->delete();
        return true;
    }
}