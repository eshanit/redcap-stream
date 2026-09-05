<?php

/*
|--------------------------------------------------------------------------
| AHP indicator registry (source: data/20260715_AHP_ Indicators.xlsx)
|--------------------------------------------------------------------------
| One entry per indicator. `status`:
|   active      - computed exactly per the agreed definition
|   provisional - computed with the recommended default definition; awaiting
|                 the programme team's answer (docs/data6_indicator_decisions.md)
|   proxy       - the source form cannot express the indicator exactly; a
|                 documented proxy is computed (decision reference in `note`)
|   blocked     - cannot be computed from current forms at all
| `type`: count | percent | sum
| `no_period` : true when the source instrument has no date field, so the
|               reporting-period filter cannot apply (all-time value shown).
*/

return [
    'groups' => [
        ['key' => 'access',  'label' => 'Service access'],
        ['key' => 'hiv',     'label' => 'HIV testing'],
        ['key' => 'art',     'label' => 'ART cascade'],
        ['key' => 'mnch',    'label' => 'ANC & PNC'],
        ['key' => 'fp_prep', 'label' => 'FP & PrEP'],
        ['key' => 'mh',      'label' => 'Mental health & substance use'],
        ['key' => 'sti',     'label' => 'STI'],
        ['key' => 'peer',    'label' => 'Peer & community'],
    ],

    'indicators' => [
        ['id' => 1,  'key' => 'access_clients',      'group' => 'access',  'type' => 'count',   'status' => 'provisional', 'label' => 'Adolescents accessing services',                    'definition' => 'Unique adolescents with at least one visit in the period (all services, deduplicated across projects).', 'note' => 'Adolescent = 10-19 at visit date (D1, awaiting confirmation).'],
        ['id' => 2,  'key' => 'access_first',        'group' => 'access',  'type' => 'count',   'status' => 'provisional', 'label' => 'First-time adolescent visits',                      'definition' => 'Adolescents whose first-ever visit falls in the period.', 'note' => null],
        ['id' => 3,  'key' => 'access_repeat',       'group' => 'access',  'type' => 'count',   'status' => 'provisional', 'label' => 'Repeat adolescent visits',                          'definition' => 'Visits in the period excluding each client\'s first-ever visit.', 'note' => null],

        ['id' => 4,  'key' => 'hiv_tested',          'group' => 'hiv',     'type' => 'count',   'status' => 'provisional', 'label' => 'Adolescents tested for HIV',                        'definition' => 'Unique adolescents with a documented HIV test (HTS, STI, PrEP or ANC register) in the period.', 'note' => 'Union of all testing entry points (D2, awaiting confirmation).'],
        ['id' => 5,  'key' => 'hiv_positive',        'group' => 'hiv',     'type' => 'count',   'status' => 'provisional', 'label' => 'Adolescents testing HIV positive',                  'definition' => 'Unique adolescents with a positive result in any testing register in the period.', 'note' => null],
        ['id' => 6,  'key' => 'hiv_positivity',      'group' => 'hiv',     'type' => 'percent', 'status' => 'provisional', 'label' => 'HIV positivity rate',                               'definition' => 'Positive / tested x 100.', 'note' => null],

        ['id' => 7,  'key' => 'art_initiated',       'group' => 'art',     'type' => 'count',   'status' => 'provisional', 'label' => 'Adolescents initiated on ART',                      'definition' => 'First-ever "Start ARV" visit in the period.', 'note' => 'Definition per D3 recommendation; re-initiations excluded.'],
        ['id' => 8,  'key' => 'art_current',         'group' => 'art',     'type' => 'count',   'status' => 'provisional', 'label' => 'Adolescents currently on ART',                      'definition' => 'Latest visit on/before period end; not dead/TO/LTFU/opted-out; next appointment less than 28 days overdue at period end.', 'note' => null],
        ['id' => 9,  'key' => 'art_retention',       'group' => 'art',     'type' => 'percent', 'status' => 'proxy',       'label' => '12-month ART retention rate',                       'definition' => 'Of adolescents initiated 12 months before the period, share with an ART visit 9-15 months after initiation and not recorded dead/TO.', 'note' => 'Provisional cohort logic; needs stakeholder validation.'],
        ['id' => 10, 'key' => 'art_ltfu',            'group' => 'art',     'type' => 'count',   'status' => 'provisional', 'label' => 'Adolescents lost to follow-up',                     'definition' => 'Next appointment more than 28 days overdue at period end; not recorded dead or transferred out.', 'note' => null],
        ['id' => 11, 'key' => 'art_ti',              'group' => 'art',     'type' => 'count',   'status' => 'active',      'label' => 'Adolescents transferred in',                        'definition' => 'ART visits categorised "Transfer in (N4)" in the period.', 'note' => null],
        ['id' => 12, 'key' => 'art_to',              'group' => 'art',     'type' => 'count',   'status' => 'active',      'label' => 'Adolescents transferred out',                       'definition' => 'Final outcome "Transfer Out" recorded in the period.', 'note' => null],
        ['id' => 13, 'key' => 'art_died',            'group' => 'art',     'type' => 'count',   'status' => 'active',      'label' => 'HIV-positive adolescents who died',                 'definition' => 'Final outcome "Died" recorded in the period.', 'note' => null],
        ['id' => 14, 'key' => 'art_vl_tested',       'group' => 'art',     'type' => 'count',   'status' => 'active',      'label' => 'Adolescents with viral load test',                  'definition' => 'Unique adolescents with a VL sample collected in the period.', 'note' => null],
        ['id' => 15, 'key' => 'art_vl_tnd',          'group' => 'art',     'type' => 'count',   'status' => 'active',      'label' => 'Adolescents with TND result',                       'definition' => 'VL result "target not detected" in the period.', 'note' => null],
        ['id' => 16, 'key' => 'art_vl_suppressed',   'group' => 'art',     'type' => 'percent', 'status' => 'active',      'label' => 'Viral load suppression (<1000)',                    'definition' => 'TND or numeric VL under 1000 copies/mL / VL tested x 100.', 'note' => null],
        ['id' => 17, 'key' => 'art_vl_high',         'group' => 'art',     'type' => 'count',   'status' => 'active',      'label' => 'Viral load 1000+ copies/mL',                        'definition' => 'Numeric VL result of 1000 or more in the period.', 'note' => null],

        ['id' => 18, 'key' => 'anc_new',             'group' => 'mnch',    'type' => 'count',   'status' => 'active',      'label' => 'New ANC adolescent bookings',                       'definition' => 'ANC registrations flagged as first booking in the period.', 'note' => null],
        ['id' => 19, 'key' => 'anc_8plus',           'group' => 'mnch',    'type' => 'percent', 'status' => 'proxy',       'label' => 'Completed 8+ ANC contacts',                         'definition' => 'Of adolescent deliveries in the period, share whose recorded ANC contacts reached 8.', 'note' => 'Delivery-cohort proxy - no pregnancy ID exists (D4).'],
        ['id' => 20, 'key' => 'births_inst',         'group' => 'mnch',    'type' => 'count',   'status' => 'proxy',       'label' => 'Institutional live births',                         'definition' => 'Mother-baby pairs registered with institutional delivery in the period.', 'note' => 'Register holds live pairs only (D5).'],
        ['id' => 21, 'key' => 'births_home',         'group' => 'mnch',    'type' => 'count',   'status' => 'provisional', 'label' => 'Home deliveries',                                   'definition' => 'Deliveries recorded as Home or BBA in the period.', 'note' => 'BBA counted as non-institutional, shown separately (D6).'],
        ['id' => 22, 'key' => 'stillbirths',         'group' => 'mnch',    'type' => 'count',   'status' => 'blocked',     'label' => 'Institutional stillbirths',                         'definition' => 'Not computable: no birth-outcome field exists in any instrument.', 'note' => 'Needs a REDCap form change (D5).'],
        ['id' => 23, 'key' => 'neonatal_deaths',     'group' => 'mnch',    'type' => 'count',   'status' => 'proxy',       'label' => 'Early neonatal deaths',                             'definition' => 'Baby follow-ups recording infant death within 7 days of birth, in the period.', 'note' => 'Birth date joined at record level.'],
        ['id' => 24, 'key' => 'maternal_deaths',     'group' => 'mnch',    'type' => 'count',   'status' => 'active',      'label' => 'Maternal deaths',                                   'definition' => 'Mother follow-ups recording death in the period.', 'note' => null],
        ['id' => 25, 'key' => 'pnc_72h',             'group' => 'mnch',    'type' => 'percent', 'status' => 'provisional', 'label' => 'PNC visit within 72 hrs',                           'definition' => 'Deliveries in the period with a mother PNC visit within 3 days of birth.', 'note' => null],
        ['id' => 26, 'key' => 'anc_first_tested',    'group' => 'mnch',    'type' => 'percent', 'status' => 'proxy',       'label' => 'Tested for HIV at first ANC',                       'definition' => 'First bookings with a documented (non-unknown) HIV status / new bookings x 100.', 'note' => 'No tested-at-visit field on the ANC register (D7).'],
        ['id' => 27, 'key' => 'bf_retest',           'group' => 'mnch',    'type' => 'percent', 'status' => 'proxy',       'label' => 'HIV retest while breastfeeding',                    'definition' => 'HIV-negative mothers in PNC follow-up retested in the period.', 'note' => 'Breastfeeding status simplification (D8).'],
        ['id' => 28, 'key' => 'art_at_delivery',     'group' => 'mnch',    'type' => 'percent', 'status' => 'active',      'label' => 'HIV-positive mothers on ART at delivery',           'definition' => 'HIV-positive deliveries with mother on ART / HIV-positive deliveries x 100.', 'note' => null],

        ['id' => 29, 'key' => 'fp_new',              'group' => 'fp_prep', 'type' => 'count',   'status' => 'active',      'label' => 'New FP users',                                      'definition' => 'FP encounters categorised "New user" in the period.', 'note' => null],
        ['id' => 30, 'key' => 'fp_repeat',           'group' => 'fp_prep', 'type' => 'count',   'status' => 'active',      'label' => 'Repeat FP users',                                   'definition' => 'FP encounters categorised "Repeat user" in the period.', 'note' => null],
        ['id' => 31, 'key' => 'prep_screened',       'group' => 'fp_prep', 'type' => 'count',   'status' => 'active',      'label' => 'Screened for PrEP eligibility',                     'definition' => 'PrEP registrations with eligibility screening in the period.', 'note' => null],
        ['id' => 32, 'key' => 'prep_initiated',      'group' => 'fp_prep', 'type' => 'count',   'status' => 'active',      'label' => 'Initiated on PrEP',                                 'definition' => 'Newly initiated PrEP clients in the period.', 'note' => null],
        ['id' => 33, 'key' => 'prep_continuing',     'group' => 'fp_prep', 'type' => 'count',   'status' => 'provisional', 'label' => 'Continuing on PrEP',                                'definition' => 'Clients recorded as continuing / active on PrEP in the period.', 'note' => null],
        ['id' => 34, 'key' => 'prep_discontinued',   'group' => 'fp_prep', 'type' => 'count',   'status' => 'provisional', 'label' => 'Discontinued from PrEP',                            'definition' => 'Clients recorded as discontinued, opted out or withdrawn in the period.', 'note' => null],

        ['id' => 35, 'key' => 'mh_screened',         'group' => 'mh',      'type' => 'count',   'status' => 'active',      'label' => 'Screened for mental health',                        'definition' => 'Unique adolescents with a completed MH screening.', 'note' => null, 'no_period' => true],
        ['id' => 36, 'key' => 'mh_positive',         'group' => 'mh',      'type' => 'count',   'status' => 'active',      'label' => 'Screening MH positive',                             'definition' => 'Unique adolescents with a positive MH screening result.', 'note' => null, 'no_period' => true],
        ['id' => 37, 'key' => 'mh_managed',          'group' => 'mh',      'type' => 'count',   'status' => 'active',      'label' => 'Referred/managed for MH',                           'definition' => 'Unique adolescents referred and/or managed after MH screening.', 'note' => null, 'no_period' => true],
        ['id' => 38, 'key' => 'su_screened',         'group' => 'mh',      'type' => 'count',   'status' => 'proxy',       'label' => 'Screened for substance use',                        'definition' => 'MH screenings in which the substance-use question was answered.', 'note' => 'Substance screening lives inside the MH tool (D9).', 'no_period' => true],
        ['id' => 39, 'key' => 'su_positive',         'group' => 'mh',      'type' => 'count',   'status' => 'active',      'label' => 'Screening positive for substance use',              'definition' => 'Unique adolescents with substance use identified.', 'note' => null, 'no_period' => true],
        ['id' => 40, 'key' => 'su_managed',          'group' => 'mh',      'type' => 'count',   'status' => 'proxy',       'label' => 'Referred/managed for substance use',                'definition' => 'Substance-positive adolescents with any MH management outcome.', 'note' => 'No substance-specific outcome field (D10).', 'no_period' => true],

        ['id' => 41, 'key' => 'sti_screened',        'group' => 'sti',     'type' => 'count',   'status' => 'provisional', 'label' => 'Screened for STIs',                                 'definition' => 'Unique adolescents with an STI register visit in the period.', 'note' => null],
        ['id' => 42, 'key' => 'sti_treated',         'group' => 'sti',     'type' => 'count',   'status' => 'active',      'label' => 'Treated for STIs',                                  'definition' => 'Unique adolescents recorded as treated in the STI register in the period.', 'note' => null],

        ['id' => 43, 'key' => 'peer_sessions',       'group' => 'peer',    'type' => 'sum',     'status' => 'active',      'label' => 'Peer-led sessions conducted',                       'definition' => 'Sum of peer-led sessions recorded in the period.', 'note' => null],
        ['id' => 44, 'key' => 'peer_reached',        'group' => 'peer',    'type' => 'sum',     'status' => 'active',      'label' => 'Adolescents reached via peer activities',           'definition' => 'Sum of adolescents recorded as reached in the period.', 'note' => null],
        ['id' => 45, 'key' => 'support_groups',      'group' => 'peer',    'type' => 'count',   'status' => 'proxy',       'label' => 'Adolescent support groups conducted',               'definition' => 'Register entries recording a support group conducted in the period.', 'note' => 'Only a yes/no field exists, no meeting count (D11).'],
    ],
];
