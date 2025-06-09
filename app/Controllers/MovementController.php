<?php
namespace App\Controllers;

use Core\BaseController;
use App\Models\Movement;
use App\Models\Client;

class MovementController extends BaseController
{
    protected $movementModel;
    protected $clientModel;

    public function __construct()
    {
        $this->movementModel = new Movement();
        $this->clientModel = new Client();
    }

    public function index()
    {
        $this->requireAuth();
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $totalMovements = $this->movementModel->getFilteredCount($startDate, $endDate);
        $totalPages = (int) ceil($totalMovements / $perPage);
        $movements = $this->movementModel->getPaginatedFiltered($page, $perPage, $startDate, $endDate);
        $this->render('movements/index', [
            'movements' => $movements,
            'page' => $page,
            'totalPages' => $totalPages,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    public function show($id)
    {
        $this->requireAuth();
        $movement = $this->movementModel->find($id);
        if (!$movement) {
            $this->notFound();
            return;
        }
        $this->view('movements/show', ['movement' => $movement]);
    }

    public function create()
    {
        $this->requireAuth();
        $clients = $this->clientModel->getAllWithBalance();
        $selectedClientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;
        $this->view('movements/create', ['clients' => $clients, 'selectedClientId' => $selectedClientId]);
    }

    public function store()
    {
        $this->requireAuth();
        $data = [
            'client_id' => $_POST['client_id'] ?? null,
            'type' => $_POST['type'] ?? '',
            'amount' => $_POST['amount'] ?? '',
            'description' => $_POST['description'] ?? '',
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'created_by' => $_SESSION['admin_id'] ?? null,
        ];
        $this->movementModel->create($data);
        $this->redirect('/movements', 'Movement added successfully.');
    }

    public function edit($id)
    {
        $this->requireAuth();
        $movement = $this->movementModel->find($id);
        $clients = $this->clientModel->getAllWithBalance();
        if (!$movement) {
            $this->notFound();
            return;
        }
        $this->view('movements/edit', ['movement' => $movement, 'clients' => $clients]);
    }

    public function update($id)
    {
        $this->requireAuth();
        $data = [
            'client_id' => $_POST['client_id'] ?? null,
            'type' => $_POST['type'] ?? '',
            'amount' => $_POST['amount'] ?? '',
            'description' => $_POST['description'] ?? '',
            'date' => $_POST['date'] ?? date('Y-m-d'),
        ];
        $this->movementModel->update($id, $data);
        $this->redirect('/movements', 'Movement updated successfully.');
    }

    public function delete($id)
    {
        $this->requireAuth();
        $this->movementModel->delete($id);
        $this->redirect('/movements', 'Movement deleted successfully.');
    }
} 