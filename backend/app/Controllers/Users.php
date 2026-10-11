<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;

class Users extends BaseController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function index()
    {
        if ($this->request->is('post')) {
            $this->handlePost();

            return redirect()->to('users.php');
        }

        $search = trim((string) $this->request->getGet('search'));
        $role   = trim((string) $this->request->getGet('role'));
        $stats  = $this->users->stats();

        return $this->page('users', [
            'pageTitle'     => 'User & Staff Management - EGOTECH WORLD',
            'search'        => $search,
            'roleFilter'    => $role,
            'users'         => $this->users->filter($search, $role),
            'totalUsers'    => $stats['total'],
            'activeUsers'   => $stats['active'],
            'cashiersCount' => $stats['cashiers'],
            'currentUserId' => (int) session()->get('user_id'),
        ]);
    }

    private function handlePost(): void
    {
        $in      = $this->request;
        $action  = (string) $in->getPost('action');
        $selfId  = (int) session()->get('user_id');
        $isAdmin = session()->get('role') === 'Admin';

        $data = [
            'employee_id' => trim((string) $in->getPost('employee_id')),
            'username'    => trim((string) $in->getPost('username')),
            'email'       => trim((string) $in->getPost('email')),
            'password'    => (string) $in->getPost('password'),
            'role'        => (string) ($in->getPost('role') ?: 'Cashier'),
            'branch'      => trim((string) $in->getPost('branch')) ?: config('Store')->defaultBranch,
            'status'      => (string) ($in->getPost('status') ?: 'Active'),
        ];

        if ($action === 'add_user') {
            if ($data['employee_id'] === '' || $data['username'] === '' || $data['email'] === '' || $data['password'] === '') {
                $this->flash('Please complete all required fields for the new staff account.', 'error');
            } elseif ($data['role'] === 'Admin' && ! $isAdmin) {
                $this->flash('Only an Admin can create Admin accounts.', 'error');
            } else {
                $this->users->setValidationRule('password', 'required|min_length[6]');
                if ($this->users->insert($data)) {
                    $this->flash("Staff account '{$data['username']}' created successfully!");
                } else {
                    $this->flash('Failed to create account: ' . implode(' ', $this->users->errors()), 'error');
                }
            }
        } elseif ($action === 'edit_user') {
            $id     = (int) $in->getPost('id');
            $target = $id > 0 ? $this->users->find($id) : null;

            if (! $target || $data['employee_id'] === '' || $data['username'] === '' || $data['email'] === '') {
                $this->flash('Please fill out all required user fields correctly.', 'error');
            } elseif (! $isAdmin && ($target['role'] === 'Admin' || $data['role'] === 'Admin')) {
                $this->flash('Only an Admin can modify Admin accounts.', 'error');
            } elseif ($id === $selfId && $data['status'] !== 'Active') {
                $this->flash('You cannot deactivate your own account.', 'error');
            } elseif ($target['role'] === 'Admin' && $target['status'] === 'Active'
                && ($data['role'] !== 'Admin' || $data['status'] !== 'Active')
                && $this->users->otherActiveAdmins($id) === 0) {
                $this->flash('At least one active Admin account must remain.', 'error');
            } else {
                $data['id'] = $id;   // lets is_unique ignore this row
                if ($this->users->update($id, $data)) {
                    if ($id === $selfId) {
                        session()->set(['username' => $data['username'], 'role' => $data['role'], 'branch' => $data['branch']]);
                    }
                    $this->flash("User account '{$data['username']}' updated successfully!");
                } else {
                    $this->flash('Failed to update user: ' . implode(' ', $this->users->errors()), 'error');
                }
            }
        } elseif ($action === 'delete_user') {
            // "Delete" deactivates: bills keep their cashier, so sales history stays correct.
            $id     = (int) $in->getPost('id');
            $target = $id > 0 ? $this->users->find($id) : null;

            if ($id === $selfId) {
                $this->flash('Action prohibited: You cannot delete your currently logged-in account.', 'error');
            } elseif (! $target) {
                $this->flash('User not found.', 'error');
            } elseif ($target['role'] === 'Admin' && ! $isAdmin) {
                $this->flash('Only an Admin can remove Admin accounts.', 'error');
            } elseif ($target['role'] === 'Admin' && $target['status'] === 'Active' && $this->users->otherActiveAdmins($id) === 0) {
                $this->flash('At least one active Admin account must remain.', 'error');
            } else {
                $this->users->update($id, ['status' => 'Inactive']);
                $this->flash('User account removed (deactivated) successfully.');
            }
        }
    }
}
