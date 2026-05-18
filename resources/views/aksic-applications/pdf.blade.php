<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AKSIC Application - {{ $aksicApplication->application_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14px;
            color: #374151;
            font-weight: normal;
        }

        .header .app-no {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table th,
        table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background-color: #f3f4f6;
            font-weight: 600;
            color: #374151;
            width: 25%;
        }

        table td {
            background-color: #ffffff;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
        }

        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-progress {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .two-column {
            width: 100%;
        }

        .two-column td {
            width: 50%;
            vertical-align: top;
            border: none;
            padding: 0;
        }

        .two-column td:first-child {
            padding-right: 10px;
        }

        .two-column td:last-child {
            padding-left: 10px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
        }

        .amount {
            font-weight: bold;
            color: #059669;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>AKSIC Application Form</h1>
        <h2>Bank of Azad Jammu & Kashmir</h2>
        <div class="app-no">Application No: {{ $aksicApplication->application_no }}</div>
    </div>

    <!-- Personal Information -->
    <div class="section">
        <div class="section-title">Personal Information</div>
        <table>
            <tr>
                <th>Application No</th>
                <td>{{ $aksicApplication->application_no }}</td>
                <th>Applicant ID</th>
                <td>{{ $aksicApplication->applicant_id }}</td>
            </tr>
            <tr>
                <th>Full Name</th>
                <td>{{ $aksicApplication->name }}</td>
                <th>Father's Name</th>
                <td>{{ $aksicApplication->fatherName }}</td>
            </tr>
            <tr>
                <th>CNIC</th>
                <td>{{ $aksicApplication->cnic }}</td>
                <th>CNIC Issue Date</th>
                <td>{{ $aksicApplication->cnic_issue_date ? $aksicApplication->cnic_issue_date->format('d-M-Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td>{{ $aksicApplication->dob ? $aksicApplication->dob->format('d-M-Y') : 'N/A' }}</td>
                <th>Phone</th>
                <td>{{ $aksicApplication->phone }}</td>
            </tr>
            <tr>
                <th>Permanent Address</th>
                <td colspan="3">{{ $aksicApplication->permanentAddress }}</td>
            </tr>
        </table>
    </div>

    <!-- Business Information -->
    <div class="section">
        <div class="section-title">Business Information</div>
        <table>
            <tr>
                <th>Business Name</th>
                <td>{{ $aksicApplication->businessName }}</td>
                <th>Business Type</th>
                <td>{{ $aksicApplication->businessType }}</td>
            </tr>
            <tr>
                <th>Business Address</th>
                <td colspan="3">{{ $aksicApplication->businessAddress }}</td>
            </tr>
            <tr>
                <th>Business Category</th>
                <td>{{ $aksicApplication->businessCategory?->name ?? 'Category ID: ' . $aksicApplication->business_category_id }}</td>
                <th>Sub Category</th>
                <td>{{ $aksicApplication->businessSubCategory?->name ?? 'Sub-Category ID: ' . $aksicApplication->business_sub_category_id }}</td>
            </tr>
            <tr>
                <th>Quota</th>
                <td>{{ $aksicApplication->quota }}</td>
                <th>Tier</th>
                <td>{{ $aksicApplication->tier }}</td>
            </tr>
        </table>
    </div>

    <!-- Financial Details -->
    <div class="section">
        <div class="section-title">Financial Details</div>
        <table>
            <tr>
                <th>Amount</th>
                <td><span class="amount">Rs. {{ number_format($aksicApplication->amount, 2) }}</span></td>
                <th>Challan Fee</th>
                <td>Rs. {{ number_format($aksicApplication->challan_fee, 2) }}</td>
            </tr>
            <tr>
                <th>Fee Status</th>
                <td>
                    <span class="status-badge {{ strtolower($aksicApplication->fee_status) === 'paid' ? 'status-paid' : 'status-pending' }}">
                        {{ $aksicApplication->fee_status }}
                    </span>
                </td>
                <th>Fee Branch Code</th>
                <td>{{ $aksicApplication->fee_branch_code }}</td>
            </tr>
        </table>
    </div>

    <!-- Application Details -->
    <div class="section">
        <div class="section-title">Application Details</div>
        <table>
            <tr>
                <th>Status</th>
                <td>
                    @php
                        $statusClass = 'status-pending';
                        if (in_array($aksicApplication->status, ['Approved', 'Final Approval', 'Disbursed'])) {
                            $statusClass = 'status-approved';
                        } elseif ($aksicApplication->status === 'Rejected') {
                            $statusClass = 'status-rejected';
                        } elseif (in_array($aksicApplication->status, ['In Progress', 'Credit Assessment', 'Document Verification'])) {
                            $statusClass = 'status-progress';
                        }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $aksicApplication->status }}</span>
                </td>
                <th>Bank Status</th>
                <td>{{ $aksicApplication->bank_status ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>District</th>
                <td>{{ $aksicApplication->district_name }}</td>
                <th>Tehsil</th>
                <td>{{ $aksicApplication->tehsil_name }}</td>
            </tr>
            <tr>
                <th>Chosen Branch ID</th>
                <td>{{ $aksicApplication->applicant_choosed_branch_id }}</td>
                <th>Chosen Branch Code</th>
                <td>{{ $aksicApplication->applicant_choosed_branch_code }}</td>
            </tr>
            <tr>
                <th>Challan Branch ID</th>
                <td>{{ $aksicApplication->challan_branch_id }}</td>
                <th>Challan Branch Code</th>
                <td>{{ $aksicApplication->challan_branch_code }}</td>
            </tr>
            <tr>
                <th>Created At</th>
                <td colspan="3">{{ $aksicApplication->created_at->format('d-M-Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <!-- Education Details -->
    @if($aksicApplication->educations && $aksicApplication->educations->count() > 0)
    <div class="section">
        <div class="section-title">Education Details</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Education Level</th>
                    <th style="width: 20%;">Degree Title</th>
                    <th style="width: 30%;">Institute</th>
                    <th style="width: 15%;">Passing Year</th>
                    <th style="width: 15%;">Grade/Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aksicApplication->educations as $education)
                <tr>
                    <td>{{ $education->education_level ?? 'N/A' }}</td>
                    <td>{{ $education->degree_title ?? 'N/A' }}</td>
                    <td>{{ $education->institute ?? 'N/A' }}</td>
                    <td>{{ $education->passing_year ?? 'N/A' }}</td>
                    <td>{{ $education->grade_or_percentage ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Status History -->
    @if($aksicApplication->statusLogs && $aksicApplication->statusLogs->count() > 0)
    <div class="section">
        <div class="section-title">Status History</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Old Status</th>
                    <th style="width: 15%;">New Status</th>
                    <th style="width: 12%;">Changed By</th>
                    <th style="width: 35%;">Remarks</th>
                    <th style="width: 23%;">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aksicApplication->statusLogs->sortByDesc('created_at') as $log)
                <tr>
                    <td>{{ $log->old_status }}</td>
                    <td>{{ $log->new_status }}</td>
                    <td>{{ $log->changed_by_type }} ({{ $log->changed_by_id }})</td>
                    <td>{{ $log->remarks ?? 'N/A' }}</td>
                    <td>{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Generated on: {{ now()->format('d-M-Y H:i:s') }} | Bank of Azad Jammu & Kashmir - AKSIC Application System</p>
        <p>This is a computer-generated document. For official use only.</p>
    </div>
</body>
</html>
