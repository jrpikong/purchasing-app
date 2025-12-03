<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Request Approval - {{ $pr->pr_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Purchase Request Approval</h1>
                    <p class="mt-1 text-sm text-gray-600">Please review and approve or reject this purchase request</p>
                </div>
                <div class="flex items-center space-x-2">
                        <span class="px-4 py-2 rounded-full text-sm font-semibold
                            @if($pr->status === 'waiting_approval') bg-yellow-100 text-yellow-800
                            @elseif($pr->status === 'approved') bg-green-100 text-green-800
                            @elseif($pr->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ strtoupper(str_replace('_', ' ', $pr->status)) }}
                        </span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <p class="text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <!-- PR Details -->
        <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Request Information</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">PR Number</label>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $pr->pr_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priority</label>
                        <span class="mt-1 inline-block px-3 py-1 rounded-full text-sm font-semibold
                                @if($pr->priority === 'urgent') bg-red-100 text-red-800
                                @elseif($pr->priority === 'high') bg-orange-100 text-orange-800
                                @elseif($pr->priority === 'medium') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ strtoupper($pr->priority) }}
                            </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Requested By</label>
                        <p class="mt-1 text-gray-900">{{ $pr->requester->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <p class="mt-1 text-gray-900">{{ $pr->department->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Request Date</label>
                        <p class="mt-1 text-gray-900">{{ $pr->request_date->format('d F Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Required Date</label>
                        <p class="mt-1 text-gray-900">{{ $pr->required_date->format('d F Y') }}</p>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700">Purpose of Purchase</label>
                    <p class="mt-2 text-gray-900 whitespace-pre-wrap">{{ $pr->purpose }}</p>
                </div>

                @if($pr->total_amount)
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                        <p class="mt-1 text-2xl font-bold text-gray-900">Rp {{ number_format($pr->total_amount, 0, ',', '.') }}</p>
                    </div>
                @endif

                @if($pr->preferred_vendor_name)
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700">Preferred Vendor</label>
                        <p class="mt-1 text-gray-900">{{ $pr->preferred_vendor_name }}</p>
                        @if($pr->preferred_vendor_reason)
                            <p class="mt-2 text-sm text-gray-600">{{ $pr->preferred_vendor_reason }}</p>
                        @endif
                    </div>
                @endif

                @if($pr->vendor_marketplace_link_1 || $pr->vendor_marketplace_link_2)
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vendor Links</label>
                        @if($pr->vendor_marketplace_link_1)
                            <a href="{{ $pr->vendor_marketplace_link_1 }}" target="_blank" class="block text-blue-600 hover:text-blue-800 underline mb-1">
                                {{ $pr->vendor_marketplace_link_1 }}
                            </a>
                        @endif
                        @if($pr->vendor_marketplace_link_2)
                            <a href="{{ $pr->vendor_marketplace_link_2 }}" target="_blank" class="block text-blue-600 hover:text-blue-800 underline">
                                {{ $pr->vendor_marketplace_link_2 }}
                            </a>
                        @endif
                    </div>
                @endif

                @if($pr->notes)
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
                        <p class="mt-2 text-gray-900 whitespace-pre-wrap">{{ $pr->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Attachments -->
        @if($pr->attachments->count() > 0)
            <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
                </div>
                <div class="p-6">
                    <ul class="space-y-2">
                        @foreach($pr->attachments as $attachment)
                            <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $attachment->filename }}</p>
                                        <p class="text-xs text-gray-500">{{ $attachment->getHumanSize() }}</p>
                                    </div>
                                </div>
                                <a href="{{ $attachment->getUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Download
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Approval Form -->
        @if($pr->status === 'waiting_approval' && $pr->current_approver_id === auth()->id())
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Your Decision</h2>
                </div>
                <form action="{{ route('pr.approval.process', $pr->id) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="action" value="approve" class="mr-2" required>
                                <span class="text-sm text-gray-900">Approve</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="action" value="reject" class="mr-2" required>
                                <span class="text-sm text-gray-900">Reject</span>
                            </label>
                        </div>
                    </div>

                    <div id="rejection-reason-field" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                        <textarea name="rejection_reason" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Comment (Optional)</label>
                        <textarea name="comment" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Add your comment here..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                            Submit Decision
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <p class="text-yellow-700">This purchase request has already been processed or you are not authorized to approve it.</p>
            </div>
        @endif

        <!-- Approval History -->
        @if($pr->approvalHistories->count() > 0)
            <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Approval History</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($pr->approvalHistories as $history)
                            <div class="flex items-start space-x-3 pb-4 border-b border-gray-200 last:border-0">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">{{ $history->actor->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $history->acted_at->format('d M Y H:i') }}</p>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold bg-{{ $history->getBadgeColor() }}-100 text-{{ $history->getBadgeColor() }}-800">
                                        {{ $history->getActionLabel() }}
                                    </span>
                                    </p>
                                    @if($history->comment)
                                        <p class="mt-2 text-sm text-gray-700">{{ $history->comment }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    // Show/hide rejection reason field
    const actionRadios = document.querySelectorAll('input[name="action"]');
    const rejectionField = document.getElementById('rejection-reason-field');
    const rejectionTextarea = rejectionField.querySelector('textarea');

    actionRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'reject') {
                rejectionField.style.display = 'block';
                rejectionTextarea.required = true;
            } else {
                rejectionField.style.display = 'none';
                rejectionTextarea.required = false;
            }
        });
    });
</script>
</body>
</html>
