<?php

namespace App\Services;

use App\Models\Aksic;
use App\Models\AksicBusinessCategory;
use App\Models\AksicRule;
use App\Models\Branch;
use App\Models\District;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AksicExcelService
{
    /**
     * @var array<string, string>
     */
    private const COLUMNS = [
        'application_no' => 'Application No',
        'cnic' => 'CNIC',
        'name' => 'Applicant Name',
        'father_name' => 'Father Name',
        'phone' => 'Phone',
        'business_name' => 'Business Name',
        'business_type' => 'Business Type',
        'quota' => 'Quota',
        'gender' => 'Gender',
        'business_category_id' => 'Business Category',
        'district_id' => 'District',
        'branch_id' => 'Branch',
        'principal_amount' => 'Principal Amount',
        'tenure' => 'Tenure',
        'disbursement_date' => 'Disbursement Date',
        'kibor_rate' => 'KIBOR Rate',
        'spread_rate' => 'Spread Rate',
        'total_rate' => 'Total Rate',
        'consent_entry' => 'Consent Entry',
        'consent_date' => 'Consent Date',
        'liquid_security' => 'Liquid Security',
        'personal_guarantees' => 'Personal Guarantees',
    ];

    /**
     * @return array{path: string, filename: string}
     */
    public function createTemplate(): array
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('AKSIC Import');

        foreach (array_values(self::COLUMNS) as $index => $heading) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column.'1', $heading);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $sheet->fromArray($this->sampleRow(), null, 'A2');
        $sheet->getStyle('A1:V1')->getFont()->setBold(true);
        $sheet->getStyle('A1:V1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
        $sheet->freezePane('A2');

        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('Lists');

        $lists = [
            'A' => ['Yes', 'No'],
            'B' => ['Existing', 'New'],
            'C' => ['Male', 'Female', 'Disabled', 'Special Person', 'Transgender'],
            'D' => ['Male', 'Female'],
            'E' => $this->categoryOptions(),
            'F' => $this->districtOptions(),
            'G' => $this->branchOptions(),
        ];

        foreach ($lists as $column => $values) {
            foreach (array_values($values) as $row => $value) {
                $dataSheet->setCellValue($column.($row + 1), $value);
            }
        }

        $dataSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $this->addListValidation($sheet, 'G2:G500', 'Lists!$B$1:$B$2');
        $this->addListValidation($sheet, 'H2:H500', 'Lists!$C$1:$C$5');
        $this->addListValidation($sheet, 'I2:I500', 'Lists!$D$1:$D$2');
        $this->addListValidation($sheet, 'J2:J500', 'Lists!$E$1:$E$'.max(1, count($lists['E'])));
        $this->addListValidation($sheet, 'K2:K500', 'Lists!$F$1:$F$'.max(1, count($lists['F'])));
        $this->addListValidation($sheet, 'L2:L500', 'Lists!$G$1:$G$'.max(1, count($lists['G'])));
        $this->addListValidation($sheet, 'S2:S500', 'Lists!$A$1:$A$2');

        $filename = 'aksic_import_template.xlsx';
        $path = storage_path('app/'.$filename);
        (new Xlsx($spreadsheet))->save($path);

        return ['path' => $path, 'filename' => $filename];
    }

    /**
     * @return array{imported: int, skipped: int, errors: array<int, string>}
     */
    public function import(UploadedFile $file): array
    {
        $reader = new XlsxReader;
        $spreadsheet = $reader->load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $errors = [];
        $imported = 0;
        $skipped = 0;
        $seenApplicationNumbers = [];
        $seenCnics = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $data = $this->rowData($sheet, $row);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $applicationNo = trim((string) ($data['application_no'] ?? ''));
            $cnic = trim((string) ($data['cnic'] ?? ''));

            if (isset($seenApplicationNumbers[$applicationNo]) || isset($seenCnics[$cnic])) {
                $errors[] = "Row {$row}: duplicate application no or CNIC inside import file.";
                $skipped++;

                continue;
            }

            $seenApplicationNumbers[$applicationNo] = true;
            $seenCnics[$cnic] = true;

            if (Aksic::query()->where('application_no', $applicationNo)->orWhere('cnic', $cnic)->exists()) {
                $errors[] = "Row {$row}: skipped because application no or CNIC already exists.";
                $skipped++;

                continue;
            }

            $payload = $this->normalizeRow($data);
            $validator = Validator::make($payload, $this->rules());

            if ($validator->fails()) {
                $errors[] = "Row {$row}: ".$validator->errors()->first();
                $skipped++;

                continue;
            }

            $ruleId = AksicRule::query()
                ->where('district_id', $payload['district_id'])
                ->where('is_active', true)
                ->value('id');

            if (! $ruleId) {
                $errors[] = "Row {$row}: no active AKSIC rule exists for selected district.";
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($payload, $ruleId): void {
                $payload['aksic_rule_id'] = $ruleId;
                $payload['status'] = 'Pending';
                $payload['total_interest'] = null;
                $payload['gender'] = $this->resolveGender($payload);
                $payload['total_rate'] = bcadd((string) $payload['kibor_rate'], (string) $payload['spread_rate'], 2);

                Aksic::create($payload);
            });

            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    /**
     * @return array<int, string|int|float>
     */
    private function sampleRow(): array
    {
        return [
            'AKSIC-SAMPLE-001',
            '12345-1234567-1',
            'Sample Applicant',
            'Sample Father',
            '03001234567',
            'Sample Business',
            'Existing',
            'Male',
            '',
            $this->categoryOptions()[0] ?? '',
            $this->districtOptions()[0] ?? '',
            $this->branchOptions()[0] ?? '',
            1000000,
            60,
            now()->toDateString(),
            12,
            2.04,
            14.04,
            'No',
            '',
            '',
            '',
        ];
    }

    private function addListValidation($sheet, string $range, string $formula): void
    {
        foreach ($sheet->rangeToArray($range, null, true, true, true) as $row => $columns) {
            foreach (array_keys($columns) as $column) {
                $validation = $sheet->getCell($column.$row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1($formula);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function categoryOptions(): array
    {
        return AksicBusinessCategory::query()
            ->where('parent_id', 0)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (AksicBusinessCategory $category): string => $category->name)
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function districtOptions(): array
    {
        return District::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (District $district): string => $district->name)
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function branchOptions(): array
    {
        return Branch::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(fn (Branch $branch): string => $branch->code)
            ->filter(fn (string $code): bool => $code !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function rowData($sheet, int $row): array
    {
        $data = [];

        foreach (array_keys(self::COLUMNS) as $index => $key) {
            $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($index + 1).$row);
            $data[$key] = $cell->getValue();
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isEmptyRow(array $data): bool
    {
        return collect($data)->filter(fn ($value): bool => trim((string) $value) !== '')->isEmpty();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeRow(array $data): array
    {
        return [
            'application_no' => trim((string) $data['application_no']),
            'cnic' => trim((string) $data['cnic']),
            'name' => trim((string) $data['name']),
            'father_name' => trim((string) $data['father_name']),
            'phone' => $this->nullableString($data['phone']),
            'business_name' => $this->nullableString($data['business_name']),
            'business_type' => trim((string) $data['business_type']),
            'is_startup_business' => trim((string) $data['business_type']) === 'New',
            'quota' => trim((string) $data['quota']),
            'gender' => $this->nullableString($data['gender']),
            'business_category_id' => $this->categoryId($data['business_category_id']),
            'business_sub_category_id' => null,
            'district_id' => $this->districtId($data['district_id']),
            'branch_id' => $this->branchId($data['branch_id']),
            'principal_amount' => $data['principal_amount'],
            'tenure' => $data['tenure'],
            'disbursement_date' => $this->dateValue($data['disbursement_date']),
            'site_visit_completed' => false,
            'kibor_rate' => $data['kibor_rate'],
            'spread_rate' => $data['spread_rate'],
            'total_rate' => $data['total_rate'],
            'consent_entry' => $this->nullableString($data['consent_entry']),
            'consent_date' => $this->dateValue($data['consent_date']),
            'liquid_security' => $this->nullableString($data['liquid_security']),
            'personal_guarantees' => $this->nullableString($data['personal_guarantees']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'application_no' => ['required', 'string', 'max:255', 'unique:aksics,application_no'],
            'cnic' => ['required', 'string', 'max:255', 'unique:aksics,cnic'],
            'name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['required', Rule::in(['Existing', 'New'])],
            'is_startup_business' => ['required', 'boolean'],
            'quota' => ['required', Rule::in(['Male', 'Female', 'Disabled', 'Special Person', 'Transgender'])],
            'gender' => ['nullable', 'required_if:quota,Disabled,Special Person', Rule::in(['Male', 'Female'])],
            'business_category_id' => ['nullable', 'integer', 'exists:aksic_business_categories,id'],
            'business_sub_category_id' => ['nullable', 'integer', 'exists:aksic_business_categories,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'principal_amount' => ['required', 'numeric', 'gt:0'],
            'tenure' => ['required', 'integer', 'min:1', 'max:600'],
            'disbursement_date' => ['required', 'date'],
            'kibor_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'spread_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'total_rate' => ['required', 'numeric', 'min:0', 'max:200'],
            'consent_entry' => ['nullable', Rule::in(['Yes', 'No'])],
            'consent_date' => ['nullable', 'date'],
            'liquid_security' => ['nullable', 'string'],
            'personal_guarantees' => ['nullable', 'string'],
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function categoryId(mixed $value): ?int
    {
        $name = trim((string) $value);

        if ($name === '') {
            return null;
        }

        return AksicBusinessCategory::query()
            ->where('parent_id', 0)
            ->where('name', $name)
            ->value('id');
    }

    private function districtId(mixed $value): ?int
    {
        $name = trim((string) $value);

        if ($name === '') {
            return null;
        }

        return District::query()
            ->where('name', $name)
            ->value('id');
    }

    private function branchId(mixed $value): ?int
    {
        $code = trim((string) $value);

        if ($code === '') {
            return null;
        }

        return Branch::query()
            ->where('code', $code)
            ->value('id');
    }

    private function dateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        $timestamp = strtotime((string) $value);

        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveGender(array $data): ?string
    {
        if (($data['quota'] ?? null) === 'Disabled' || ($data['quota'] ?? null) === 'Special Person') {
            return $data['gender'] ? (string) $data['gender'] : null;
        }

        return (string) $data['quota'];
    }
}
