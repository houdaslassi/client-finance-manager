<?php
namespace App\Controllers;

use App\Models\Client;
use Core\BaseController;

class ClientController extends BaseController {
    private $clientModel;

    public function __construct() {
        parent::__construct();
        $this->clientModel = new Client();
    }

    public function index() {
        $this->requireAuth();
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $totalClients = $this->clientModel->getTotalCount();
        $totalPages = (int) ceil($totalClients / $perPage);
        $clients = $this->clientModel->getPaginated($page, $perPage);
        $this->render('clients/index', [
            'clients' => $clients,
            'title' => 'Clients',
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function show($id) {
        $this->requireAuth();
        $data = $this->clientModel->getWithMovements($id);
        
        if (!$data) {
            $this->notFound('Client not found');
            return;
        }

        $this->render('clients/show', [
            'client' => $data['client'],
            'movements' => $data['movements'],
            'balance' => $data['balance'],
            'title' => $data['client']['name']
        ]);
    }

    public function create() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->clientModel->create($_POST);
            
            if (isset($result['success']) && !$result['success']) {
                $this->render('clients/create', [
                    'errors' => $result['errors'],
                    'old' => $_POST,
                    'title' => 'Add New Client'
                ]);
                return;
            }

            $this->redirect('/clients', 'Client created successfully');
            return;
        }

        $this->render('clients/create', [
            'title' => 'Add New Client'
        ]);
    }

    public function edit($id) {
        $this->requireAuth();
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->notFound('Client not found');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->clientModel->update($id, $_POST);
            
            if (isset($result['success']) && !$result['success']) {
                $this->render('clients/edit', [
                    'client' => $client,
                    'errors' => $result['errors'],
                    'old' => $_POST,
                    'title' => 'Edit Client'
                ]);
                return;
            }

            $this->redirect('/clients', 'Client updated successfully');
            return;
        }

        $this->render('clients/edit', [
            'client' => $client,
            'title' => 'Edit Client'
        ]);
    }

    public function delete($id) {
        $this->requireAuth();
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->notFound('Client not found');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->clientModel->delete($id);
            $this->redirect('/clients', 'Client deleted successfully');
            return;
        }

        $this->render('clients/delete', [
            'client' => $client,
            'title' => 'Delete Client'
        ]);
    }
} 