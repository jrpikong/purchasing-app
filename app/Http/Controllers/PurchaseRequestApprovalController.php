<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseRequestApprovalController extends Controller
{
    public function __construct(
        private PurchaseRequestApprovalService $approvalService
    ) {}

    /**
     * Handle approval link from email
     */
    public function approvalLink(Request $request, $id)
    {
        // Verify signature
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired approval link.');
        }

        // Find PR
        $pr = PurchaseRequest::findOrFail($id);

        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('intended_approval', [
                    'pr_id' => $id,
                    'approver_id' => $request->approver_id,
                    'token' => $request->token,
                ]);
        }

        // Verify user is the designated approver
        $approverId = $request->approver_id;
        if (Auth::id() != $approverId) {
            abort(403, 'You are not authorized to approve this request.');
        }

        // Validate token
        if (!$this->approvalService->validateToken($pr, $request->token)) {
            abort(403, 'Invalid or expired approval token.');
        }

        // Redirect to approval page with token
        return redirect()->route('pr.approval.page', [
            'id' => $id,
            'token' => $request->token,
        ]);
    }

    /**
     * Show approval page
     */
    public function approvalPage(Request $request, $id)
    {
        $pr = PurchaseRequest::with([
            'requester',
            'department',
            'preferredVendor',
            'attachments',
            'approvalHistories.actor'
        ])->findOrFail($id);

        // Verify user is the designated approver
        if (Auth::id() != $pr->current_approver_id) {
            abort(403, 'You are not authorized to approve this request.');
        }

        // Validate token
        if (!$this->approvalService->validateToken($pr, $request->token)) {
            abort(403, 'Invalid or expired approval token.');
        }

        return view('purchase-requests.approval', [
            'pr' => $pr,
            'token' => $request->token,
        ]);
    }

    /**
     * Process approval
     */
    public function processApproval(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'token' => 'required|string',
            'comment' => 'nullable|string|max:1000',
            'rejection_reason' => 'required_if:action,reject|string|max:1000',
        ]);

        $pr = PurchaseRequest::findOrFail($id);
        $user = Auth::user();

        // Verify user is the designated approver
        if ($user->id != $pr->current_approver_id) {
            return back()->with('error', 'You are not authorized to approve this request.');
        }

        // Validate token
        if (!$this->approvalService->validateToken($pr, $request->token)) {
            return back()->with('error', 'Invalid or expired approval token.');
        }

        try {
            if ($request->action === 'approve') {
                $this->approvalService->approve($pr, $user, $request->comment);

                return redirect()->route('pr.approval.success')
                    ->with('success', "Purchase Request {$pr->pr_number} has been approved successfully.");
            } else {
                $this->approvalService->reject($pr, $user, $request->rejection_reason, $request->comment);

                return redirect()->route('pr.approval.success')
                    ->with('success', "Purchase Request {$pr->pr_number} has been rejected.");
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show success page
     */
    public function approvalSuccess()
    {
        return view('purchase-requests.approval-success');
    }
}
