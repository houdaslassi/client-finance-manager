<?php
namespace App\Controllers\API;

use App\Models\Movement;
use App\Models\Client;

class MovementAPIController extends BaseAPIController {

    /**
     * Get movements for a specific user/client
     * GET /api/movements?client_id=X
     */
    public function getUserMovements() {
        $userType = $this->authenticateAPI();

        $this->validateAPIMethod(['GET']);

        $this->validateRequiredParams(['client_id']);

        $clientId = (int) $_GET['client_id'];

        try {
            $clientModel = new Client();
            $client = $this->validateRecordExists($clientModel, $clientId, 'Client');

            $movementModel = new Movement();
            $movements = $movementModel->getByClientId($clientId);

            $filteredMovements = $this->applyMovementFilters($movements);

            $summary = $this->calculateFinancialSummary($filteredMovements);

            $this->logAPIActivity('get_user_movements', [
                'client_id' => $clientId,
                'user_type' => $userType,
                'movement_count' => count($filteredMovements),
                'filters_applied' => $this->getActiveFilters()
            ]);

            $this->apiSuccess([
                'user' => [
                    'id' => $client['id'],
                    'name' => $client['name'],
                    'email' => $client['email']
                ],
                'movements' => array_values($filteredMovements),
                'summary' => $summary,
                'filters' => $this->getActiveFilters()
            ]);

        } catch (\Exception $e) {
            $this->logAPIActivity('get_user_movements_error', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'user_type' => $userType ?? 'unauthenticated'
            ]);

            $this->apiError('Failed to fetch user movements', 500);
        }
    }


    public function createMovement() {
        $userType = $this->authenticateAPI();

        $this->validateAPIMethod(['POST']);

        $input = $this->getJSONInput();
        $this->validateMovementCreationInput($input);

        try {
            $clientModel = new Client();
            $client = $this->validateRecordExists($clientModel, $input['client_id'], 'Client');

            $movementData = [
                'client_id' => (int) $input['client_id'],
                'type' => $input['type'],
                'amount' => $this->validatePositiveNumber($input['amount'], 'amount'),
                'description' => trim($input['description']),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $movementModel = new Movement();
            $movementId = $movementModel->create($movementData);

            $createdMovement = $movementModel->find($movementId);

            $this->logAPIActivity('create_movement_success', [
                'movement_id' => $movementId,
                'client_id' => $input['client_id'],
                'type' => $input['type'],
                'amount' => $movementData['amount'],
                'user_type' => $userType
            ]);

            $this->apiSuccess([
                'movement_id' => $movementId,
                'movement' => $createdMovement,
                'client' => [
                    'id' => $client['id'],
                    'name' => $client['name']
                ]
            ], 'Movement created successfully', 201);

        } catch (\Exception $e) {
            $this->logAPIActivity('create_movement_error', [
                'input' => $input,
                'error' => $e->getMessage(),
                'user_type' => $userType ?? 'unauthenticated'
            ]);

            $this->apiError('Failed to create movement', 500);
        }
    }


    private function applyMovementFilters(array $movements) {
        $startDate = $this->validateDateFormat($_GET['start_date'] ?? null, 'start_date');
        $endDate = $this->validateDateFormat($_GET['end_date'] ?? null, 'end_date');
        $type = $_GET['type'] ?? null;


        if ($type && !in_array($type, ['income', 'expense', 'earning'])) {
            $this->apiError("Type filter must be 'income', 'earning', or 'expense'", 400);
        }

        if (!$startDate && !$endDate && !$type) {
            return $movements;
        }

        // Apply filters
        return array_filter($movements, function($movement) use ($startDate, $endDate, $type) {
            $movementDate = date('Y-m-d', strtotime($movement['created_at']));

            // Date range filtering
            if ($startDate && $movementDate < $startDate) {
                return false;
            }

            if ($endDate && $movementDate > $endDate) {
                return false;
            }

            // Type filtering
            if ($type && $movement['type'] !== $type) {
                return false;
            }

            return true;
        });
    }


    private function calculateFinancialSummary(array $movements) {
        $totalIncome = 0;
        $totalExpenses = 0;
        $incomeCount = 0;
        $expenseCount = 0;

        foreach ($movements as $movement) {
            $amount = (float) $movement['amount'];

            if ($movement['type'] === 'income' || $movement['type'] === 'earning') {
                $totalIncome += $amount;
                $incomeCount++;
            } else if ($movement['type'] === 'expense') {
                $totalExpenses += $amount;
                $expenseCount++;
            }
        }

        return [
            'total_movements' => count($movements),
            'income' => [
                'count' => $incomeCount,
                'total' => $totalIncome
            ],
            'expenses' => [
                'count' => $expenseCount,
                'total' => $totalExpenses
            ],
            'current_balance' => $totalIncome - $totalExpenses,
            'net_flow' => $totalIncome - $totalExpenses
        ];
    }


    private function getActiveFilters() {
        return [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'type' => $_GET['type'] ?? null
        ];
    }


    private function validateMovementCreationInput(array $input) {
        // Check required fields
        $requiredFields = ['client_id', 'type', 'amount', 'description'];
        $missing = [];

        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || $input[$field] === '' || $input[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $this->apiError(
                'Missing required fields: ' . implode(', ', $missing),
                400,
                ['required_fields' => $requiredFields, 'missing_fields' => $missing]
            );
        }

        if (!in_array($input['type'], ['income', 'expense'])) {
            $this->apiError(
                "Type must be 'income' or 'expense'",
                400,
                ['provided_type' => $input['type'], 'valid_types' => ['income', 'expense']]
            );
        }

        if (!is_numeric($input['client_id']) || $input['client_id'] <= 0) {
            $this->apiError('client_id must be a positive integer', 400);
        }

        if (!is_numeric($input['amount'])) {
            $this->apiError('amount must be a number', 400);
        }

        if (strlen(trim($input['description'])) < 3) {
            $this->apiError('description must be at least 3 characters long', 400);
        }

        if (strlen(trim($input['description'])) > 500) {
            $this->apiError('description cannot exceed 500 characters', 400);
        }
    }
}
