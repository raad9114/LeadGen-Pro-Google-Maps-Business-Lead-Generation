<?php
/**
 * User Management Controller
 */

class UserController
{
    public function index(): void
    {
        Session::requireAdmin();
        $pageTitle = 'Users';
        $currentPage = 'users';

        $userModel = new User();
        $users = $userModel->getAll();

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function list(): void
    {
        Session::requireAdmin();
        $userModel = new User();
        Response::success($userModel->getAll());
    }

    public function store(): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $validator = new Validator();
        $validator->required('username', $data['username'] ?? '', 'Username')
                  ->required('email', $data['email'] ?? '', 'Email')
                  ->email('email', $data['email'] ?? '', 'Email')
                  ->required('password', $data['password'] ?? '', 'Password')
                  ->minLength('password', $data['password'] ?? '', 6, 'Password');

        if ($validator->fails()) {
            Response::error($validator->firstError());
            return;
        }

        $userModel = new User();

        // Check uniqueness
        if ($userModel->findByUsername($data['username'])) {
            Response::error('Username already exists');
            return;
        }
        if ($userModel->findByEmail($data['email'])) {
            Response::error('Email already exists');
            return;
        }

        $id = $userModel->create($data);
        Response::success(['id' => $id], 'User created successfully');
    }

    public function update(string $id): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $userModel = new User();
        $userModel->update((int) $id, $data);
        Response::success(null, 'User updated successfully');
    }

    public function delete(string $id): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        if ((int) $id === Session::userId()) {
            Response::error('You cannot delete your own account');
            return;
        }

        $userModel = new User();
        $userModel->delete((int) $id);
        Response::success(null, 'User deleted');
    }
}
