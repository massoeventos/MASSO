<?php
namespace Masso\Traits;

trait Rbac
{    

    public $hash_roles;
    public $hash_permissions;

    public function isAdmin(){
        return $this->is_admin;
    }

    public function roles()
    {
        return $this->belongsTo('Masso\Role', 'role_id');
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole( $role )
    {
        $roles = $this->roles()->pluck('slug')->toArray();
        if(false !== strpos($role, '|')) {
            $roleArr = explode('|', $role);
        } else {
            $roleArr = [$role];
        }
        return !empty(array_intersect($roleArr, $roles));
    }
    /**
     * @param string $operation
     * @return bool
     */
    public function canDo($operation)
    {
        $role = $this->hash_roles;
        if( empty($this->hash_roles) )
            $role = $this->hash_roles = $this->roles;

        if( empty($this->hash_permissions) )
            $this->hash_permissions = $role->permissions()->pluck('slug')->toArray();

        $permissions = [];
        $permissions = array_merge($permissions, $this->hash_permissions);
        $permissions = array_unique($permissions);
        
        if(false !== strpos($operation, '|')) {
            $operationArr = explode('|', $operation);
        } else {
            $operationArr = [$operation];
        }

        return !empty(array_intersect($operationArr, $permissions));
    }
}