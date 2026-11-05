<?php

return [
    'legacy_projects' => [32, 39, 48],

    'data6_projects' => [
        'fch' => 76,
        'oi_art' => 78,
        'opd' => 79,
    ],

    'data6_unit' => [
        'title' => 'AHP Register 2025 v2',
        // Service projects - FCH/OI-ART/OPD. This is the selectable project
        // list (route filters, project tabs); it drives which project_id
        // values the UI treats as a real "project" with its own services.
        'project_ids' => [76, 78, 79],
        // AHP-Demographics: centralised client demographics capture (date
        // of birth, sex, district, facility), replacing per-project demog_*
        // entry. It's a different physical table (redcap_data5), not just a
        // different project_id - REDCap assigns a project's data table
        // independently of which app project it belongs to. Not a
        // selectable project (it has no services of its own), but included
        // in the unit's total record count and in data6:sync via
        // ProjectData6Service::allProjectIds()/rows()/tableFor().
        'demographics_project_ids' => [82],
    ],
];