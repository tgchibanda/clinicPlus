<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\InsurancePlan;
use App\Models\InsuranceSubscription;
use App\Models\InsuranceDependent;
use App\Models\InsurancePayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class InsurancePublicController extends Controller
{
    public function verifyPatient(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3',
            'phone' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $name = $request->input('name');
        $phone = $request->input('phone');

        // Search for patients matching phone and name
        $matches = Patient::where('phone', 'LIKE', '%' . $phone . '%')
            ->where(function($query) use ($name) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $name . '%'])
                      ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ['%' . $name . '%']);
            })
            ->get(['id', 'first_name', 'last_name', 'phone', 'date_of_birth']);

        if ($matches->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No patient found with the provided name and phone number.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'matches' => $matches->map(fn($p) => [
                'id' => $p->id,
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'phone' => $p->phone,
                'date_of_birth' => $p->date_of_birth,
            ])
        ]);
    }

    public function publicSignup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'owner_plan_id' => 'required|exists:insurance_plans,id',
            'dependents' => 'nullable|array',
            'dependents.*.first_name' => 'required|string',
            'dependents.*.last_name' => 'required|string',
            'dependents.*.date_of_birth' => 'required|date|before:today',
            'dependents.*.gender' => 'required|string|in:male,female,other',
            'dependents.*.plan_id' => 'required|exists:insurance_plans,id',
            'dependents.*.relationship' => 'nullable|string',
            'accept_declaration' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $patient = Patient::findOrFail($request->patient_id);
            $policy_number = $this->generatePolicyNumber();
            // Create subscription
            $subscription = InsuranceSubscription::create([
                'patient_id' => $patient->id,
                'plan_id' => $request->owner_plan_id,
                'status' => 'pending',
                'started_at' => now(),
                'next_due_date' => now()->addMonth()->startOfDay(),
                'policy_number' => $policy_number,
                'last_notification' => '',
            ]);

            // Add dependents
            if ($request->has('dependents') && is_array($request->dependents)) {
                foreach ($request->dependents as $dependentData) {
                    InsuranceDependent::create([
                        'subscription_id' => $subscription->id,
                        'first_name' => $dependentData['first_name'],
                        'last_name' => $dependentData['last_name'],
                        'date_of_birth' => $dependentData['date_of_birth'],
                        'gender' => $dependentData['gender'],
                        'plan_id' => $dependentData['plan_id'],
                        'relationship' => $dependentData['relationship'] ?? null,
                    ]);
                }
            }

            // Log event
            $subscription->logEvent('subscription_created', [
                'patient_id' => $patient->id,
                'dependents_count' => count($request->dependents ?? []),
            ]);

            DB::commit();

            $monthlyTotal = $subscription->calculateMonthlyTotal();

            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->id,
                'monthly_total' => $monthlyTotal,
                'next_due_date' => $subscription->next_due_date->format('Y-m-d'),
                'message' => 'Subscription created successfully. Please proceed with payment.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generatePolicyNumber(string $clinicCode = 'N'): string
    {
        $year = now()->year;
        $last = InsuranceSubscription::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last && $last->policy_number) {
            // Try to parse trailing 4 digits
            $tail = substr($last->policy_number, -4);
            if (ctype_digit($tail)) {
                $seq = (int)$tail + 1;
            }
        }

        return sprintf('%s%s%04d', strtoupper($clinicCode), $year, $seq);
    }

    public function getPlans(): JsonResponse
    {
        $plans = InsurancePlan::where('active', true)->get();
        
        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);
    }
}