<?php

namespace App\Services\User;

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

        if ($request->has('is_active')) {
            $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $users->where('is_active', $isActive);
            }
        } elseif ($request->has('status')) {
            $isActive = filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $users->where('is_active', $isActive);
            }
        }

        if($request->has('sort_by')){
            $users->orderBy($request->sort_by, $request->sort_order);
        }

        

        return $users->paginate($request->per_page);
    }

    public function getUserById(int $id):User{
        return User::with('departments', 'roles')->findOrFail($id);
    }

    public function createUser(array $data):User{
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'pin' => $data['pin'] ?? null,
            'is_active' => $data['is_active'] ?? true
        ]);

        if(isset($data['department_id'])){
            $user->departments()->sync([$data['department_id']]);
        }
        
        if(isset($data['role_id'])){
            $user->roles()->sync([$data['role_id']]);
        }

        return $user->load('departments', 'roles');
    }

    public function updateUser(int $id, Request $request):User{
        $user = User::findOrFail($id);
        
        $data = $request->validated();

        $currentEmail = $user->email;
        $newEmail = $data['email'];

        if($currentEmail == $newEmail){
            $email = null;
        }
        
        $updateData = [
            'name' => $data['name'],
            'email' => $newEmail,
        ];
        
        if(isset($data['password'])){
             $updateData['password'] = Hash::make($data['password']);
        }
        
        if(isset($data['pin'])){
            $updateData['pin'] = $data['pin'];
        }

        if (array_key_exists('is_active', $data)) {
            $updateData['is_active'] = $data['is_active'];
        }

        $user->update($updateData);

        if(isset($data['department_id'])){
            $user->departments()->sync([$data['department_id']]);
        }

        if(isset($data['role_id'])){
            $user->roles()->sync([$data['role_id']]);
        }

        $user->save();

        return $user->load('departments', 'roles');
    }

    public function deleteUser(int $id):void{
        $user = User::findOrFail($id);
        $user->delete();
    }
}
