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
        // Authenticate API request
        $userType = $this->authenticateAPI();

        // Validate HTTP method
        $this->validateAPIMethod(['GET']);

        // Validate required parameters
        $this->validateRequiredParams(['client_id']);

        $clientId = (int) $_GET['client_id'];

        try {
            // Validate client exists and get client data
            $clientModel = new Client();
            $client = $this->validateRecordExists($clientModel, $clientId, 'Client');

            // Get movements for this client
            $movementModel = new Movement();
            $movements = $movementModel->getByClientId($clientId);

            // Apply optional filters
            $filteredMovements = $this->applyMovementFilters($movements);

            // Calculate financial summary
            $summary = $this->calculateFinancialSummary($filteredMovements);

            // Log API activity
            $this->logAPIActivity('get_user_movements', [
                'client_id' => $clientId,
                'user_type' => $userType,
                'movement_count' => count($filteredMovements),
                'filters_applied' => $this->getActiveFilters()
            ]);

            // Return successful response
            $this->apiSuccess([
                'user' => [
                    'id' => $client['id'],
                    'name' => $client['name'],
                    'email' => $client['email']
                ],
                'movements' => array_values($filteredMovements), // Re-index array after filtering
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

    /**
     * Create a new movement for a user
     * POST /api/movements
     */
    public function createMovement() {
        // Authenticate API request
        $userType = $this->authenticateAPI();

        // Validate HTTP method
        $this->validateAPIMethod(['POST']);

        // Get and validate JSON input
        $input = $this->getJSONInput();
        $this->validateMovementCreationInput($input);

        try {
            // Validate client exists
            $clientModel = new Client();
            $client = $this->validateRecordExists($clientModel, $input['client_id'], 'Client');

            // Prepare movement data
            $movementData = [
                'client_id' => (int) $input['client_id'],
                'type' => $input['type'],
                'amount' => $this->validatePositiveNumber($input['amount'], 'amount'),
                'description' => trim($input['description']),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Create movement
            $movementModel = new Movement();
            $movementId = $movementModel->create($movementData);

            // Get the created movement with client info
            $createdMovement = $movementModel->find($movementId);

            // Log successful creation
            $this->logAPIActivity('create_movement_success', [
                'movement_id' => $movementId,
                'client_id' => $input['client_id'],
                'type' => $input['type'],
                'amount' => $movementData['amount'],
                'user_type' => $userType
            ]);

            // Return success response with created movement
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

    /**
     * Apply optional filters to movements array
     * @param array $movements
     * @return array Filtered movements
     */
    private function applyMovementFilters(array $movements) {
        $startDate = $this->validateDateFormat($_GET['start_date'] ?? null, 'start_date');
        $endDate = $this->validateDateFormat($_GET['end_date'] ?? null, 'end_date');
        $type = $_GET['type'] ?? null;

        // Validate type filter if provided
        if ($type && !in_array($type, ['income', 'expense', 'earning'])) {
            $this->apiError("Type filter must be 'income', 'earning', or 'expense'", 400);
        }

        // If no filters, return original movements
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

    /**
     * Calculate financial summary for movements
     * @param array $movements
     * @return array Summary data
     */
    private function calculateFinancialSummary(array $movements) {
        $totalIncome = 0;
        $totalExpenses = 0;
        $incomeCount = 0;
        $expenseCount = 0;

        foreach ($movements as $movement) {
            $amount = (float) $movement['amount'];

            // Handle both 'income'/'earning' and 'expense' types
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

    /**
     * Get currently active filters for response
     * @return array Active filters
     */
    private function getActiveFilters() {
        return [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'type' => $_GET['type'] ?? null
        ];
    }

    /**
     * Validate input for movement creation
     * @param array $input
     */
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

        // Validate type
        if (!in_array($input['type'], ['income', 'expense'])) {
            $this->apiError(
                "Type must be 'income' or 'expense'",
                400,
                ['provided_type' => $input['type'], 'valid_types' => ['income', 'expense']]
            );
        }

        // Validate client_id is numeric
        if (!is_numeric($input['client_id']) || $input['client_id'] <= 0) {
            $this->apiError('client_id must be a positive integer', 400);
        }

        // Validate amount (will be validated again in validatePositiveNumber)
        if (!is_numeric($input['amount'])) {
            $this->apiError('amount must be a number', 400);
        }

        // Validate description length
        if (strlen(trim($input['description'])) < 3) {
            $this->apiError('description must be at least 3 characters long', 400);
        }

        if (strlen(trim($input['description'])) > 500) {
            $this->apiError('description cannot exceed 500 characters', 400);
        }
    }
}
