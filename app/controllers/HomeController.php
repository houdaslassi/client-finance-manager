<?php
namespace App\Controllers;

use Core\BaseController;

class HomeController extends BaseController {
    public function index() {
        $this->render('home/index', [
            'title' => 'Home Page',
            'message' => 'Welcome to Client Finance Manager'
        ]);
    }

    public function about() {
        $this->render('home/about', [
            'title' => 'About Us',
            'message' => 'This is a simple finance management system'
        ]);
    }

    public function test() {
        $this->json([
            'status' => 'success',
            'message' => 'API endpoint working!'
        ]);
    }

    public function dashboard() {
        $this->render('home/dashboard');
    }
} 