<?php
declare(strict_types=1);

final class AuthController
{
    public function loginGet(): void
    {
        View::render('auth/login', ['title' => 'Login', 'error' => null]);
    }

    public function loginPost(): void
    {
        if (!CSRF::validate()) {
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            View::render('auth/login', ['title' => 'Login', 'error' => 'Invalid CSRF token.']);
            return;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Flash::set('error', 'Email and password are required.');
            View::render('auth/login', ['title' => 'Login', 'error' => 'Email and password are required.']);
            return;
        }

        if (!Auth::login($email, $password)) {
            Flash::set('error', 'Invalid credentials.');
            View::render('auth/login', ['title' => 'Login', 'error' => 'Invalid credentials.']);
            return;
        }

        Flash::set('success', 'Logged in successfully.');
        header('Location: ./index.php?r=dashboard');
        exit;
    }

    public function registerGet(string $role): void
    {
        View::render('auth/register', [
            'title' => 'Register - ' . ucfirst($role),
            'role' => $role,
            'error' => null,
        ]);
    }

    public function registerPost(string $role): void
    {
        if (!CSRF::validate()) {
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            View::render('auth/register', [
                'title' => 'Register - ' . ucfirst($role),
                'role' => $role,
                'error' => 'Invalid CSRF token.',
            ]);
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            Flash::set('error', 'Name, email, and password are required.');
            View::render('auth/register', [
                'title' => 'Register - ' . ucfirst($role),
                'role' => $role,
                'error' => 'Name, email, and password are required.',
            ]);
            return;
        }

        // Admin registration: allow only if there is no admin yet, otherwise require admin role.
        if ($role === 'admin') {
            $adminExists = Database::fetchOne("SELECT id FROM users WHERE role = 'admin' LIMIT 1") !== null;
            if ($adminExists) {
                if (!Auth::user()) {
                    Flash::set('error', 'Admin registration requires an admin account.');
                    header('Location: ./index.php?r=login');
                    exit;
                }
                RBAC::requireRole(['admin']);
            }
        }

        $ok = Auth::register($name, $email, $password, $role);
        if (!$ok) {
            Flash::set('error', 'Registration failed. Email must be unique.');
            View::render('auth/register', [
                'title' => 'Register - ' . ucfirst($role),
                'role' => $role,
                'error' => 'Registration failed (check email uniqueness and role).',
            ]);
            return;
        }

        Flash::set('success', 'Account created. Please login.');
        header('Location: ./index.php?r=login');
        exit;
    }
}

