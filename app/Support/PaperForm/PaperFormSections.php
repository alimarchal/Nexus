<?php

declare(strict_types=1);

namespace App\Support\PaperForm;

/**
 * The sections of the old BAJK paper Account Opening Form, in the order and wording of the paper form.
 * Fields marked required are the ones printed with a * on the paper form.
 */
final class PaperFormSections
{
    /** @var array<string, string> */
    private const YES_NO = ['yes' => 'Yes', 'no' => 'No'];

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::header(),
            self::currencyAndType(),
            self::nature(),
            self::individualParticulars(),
            self::personalInformation(),
            self::nextOfKin(),
            self::businessParticulars(),
            self::bajkRelationships(),
            self::otherBanks(),
            self::wallet(),
            self::zakat(),
            self::products(),
            self::operatingInstructions(),
            self::specialInstructions(),
            self::introducer(),
            self::terms(),
            self::signatures(),
            ...self::bankUse(),
            ...self::documents(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function header(): array
    {
        return [
            'key' => 'header', 'number' => 1, 'title' => 'Account Header',
            'fields' => [
                ['name' => 'branch_code', 'type' => 'text', 'label' => 'Branch Code', 'max' => 20],
                ['name' => 'branch_name', 'type' => 'text', 'label' => 'Branch Name'],
                ['name' => 'account_no', 'type' => 'text', 'label' => 'Account No', 'max' => 40],
                ['name' => 'relationship_id', 'type' => 'text', 'label' => 'Relationship ID', 'max' => 40],
                ['name' => 'form_date', 'type' => 'date', 'label' => 'Date'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function currencyAndType(): array
    {
        return [
            'key' => 'currency_type', 'number' => 2, 'title' => 'Currency and Type of Account',
            'fields' => [
                ['name' => 'pkr_account_types', 'type' => 'checks', 'label' => 'Pak Rupee A/c', 'other' => 'other', 'options' => [
                    'current' => 'Current A/c', 'pls_saving' => 'PLS Saving A/c', 'sd' => 'SD A/c', 'bmba' => 'BMBA A/c', 'other' => 'Other (specify)',
                ]],
                ['name' => 'fcy_account_types', 'type' => 'checks', 'label' => 'Foreign Currency A/c', 'other' => 'other', 'options' => [
                    'current' => 'Current A/c', 'saving' => 'Saving A/c', 'term' => 'Term A/c', 'bmba' => 'BMBA A/c', 'other' => 'Other (specify)',
                ]],
                ['name' => 'currency', 'type' => 'radio', 'label' => 'Currency', 'other' => 'other', 'options' => [
                    'usd' => 'US Dollar', 'gbp' => 'GBP', 'jpy' => 'JPY', 'eur' => 'Euro', 'other' => 'Other (specify)',
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function nature(): array
    {
        return [
            'key' => 'nature', 'number' => 3, 'title' => 'Nature of Account',
            'fields' => [
                ['name' => 'account_nature', 'type' => 'radio', 'label' => 'Nature of Account', 'required' => true,
                    'options' => ['individual' => 'Individual A/c', 'business' => 'Business A/c']],
                ['name' => 'individual_kind', 'type' => 'radio', 'label' => 'Individual A/c', 'required' => true,
                    'show' => ['account_nature' => 'individual'], 'options' => ['single' => 'Single', 'joint' => 'Joint']],
                ['name' => 'business_kind', 'type' => 'radio', 'label' => 'Business A/c', 'required' => true, 'other' => 'other',
                    'show' => ['account_nature' => 'business'], 'options' => [
                        'partnership' => 'Partnership', 'joint_stock' => 'Joint Stock Company', 'govt_institution' => 'Govt. Institution',
                        'sole_proprietorship' => 'Sole Proprietorship', 'foreign_mission' => 'Foreign Mission/Diplomats',
                        'association' => 'Association/Club/Trust', 'other' => 'Other (specify)',
                    ]],
                ['name' => 'partnership_kind', 'type' => 'radio', 'label' => 'Partnership', 'show' => ['business_kind' => 'partnership'],
                    'options' => ['registered' => 'Registered', 'unregistered' => 'Unregistered']],
                ['name' => 'company_kind', 'type' => 'radio', 'label' => 'Joint Stock Company', 'show' => ['business_kind' => 'joint_stock'],
                    'options' => ['public' => 'Public', 'private' => 'Private']],
                ['name' => 'govt_kind', 'type' => 'radio', 'label' => 'Govt. Institution', 'show' => ['business_kind' => 'govt_institution'],
                    'options' => ['federal' => 'Federal', 'provincial' => 'Provincial']],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function individualParticulars(): array
    {
        return [
            'key' => 'particulars', 'number' => 4, 'title' => 'Particulars of Account (Individual: Single/Joint)',
            'show' => ['account_nature' => 'individual'],
            'fields' => [
                ['name' => 'title_of_account', 'type' => 'text', 'label' => 'Title of Account', 'required' => true],
                ['name' => 'son_of', 'type' => 'text', 'label' => 'S/O'],
                ['name' => 'key_word', 'type' => 'secret', 'label' => 'Key/Secret Word (at least 6 characters)', 'required' => true, 'min' => 6, 'max' => 50,
                    'help' => 'Kept encrypted. Used to identify the customer on the phone.'],
                ...self::address('mailing', 'Mailing Address', true),
                ...self::address('permanent', 'Permanent Address', false),
                ['name' => 'email', 'type' => 'email', 'label' => 'E-mail (mandatory for Internet Banking)'],
                ['name' => 'mobile', 'type' => 'text', 'label' => 'Mobile (mandatory for Mobile Banking)', 'max' => 30],
                ['name' => 'fax', 'type' => 'text', 'label' => 'Fax', 'max' => 30],
                ['name' => 'hold_mail', 'type' => 'radio', 'label' => 'Hold Mail', 'options' => self::YES_NO],
            ],
        ];
    }

    /**
     * Personal Information, once for applicant 1 and once (joint accounts only) for applicant 2.
     *
     * @return array<string, mixed>
     */
    private static function personalInformation(): array
    {
        $fields = [];

        foreach ([1, 2] as $n) {
            $show = $n === 2 ? ['individual_kind' => 'joint'] : [];
            $required = true;
            $applicant = 'Applicant '.$n;

            $fields[] = ['name' => 'heading_applicant_'.$n, 'type' => 'heading', 'label' => $applicant, 'show' => $show];
            $fields[] = ['name' => 'a'.$n.'_title', 'type' => 'select', 'label' => $applicant.': Mr./Mrs./Ms.', 'show' => $show,
                'options' => ['mr' => 'Mr.', 'mrs' => 'Mrs.', 'ms' => 'Ms.']];
            $fields[] = ['name' => 'a'.$n.'_name', 'type' => 'text', 'label' => $applicant.': Name', 'required' => $required, 'show' => $show];
            $fields[] = ['name' => 'a'.$n.'_parent_relation', 'type' => 'radio', 'label' => $applicant.': S/O, D/O, W/O', 'required' => $required, 'show' => $show,
                'options' => ['so' => 'S/O', 'do' => 'D/O', 'wo' => 'W/O']];
            $fields[] = ['name' => 'a'.$n.'_parent_name', 'type' => 'text', 'label' => $applicant.': Father\'s / Husband\'s name', 'required' => $required, 'show' => $show];
            $fields[] = ['name' => 'a'.$n.'_gender', 'type' => 'radio', 'label' => $applicant.': Gender', 'required' => $required, 'show' => $show,
                'options' => ['male' => 'Male', 'female' => 'Female']];
            $fields[] = ['name' => 'a'.$n.'_marital_status', 'type' => 'radio', 'label' => $applicant.': Marital Status', 'required' => $required, 'show' => $show,
                'options' => ['married' => 'Married', 'single' => 'Single']];
            $fields[] = ['name' => 'a'.$n.'_dob', 'type' => 'date', 'label' => $applicant.': Date of Birth', 'past' => true, 'show' => $show];
            $fields[] = ['name' => 'preferred_language_'.$n, 'type' => 'text', 'label' => 'Preferred Language (Applicant '.$n.')', 'max' => 60, 'show' => $show];
            $fields[] = ['name' => 'a'.$n.'_nationality', 'type' => 'text', 'label' => $applicant.': Nationality', 'required' => $required, 'show' => $show, 'max' => 60];
            $fields[] = ['name' => 'a'.$n.'_country_of_residence', 'type' => 'text', 'label' => $applicant.': Country of Residence', 'required' => $required, 'show' => $show, 'max' => 60];
            $fields[] = ['name' => 'a'.$n.'_id_type', 'type' => 'radio', 'label' => $applicant.': Identification', 'show' => $show,
                'options' => ['cnic' => 'CNIC', 'passport' => 'Passport', 'alien_card' => 'Alien Registration Card']];
            $fields[] = ['name' => 'a'.$n.'_id_number', 'type' => 'text', 'label' => $applicant.': Identification number', 'show' => $show, 'max' => 40];
            $fields[] = ['name' => 'a'.$n.'_ntn', 'type' => 'text', 'label' => $applicant.': NTN Number (if available)', 'show' => $show, 'max' => 30];
            $fields[] = ['name' => 'a'.$n.'_occupation', 'type' => 'radio', 'label' => $applicant.': Occupation', 'required' => $required, 'show' => $show, 'other' => 'other',
                'options' => [
                    'govt_service' => 'Government Service', 'private_service' => 'Private Service', 'self_employed' => 'Self-Employed',
                    'house_wife' => 'House Wife', 'student' => 'Student', 'unemployed' => 'Unemployed', 'agriculture' => 'Agriculture', 'other' => 'Other (specify)',
                ]];
            $fields[] = ['name' => 'a'.$n.'_employer', 'type' => 'text', 'label' => $applicant.': Employer\'s Name', 'show' => $show];
        }

        return [
            'key' => 'personal', 'number' => 5, 'title' => 'Personal Information',
            'show' => ['account_nature' => 'individual'],
            'fields' => $fields,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function nextOfKin(): array
    {
        return [
            'key' => 'next_of_kin', 'number' => 6, 'title' => 'Next of Kin',
            'show' => ['account_nature' => 'individual'],
            'fields' => [
                ['name' => 'nok_name', 'type' => 'text', 'label' => 'Name'],
                ['name' => 'nok_relationship', 'type' => 'text', 'label' => 'Relationship'],
                ['name' => 'nok_cnic', 'type' => 'text', 'label' => 'CNIC No.', 'max' => 20],
                ['name' => 'nok_telephone', 'type' => 'text', 'label' => 'Telephone No.', 'max' => 30],
                ['name' => 'nok_address', 'type' => 'textarea', 'label' => 'Address', 'max' => 500],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function businessParticulars(): array
    {
        return [
            'key' => 'business', 'number' => 7, 'title' => 'Particulars of Account (Business Account)',
            'show' => ['account_nature' => 'business'],
            'fields' => [
                ['name' => 'title_of_account', 'type' => 'text', 'label' => 'Title of Account (must contain the company/business name)', 'required' => true],
                ['name' => 'business_name', 'type' => 'text', 'label' => 'Company/Business Name', 'required' => true],
                ['name' => 'nature_of_business', 'type' => 'radio', 'label' => 'Nature of Business', 'required' => true, 'other' => 'other_services', 'options' => [
                    'import_export' => 'Import/Export', 'agriculture' => 'Agriculture', 'manufacturing' => 'Manufacturing',
                    'exchange_company' => 'Exchange Company', 'nbfi' => 'NBFI', 'scheduled_bank' => 'Scheduled Bank', 'other_services' => 'Other Services (specify)',
                ]],
                ['name' => 'commencement_date', 'type' => 'date', 'label' => 'Date of Commencement of Business'],
                ...self::address('mailing', 'Office/Mailing Address', true),
                ...self::address('permanent', 'Head Office/Registered Address', true),
                ['name' => 'email', 'type' => 'email', 'label' => 'Office E-mail', 'required' => true],
                ['name' => 'fax', 'type' => 'text', 'label' => 'Office Fax', 'required' => true, 'max' => 30],
                ['name' => 'business_ntn', 'type' => 'text', 'label' => 'NTN', 'max' => 30],
                ['name' => 'business_cnic', 'type' => 'text', 'label' => 'CNIC No. (for sole proprietorship only)', 'required' => true, 'max' => 20,
                    'show' => ['business_kind' => 'sole_proprietorship']],
                ['name' => 'hold_mail', 'type' => 'radio', 'label' => 'Hold Mail', 'options' => self::YES_NO],
                ['name' => 'contact_persons', 'type' => 'repeater', 'label' => 'Contact Persons', 'rows' => 4, 'columns' => [
                    ['name' => 'name', 'label' => 'Name'],
                    ['name' => 'designation', 'label' => 'Designation'],
                    ['name' => 'telephone', 'label' => 'Telephone No.', 'max' => 30],
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function bajkRelationships(): array
    {
        return [
            'key' => 'bajk_relationships', 'number' => 8, 'title' => 'Your Existing Relationships with BAJK (Business Account)',
            'show' => ['account_nature' => 'business'],
            'fields' => [
                ['name' => 'bajk_relationships', 'type' => 'repeater', 'label' => 'Existing relationships with BAJK', 'rows' => 3, 'columns' => [
                    ['name' => 'branch_name', 'label' => 'Branch Name'],
                    ['name' => 'branch_code', 'label' => 'Branch Code', 'max' => 20],
                    ['name' => 'account_no', 'label' => 'Account/Loan/Card No.', 'max' => 40],
                    ['name' => 'account_title', 'label' => 'Account/Loan/Card Title'],
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function otherBanks(): array
    {
        return [
            'key' => 'other_banks', 'number' => 9, 'title' => 'Your Relationship with Other Banks',
            'fields' => [
                ['name' => 'other_banks', 'type' => 'repeater', 'label' => 'Other banks (up to 2)', 'rows' => 2, 'columns' => [
                    ['name' => 'bank_name', 'label' => 'Bank Name'],
                    ['name' => 'deposit', 'label' => 'Deposit', 'type' => 'checkbox'],
                    ['name' => 'credit_cards', 'label' => 'Credit Cards', 'type' => 'checkbox'],
                    ['name' => 'loans', 'label' => 'Loans', 'type' => 'checkbox'],
                    ['name' => 'other', 'label' => 'Other (specify)'],
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function wallet(): array
    {
        return [
            'key' => 'wallet', 'number' => 10, 'title' => 'BAJK Wallet',
            'fields' => [
                ['name' => 'card_type', 'type' => 'radio', 'label' => 'Type of ATM/Debit Card (tick only one; for individual and sole proprietorship only)',
                    'show' => ['any' => [['account_nature' => 'individual'], ['business_kind' => 'sole_proprietorship']]],
                    'options' => ['silver' => 'Silver', 'gold' => 'Gold']],
                ['name' => 'name_on_card', 'type' => 'text', 'label' => 'Name on Card', 'max' => 19,
                    'show' => ['any' => [['account_nature' => 'individual'], ['business_kind' => 'sole_proprietorship']]]],
                ['name' => 'supplementary_card', 'type' => 'radio', 'label' => 'Supplementary Card Required (fill the prescribed form)', 'options' => ['no' => 'No', 'yes' => 'Yes']],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function zakat(): array
    {
        return [
            'key' => 'zakat', 'number' => 11, 'title' => 'Zakat Deduction',
            'fields' => [
                ['name' => 'zakat_deduction', 'type' => 'radio', 'label' => 'Zakat Deduction', 'options' => [
                    'yes' => 'Yes (only on PLS A/c)', 'no' => 'No (subject to submission of affidavit/declaration)',
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function products(): array
    {
        return [
            'key' => 'products', 'number' => 12, 'title' => 'Other BAJK Products of Interest',
            'fields' => [
                ['name' => 'other_products', 'type' => 'checks', 'label' => 'Products of interest', 'other' => 'other', 'options' => [
                    'credit_cards' => 'Credit Cards', 'personal_loan' => 'Personal Loan', 'car_financing' => 'Car Financing',
                    'mortgage' => 'Mortgage', 'other' => 'Other (specify)',
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function operatingInstructions(): array
    {
        return [
            'key' => 'operating', 'number' => 13, 'title' => 'Operating Instructions',
            'fields' => [
                ['name' => 'signing_authority', 'type' => 'radio', 'label' => 'Signing Authority', 'other' => 'other', 'options' => [
                    'singly' => 'Singly', 'either_or_survivor' => 'Either or Survivor', 'jointly' => 'Jointly',
                    'mandate' => 'Mandate (fill the prescribed form)', 'other' => 'Other (specify)',
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function specialInstructions(): array
    {
        return [
            'key' => 'special_instructions', 'number' => 14, 'title' => 'Special Instructions',
            'fields' => [
                ['name' => 'special_instructions', 'type' => 'textarea', 'label' => 'Special Instructions'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function introducer(): array
    {
        return [
            'key' => 'introducer', 'number' => 15, 'title' => 'Introducer',
            'fields' => [
                ['name' => 'introducer_name', 'type' => 'text', 'label' => 'Name', 'required' => true],
                ['name' => 'introducer_cnic', 'type' => 'text', 'label' => 'CNIC No.', 'max' => 20],
                ['name' => 'introducer_telephone', 'type' => 'text', 'label' => 'Telephone No.', 'max' => 30],
                ['name' => 'introducer_address', 'type' => 'textarea', 'label' => 'Address', 'max' => 500],
                ['name' => 'introducer_bank_branch', 'type' => 'text', 'label' => 'Bank/Branch', 'required' => true],
                ['name' => 'introducer_account_no', 'type' => 'text', 'label' => 'Account No.', 'required' => true, 'max' => 40],
                ['name' => 'introducer_signed', 'type' => 'checkbox', 'label' => 'Introducer signature taken on the form'],
                ['name' => 'introducer_known_years', 'type' => 'number', 'label' => 'How long has the introducer known the applicant: Years', 'integer' => true, 'max' => 99],
                ['name' => 'introducer_known_months', 'type' => 'number', 'label' => 'How long has the introducer known the applicant: Months', 'integer' => true, 'max' => 11],
                ['name' => 'introducer_verified_by', 'type' => 'text', 'label' => 'Verified By'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function terms(): array
    {
        return [
            'key' => 'terms', 'number' => 16, 'title' => 'Terms & Conditions and Indemnity & Undertaking',
            'fields' => [
                ['name' => 'terms_text', 'type' => 'terms', 'label' => 'Terms & Conditions and Indemnity & Undertaking'],
                ['name' => 'terms_accepted', 'type' => 'checkbox', 'required' => true,
                    'label' => 'I/We agree with the Terms & Conditions and the Indemnity and Undertaking, and confirm the information supplied is correct'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function signatures(): array
    {
        return [
            'key' => 'signatures', 'number' => 17, 'title' => 'Authorized Signature',
            'fields' => [
                ['name' => 'signatories', 'type' => 'repeater', 'label' => 'Applicants (up to 4)', 'rows' => 4, 'columns' => [
                    ['name' => 'name', 'label' => 'Applicant\'s Name'],
                    ['name' => 'signed', 'label' => 'Applicant\'s Signature taken', 'type' => 'checkbox'],
                    ['name' => 'stamped', 'label' => 'Company\'s/Organization\'s Stamp taken', 'type' => 'checkbox'],
                ]],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function bankUse(): array
    {
        $individual = ['account_nature' => 'individual'];
        $business = ['account_nature' => 'business'];

        return [
            [
                'key' => 'bank_use', 'number' => 18, 'title' => 'For Bank Use Only: Special Category of Account',
                'fields' => [
                    ['name' => 'special_category', 'type' => 'checks', 'label' => 'Special Category of Account', 'options' => [
                        'bajk_staff' => 'BAJK Staff', 'minor' => 'Minor', 'illiterate' => 'Illiterate',
                        'parda_nasheen' => 'Parda Nasheen', 'blind' => 'Blind', 'registered_alien' => 'Registered Alien',
                    ]],
                ],
            ],
            [
                'key' => 'kyc_individual', 'number' => 18, 'title' => 'For Bank Use Only: KYC (Individual Account)', 'show' => $individual,
                'fields' => [
                    ['name' => 'kyc_customer_type', 'type' => 'radio', 'label' => 'Type of Customer', 'options' => ['walk_in' => 'Walk in', 'marketed' => 'Marketed', 'referred' => 'Referred by']],
                    ['name' => 'kyc_referred_by', 'type' => 'text', 'label' => 'Referred by', 'show' => ['kyc_customer_type' => 'referred']],
                    ['name' => 'kyc_public_figure', 'type' => 'radio', 'label' => 'Public Figure', 'options' => self::YES_NO],
                    ['name' => 'kyc_source_of_funds', 'type' => 'checks', 'label' => 'Source of Funds', 'other' => 'other', 'options' => [
                        'salary' => 'Salary', 'family_business' => 'Family Business', 'stock_investment' => 'Stock/Investment',
                        'home_remittance' => 'Home Remittance', 'agriculture_rented_property' => 'Agriculture/Rented Property', 'other' => 'Other (specify)',
                    ]],
                    ['name' => 'kyc_transaction_mode', 'type' => 'checks', 'label' => 'Usual Mode of Transaction', 'other' => 'other', 'options' => [
                        'cash' => 'Cash', 'clearing' => 'Clearing', 'collection' => 'Collection', 'remittance' => 'Remittance', 'other' => 'Other (specify)',
                    ]],
                    ['name' => 'kyc_purpose', 'type' => 'checks', 'label' => 'Purpose of Account', 'other' => 'other', 'options' => [
                        'personal_saving' => 'Personal Saving', 'business' => 'Business', 'other' => 'Other (specify)',
                    ]],
                    ['name' => 'kyc_beneficiary', 'type' => 'text', 'label' => 'Ultimate Beneficiary of Account (if different from the customer)'],
                    ['name' => 'kyc_beneficiary_relationship', 'type' => 'text', 'label' => 'Relationship with Customer'],
                    ['name' => 'initial_deposit', 'type' => 'number', 'label' => 'Initial Deposit'],
                    ['name' => 'expected_average_balance', 'type' => 'number', 'label' => 'Expected Average Balance'],
                ],
            ],
            [
                'key' => 'kyc_business', 'number' => 18, 'title' => 'For Bank Use Only: KYC (Business Account)', 'show' => $business,
                'fields' => [
                    ['name' => 'kyc_customer_type', 'type' => 'radio', 'label' => 'Type of Customer', 'options' => ['walk_in' => 'Walk in', 'marketed' => 'Marketed', 'referred' => 'Referred by']],
                    ['name' => 'kyc_referred_by', 'type' => 'text', 'label' => 'Referred by', 'show' => ['kyc_customer_type' => 'referred']],
                    ['name' => 'kyc_parent_company', 'type' => 'text', 'label' => 'Parent Company/Group Name'],
                    ['name' => 'kyc_group_companies', 'type' => 'text', 'label' => 'Other Companies of the Group'],
                    ['name' => 'kyc_geographic_area', 'type' => 'text', 'label' => 'Main Geographic Area of Activity'],
                    ['name' => 'kyc_source_of_funds', 'type' => 'checks', 'label' => 'Source of Funds', 'other' => 'other', 'options' => [
                        'export_proceeds' => 'Export Proceeds', 'property_real_estate' => 'Property/Real Estate', 'local_trading' => 'Local Trading',
                        'equity_fx_trading' => 'Equity/FX Trading', 'charity_donations' => 'Charity and Funds Donations', 'other' => 'Other (specify)',
                    ]],
                    ['name' => 'kyc_transaction_mode', 'type' => 'checks', 'label' => 'Usual Mode of Transaction', 'other' => 'other', 'options' => [
                        'cash' => 'Cash', 'clearing' => 'Clearing', 'remittance' => 'Remittance', 'collection' => 'Collection', 'other' => 'Other (specify)',
                    ]],
                    ['name' => 'kyc_beneficiary', 'type' => 'text', 'label' => 'Ultimate Beneficiary of Account (if different from the customer)'],
                    ['name' => 'kyc_beneficiary_relationship', 'type' => 'text', 'label' => 'Relationship with Customer'],
                    ['name' => 'initial_deposit', 'type' => 'number', 'label' => 'Initial Deposit'],
                    ['name' => 'expected_average_balance', 'type' => 'number', 'label' => 'Expected Average Balance'],
                ],
            ],
            [
                'key' => 'recommendation', 'number' => 18, 'title' => 'For Bank Use Only: Recommendation',
                'fields' => [
                    ['name' => 'recommendation', 'type' => 'repeater', 'label' => 'Sales Staff/Relationship Manager (1) and (2)', 'rows' => 2, 'columns' => [
                        ['name' => 'name', 'label' => 'Name of Sales Staff/Relationship Manager'],
                        ['name' => 'employee_no', 'label' => 'Employee No.', 'max' => 30],
                        ['name' => 'signed', 'label' => 'Signature taken', 'type' => 'checkbox'],
                    ]],
                ],
            ],
            [
                'key' => 'authorization', 'number' => 18, 'title' => 'For Bank Use Only: Authorization',
                'fields' => [
                    ['name' => 'authorized_by_name', 'type' => 'text', 'label' => 'Authorized by (Name)', 'required' => true],
                    ['name' => 'authorized_by_employee_no', 'type' => 'text', 'label' => 'Authorized by: Employee No./P.A No.', 'required' => true, 'max' => 30],
                    ['name' => 'authorized_by_signed', 'type' => 'checkbox', 'label' => 'Authorized by: signature taken'],
                    ['name' => 'checker_name', 'type' => 'text', 'label' => 'Branch Supervisor/Data Entry Checker (Name)', 'required' => true],
                    ['name' => 'checker_employee_no', 'type' => 'text', 'label' => 'Branch Supervisor/Data Entry Checker: Employee No./P.A No.', 'required' => true, 'max' => 30],
                    ['name' => 'checker_signed', 'type' => 'checkbox', 'label' => 'Branch Supervisor/Data Entry Checker: signature taken'],
                ],
            ],
        ];
    }

    /**
     * Section 19: the documents checklist. Each group shows for the nature of account it belongs to.
     *
     * @return list<array<string, mixed>>
     */
    private static function documents(): array
    {
        $individual = ['any' => [['account_nature' => 'individual'], ['business_kind' => 'sole_proprietorship']]];

        $checklist = fn (string $name, string $label, array $show, array $items): array => [
            'name' => $name, 'type' => 'checks', 'label' => $label, 'show' => $show, 'options' => array_combine(
                array_map(fn (int $i): string => 'd'.($i + 1), array_keys($items)),
                $items,
            ),
        ];

        return [[
            'key' => 'documents', 'number' => 19, 'title' => 'Documents/Papers to be Obtained',
            'fields' => [
                $checklist('docs_individual', 'Individuals', $individual, [
                    'Attested copy of CNIC/Passport',
                    'Another photo document, if the CNIC has no photo',
                    'Service card, for salaried persons',
                    'Photo and thumb impressions, for illiterate persons',
                    'Alien Registration Card (NADRA), for registered aliens',
                    'Proof of minority, for minor accounts',
                ]),
                $checklist('docs_sole_proprietorship', 'Sole Proprietorship (in addition to the individual documents)', ['business_kind' => 'sole_proprietorship'], [
                    'Request on the business letterhead (optional)',
                    'NTN/Sales Tax Registration certificate (optional)',
                    'Membership of a trade body or chamber of commerce (optional)',
                ]),
                $checklist('docs_partnership', 'Partnership', ['business_kind' => 'partnership'], [
                    'CNIC copies of all partners',
                    'Partnership deed signed by all partners',
                    'Registration certificate (or a note that the firm is unregistered)',
                    'Authority letter for the operating person',
                ]),
                $checklist('docs_joint_stock', 'Joint Stock Companies', ['business_kind' => 'joint_stock'], [
                    'Board resolution',
                    'Memorandum & Articles of Association',
                    'Certificate of Incorporation',
                    'Certificate of Commencement of Business',
                    'CNIC copies of all directors',
                ]),
                $checklist('docs_association', 'Clubs, Societies and Associations', ['business_kind' => 'association'], [
                    'Registration certificate',
                    'By-laws/rules',
                    'Governing body resolution',
                    'CNIC of the authorized persons',
                    'Undertaking about changes in authorized persons',
                ]),
                $checklist('docs_trust', 'Trust Accounts', ['business_kind' => 'association'], [
                    'Certificate of Registration',
                    'NIC copies of all trustees',
                    'Instrument of Trust',
                ]),
                $checklist('docs_foreign_mission', 'Foreign Missions/Diplomats', ['business_kind' => 'foreign_mission'], [
                    'Documents as per the Foreign Exchange manual',
                ]),
                $checklist('docs_agents', 'Agents Accounts', ['business_kind' => 'other'], [
                    'Certified Power of Attorney',
                    'CNIC of the agent',
                ]),
                $checklist('docs_executors', 'Executors and Administrators', ['business_kind' => 'other'], [
                    'CNIC copies of the Executor/Administrator',
                    'Letter of Administration or Probate',
                ]),
            ],
        ]];
    }

    /**
     * Tehsil/District, City/Village/Raqba, Post Code and Telephone of one address.
     *
     * @return list<array<string, mixed>>
     */
    private static function address(string $prefix, string $label, bool $required): array
    {
        return [
            ['name' => $prefix.'_heading', 'type' => 'heading', 'label' => $label.($required ? ' *' : '')],
            ['name' => $prefix.'_tehsil_district', 'type' => 'text', 'label' => $label.': Tehsil/District', 'required' => $required],
            ['name' => $prefix.'_city', 'type' => 'text', 'label' => $label.': City/Village/Raqba', 'required' => $required],
            ['name' => $prefix.'_post_code', 'type' => 'text', 'label' => $label.': Post Code', 'max' => 12],
            ['name' => $prefix.'_telephone', 'type' => 'text', 'label' => $label.': Telephone', 'required' => $required, 'max' => 30],
        ];
    }
}
