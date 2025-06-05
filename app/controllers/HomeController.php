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
        $this->requireAuth();
        
        // Get total clients
        $client = new \App\Models\Client();
        $totalClients = $client->count();
        
        // Get total movements
        $movement = new \App\Models\Movement();
        $totalMovements = $movement->count();
        
        // Get total income and expenses
        $totalIncome = $movement->getTotalIncome();
        $totalExpenses = $movement->getTotalExpenses();
        
        $this->render('home/dashboard', [
            'totalClients' => $totalClients,
            'totalMovements' => $totalMovements,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses
        ]);
    }
} 