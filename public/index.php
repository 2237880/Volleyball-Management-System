<?php
declare(strict_types=1);

session_start();

// ---- Minimal lightweight MVC bootstrap ----
$BASE_PATH = dirname(__DIR__);

require_once $BASE_PATH . '/app/lib/View.php';
require_once $BASE_PATH . '/app/lib/Database.php';
require_once $BASE_PATH . '/app/lib/Auth.php';
require_once $BASE_PATH . '/app/lib/RBAC.php';
require_once $BASE_PATH . '/app/lib/CSRF.php';
require_once $BASE_PATH . '/app/lib/Flash.php';
require_once $BASE_PATH . '/app/controllers/AuthController.php';
require_once $BASE_PATH . '/app/models/TeamModel.php';
require_once $BASE_PATH . '/app/controllers/TeamsController.php';
require_once $BASE_PATH . '/app/controllers/MatchesController.php';
require_once $BASE_PATH . '/app/models/MatchModel.php';
require_once $BASE_PATH . '/app/controllers/ScoresController.php';
require_once $BASE_PATH . '/app/models/ScoreModel.php';
require_once $BASE_PATH . '/app/controllers/UploadsController.php';

$route = $_GET['r'] ?? 'home'; // e.g. ?r=teams, ?r=matches, ?r=dashboard

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function require_login(): void
{
    if (!isset($_SESSION['user'])) {
        redirect('./index.php?r=login');
    }
}

// Routes implemented later during the project build.
$routes = [
    'home' => function () {
        View::render('home', ['title' => 'Volleyball Management System']);
    },
    'dashboard' => function () {
        require_login();
        View::render('dashboard', ['title' => 'Dashboard (setup in progress)']);
    },
    'logout' => function () {
        Auth::logout();
        redirect('./index.php?r=home');
    },
    'login' => function () {
        $ctrl = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->loginPost();
            return;
        }
        $ctrl->loginGet();
    },
    'register-player' => function () {
        $ctrl = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->registerPost('player');
            return;
        }
        $ctrl->registerGet('player');
    },
    'register-coach' => function () {
        $ctrl = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->registerPost('coach');
            return;
        }
        $ctrl->registerGet('coach');
    },
    'register-admin' => function () {
        $ctrl = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->registerPost('admin');
            return;
        }
        $ctrl->registerGet('admin');
    },
    'teams' => function () {
        require_login();
        (new TeamsController())->index();
    },
    'teams-create' => function () {
        require_login();
        (new TeamsController())->create();
    },
    'teams-edit' => function () {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            exit('Missing team id');
        }
        (new TeamsController())->edit($id);
    },
    'teams-view' => function () {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            exit('Missing team id');
        }
        (new TeamsController())->view($id);
    },
    'matches' => function () {
        require_login();
        (new MatchesController())->index();
    },
    'matches-create' => function () {
        require_login();
        (new MatchesController())->create();
    },
    'match-history' => function () {
        require_login();
        (new ScoresController())->history();
    },
    'score-edit' => function () {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            exit('Missing match id');
        }
        (new ScoresController())->edit($id);
    },
    'uploads-player-image' => function () {
        require_login();
        (new UploadsController())->playerImage();
    },
    'uploads-match-report' => function () {
        require_login();
        (new UploadsController())->matchReport();
    },
];

if (!array_key_exists($route, $routes)) {
    http_response_code(404);
    View::render('home', ['title' => '404 - Page not found']);
    exit;
}

$routes[$route]();

