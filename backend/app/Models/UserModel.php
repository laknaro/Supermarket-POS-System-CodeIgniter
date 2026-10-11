<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    public const ROLES = ['Admin', 'Manager', 'Cashier', 'Inventory Staff'];

    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['employee_id', 'username', 'email', 'password', 'role', 'branch', 'status'];
    protected $useTimestamps = false;

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected $validationRules = [
        'id'          => 'permit_empty|is_natural_no_zero',
        'employee_id' => 'required|max_length[50]|is_unique[users.employee_id,id,{id}]',
        'username'    => 'required|max_length[100]|is_unique[users.username,id,{id}]',
        'email'       => 'required|valid_email|max_length[150]|is_unique[users.email,id,{id}]',
        'password'    => 'permit_empty|min_length[6]',
        'role'        => 'required|in_list[Admin,Manager,Cashier,Inventory Staff]',
        'branch'      => 'permit_empty|max_length[100]',
        'status'      => 'required|in_list[Active,Inactive]',
    ];

    protected $validationMessages = [
        'employee_id' => ['is_unique' => 'Employee ID already exists.'],
        'username'    => ['is_unique' => 'Username already exists.'],
        'email'       => ['is_unique' => 'Email already exists.'],
        'password'    => ['min_length' => 'Password must be at least 6 characters.'],
    ];

    protected function hashPassword(array $data): array
    {
        if (array_key_exists('password', $data['data'])) {
            if ($data['data']['password'] === '' || $data['data']['password'] === null) {
                unset($data['data']['password']);   // blank on edit = keep the current password
            } else {
                $data['data']['password'] = password_hash((string) $data['data']['password'], PASSWORD_BCRYPT);
            }
        }

        return $data;
    }

    /** List for the Users page. Password hashes are never loaded into the page. */
    public function filter(string $search = '', string $role = ''): array
    {
        $builder = $this->select('id, employee_id, username, email, role, branch, status, created_at')->orderBy('id', 'DESC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('username', $search)
                ->orLike('employee_id', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }
        if (in_array($role, self::ROLES, true)) {
            $builder->where('role', $role);
        }

        return $builder->findAll();
    }

    /** @return array{total:int,active:int,cashiers:int} */
    public function stats(): array
    {
        return [
            'total'    => $this->countAllResults(),
            'active'   => $this->where('status', 'Active')->countAllResults(),
            'cashiers' => $this->where('role', 'Cashier')->where('status', 'Active')->countAllResults(),
        ];
    }

    public function otherActiveAdmins(int $exceptId): int
    {
        return $this->where('role', 'Admin')->where('status', 'Active')->where('id !=', $exceptId)->countAllResults();
    }
}
