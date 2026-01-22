<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Role;


class UserService{
    public function getAllUser(Request $request){
        $users = User::query()->with('departments', 'roles:id,name');

        if($request->has('search')){
            $users->where('name', 'like', "%{$request->search}%");
        }

        if($request->has('role_id')){
            $users->whereHas('roles', function($q) use ($request){
                 $q->where('roles.id', $request->role_id); // Assuming role search by name, or use role_id
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
            'pin' => $data['pin'] ?? null
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