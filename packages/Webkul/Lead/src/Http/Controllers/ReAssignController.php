<?php

namespace Webkul\Lead\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\Lead\Repositories\LeadRepository;

class ReAssignController extends Controller
{
    public function __construct(protected LeadRepository $leadRepository)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'leads'          => ['required', 'array', 'min:1'],
            'leads.*'        => ['integer', 'exists:leads,id'],
            'user_id'        => ['required', 'integer', 'exists:users,id'],
            'type'           => ['nullable', 'in:fresh,cold_call'],
            'duplicateFresh' => ['sometimes', 'boolean'],
            'sameStage'      => ['sometimes', 'boolean'],
            'asSalesman'     => ['sometimes', 'boolean'],
            'clearHistory'   => ['sometimes', 'boolean'],
        ]);

        $updated = 0;

        foreach ($data['leads'] as $leadId) {
            $lead = $this->leadRepository->find($leadId);

            if (! $lead) {
                continue;
            }

            $lead->user_id = $data['user_id'];

            if (! empty($data['type'])) {
                // TODO: Persist the requested lead type update once the project schema supports it.
            }

            if (! empty($data['duplicateFresh'])) {
                // TODO: Duplicate the lead as fresh when the duplication workflow is available.
            }

            if (! empty($data['sameStage'])) {
                // TODO: Lock or sync stages between owners when the pipeline policy is defined.
            }

            if (! empty($data['asSalesman'])) {
                // TODO: Assign salesman specific relations once the salesman module is present.
            }

            if (! empty($data['clearHistory'])) {
                // TODO: Clear activity history after confirming the audit requirements.
            }

            $lead->save();
            $updated++;
        }

        return response()->json([
            'updated' => $updated,
            'message' => $updated
                ? __('Leads reassigned successfully.')
                : __('No leads were updated.'),
        ]);
    }
}
