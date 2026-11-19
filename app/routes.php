<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\ArticleController;
use App\Controllers\CalculatorController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Core\Router;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

$router->get('/calculators', [CalculatorController::class, 'index']);
$router->get('/calculator/{slug}', [CalculatorController::class, 'show']);

$router->get('/articles', [ArticleController::class, 'index']);
$router->get('/articles/{slug}', [ArticleController::class, 'show']);

$router->get('/about', [PageController::class, 'about']);
$router->get('/privacy', [PageController::class, 'privacy']);

$router->get('/api/articles', [ApiController::class, 'articles']);
$router->get('/api/articles/{slug}', [ApiController::class, 'article']);
$router->get('/api/calculators', [ApiController::class, 'calculators']);
$router->post('/api/calculations', [ApiController::class, 'saveCalculation']);

$router->get('/admin/login', [AdminController::class, 'loginForm']);
$router->post('/admin/login', [AdminController::class, 'login']);
$router->get('/admin/setup', [AdminController::class, 'setupForm']);
$router->post('/admin/setup', [AdminController::class, 'setup']);
$router->post('/admin/logout', [AdminController::class, 'logout']);

$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/articles', [AdminController::class, 'articles']);
$router->get('/admin/articles/create', [AdminController::class, 'articleCreate']);
$router->post('/admin/articles', [AdminController::class, 'articleStore']);
$router->get('/admin/articles/{id}/edit', [AdminController::class, 'articleEdit']);
$router->post('/admin/articles/{id}', [AdminController::class, 'articleUpdate']);
$router->post('/admin/articles/{id}/delete', [AdminController::class, 'articleDelete']);

$router->get('/admin/calculators', [AdminController::class, 'calculators']);
$router->post('/admin/calculators', [AdminController::class, 'saveCalculators']);

return $router;
