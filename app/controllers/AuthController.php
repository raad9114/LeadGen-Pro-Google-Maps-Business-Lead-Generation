<?php
/**
 * Auth Controller
 */

class AuthController
{
    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            Response::redirect(Config::appUrl() . '/dashboard');
        }

        $pageTitle = 'Login';
        include TEMPLATE_PATH . '/pages/login.php';
    }

    public function login(): void
    {
        CSRF::validateOrDie();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $validator = new Validator();
        $validator->required('username', $username, 'Username')
                  ->required('password', $password, 'Password');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect(Config::appUrl() . '/login');
            return;
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::flash('error', 'Invalid username or password.');
            Response::redirect(Config::appUrl() . '/login');
            return;
        }

        if ($user['status'] !== 'active') {
            Session::flash('error', 'Your account is inactive. Contact administrator.');
            Response::redirect(Config::appUrl() . '/login');
            return;
        }

        // Login successful
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['full_name'] ?: $user['username']);
        Session::set('username', $user['username']);

        $userModel->updateLastLogin($user['id']);

        Response::redirect(Config::appUrl() . '/dashboard');
    }

    public function logout(): void
    {
        Session::destroy();
        Response::redirect(Config::appUrl() . '/login');
    }
}
