<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerCategory;
use App\Models\DocumentRequirement;
use App\Models\DocumentType;
use Illuminate\Database\Seeder;

/**
 * AOF #27 -- "Nature of Account -> Minimum Documentation to be Obtained".
 *
 * document_types        : har document ka master
 * document_requirements : kaunsi customer_category ke liye kaunsa document
 *
 * Yeh mapping bilkul dono PDFs ki documentation tables se li gayi hai:
 *   AOF-Individual p.12  -> Individuals, Sole Proprietorship, Minor, PEP
 *   AOF-Entity p.12-14   -> Partnership, Limited Companies, Clubs/Societies/
 *                           Associations/Trusts, Government & Armed Forces,
 *                           NGOs/NPOs/Charities, Agents, Executors, PEP,
 *                           Foreign Missions
 */
class DocumentReferenceSeeder extends Seeder
{
    public function run(): void
    {

        // code => [name, requires_attestation, sort]
        $types = [
            'ID_DOC_COPY' => ['Photocopy of valid identity document (CNIC/SNIC/NICOP/POC/ARC/PoR/Passport)', true, 1],
            'FORM_B_CRC' => ['Form-B / Birth Certificate / Student ID card of minor', true, 2],
            'GUARDIAN_ID' => ['Photocopy of identity document of the guardian of the minor', true, 3],
            'SOURCE_OF_FUNDS_DECL' => ['Declaration for source of funds', false, 4],
            'PROOF_OF_INCOME' => ['Proof of income (salary slip / utility bill / rent agreement / aqama etc.)', false, 5],
            'SALARY_SLIP_APPOINTMENT' => ['Salary slip, copy of appointment letter or letter from employer', false, 6],
            'LANDHOLDING_EVIDENCE' => ['Landholding record / Passbook / Form 7 / Form 2 / Dhal receipt / Khasra-Khatooni / Land Agreement', false, 7],
            'STOCK_TRADING_SHEET' => ['Trading sheet of stock investor', false, 8],
            'PHYSICAL_VERIFICATION' => ['Physical verification report', false, 9],
            'SS_UNDERTAKING' => ['Undertaking for Acceptance of Different Specimen Signatures for Account Operation', false, 10],
            'REG_CERTIFICATE' => ['Registration certificate for registered concerns / Certificate of Incorporation', true, 11],
            'SALES_TAX_NTN' => ['Sales tax registration or NTN', false, 12],
            'TRADE_BODY_MEMBERSHIP' => ['Certificate or proof of membership of trade bodies', false, 13],
            'SOLE_PROP_DECLARATION' => ['Declaration of sole proprietorship on business letter head including Source of Fund', false, 14],
            'ACCOUNT_REQUISITION_LETTERHEAD' => ['Account opening requisition on business letter head', false, 15],
            'UBO_DECLARATION' => ['Ultimate Beneficial Owner (UBO) Declaration Form', false, 16],
            'RENTAL_OWNERSHIP_DOC' => ['Rental/Ownership document such as Rental Agreement or Lease document of business place', false, 17],
            'MEMORANDUM_ARTICLES' => ['Memorandum & Article of Association / By-laws / Rules and Regulations', true, 18],
            'BOARD_RESOLUTION' => ['Resolution of the Board of Directors/Trustees/Executive Committee for opening of account', false, 19],
            'LIST_OF_TRUSTEES' => ['List of trustees/members/governing body on letterhead', false, 20],
            'NPO_DECLARATION_FORM' => ['Declaration form for NPOs/NGOs/Charities, Trust, Clubs, Societies and Associations signed by Governing Body', false, 21],
            'LATEST_FINANCIALS' => ['Latest financial statement including audit report', false, 22],
            'EAD_APPROVAL' => ['Approval of EAD, Government of Pakistan (International NGO receiving funds from abroad)', false, 23],
            'DECLARATION_QUESTIONNAIRE' => ['Declaration statement / questionnaire', false, 24],
            'RUBBER_STAMP' => ['Rubber stamp along with secretary/Member signature affixed on all documents', false, 25],
            'CIF_ALL_SIGNATORIES' => ['CIF of all members and authorized signatories', false, 26],
            'SOURCE_OF_FUND_GOVERNING_BODY' => ['Source of Fund / proof of income of Governing Body/Board of Trustees/Controlling Persons', false, 27],
            'GOVT_AUTHORITY_LETTER' => ['Resolution/Authority letter from concerned department duly endorsed by Admin/Finance Division', false, 28],
            'FUND_ALLOCATION_LETTER' => ['Fund allocation letter issued by concerned authority', false, 29],
            'GAZETTE_NOTIFICATION' => ['Authenticated list of authorized individuals along with the gazette notification', false, 30],
            'POWER_OF_ATTORNEY' => ['Certified copy of Power of Attorney or Agency Agreement', true, 31],
            'LETTER_OF_ADMINISTRATION' => ['Certified copy of Letter of Administration or Probate', true, 32],
            'TAX_EXEMPTION_CERTIFICATE' => ['Tax exemption certificate', false, 33],
            'PEP_FORM' => ['PEP Form approved from Head Office', false, 34],
            'QA22_FORM' => ['QA-22 form (foreigner residing in Pakistan)', false, 35],
            'VERNACULAR_FORM' => ['Vernacular form on 100 rupees bond paper (signature other than English/Urdu)', false, 36],
            'PASSPORT_PHOTOS' => ['Two passport sized attested photographs (shaky/immature signature case)', true, 37],

            // AOF-Entity page 12 — Partnership / LLP / Limited Companies / Branch Office
            'PARTNERSHIP_DEED' => ['Attested copy of "Partnership Deed" duly signed by all partners of the firm', true, 38],
            'REGISTRAR_OF_FIRMS_CERT' => ['Attested copy of Registration Certificate with Registrar of Firms (unregistered partnership must be stated on the account opening form)', true, 39],
            'PARTNERS_AUTHORITY_LETTER' => ['Authority letter from all partners, in original, authorizing the person(s) to operate the firm\'s account', false, 40],
            'CHAMBER_MEMBERSHIP_CERT' => ['Membership Certificate — Chamber of Commerce "Where Applicable"', false, 41],
            'LLP_AGREEMENT' => ['Limited Liability Partnership Deed / Agreement', true, 42],
            'LLP_FORM_III' => ['LLP-Form-III (initial particulars of partners / designated partner in case of newly incorporated LLP)', true, 43],
            'LLP_FORM_V' => ['LLP-Form-V (change in partners / designated partner in case of already incorporated LLP)', true, 44],
            'CERT_OF_INCORPORATION' => ['Certificate of Incorporation', true, 45],
            'CERT_COMMENCEMENT_BUSINESS' => ['Certificate of commencement of business, wherever applicable', true, 46],
            'LIST_OF_DIRECTORS' => ['List of directors required on company letterhead', false, 47],
            'FORM_29' => ['Form-29 issued under Companies Ordinance 1984, or Annexure IV/INC form II issued under Companies Act 2017 ("Form-A/Form C") where applicable', true, 48],
            'SHAREHOLDER_VERIFICATION_10' => ['Identification and verification of natural person shareholders holding 10% or above stake in the entity', false, 49],
            'RISK_REGISTER_UBO' => ['Copy of Risk Register (Register of Beneficial Owners)', false, 50],
            'SHAREHOLDING_LETTERHEAD' => ['Shareholding of the company on company letterhead "Where Applicable"', false, 51],
            'FORM_II_FOREIGN_BRANCH' => ['Form II about particulars of directors, Principal Officer etc. for a newly registered branch or liaison office of a foreign company', true, 52],
            'FORM_III_FOREIGN_BRANCH' => ['Form III about change in directors, principal officers etc. in an already registered foreign company branch or liaison office', true, 53],
            'BOI_PERMISSION_LETTER' => ['Copy of permission letter from the relevant authority i.e. Board of Investment', true, 54],
            'PRINCIPAL_OFFICE_LETTER' => ['Letter from the Principal Office of the entity authorizing the person(s) to open and operate the account', false, 55],
            'TRUST_REGISTRATION_CERT' => ['Certificate of Registration / Instrument of Trust', true, 56],
            'FOREIGN_EXCHANGE_MANUAL' => ['As per Foreign Exchange manual for details on Resident / Non-Resident accounts', false, 57],
        ];

        foreach ($types as $code => [$name, $attest, $sort]) {
            DocumentType::updateOrCreate(['code' => $code], [
                'name' => $name, 'requires_attestation' => $attest,
                'sort_order' => $sort, 'is_active' => true,
            ]);
        }

        /* -------- category => documents mapping (form ki tables se) -------- */
        $map = [
            'INDIVIDUAL' => [
                ['ID_DOC_COPY', true, null],
                ['SOURCE_OF_FUNDS_DECL', false, 'profession = HOUSEWIFE'],
                ['LANDHOLDING_EVIDENCE', false, 'profession = AGRICULTURE'],
                ['PROOF_OF_INCOME', false, 'profession IN (HOUSEWIFE, PROF_STUDENT, UNEMPLOYED)'],
                ['SALARY_SLIP_APPOINTMENT', false, 'profession IN (GOVT_SERVICE, PRIVATE_SERVICE)'],
                ['STOCK_TRADING_SHEET', false, 'income_source = STOCK_INVESTMENT'],
                ['PHYSICAL_VERIFICATION', false, 'profession = SELF_EMPLOYED'],
                ['SS_UNDERTAKING', true, null],
                ['PASSPORT_PHOTOS', false, 'signature shaky/immature or photo account'],
                ['QA22_FORM', false, 'foreigner residing in Pakistan'],
                ['VERNACULAR_FORM', false, 'signature other than English/Urdu'],
            ],
            'JOINT' => [
                ['ID_DOC_COPY', true, 'CDD on all joint account holders as if each were an individual customer'],
                ['SS_UNDERTAKING', true, null],
            ],
            'SOLE_PROP' => [
                ['ID_DOC_COPY', true, null],
                ['REG_CERTIFICATE', true, 'registered concerns'],
                ['SALES_TAX_NTN', false, 'wherever applicable'],
                ['TRADE_BODY_MEMBERSHIP', false, 'wherever applicable'],
                ['SOLE_PROP_DECLARATION', true, null],
                ['ACCOUNT_REQUISITION_LETTERHEAD', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
                ['UBO_DECLARATION', true, null],
                ['SS_UNDERTAKING', true, null],
                ['RENTAL_OWNERSHIP_DOC', false, 'where applicable'],
            ],
            // AOF-Entity page 12 — Partnership
            'PARTNERSHIP' => [
                ['ID_DOC_COPY', true, 'All partners and authorized signatories'],
                ['PARTNERSHIP_DEED', true, null],
                ['REGISTRAR_OF_FIRMS_CERT', true, 'Unregistered partnership must be stated on the form'],
                ['PARTNERS_AUTHORITY_LETTER', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['SOURCE_OF_FUND_GOVERNING_BODY', true, 'Source of fund / proof of income of the UBO'],
                ['SS_UNDERTAKING', true, null],
                ['RUBBER_STAMP', true, 'Company rubber stamp affixed with partner sign'],
                ['CHAMBER_MEMBERSHIP_CERT', false, 'Where applicable'],
            ],
            // AOF-Entity page 12 — Private / Public Limited Companies / Corporations
            'LIMITED_CO' => [
                ['BOARD_RESOLUTION', true, 'Specifying the person(s) authorized to open and operate the account'],
                ['MEMORANDUM_ARTICLES', true, null],
                ['CERT_OF_INCORPORATION', true, null],
                ['CERT_COMMENCEMENT_BUSINESS', true, 'Wherever applicable'],
                ['LIST_OF_DIRECTORS', true, null],
                ['FORM_29', true, 'Or Annexure IV/INC form II under Companies Act 2017'],
                ['ID_DOC_COPY', true, 'All directors and persons authorized to open and operate the account'],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['SHAREHOLDER_VERIFICATION_10', true, 'Shareholders holding 10% or above stake'],
                ['RUBBER_STAMP', true, 'With company secretary signature'],
                ['SOURCE_OF_FUND_GOVERNING_BODY', true, 'Source of fund / proof of income of the UBO'],
                ['RISK_REGISTER_UBO', false, null],
                ['SHAREHOLDING_LETTERHEAD', false, 'Where applicable'],
                ['PHYSICAL_VERIFICATION', true, null],
                ['SS_UNDERTAKING', true, null],
                ['CHAMBER_MEMBERSHIP_CERT', false, 'Where applicable'],
            ],
            // AOF-Entity page 12 — Branch Office or Liaison Office of Foreign Companies
            'FOREIGN_BRANCH' => [
                ['FORM_II_FOREIGN_BRANCH', true, 'Newly registered branch or liaison office'],
                ['FORM_III_FOREIGN_BRANCH', true, 'Already registered foreign company'],
                ['BOI_PERMISSION_LETTER', true, null],
                ['ID_DOC_COPY', true, 'All signatories of the account'],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['LIST_OF_DIRECTORS', true, null],
                ['PRINCIPAL_OFFICE_LETTER', true, null],
                ['RUBBER_STAMP', true, 'With company secretary signature'],
                ['PHYSICAL_VERIFICATION', true, null],
                ['SS_UNDERTAKING', true, null],
            ],
            // AOF-Entity page 13 — Foreign Mission / Diplomats
            'FOREIGN_MISSION' => [
                ['FOREIGN_EXCHANGE_MANUAL', true, null],
                ['ID_DOC_COPY', true, null],
                ['CIF_ALL_SIGNATORIES', true, null],
            ],
            'TRUST' => [
                ['TRUST_REGISTRATION_CERT', true, null],
                ['MEMORANDUM_ARTICLES', true, 'By-laws / Rules and Regulations'],
                ['BOARD_RESOLUTION', true, 'Resolution of the Governing body / Board of Trustees / Executive Committee'],
                ['LIST_OF_TRUSTEES', true, null],
                ['NPO_DECLARATION_FORM', true, null],
                ['ID_DOC_COPY', true, null],
                ['LATEST_FINANCIALS', false, null],
                ['DECLARATION_QUESTIONNAIRE', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
                ['RUBBER_STAMP', true, null],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['SOURCE_OF_FUND_GOVERNING_BODY', true, null],
            ],
            'GOVT_INSTITUTION' => [
                ['GOVT_AUTHORITY_LETTER', true, null],
                ['FUND_ALLOCATION_LETTER', true, null],
                ['GAZETTE_NOTIFICATION', false, 'Armed Forces / allied accounts'],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['ID_DOC_COPY', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
                ['SS_UNDERTAKING', true, null],
            ],
            'NGO_NPO' => [
                ['REG_CERTIFICATE', true, null],
                ['MEMORANDUM_ARTICLES', true, null],
                ['BOARD_RESOLUTION', true, null],
                ['NPO_DECLARATION_FORM', true, null],
                ['ID_DOC_COPY', true, null],
                ['LATEST_FINANCIALS', false, null],
                ['EAD_APPROVAL', false, 'International NGO receiving funds from abroad'],
                ['DECLARATION_QUESTIONNAIRE', true, null],
                ['LIST_OF_TRUSTEES', true, null],
                ['RUBBER_STAMP', true, null],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
                ['TAX_EXEMPTION_CERTIFICATE', false, 'wherever applicable'],
                ['SS_UNDERTAKING', true, null],
                ['SOURCE_OF_FUND_GOVERNING_BODY', true, null],
            ],
            'AGENT' => [
                ['POWER_OF_ATTORNEY', true, null],
                ['ID_DOC_COPY', true, null],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
                ['SS_UNDERTAKING', true, null],
            ],
            'EXECUTOR' => [
                ['ID_DOC_COPY', true, null],
                ['LETTER_OF_ADMINISTRATION', true, null],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
                ['SS_UNDERTAKING', true, null],
            ],
            'CLUB' => [
                ['LIST_OF_TRUSTEES', true, null],
                ['NPO_DECLARATION_FORM', true, null],
                ['ID_DOC_COPY', true, null],
                ['LATEST_FINANCIALS', false, null],
                ['RUBBER_STAMP', true, null],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
            ],
            'SOCIETY' => [
                ['LIST_OF_TRUSTEES', true, null],
                ['NPO_DECLARATION_FORM', true, null],
                ['ID_DOC_COPY', true, null],
                ['RUBBER_STAMP', true, null],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
            ],
            'ASSOCIATION' => [
                ['LIST_OF_TRUSTEES', true, null],
                ['NPO_DECLARATION_FORM', true, null],
                ['ID_DOC_COPY', true, null],
                ['RUBBER_STAMP', true, null],
                ['CIF_ALL_SIGNATORIES', true, null],
                ['PHYSICAL_VERIFICATION', true, null],
            ],
        ];

        /*
        | The printed tables group several category boxes under one documentation
        | row: the "Private/PBAJK Limited Companies/Corporation" row covers every
        | limited-company box, and the "Trust, Clubs, Societies and Associations"
        | row covers the Local Zakat Committee. These aliases reuse that row so no
        | category is left without a checklist.
        */
        $aliases = [
            'PUBLIC_LISTED' => 'LIMITED_CO',
            'PUBLIC_UNLISTED' => 'LIMITED_CO',
            'PRIVATE' => 'LIMITED_CO',
            'MONEY_EXCHANGE' => 'LIMITED_CO',
            'LOCAL_ZAKAT' => 'ASSOCIATION',
        ];

        foreach ($aliases as $categoryCode => $source) {
            $map[$categoryCode] = $map[$source];
        }

        /*
        | "Others" boxes are not itemised in the printed documentation tables, so
        | they get the baseline every account needs under the Important Notes.
        */
        $baseline = [
            ['ID_DOC_COPY', true, 'Important Notes (b): attested after original seen'],
            ['CIF_ALL_SIGNATORIES', true, null],
            ['PHYSICAL_VERIFICATION', true, null],
            ['SS_UNDERTAKING', true, null],
        ];

        $map['IND_OTHERS'] = $baseline;
        $map['ENT_OTHERS'] = $baseline;

        $typeIds = DocumentType::pluck('id', 'code');
        $categoryIds = CustomerCategory::pluck('id', 'code');

        foreach ($map as $categoryCode => $documents) {
            if (! isset($categoryIds[$categoryCode])) {
                continue;
            }

            $keptTypeIds = [];

            foreach ($documents as $index => [$docCode, $mandatory, $when]) {
                if (! isset($typeIds[$docCode])) {
                    continue;
                }

                DocumentRequirement::updateOrCreate(
                    ['document_type_id' => $typeIds[$docCode], 'customer_category_id' => $categoryIds[$categoryCode]],
                    [
                        'is_mandatory' => $mandatory,
                        'applies_when' => $when,
                        'sort_order' => $index + 1,
                    ]
                );

                $keptTypeIds[] = $typeIds[$docCode];
            }

            // The printed checklist is the single source of truth: drop anything a
            // previous run left behind that the form no longer lists.
            DocumentRequirement::where('customer_category_id', $categoryIds[$categoryCode])
                ->whereNotIn('document_type_id', $keptTypeIds)
                ->delete();
        }
    }
}
