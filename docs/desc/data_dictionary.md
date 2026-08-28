# REDCap Data Dictionary Documentation for FCH, OIART, and OPD Projects

## Project Overview
This project consists of three sub‑projects (FCH, OIART, OPD) that share a common set of demographic fields and several service‑specific instruments. The data are collected in REDCap using the provided CSV data dictionaries.

All dates are in **YYYY‑MM‑DD** format.

---

## Common Demographics Instrument
**Used in:** FCH, OIART, OPD  
**Purpose:** Store client demographic information once per record.  
**Fields:**

| Field Name            | Type     | Label / Choices                                            | Branching Logic                    | Required | Notes                              |
|-----------------------|----------|------------------------------------------------------------|------------------------------------|----------|------------------------------------|
| record_id             | text     | Record ID (format: FAC/YYYY/###, e.g. CHK/2024/001)        |                                    |          | Unique identifier                 |
| demog_district        | radio    | District: BIK, ZAK, CHI                                    |                                    | Yes      |                                    |
| demog_facility        | radio    | Facility: SIL, BIK, CKK, MSH, NDG, MUS, BTA, HRV, CHI, CHK |                                    | Yes      |                                    |
| demog_firstname       | text     | First name                                                 |                                    | Yes      |                                    |
| demog_surname         | text     | Surname                                                    |                                    | Yes      |                                    |
| demog_dateofbirth     | text     | Date of Birth                                              |                                    | Yes      | date_ymd                           |
| demog_age             | calc     | Age = rounddown(datediff([demog_dateofbirth], today, 'y')) |                                    |          | Auto‑calculated                   |
| demog_gender          | radio    | Gender: 1=Male, 2=Female                                   |                                    | Yes      |                                    |
| demog_address         | notes    | Client address                                             |                                    |          |                                    |
| demog_have_contact    | radio    | Does client have contact number? Y/N/NA                    |                                    | Yes      |                                    |
| demog_contact         | text     | Client contact number                                      | [demog_have_contact] = 'Y'        |          |                                    |
| demog_marital_status  | radio    | 1=Married,2=Never Married,3=Widowed,4=Divorced/Separated, 5=Living together,6=Minor,7=Not Given,8=Divorced |          |                                    |
| demog_education       | radio    | 0=No Education,1=Creche/ECD,2=Primary,3=Secondary,4=A level,5=College,6=Degree,7=Post Graduate,8=Not Provided | |   |                                    |
| demog_client_profile  | radio    | 1=General Pop,2=Sex Worker,3=MSM,4=WSW,5=PWUD,6=PWID,7=Transgender,8=Other | | Yes |                                    |
| demog_client_profile_other | text | Please specify                                         | [demog_client_profile]='8'       | Yes      |                                    |

---

## FCH (Family & Child Health) Sub‑Project
Instruments: STI Register, Family Planning, ANC Initial, ANC Follow‑ups, PNCR (Mother‑Baby Pair Initial), PNCM (Mother Follow‑ups), PNCB (Baby Follow‑ups), PrEP Initial, PrEP Follow‑ups, Mental Health, Health Education, Counselling, Peer Support, HTS.

---

### STI Register
**Instrument Name:** sti_register  
**Access:** sti_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| sti_access | radio | Did client access this service? Y/N/UN | | Yes |
| sti_visit_date | text | Date of visit | [sti_access]='Y' | Yes |
| sti_date | text | Date (STI review) | [sti_access]='Y' | Yes |
| sti_client_history | notes | Presenting complaint / history | [sti_access]='Y' | |
| sti_repeat | radio | New or Repeat STI Episode: N=New, R=Repeat | [sti_access]='Y' | Yes |
| sti_syndrome | checkbox | 1=Urethral discharge,2=Genital Ulcer,3=Vaginal discharge,4=Lower abdominal pain,5=Inguinal bubo,6=Acute scrotal swelling,7=Ophthalmia Neonatorum,8=Balanitis/Balanopostitis,9=Genital Warts,10=Congenital syphilis,11=Anal Warts,12=Genital Herpes,13=Other,14=NIL | [sti_access]='Y' | Yes |
| sti_syndrome_other | text | Other specify | [sti_syndrome(13)]='1' | Yes |
| sti_syphilis_test | yesno | Tested for syphilis? | [sti_access]='Y' | Yes |
| sti_syphilis_test_why | radio | Purpose: a=Antenatal,b=Sexual Abuse,c=Contact,d=Syphilis exposed infant,e=STI client,f=Partner of pregnant/breastfeeding,g=Other,h=NIL | [sti_syphilis_test]='1' | Yes |
| sti_syph_test_why_other | text | Specify | [sti_syphilis_test_why]='g' | Yes |
| sti_syphilis_result | radio | P=Positive, N=Negative, NL=NIL | [sti_syphilis_test]='1' | Yes |
| sti_syphilis_test_nd | notes | Why not tested? | [sti_syphilis_test]='0' | Yes |
| sti_gonorrhea_test | yesno | Tested for gonorrhea? | [sti_access]='Y' | Yes |
| sti_gonorrhea_nd | notes | Why not tested for gonorrhea? | [sti_gonorrhea_test]='0' | Yes |
| sti_gonorrhea_test_why | radio | Purpose (same choices as syphilis) | [sti_gonorrhea_test]='1' | Yes |
| sti_gonorr_test_why_other | text | Specify | [sti_gonorrhea_test_why]='g' | |
| sti_gonorrhea_result | radio | P=Positive, N=Negative, NL=NIL | [sti_gonorrhea_test]='1' | Yes |
| sti_patient_treated | yesno | Was patient treated? | [sti_access]='Y' | Yes |
| sti_treatment | notes | Treatment given (dose, frequency, duration) | [sti_access]='Y' | Yes | @HIDDEN |
| sti_hiv_test | yesno | Was client tested for HIV? | [sti_access]='Y' | Yes |
| sti_hiv_test_result | radio | P=Positive, N=Negative, I=Inconclusive, NA=Not Applicable | [sti_hiv_test]='1' | Yes |
| sti_hiv_test_nd | notes | Why not tested? | [sti_hiv_test]='0' | Yes |
| sti_contact_recieved | yesno | STI Contact slips received? | [sti_access]='Y' | Yes |
| sti_contact_treated | yesno | STI Contact treated? | [sti_access]='Y' | Yes |

---

### Family Planning
**Instrument Name:** family_planning  
**Access:** fp_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| fp_access | radio | Did client access this service? Y/N/UN | | Yes |
| fp_date | text | Date of encounter | [fp_access]='Y' | Yes |
| fp_client_category | radio | N=New User, R=Repeat User, NL=NIL | [fp_access]='Y' | Yes |
| fp_on_contra | yesno | Currently on any contraception? | [fp_access]='Y' | Yes |
| fp_no_contra_why | notes | Why not? | [fp_on_contra]='0' | Yes |
| fp_want_contra | yesno | Want to be on contraception? | [fp_access]='Y' | Yes |
| fp_not_want_contra_why | notes | Why not? | [fp_want_contra]='0' | Yes |
| fp_method | checkbox | 1=Progestogen only Pills,2=Combined Oral Pills,3=Emergency Contraceptives,4=Injectables,5=Implants,6=IUCD,7=Tubal Ligation,8=Vasectomy,9=Male Condoms,10=Female Condoms,11=NIL | [fp_access]='Y' | Yes |
| fb_progesto_status | radio | N/R/NL | [fp_method(1)]='1' | Yes |
| fb_combinedoral_status | radio | N/R/NL | [fp_method(2)]='1' | Yes |
| fb_emcontra_status | radio | N/R/NL | [fp_method(3)]='1' | Yes |
| fb_injectables_type | radio | 1=DMPA Intramuscular,2=DMPA Subcutaneous,3=NIL | [fp_method(4)]='1' | Yes |
| fp_intramuscular_status | radio | N/R/NL | [fb_injectables_type]='1' | Yes |
| fp_subcutaneous_status | radio | N/R/NL | [fb_injectables_type]='2' | Yes |
| fp_implant_period | radio | 1=Three Years,2=Five Years,3=NIL | [fp_method(5)]='1' | Yes |
| fp_3_status | radio | 1=New Insertion,2=Repeat Insertion,3=Removals,4=NIL | [fp_implant_period]='1' | Yes |
| fp_5_status | radio | 1=New Insertion,2=Repeat Insertion,3=Removals,4=NIL | [fp_implant_period]='2' | Yes |
| fp_iucd_type | radio | 1=Copper T (Non-hormonal),2=LNG-IUS (Hormonal),3=NIL | [fp_method(6)]='1' | Yes |
| fp_coppert_status | radio | 1=New Insertion,2=Repeat Insertion,3=Removals,4=NIL | [fp_iucd_type]='1' | Yes |
| fp_lnguis_status | radio | 1=New Insertion,2=Repeat Insertion,3=Removals,4=NIL | [fp_iucd_type]='2' | Yes |
| fp_method_prov | checkbox | 1=Pill,2=Injectable,3=Implant,4=IUCD,5=Condom,6=Permanent Method,7=Other,8=NIL | [fp_access]='Y' | |
| fp_method_specify | notes | Other specify | [fp_method_prov(7)]='1' | Yes |

---

### ANC Initial Register (ANCR)
**Instrument Name:** anc_initial_register  
**Access:** ancr_access = 'Y'  
**Fields:** (abbreviated, see full CSV for details)

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| ancr_access | radio | Did client access? Y/N/UN | | Yes |
| ancr_date | text | Date | [ancr_access]='Y' | Yes |
| ancr_first_booking | yesno | First Booking? | [ancr_access]='Y' | Yes |
| ancr_transfer_in | yesno | Transfer In? | [ancr_access]='Y' | Yes |
| ancr_transfer_from | radio | Facility list (SIL,BIK,CKK,MSH,NDG,MUS,BTA,HRV,CHI,CHK,OTH) | [ancr_transfer_in]='1' | Yes |
| ancr_transfer_specify | text | Other | [ancr_transfer_from]='OTH' | Yes |
| ancr_parity | text | Parity (integer) | [ancr_access]='Y' | Yes |
| ancr_gravida | text | Gravida (integer) | [ancr_access]='Y' | Yes |
| ancr_lnmp_sure | yesno | Sure of LNMP date? | [ancr_access]='Y' | Yes |
| ancr_lmnp_date | text | LNMP Date | [ancr_lnmp_sure]='1' | Yes |
| ancr_edd | text | EDD (auto-calc) | [ancr_lnmp_sure]='1' | | @CALCDATE([ancr_lmnp_date],280,'d') |
| ancr_ga | calc | GA at booking (weeks) | [ancr_lnmp_sure]='1' | |
| ancr_height | text | Height (cm) | [ancr_access]='Y' | Yes |
| ancr_blood_tested | yesno | Blood type tested? | [ancr_access]='Y' | Yes |
| ancr_blood_group | radio | Types: A+, A-, B+, B-, AB+, AB-, O+, O-, NIL | [ancr_blood_tested]='1' | Yes |
| ancr_blood_type_nd | notes | Why not tested? | [ancr_blood_tested]='0' | Yes |
| ancr_hiv_prior | radio | 0=Negative,1=Positive,2=Unknown | [ancr_access]='Y' | Yes |
| ancr_hiv_current_status | radio | 0=Negative,1=Positive,2=Unknown | [ancr_access]='Y' | Yes |
| ancr_art_already | yesno | Already on ART? | [ancr_access]='Y' | Yes |
| ancr_contact_number | text | ANC visit number (integer) | [ancr_access]='Y' | Yes |
| ancr_contact_date | text | Contact date | [ancr_access]='Y' | |
| ancr_client_weight | text | Weight (kg) | [ancr_access]='Y' | Yes |
| ancr_muac | text | MUAC (cm) | [ancr_access]='Y' | |
| ancr_bp_done | yesno | BP done? | [ancr_access]='Y' | Yes |
| ancr_client_systolic | text | Systolic | [ancr_bp_done]='1' | Yes |
| ancr_diastolic | text | Diastolic | [ancr_bp_done]='1' | Yes |
| ancr_urinalysis | yesno | Urinalysis done? | [ancr_access]='Y' | Yes |
| ancr_urinalysis_protein | radio | P=Positive, N=Negative, 1=Unknown | [ancr_urinalysis]='1' | Yes |
| ancr_syphilis_done | yesno | Syphilis test done? | [ancr_access]='Y' | Yes |
| ancr_syphilis_treat_dose | radio | 1=1st,2=2nd,3=3rd | [ancr_syphilis_done]='1' | |
| ancr_hepb_screen | yesno | Screened for Hep B? | [ancr_access]='Y' | Yes |
| ancr_hep_b_treatment | radio | I=Initiated, R=Resupplied, NA=Not Applicable | [ancr_hepb_screen]='1' | |
| ancr_malaria_pro_given | yesno | Malaria prophylaxis given? | [ancr_access]='Y' | Yes |
| ancr_prophylaxis | radio | IPTp1‑8, Unknown | [ancr_malaria_pro_given]='1' | Yes |
| ancr_tt_given | yesno | Tetanus Toxoid given? | [ancr_access]='Y' | Yes |
| ancr_tt_complete | yesno | TT complete? | [ancr_tt_given]='1' | Yes |
| ancr_tt_what | radio | TT1‑TT4, Td1‑Td5, Unknown | [ancr_tt_given]='1' | Yes |
| ancr_iron_sups | yesno | Iron/Folate given? | [ancr_access]='Y' | |
| ancr_calcium_sups | yesno | Calcium given? | [ancr_access]='Y' | Yes |

---

### ANC Booking Follow‑ups (ANC)
**Instrument Name:** anc_booking_follow_ups  
**Access:** anc_access = 'Y'  
**Fields:** (partial list – many fields mirror ANCR but for subsequent visits)

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| anc_access | radio | Did client access? Y/N/UN | | Yes |
| anc_date | text | Date | [anc_access]='Y' | Yes |
| anc_height | text | Height (cm) | [anc_access]='Y' | Yes |
| anc_contact_number | text | ANC visit number | [anc_access]='Y' | Yes |
| anc_contact_date | text | Contact date | [anc_access]='Y' | | @HIDDEN |
| anc_weight | text | Weight (kg) | [anc_access]='Y' | Yes |
| anc_muac_done | yesno | MUAC done? | [anc_access]='Y' | Yes |
| anc_muac | text | MUAC (cm) | [anc_muac_done]='1' | Yes |
| anc_muac_nd | notes | Why not measured? | [anc_muac_done]='0' | Yes |
| anc_bp | yesno | BP measured? | [anc_access]='Y' | Yes |
| anc_systolic | text | Systolic | [anc_bp]='1' | Yes |
| anc_diastolic | text | Diastolic | [anc_bp]='1' | Yes |
| anc_bp_nd | notes | Why not measured? | [anc_bp]='0' | Yes |
| anc_pale_pink_assess | yesno | Pale/pink assessment done? | [anc_access]='Y' | Yes |
| anc_pale_pink_result | radio | 1=Pale, 2=Pink | [anc_pale_pink_assess]='1' | Yes |
| anc_pres_pos_select | radio | Baby position: 1=Occiput Anterior,2=Occiput Posterior,3=Transverse,4=Breech,5=Other | [anc_access]='Y' | Yes |
| anc_pres_position | notes | Other position | [anc_pres_pos_select]='5' | Yes |
| anc_ga_at_visit | calc | GA (weeks) auto‑calc | [anc_access]='Y' | |
| anc_symp_height | text | Symphysio‑fundal height (cm) | [anc_access]='Y' | Yes |
| anc_foetal_heart_rate_yn | yesno | Foetal heart‑rate record? | [anc_access]='Y' | Yes |
| anc_foetal_heart_rate | text | Heart‑rate (bpm) | [anc_foetal_heart_rate_yn]='1' | |
| anc_foetal_heart_category | radio | NH=Not Heard, HR=Heard Regular, FMF=Fetal Movement Failed | [anc_foetal_heart_rate_yn]='0' | |
| anc_urinalysis | yesno | Urinalysis done? | [anc_access]='Y' | Yes |
| anc_urinalysis_protein | radio | P=Positive, N=Negative | [anc_urinalysis]='1' | Yes |
| anc_urinalysis_glucose | radio | P=Positive, N=Negative | [anc_urinalysis]='1' | Yes |
| anc_urinalysis_nd | notes | Why not done? | [anc_urinalysis]='0' | Yes |
| anc_syphilis_done | yesno | Syphilis test done? | [anc_access]='Y' | Yes |
| anc_syphilis_result | radio | P=Positive, N=Negative | [anc_syphilis_done]='1' | Yes |
| anc_syph_positive | descriptive | “Patient should go for Health Education and Counselling” | [anc_syphilis_result]='P' | |
| anc_syphilis_treat_dose | radio | 1st,2nd,3rd | [anc_syphilis_result]='P' | Yes |
| anc_syphilis_nd | notes | Why not done? | [anc_syphilis_done]='0' | Yes |
| anc_hepb_screen | yesno | Hep B screen? | [anc_access]='Y' | Yes |
| anc_hep_b_treatment | radio | I/R/NA | [anc_hepb_screen]='1' | Yes |
| anc_hep_b_resuls | radio | N=Normal, P=Presumptive, ND=Not Done | [anc_hepb_screen]='1' | Yes |
| anc_hiv_test_results | radio | P=Positive, N=Negative | [anc_access]='Y' | Yes |
| anc_art | radio | I=Initiated, R=Resupplied | [anc_hiv_test_results]='P' | Yes |
| anc_follow_up | radio | 1=On treatment,2=Defaulted,3=Lost,4=Not Applicable | [anc_art]='I' | Yes |
| anc_tb_screen | radio | N=Normal, P=Presumptive, NA=Not Applicable | [anc_access]='Y' | Yes |
| anc_cd4 | yesno | CD4 done? | [anc_access]='Y' | Yes |
| anc_cd4_results | text | CD4 count | [anc_cd4]='1' | Yes |
| anc_cd4_nd | notes | Why not done? | [anc_cd4]='0' | Yes |
| anc_vl | yesno | Viral Load done? | [anc_access]='Y' | Yes |
| anc_viral_load | text | VL result | [anc_vl]='1' | |
| anc_viral_load_nd | notes | Why not done? | [anc_vl]='0' | Yes |
| anc_partner_positive | radio | 1=Yes,0=No,2=Unknown | [anc_access]='Y' | Yes |
| anc_partner_test | yesno | Partner HIV test in ANC? | [anc_access]='Y' | Yes |
| anc_partner_result | radio | P=Positive, N=Negative | [anc_partner_test]='1' | Yes |
| anc_prep_given | yesno | PrEP given | [anc_access]='Y' | Yes |
| anc_uss_done | yesno | USS done? | [anc_access]='Y' | Yes |
| anc_uss_results | radio | N=Normal, A=Abnormal | [anc_uss_done]='1' | Yes |
| anc_uss_edd | text | EDD from USS | [anc_uss_done]='1' | Yes |
| anc_uss_babies | text | Number of babies | [anc_uss_done]='1' | Yes |
| anc_ppp_counselling | checkbox | 1=Implants,2=POP,3=BTL,4=IUCD,5=LAM,6=Barriers,7=PPIUCD | [anc_access]='Y' | |
| anc_martenal_counseling | yesno | Maternal/infant nutrition counselling done? | [anc_access]='Y' | Yes |
| anc_malaria_pro_given | yesno | Malaria prophylaxis given? | [anc_access]='Y' | Yes |
| anc_prophylaxis | radio | IPTp1‑8 | [anc_malaria_pro_given]='1' | Yes |
| anc_tt_given | yesno | TT given? | [anc_access]='Y' | Yes |
| anc_tt_given_nd | notes | Why not given? | [anc_tt_given]='0' | Yes |
| anc_tt_complete | yesno | TT complete? | [anc_tt_given]='1' | Yes |
| anc_tt_what | radio | TT1‑TT4, Td1‑Td5 | [anc_tt_given]='1' | Yes |
| anc_iron_sups | yesno | Iron/Folate given? | [anc_access]='Y' | Yes |
| anc_iron_sups_nd | notes | Why not given? | [anc_iron_sups]='0' | Yes |
| anc_calcium_sups | yesno | Calcium given? | [anc_access]='Y' | Yes |
| anc_calcium_sups_nd | notes | Why not given? | [anc_calcium_sups]='0' | Yes |
| anc_preg_complications | checkbox | 1=PIH,2=Pre‑Eclampsia,3=Gestational Diabetes,4=Cardiac disease,5=IUGR,6=PProm,7=Foetal distress,8=None | [anc_access]='Y' | Yes |
| anc_delivery_plan | radio | C=C‑Section, N=NVD | [anc_access]='Y' | Yes |
| anc_comments | notes | Comments | [anc_access]='Y' | |
| anc_filled_by | radio | 1=Provider,2=Nurse,3=Midwife | [anc_access]='Y' | Yes |

---

### PNCR – Mother‑Baby Pair Initial Register
**Instrument Name:** mother_baby_pair_initial_register_per_baby  
**Access:** pncr_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| pncr_access | radio | Did client access? Y/N/UN | | Yes |
| pncr_date | text | Date of enrollment | [pncr_access]='Y' | Yes |
| pncr_anc_booked | yesno | Mother booked for ANC? | [pncr_access]='Y' | Yes |
| pncr_transfer_in | yesno | Transfer In? | [pncr_access]='Y' | |
| pncr_transfer_from | radio | Facility list (SIL,BIK,CKK,MSH,NDG,MUS,BTA,HRV,CHI,CHK,OTH) | [pncr_transfer_in]='1' | Yes |
| pncr_transfer_in_oth | text | Other facility | [pncr_transfer_from]='OTH' | Yes |
| pncr_anc_number | text | ANC number | [pncr_anc_booked]='1' | Yes |
| pncr_mother_baby | text | Mother‑Baby number | [pncr_access]='Y' | Yes |
| pncr_mother_height | text | Mother’s height (cm) | [pncr_access]='Y' | |
| pncr_ga | text | GA at delivery | [pncr_access]='Y' | Yes |
| pncr_place_of_delivery | radio | 1=Institutional,2=Home,3=BBA | [pncr_access]='Y' | Yes |
| pncr_mode_of_delivery | radio | 1=NVD,2=Breech,3=CS,4=Forceps,5=Vacuum | [pncr_access]='Y' | Yes |
| pncr_hiv_status_post | radio | P=Positive, N=Negative, I=Inconclusive, UNK=Unknown | [pncr_access]='Y' | Yes |
| pncr_hiv_status_art | yesno | Mother on ART? | [pncr_access]='Y' | Yes |
| pncr_syphilis_screened | radio | 1=Yes,2=No,3=Unknown | [pncr_access]='Y' | Yes |
| pncr_syphilis_results | radio | P=Positive, N=Negative | [pncr_syphilis_screened]='1' | Yes |
| pncr_doses_received | radio | 0‑3 doses | [pncr_syphilis_results]='P' | Yes |
| pncr_full_name | text | Infant’s full name | [pncr_access]='Y' | Yes |
| pncr_national_id | text | National ID | [pncr_access]='Y' | |
| pncr_date_of_birth | text | Infant DOB | [pncr_access]='Y' | Yes |
| pncr_infant_sex | radio | M=Male, F=Female | [pncr_access]='Y' | Yes |
| pncr_birth_weight | text | Birth weight (g) | [pncr_access]='Y' | Yes |
| pncr_birth_length | text | Birth length (cm) | [pncr_access]='Y' | Yes |
| pncr_apgar_score_1min | text | APGAR 1 min (0‑10) | [pncr_access]='Y' | Yes |
| pncr_apgar_score_5min | text | APGAR 5 min (0‑10) | [pncr_access]='Y' | Yes |
| pncr_breast_fed_hour | yesno | Breast‑fed in first hour? | [pncr_access]='Y' | Yes |
| pncr_breast_fed_nd | notes | Why not? | [pncr_breast_fed_hour]='0' | Yes |
| pncr_infant_syphil_exp | radio | 1=Yes,0=No,2=Unknown | [pncr_access]='Y' | Yes |
| pncr_inf_syphil_treated | yesno | Treated? | [pncr_infant_syphil_exp]='1' | Yes |
| pncr_inf_con_syphil | yesno | Congenital syphilis? | [pncr_access]='Y' | Yes |
| pncr_inf_con_syphil_treated | yesno | Treated? | [pncr_inf_con_syphil]='1' | Yes |

---

### PNCM – Mother Follow‑ups
**Instrument Name:** mother_baby_follow_ups_mother  
**Access:** pncm_access = 'Y'  
**Fields:** (key fields)

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| pncm_access | radio | Did client access? Y/N/UN | | Yes |
| pncm_visit | dropdown | Visit (Day 1,3,7, 6W,10W,14W,5M,6M,… up to 24M) | [pncm_access]='Y' | Yes |
| pncm_mother_baby_number | text | Mother‑Baby number | [pncm_access]='Y' | Yes |
| pncm_visit_date | text | Date of visit | [pncm_access]='Y' | Yes |
| pncm_weight | text | Mother’s weight (kg) | [pncm_access]='Y' | Yes |
| pncm_bp | yesno | BP measured? | [pncm_access]='Y' | Yes |
| pncm_bp_systolic | text | Systolic | [pncm_bp]='1' | Yes |
| pncm_bp_diastolic | text | Diastolic | [pncm_bp]='1' | Yes |
| pncm_bp_nd | notes | Why not measured? | [pncm_bp]='0' | Yes |
| pncm_hb | yesno | HB test done? | [pncm_access]='Y' | Yes |
| pncm_hb_results | text | HB result | [pncm_hb]='1' | Yes |
| pncm_hb_nd | notes | Why not done? | [pncm_hb]='0' | Yes |
| pncm_muac_done | yesno | MUAC measured? | [pncm_access]='Y' | Yes |
| pncm_muac | text | MUAC (cm) | [pncm_muac_done]='1' | Yes |
| pncm_muac_nd | notes | Why not measured? | [pncm_muac_done]='0' | Yes |
| pncm_pelvic_exam_done | yesno | Pelvic exam done? | [pncm_access]='Y' | Yes |
| pncm_nutritional_support | yesno | Nutritional support given? | [pncm_access]='Y' | Yes |
| pncm_iycf_counselling | yesno | IYCF counselling done? | [pncm_access]='Y' | Yes |
| pncm_familiy_planning | checkbox | 1=Condom,2=Oral,3=Injectables,4=Implants,5=IUCD,6=Permanent,7=None,8=Other | [pncm_access]='Y' | Yes |
| pncm_breast_cancer_done | yesno | Breast cancer screening done? | [pncm_access]='Y' | Yes |
| pncm_breast_cancer_method | radio | 1=Palpation,2=Mammography,3=Biopsy,4=Other | [pncm_breast_cancer_done]='1' | Yes |
| pncm_breast_cancer_other | text | Specify other | [pncm_breast_cancer_method]='4' | Yes |
| pnpm_breast_cancer_results | radio | N=Normal, A=Abnormal, NA=Not Applicable | [pncm_breast_cancer_method] in (1,2,3,4) | Yes |
| pncm_cervical_cancer_done | yesno | Cervical cancer screening done? | [pncm_access]='Y' | Yes |
| pncm_cervic_screen_method | radio | 1=PAP Smear,2=VIAC,3=Biopsy,4=Other | [pncm_cervical_cancer_done]='1' | Yes |
| pncm_cervic_cancer_results | radio | N/A/NA | [pncm_cervic_screen_method] in (1,2,3,4) | Yes |
| pncm_abnormal_cells_treat | radio | 1=Yes,0=No,2=On Treatment | [pncm_cervic_cancer_results] in (N,A,NA) | Yes |
| pncm_cells_trtment_mthd | radio | 1=Cryotherapy,2=Leep,3=Cone‑biopsy,4=Hysterectomy,5=Other | [pncm_abnormal_cells_treat] in (1,2) | Yes |
| pncm_tb_screening | radio | 1=Yes,0=No,2=On Treatment | [pncm_access]='Y' | Yes |
| pncm_tb_results | radio | N=Normal, P=Presumptive, NA=Not Applicable | [pncm_tb_screening]='1' | Yes |
| pncm_tb_test | radio | 1=Yes,0=No,2=Not Applicable | [pncm_access]='Y' | Yes |
| pncm_tb_test_result | radio | P=Positive, N=Negative | [pncm_tb_test]='1' | Yes |
| pncm_iso_therapy | radio | 1=Yes,0=No,2=Not Applicable | [pncm_access]='Y' | Yes |
| pncm_iso_therapy_nd | notes | Why not done? | [pncm_iso_therapy]='0' | Yes |
| pncm_hiv_tested | radio | 1=Yes,0=No,2=Not Applicable | [pncm_access]='Y' | Yes |
| pncm_hiv_results | radio | P/N/I | [pncm_hiv_tested]='1' | Yes |
| pncm_hiv_partner | radio | P/N/I/UNK | [pncm_access]='Y' | Yes |
| pncm_ctx_given | radio | I=Initiated, R=Resupply, NA=Not Applicable | [pncm_access]='Y' | Yes |
| pncm_cd4_collect | radio | 1=Yes,0=No,2=Not Applicable | [pncm_access]='Y' | Yes |
| pncm_cd4_no_done | notes | Why not done? | [pncm_cd4_collect]='0' | Yes |
| pncm_viral_load_collect | radio | 1=Yes,0=No,2=Not Applicable | [pncm_access]='Y' | Yes |
| pncm_vl_nd | notes | Why not done? | [pncm_viral_load_collect]='0' | Yes |
| pncm_art | radio | I=Initiated, A=Already on ART, NA=Not Applicable | [pncm_access]='Y' | Yes |
| pncm_mother_assess | checkbox | 1=Oedema,2=Varicose veins,3=Abnormal lochia,4=Vaginal bleeding,5=Premium tear/episiotomy not healing,6=Gaping genital wound,7=Tender Uterus,8=Cracked nipples,9=Breast Abscess,10=Engorged breasts,11=High Temperature,12=Leakage of urine,13=Depression,14=Normal | [pncm_access]='Y' | Yes |
| pncm_relavant_info | yesno | Relevant core information given | [pncm_access]='Y' | Yes |
| pncm_mother_follow_up | radio | 1=In Care,2=Mother transferred out,3=Missed appointment,4=Lost to follow up,5=Dead,6=Not Applicable | [pncm_access]='Y' | Yes |
| pncm_comment | notes | Comment | [pncm_access]='Y' | |

---

### PNCB – Baby Follow‑ups
**Instrument Name:** mother_baby_follow_ups_baby  
**Access:** pncb_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| pncb_access | radio | Did client access? Y/N/UN | | Yes |
| pncb_visit | dropdown | Visit (same list as PNCM) | [pncb_access]='Y' | Yes |
| pncb_visit_date | text | Date of visit | [pncb_access]='Y' | Yes |
| pncb_mother_baby_number | text | Mother‑Baby number | [pncb_access]='Y' | Yes |
| pncb_inf_weight | text | Infant weight (g) | [pncb_access]='Y' | Yes |
| pncb_inf_length | text | Height/Length (cm) | [pncb_access]='Y' | Yes |
| pncb_inf_head_circum | text | Head circumference (cm) | [pncb_access]='Y' | Yes |
| pncb_inf_health_assess | checkbox | 1=High temperature,2=Jaundice,3=Thrush,4=Oedema,5=Fast breathing,6=Hypothermia,7=Convulsions,8=Lethargy,9=Malformations,10=Other | [pncb_access]='Y' | Yes |
| pncb_muac | yesno | MUAC measured? | [pncb_access]='Y' | Yes |
| pncb_muac_measurement | text | MUAC (cm) | [pncb_muac]='1' | Yes |
| pncb_muac_nd | notes | Why not done? | [pncb_muac]='0' | Yes |
| pncb_wasting | radio | 1=Severe,2=Moderate,3=No | [pncb_access]='Y' | Yes |
| pncb_overweight | radio | 1=Severe,2=Moderate,3=No | [pncb_access]='Y' | Yes |
| pncb_stunted | radio | 1=Severe,2=Moderate,3=No | [pncb_access]='Y' | Yes |
| pncb_underweight | radio | 1=Severe,2=Moderate,3=No | [pncb_access]='Y' | Yes |
| pncb_imam_admission | radio | 1=Admitted,2=Relapse,3=Transfer in,4=Referred,5=Opted Out,6=Not Applicable | [pncb_access]='Y' | Yes |
| pncb_imam_outcome | radio | 1=Cured,2=Defaulter,3=Died,4=Non‑recovery,5=Transfer out | [pncb_imam_admission] in (1,2,3,4,5) | Yes |
| pncb_feeding_option | radio | 1=Exclusive breast feeding,2=Exclusive formula,3=Mixed feeding,4=Breast + complementary,5=Solid/semi‑solid only,6=Breast discontinued | [pncb_access]='Y' | Yes |
| pncb_min_diet_diversity | yesno | Minimum Dietary Diversity? | [pncb_feeding_option] in (3,4,5,6) | Yes |
| pncb_inf_eat_min_diet | checkbox | 1=Grains/roots/tubers,2=Legumes/nuts,3=Dairy,4=Flesh foods,5=Eggs,6=Vitamin A rich fruits/veg,7=Other fruits/veg | [pncb_min_diet_diversity]='1' | Yes |
| pncb_min_meal_frequency | yesno | Minimum Meal Frequency? | [pncb_feeding_option] in (3,4,5,6) | Yes |
| pncb_min_meal_frequency_on | radio | 1=2 times (6‑8.9m breastfed),2=3 times (9‑23.9m breastfed),3=4 times (6‑23.9m non‑breastfed) | [pncb_min_meal_frequency]='1' | Yes |
| pncb_malnutrition_treated | radio | T=Treated, R=Referred, N=Not Treated | [pncb_access]='Y' | Yes |
| pncb_vitamin_a | radio | 1=Yes,0=No,2=Not Applicable | [pncb_access]='Y' | Yes |
| pncb_bcg | radio | 1=Yes,0=No,2=NA | [pncb_access]='Y' | Yes |
| pncb_not_given | notes | Why BCG not given? | [pncb_bcg]='0' | Yes |
| pncb_pentavalent | radio | 1=Yes,0=No,2=NA | [pncb_access]='Y' | Yes |
| pncb_ipv | radio | 1=Yes,0=No,2=NA | [pncb_access]='Y' | Yes |
| pncb_opv | radio | 1=Yes,0=No,2=NA | [pncb_access]='Y' | Yes |
| pncb_pneumococcal | radio | 1=Yes,0=No,2=NA | [pncb_access]='Y' | Yes |
| pncb_rotavirus | radio | 1=Yes,0=No,2=NA | [pncb_access]='Y' | Yes |
| pncb_measles_rubella | radio | 1=Yes,0=No,2=NA | [pncb_access]='Y' | Yes |
| pncb_dpt | radio | 1=Yes,0=No,2=NA | [pncb_access]='Y' | Yes |
| pncb_child_tb_exposed | radio | 1=Yes,2=No,3=Unknown | [pncb_access]='Y' | Yes |
| pncb_ipt_given | radio | 1=Yes,2=No,3=Not Applicable | [pncb_child_tb_exposed]='1' | Yes |
| pncb_ipt_specify | text | Specify medicine | [pncb_ipt_given]='1' | |
| pncb_hiv_exposed | yesno | Child HIV exposed? | [pncb_access]='Y' | Yes |
| pncb_arv_prophylaxis | radio | 1=Yes,0=No,2=NA | [pncb_hiv_exposed]='1' | Yes |
| pncb_arv_specify | text | Specify medicine | [pncb_arv_prophylaxis]='1' | |
| pncb_hiv_test_done | yesno | HIV test done? | [pncb_access]='Y' | Yes |
| pncb_hiv_test_type | radio | 1=Virological,2=Antibody | [pncb_hiv_test_done]='1' | Yes |
| pncb_result_given_client | yesno | HIV results given to client? | [pncb_hiv_test_type] in (1,2) | Yes |
| pncb_cotrimoxazole | radio | I=Initiated, R=Resupply, NA=Not Applicable | [pncb_result_given_client] in (1,0) | Yes |
| pncb_art_given | radio | I/R/NA | [pncb_cotrimoxazole] in (I,R,NA) | Yes |
| pncb_blood_vl | radio | 1=Yes,0=No,2=NA | [pncb_art_given] in (NA,R,I) | Yes |
| pncb_vl_result | text | VL result | [pncb_blood_vl]='1' | Yes |
| pncb_infant_follow_ups | radio | 1=Infant HIV negative,2=Infant HIV positive,3=In Care,4=Infant transferred out,5=Infant lost to follow up,6=Infant dead | [pncb_access]='Y' | Yes |

---

### PrEP Initial Register
**Instrument Name:** prep_initial_register  
**Access:** prepr_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| prepr_access | radio | Did client access? Y/N/UN | | Yes |
| prepr_date | text | Enroll Date | [prepr_access]='Y' | Yes |
| prepr_transfer_in | yesno | Transfer In? | [prepr_access]='Y' | Yes |
| prepr_screened | yesno | Screened for PrEP eligibility? | [prepr_access]='Y' | Yes |
| prepr_screen_outcome | radio | E=Eligible, NE=Not Eligible | [prepr_screened]='1' | Yes |
| prepr_clinical_eligible | yesno | Clinical eligibility? | [prepr_access]='Y' | Yes |
| prepr_visit_status | radio | N=Newly Initiated, C=Continuing, D=Discontinued | [prepr_access]='Y' | Yes |
| prepr_discontinued_reason | checkbox | 1=Change in risk profile,2=Plans to reinitiate,3‑5=Adverse events (mild/moderate/severe),6=Creatinine fail,7=Partner/parent concerns,8=Prefers other prevention,9=Pill burden,10=Change in location,11=Fear of retest,12=Other | [prepr_visit_status]='D' | Yes |
| prepr_discontinued_specify | notes | Specify | [prepr_discontinued_reason(12)]='1' | Yes |
| prepr_prep_type | radio | O=Oral, R=Ring, I=Injectable | [prepr_access]='Y' | Yes |
| prepr_prep_number | text | PrEP number | [prepr_access]='Y' | Yes |
| prepr_age | radio | Age groups: 1=10‑14,2=15‑19,… 12=65+ | [prepr_access]='Y' | Yes |
| prepr_age_explain | notes | Explain if mature minor (<16) | [prepr_age] in (1,2) | Yes |
| prepr_entry_point | radio | A=ANC, B=VCT, C=STI, D=VMMC, E=InPatient, F=TB | [prepr_access]='Y' | Yes |
| prepr_risk_assessment | radio | HR=High risk, NHR=Not high risk, AcHIV=Suspected acute HIV, PEP=Post‑exposure prophylaxis offered, REQ=Client requests PrEP, ND=Not done | [prepr_access]='Y' | Yes |
| prepr_creatinine_done | yesno | Creatinine clearance test done? | [prepr_access]='Y' | Yes |
| prepr_creatinine | radio | P=Pass (>60ml/min), F=Fail | [prepr_creatinine_done]='1' | Yes |
| prepr_sti_screening | yesno | STI Screening done? | [prepr_access]='Y' | Yes |
| prepr_sti_results | radio | NEG=No STI, HepB, HepC, Syph, O=Other | [prepr_sti_screening]='1' | Yes |
| prepr_other_sti | notes | Specify other STIs | [prepr_sti_results]='O' | Yes |
| prepr_sti | descriptive | “Please complete the STI Register” | [prepr_sti_results] in (HepB,HepC,Syph,O) or [prepr_other_sti] not empty | |
| prepr_prep_initiate | yesno | Client agrees to initiate PrEP? | [prepr_access]='Y' | Yes |
| prepr_first_prep | yesno | First initiation this year? | [prepr_prep_initiate]='1' | Yes | @HIDDEN |
| prepr_prep_initiate_no | notes | Why reject? | [prepr_prep_initiate]='0' | Yes | @HIDDEN |
| prepr_prep_start_date | text | PrEP start date | [prepr_prep_initiate]='1' | Yes |

---

### PrEP Follow‑ups
**Instrument Name:** prep_follow_ups  
**Access:** prep_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| prep_access | radio | Did client access? Y/N/UN | | Yes |
| prep_visit_date | text | Visit date | [prep_access]='Y' | Yes |
| prep_next_visit_date | text | Next visit date | [prep_access]='Y' | Yes |
| prep_hiv_test | yesno | HIV test done? | [prep_access]='Y' | Yes |
| prep_hiv_test_results | radio | P/N/I | [prep_hiv_test]='1' | Yes |
| prep_hiv_test_nd | notes | Why not done? | [prep_hiv_test]='0' | Yes |
| prep_visit_status | radio | E=Earlier, OT=On time, L=Late (<2w), D=Default (>2w,<90d), P=Previously LTFU (>90d) | [prep_access]='Y' | Yes |
| prep_risk_assessment | radio | same as initial | [prep_access]='Y' | Yes |
| prep_prep_type | radio | O/R/I | [prep_access]='Y' | Yes |
| prep_pbreast | radio | P=Pregnant, B=Breast Feeding, NA=Not Applicable | [prep_access]='Y' | Yes |
| prep_follow_up_status | radio | Px=Active, OO=Opted Out, WTH=Withdrawn, TO=Transfer out, TI=Transfer in, SC=Seroconversion, D=Died, LTFU=Lost | [prep_access]='Y' | Yes |
| prep_adverse_events | radio | NON=Non reported, MIL=Mild, MOD=Moderate, SAE=Severe | [prep_access]='Y' | Yes |
| prep_copt_out_date | text | Date of Opt Out | [prep_follow_up_status]='OO' | Yes |
| prep_cwithdrawal_date | text | Date of Clinical Withdrawal | [prep_follow_up_status]='WTH' | Yes |
| prep_withdrawal_reason | radio | RP=Change in risk, REF=Plans re‑initiate, MIL/MOD/SAE, CRCL=Creatinine fail, PAR=Partner/parent, OPREV=Prefers other, PD=Pill burden, CIL=Change location, FPHR=Fear retest, O=Other | [prep_follow_up_status] in (OO,WTH,TO,LTFU) | Yes |
| prep_with_other | text | Other specify | [prep_withdrawal_reason]='O' | Yes |
| prep_client_outcome | radio | 1=Switching oral to injectable,2=Switching injectable to oral,3=Discontinued,4=Transferred out | [prep_access]='Y' | Yes |
| prep_service_provider | radio | 1=Provider,2=Nurse,3=Midwife | [prep_access]='Y' | Yes |
| prep_comments | notes | Comments | [prep_access]='Y' | |

---

### Mental Health
**Instrument Name:** mental_health  
**Access:** mh_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| mh_access | radio | Did client access? Y/N/UN | | Yes |
| mh_screening_tools | yesno | Does client have/undergo any Mental Health Screening Tools? | [mh_access]='Y' | Yes |
| mh_screening_results | radio | P=Positive, N=Negative, NA=Not Applicable | [mh_screening_tools]='1' | Yes |
| mh_management_outcome | radio | R=Referred, M=Managed, B=Both, N=Not Applicable | [mh_screening_tools]='1' | Yes |
| mh_substance_identified | yesno | Substance use identified? | [mh_screening_tools]='1' | Yes |
| mh_substance_used | checkbox | 1=Benzodiazepines,2=Opioids,3=Alcohol,4=Crystal Meth,5=Cannabis,6=Cocaine,7=Homemade,8=Other | [mh_substance_identified]='1' | Yes |
| mh_substance_oth | notes | Specify other | [mh_substance_used(8)]='1' | Yes |
| mh_pnq | radio | A=2 Points, B=3‑4, C=5‑6, D=7‑8, E=9‑10 | [mh_screening_tools]='1' | |
| mh_ssq | radio | 0=None,1=Slight,2=Moderate,3=Severe | [mh_screening_tools]='1' | |
| mh_substance_use | text | Substance use | [mh_screening_tools]='1' | | @HIDDEN |

---

### Health Education
**Instrument Name:** health_education  
**Access:** he_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| he_access | radio | Did client access? Y/N/UN | | Yes |
| he_receive | yesno | Did client receive health education? | [he_access]='Y' | Yes |
| he_receive_from | checkbox | 1=Peer,2=Adolescent nurse,3=VHW,4=Other HCW,5=Digital Health Hub App | [he_receive]='1' | Yes |
| he_topics | checkbox | 1=STI,2=HIV,3=PrEP,4=Contraception,5=Menstrual Health,6=ANC,7=PNC,8=Depression,9=Anxiety,10=Substance use,11=Other | [he_receive_from] any selected | Yes |
| hp_topics_other | text | Specify other | [he_topics(11)]='1' | Yes |

---

### Counselling
**Instrument Name:** counselling  
**Access:** couns_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| couns_access | radio | Did client access? Y/N/UN | | Yes |
| couns_receive | yesno | Did client receive any counselling? | [couns_access]='Y' | Yes |
| counc_who | checkbox | 1=Peer,2=Adolescent Nurse,3=Counsellor,4=VHW,5=Other HCW | [couns_receive]='1' | Yes |

---

### Peer Support
**Instrument Name:** peer_support_register  
**Access:** pls_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| pls_access | radio | Did client access? Y/N/UN | | Yes |
| pls_session_conducted | yesno | Was a peer‑led session conducted? | [pls_access]='Y' | Yes |
| pls_date | text | Session date | [pls_session_conducted]='1' | Yes |
| pls_number | text | Number of peer‑led sessions conducted | [pls_session_conducted]='1' | Yes |
| pls_ado_number | text | Number of adolescents reached | [pls_session_conducted]='1' | Yes |
| pls_support_conducted | yesno | Was a support group conducted? | [pls_access]='Y' | Yes |

---

### HTS (HIV Testing Services)
**Instrument Name:** hiv_testing_services_hts_register  
**Access:** hts_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| hts_access | radio | Did client access? Y/N/UN | | Yes |
| hts_tested | yesno | Was client tested for HIV? | [hts_access]='Y' | Yes |
| hts_hiv_date | text | HIV Test date | [hts_tested]='1' | Yes |
| hts_hiv_result | radio | P=Positive, N=Negative | [hts_tested]='1' | Yes |
| hts_art_init | radio | Was client initiated on ART? Y/N/NA | [hts_access]='Y' | Yes |

---

## OIART Sub‑Project
**Instruments:** Demographics (shared), STI Register, Family Planning, PrEP Initial, PrEP Follow‑ups, Mental Health, Health Education, Counselling, Peer Support, OIART Initial Register, OIART Initial Baseline, OIART Follow‑ups.

The shared instruments (STI, FP, PrEP, MH, HE, Counselling, Peer Support) are identical to those in FCH, so they are not repeated here.

### OIART Initial Register (artr_access)
**Instrument Name:** oiart_initial_register  
**Access:** artr_access = 'Y'  
**Fields:** (selected key fields)

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| artr_access | radio | Did client access? Y/N/UN | | Yes |
| artr_registration_date | text | Date of registration/enrollment into HIV Care | [artr_access]='Y' | Yes |
| artr_number | text | OI or ART Number | [artr_access]='Y' | |
| artr_referred | yesno | Was client referred? | [artr_access]='Y' | Yes |
| artr_linkage_have | yesno | Does client have a linkage number? | [artr_referred]='1' | Yes |
| artr_linkage_type | radio | 1=EID,2=HTS,3=PMTCT,4=STI,5=TB,6=VMMC,7=Other | [artr_linkage_have]='1' | Yes |
| artr_linkage_type_othr | text | Other specify | [artr_linkage_type]='7' | Yes |
| artr_linkage_number | text | Linkage Number | [artr_linkage_type] in (1..7) | Yes |
| artr_referred_institution | text | Name of referring institution | [artr_linkage_have]='0' | Yes |
| artr_first_hiv_test | text | Date of first confirmed HIV test | [artr_access]='Y' | |
| artr_ist_hiv_inst | text | Institution where first test done | [artr_access]='Y' | |
| artr_hiv_test_type | radio | 1=AB, 2=PCR | [artr_access]='Y' | |
| artr_hiv_test_reason | radio | 1=Antenatal,2=PrEP,3=PEP,4=Hospital/illness,5=TB,6=VCT,7=Occupational,8=Confirmatory,9=Retesting for ART,10=Death of child/spouse,11=Spouse/child<5 on ART,12=Other | [artr_access]='Y' | |
| artr_hiv_reas_oth | text | Other reason | [artr_access]='Y' | @HIDDEN |
| artr_symptoms | checkbox | 1=Flue‑like,2=Fever>1m,3=Dysphagia,4=Abdominal pain,5=Chronic fatigue,6=Numbness,7=Headache,8=Cough,9=Productive cough,10=Labored respiration,11=Night sweats,12=Nausea/vomiting,13=Diarrhoea>1m,14=Chronic pain,15=Burning hands/feet,16=Other | [artr_access]='Y' | Yes |
| artr_symptoms_oth | notes | Specify other symptoms | [artr_symptoms(16)]='1' | Yes |
| artr_hiv_signs | checkbox | 1=Coughing,2=Pregnant,3=Neck stiffness,4=Genital ulcer,5=Mental confusion,6=Failure to thrive,7=Weight loss,8=Other | [artr_access]='Y' | Yes |
| artr_signs_oth | notes | Specify other signs | [artr_hiv_signs(8)]='1' | Yes |
| artr_weight_loss | radio | 1=Nil,2=<10%,3=>10% | [artr_hiv_signs(7)]='1' | Yes |
| artr_medical_history | checkbox | 1=Wasting syndrome,2=Thrush,3=Oesophageal candidiasis,4=Lymphadenopathy,5=Herpes simplex,6=Herpes Zoster,7=Cervical screening,8=Kaposi Sarcoma,9=Cryptococcal meningitis,10=Diarrhoea>1m,11=Recurrent URTI,12=Fever>1m,13=Recurrent pneumonia,14=TB‑Pulmonary,15=TB‑Extra Pulmonary,16=Family TB,17=Heavy alcohol,18=Other skin rash,19=Disseminated crypto,20=Diabetes,21=Hypertension,22=Obesity,23=Ischemic heart,24=Other | [artr_access]='Y' | Yes |
| artr_medhistory_oth | notes | Specify other | [artr_medical_history]='24' | Yes |
| artr_bio_contacts | yesno | Has biological contacts? | [artr_access]='Y' | Yes |
| artr_contacts_count | text | Number of contacts | [artr_bio_contacts]='1' | Yes |
| _1st_contact_... | various | Name, relation, gender, DOB, tested, result, in care, on ART, number | [artr_bio_contacts]='1' | Varies |
| _2nd_contact_... | various | Same as above | [artr_bio_contacts]='1' && [artr_contacts_count]>1 | Varies |
| _3rd_contact_... | various | Same as above (only relation, gender, DOB, tested, result, care, ART, number) | [artr_bio_contacts]='1' && [artr_contacts_count]>2 | Varies |
| artr_prophylaxis | yesno | Drug history or prophylaxis | [artr_access]='Y' | Yes |
| artr_prophylaxis_regimen | radio | 1=sNVP,2=AZT,3=3TC,4=PrEP | [artr_prophylaxis]='1' | Yes |
| artr_prophy_date_start | text | Date started | [artr_prophylaxis]='1' | |
| artr_prophy_date_last | text | Date last taken | [artr_prophylaxis]='1' | |
| artr_arv_exposure | yesno | Prior ARV exposure | [artr_access]='Y' | Yes |
| artr_arv_exposure_regimen | radio | same as above | [artr_arv_exposure]='1' | Yes |
| artr_arv_date_start | text | Date started | [artr_arv_exposure]='1' | |
| artr_arv_date_end | text | Date completed | [artr_arv_exposure]='1' | |
| artr_pmtctc_exposure | yesno | Prior PMTCT exposure | [artr_access]='Y' | Yes |
| artr_pmtctc_exp_dose | text | Dosage (mg) | [artr_pmtctc_exposure]='1' | Yes |
| artr_pmtctc_date_start | text | Date started | [artr_pmtctc_exposure]='1' | |
| artr_pmtctc_date_end | text | Date completed | [artr_pmtctc_exposure]='1' | |
| artr_cotrimoxazole | yesno | Cotrimoxazole | [artr_access]='Y' | Yes |
| artr_cotrimoxazole_dose | text | Dosage (mg) | [artr_cotrimoxazole]='1' | Yes |
| artr_cotri_date_start | text | Date started | [artr_cotrimoxazole]='1' | |
| artr_cotri_date_ended | text | Date stopped | [artr_cotrimoxazole]='1' | |
| artr_fluconazole | yesno | Fluconazole | [artr_access]='Y' | Yes |
| artr_fluconazole_dosage | text | Dosage | [artr_fluconazole]='1' | Yes |
| artr_fluco_date_start | text | Date started | [artr_fluconazole]='1' | |
| artr_fluco_date_end | text | Date completed | [artr_fluconazole]='1' | |
| artr_tb_therapy | yesno | Current TB therapy | [artr_access]='Y' | Yes |
| artr_tb_category | radio | 1=Category 1,2=Category 2 | [artr_tb_therapy]='1' | Yes |
| artr_other_allrgy | text | Other drug allergies | [artr_access]='Y' | |

---

### OIART Initial Baseline (artib_access)
**Instrument Name:** oiart_initial_baseline  
**Access:** artib_access = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| artib_access | radio | Did client access? Y/N/UN | | Yes |
| artib_cd4 | yesno | CD4 count done? | [artib_access]='Y' | Yes |
| artib_cd4_date | text | CD4 date | [artib_cd4]='1' | Yes |
| artib_cd4_count | text | CD4 result | [artib_cd4]='1' | Yes |
| artib_cd4_nd | notes | Why not done? | [artib_cd4]='0' | Yes |
| artib_cd4_perc | yesno | CD4% done? | [artib_access]='Y' | Yes | @HIDDEN |
| artib_cd4_percent_result | text | CD4% | [artib_cd4_perc]='1' | Yes |
| artib_cd4_perc_date | text | CD4% date | [artib_cd4_perc]='1' | Yes |
| artib_cd4_perc_nd | notes | Why not done? | [artib_cd4_perc]='0' | Yes |
| artib_hb | yesno | HB done? | [artib_access]='Y' | Yes |
| artib_hb_result | text | HB result | [artib_hb]='1' | Yes |
| artib_hb_result_date | text | HB date | [artib_hb]='1' | Yes |
| artib_hb_nd | notes | Why not done? | [artib_hb]='0' | Yes |
| artib_alt | yesno | ALT done? | [artib_access]='Y' | Yes |
| artib_alt_result | text | ALT result (mmol/L) | [artib_alt]='1' | Yes |
| artib_alt_result_date | text | ALT date | [artib_alt]='1' | Yes |
| artib_alt_nd | notes | Why not done? | [artib_alt]='0' | Yes |
| artib_wbc | yesno | WBC done? | [artib_access]='Y' | Yes |
| artib_wbc_result | text | WBC result (cell/ul) | [artib_wbc]='1' | |
| artib_wbc_result_date | text | WBC date | [artib_wbc]='1' | Yes |
| artib_wbc_nd | notes | Why not done? | [artib_wbc]='0' | Yes |
| artib_creatinine | yesno | Creatinine done? | [artib_access]='Y' | Yes |
| artib_creatinine_result | text | Creatinine result | [artib_creatinine]='1' | Yes |
| artib_creatin_result_date | text | Creatinine date | [artib_creatinine]='1' | Yes |
| artib_creatin_nd | notes | Why not done? | [artib_creatinine]='0' | Yes |
| artib_viral_load | yesno | VL done? | [artib_access]='Y' | Yes |
| artib_viral_load_value | yesno | Results in number format? | [artib_viral_load]='1' | Yes |
| artib_viral_load_result | text | VL result (copies/ml) | [artib_viral_load_value]='1' | Yes |
| artib_viral_load_result_2 | text | VL result (if not numeric) | [artib_viral_load_value]='0' | Yes |
| artib_v_load_result_date | text | VL date | [artib_viral_load]='1' | Yes |
| artib_v_load_nd | notes | Why not done? | [artib_viral_load]='0' | Yes |
| artib_weight | text | Weight (kg) | [artib_access]='Y' | Yes |
| artib_height | text | Height (cm) | [artib_access]='Y' | Yes |
| artib_bp | yesno | BP measured? | [artib_access]='Y' | Yes |
| artib_bp_systolic | text | Systolic | [artib_bp]='1' | Yes |
| artib_bp_diastolic | text | Diastolic | [artib_bp]='1' | Yes |
| artib_temperature | text | Temperature | [artib_access]='Y' | Yes |
| artib_pulse | text | Pulse | [artib_access]='Y' | Yes |
| artib_lymph_nodes | yesno | Enlarged lymph nodes? | [artib_access]='Y' | Yes |
| artib_pallor | yesno | Pallor? | [artib_access]='Y' | Yes |
| artib_jaundice | yesno | Jaundice? | [artib_access]='Y' | Yes |
| artib_cyanosis | yesno | Cyanosis? | [artib_access]='Y' | Yes |
| artob_mental_status | radio | N=Normal, A=Abnormal | [artib_access]='Y' | Yes |
| artib_central_nevous | radio | N=Normal, A=Abnormal | [artib_access]='Y' | Yes |

---

### OIART Follow‑ups (ART)
**Instrument Name:** oiart_follow_ups  
**Access:** art_access = 'Y'  
**Fields:** (selected – many fields are present; this is a summary)

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| art_access | radio | Did client access? Y/N/UN | | Yes |
| art_visit_number | text | Visit number | [art_access]='Y' | Yes |
| art_review_date | text | Date of review | [art_access]='Y' | Yes |
| art_visit_type | radio | A=Self (not DSD),B=Caregiver,C=Another clinic,D=Malayitsha,E=CARG,F=Clubs,G=Fast Track,H=Outreach,I=Drop‑in,J=OFCAD,K=Private Pharmacy,L=Other | [art_access]='Y' | Yes |
| art_visit_specify | text | Other specify | [art_visit_type]='L' | Yes |
| art_weight | text | Weight (kg) | [art_access]='Y' | Yes |
| art_height | text | Height (cm) | [art_access]='Y' | Yes |
| art_bmi | calc | BMI = (100*100*weight)/(height^2) | [art_access]='Y' | |
| art_bp | yesno | BP measured? | [art_access]='Y' | Yes |
| art_systolic | text | Systolic | [art_bp]='1' | Yes |
| art_diastolic | text | Diastolic | [art_bp]='1' | Yes |
| art_bp_nd | notes | Why not measured? | [art_bp]='0' | Yes |
| art_preg_lact | radio | P=Pregnant, EBF=Exclusive Breast Feeding, EFF=Exclusive Formula, MF=Mixed (<6m), BFCF=Breast + Complementary, SBF=Stopped BF, NPL=Neither, NA=Not applicable | [art_access]='Y' | Yes |
| art_family_planning | checkbox | A=Abstinence, O=Not using, P=Pills, J=Injections, M=Implants, Z=Sterilization, C=Condom, T=Traditional, L=IUD, D=Dual | [art_access]='Y' | Yes |
| art_functional_status | radio | W=Work/School, A=Ambulatory, B=Bedridden | [art_access]='Y' | Yes |
| art_who_stage | radio | 1‑4 | [art_access]='Y' | Yes |
| art_tb_screening | yesno | TB screening done? | [art_access]='Y' | Yes |
| art_tb_screening_result | radio | Y=Screened no signs, S=Presumptive, ON=On TB treatment, N=Not assessed | [art_tb_screening]='1' | Yes |
| art_tb_screening_nd | notes | Why not done? | [art_tb_screening]='0' | Yes |
| art_tb_investigations | yesno | TB investigations done? | [art_access]='Y' | Yes |
| art_tb_inv_type | radio | 1=Xpert MTB/Rif,2=Ultra Lf‑LAM,3=TST | [art_tb_investigations]='1' | Yes |
| art_tb_inv_result | radio | 1=Active TB not started,2=Active TB started,3=No active TB | [art_tb_inv_type] in (1,2,3) | Yes |
| art_tb_inv_nd | notes | Why not done? | [art_tb_investigations]='0' | Yes |
| art_opp_infections | checkbox | Z=Zoster,P=Pneumonia,D=Dementia,T=Thrush,U=Ulcers,I=IRIS,W=Weight loss,To=Toxoplasmosis,STI,Hypertension,Cx=Cancer,DM=Diabetes,Screened,HBV,HCV,O=Other | [art_access]='Y' | Yes |
| art_hypertension_status | radio | 1=Diagnosed,2=Managed | [art_opp_infections(H)]='1' | Yes |
| art_dm_status | radio | D1=Screened,T1D,T2D,D3=Managed | [art_opp_infections(DM)]='1' | Yes |
| art_hbv_status | radio | 1=Tested,2=Positive,3=On TDF | [art_opp_infections(HBV)]='1' | Yes |
| art_hcv_status | radio | 1=Tested,2=Positive,3=Treated,4=Cured | [art_opp_infections(HCV)]='1' | Yes |
| art_infections_other | text | Specify other | [art_opp_infections(O)]='1' | Yes |
| art_mental_disorders | yesno | Screened for mental health? | [art_access]='Y' | Yes |
| art_mental_result | radio | ND=No disorder,D=Depression,A=Anxiety,SA=Substance misuse,O=Other | [art_mental_disorders]='1' | Yes |
| art_mental_management | radio | R=Referred,Rx=Treated,NT=Not Treated,NA=Not Applicable | [art_mental_disorders]='1' | Yes |
| art_contrimoxazole | yesno | On Cotrimoxazole? | [art_access]='Y' | Yes |
| art_cotri_type | radio | 1=Tablets,2=Liquid | [art_contrimoxazole]='1' | Yes |
| art_cotro_quantity | text | Quantity dispensed | [art_cotri_type] in (1,2) | Yes |
| art_cotri_adherence | text | Adherence (%) | [art_cotri_type] in (1,2) | |
| art_tpt | yesno | Eligible for TPT? | [art_access]='Y' | Yes |
| art_tpt_ineligible | radio | TB=Active TB,ON=On TB Tx,AL=Active liver disease,AA=Heavy alcohol,CPT=Completed IPT,DDI=Drug interactions | [art_tpt]='0' | Yes |
| art_tpt_status | radio | AT=Active TB,II=INH Initiated,3I=3HP Initiated,CT=Continue INH,TC=INH Completed,IAT=INH stopped due to TB,PB=Pregnant (switch),RI=Restart INH,R3=Restart 3HP,TNI=Not initiated,PN=Peripheral neuropathy,PP=Patient refused | [art_tpt]='1' | Yes |
| art_tpt_type | radio | 1=Tablets,2=Liquid | [art_tpt]='1' | Yes |
| art_tpt_med_quantity | text | Quantity | [art_tpt_type] in (1,2) | Yes |
| art_tpt_med_adherence | text | Adherence | [art_tpt_type] in (1,2) | Yes |
| art_crypro | yesno | Cryptococcal screening done? | [art_access]='Y' | Yes |
| art_crypto_result | radio | P=Positive,N=Negative,NA=Not Assessed | [art_crypro]='1' | Yes |
| art_crypto_nd | notes | Why not done? | [art_crypro]='0' | Yes |
| art_crypto_investigation | yesno | CSF investigation done? | [art_access]='Y' | Yes |
| art_csf_inv_result | radio | P/N | [art_crypto_investigation]='1' | Yes |
| art_crypto_pre_results | yesno | Pre‑emptive treatment results? | [art_crypto_investigation]='1' | Yes |
| art_meningitis_treatment | radio | 1=Liposomal AmB + Flucytosine + Fluconazole,2=AmB + Flucytosine,3=Flucytosine + Fluconazole,4=Other | [art_crypto_pre_results]='1' | Yes |
| art_crypto_treat_oth | notes | Other treatments | [art_access]='Y' | Yes |
| art_arv_status | radio | 1=No ARV,2=Start ARV,3=Continue,4=Change,5=Stop,6=Restart | [art_access]='Y' | Yes |
| art_arv_category | radio | 1=Newly Initiated (N1),2=Re‑initiated<3m (N2.1),3=Re‑init 3‑5m (N2.2),4=Re‑init 6+m (N2.3),5=Re‑engagement<3m (N3.1),6=Re‑engage 3‑5m (N3.2),7=Re‑engage 6+m (N3.3),8=Transfer in (N4) | [art_arv_status] in (2,3,4,5,6) | Yes |
| art_adverse_status | radio | 1=INH minor,2=INH stop,3=3HP minor,4=3HP stop,5=CTX minor,6=CTX stop,7=Diflucan minor,8=Diflucan stop,9=1st line ART minor,10=1st line stop,11=2nd line minor,12=2nd stop,13=3rd minor,14=3rd stop | [art_access]='Y' | Yes |
| art_arv_no_reason | radio | Reasons for not on ARV (11‑19) | [art_arv_status]='1' | Yes |
| art_arv_start_reason | radio | 215=Treat all,216=Pregnant,217=Lactating,218=Other | [art_arv_status]='2' | Yes |
| art_arv_stop_reasons | radio | Reasons for stopping/changing (401‑427) | [art_access]='Y' | Yes |
| art_arv_stopadv_oth | text | Other adverse event | [art_arv_stop_reasons]='14' | Yes |
| art_arv_stop_oth | text | Other reason | [art_arv_stop_reasons]='20' | Yes |
| art_arv_regimen | radio | 1=Adult 1st,2=Adult 2nd,3=Adult 3rd,4=Children 1st,5=Children 2nd,6=Children 3rd | [art_arv_status] in (2,4,3,4,5) | Yes |
| art_arv_a1_combo | radio | 1‑17 (see CSV) | [art_arv_regimen]='1' | Yes |
| art_arv_a1reg_other | text | Other specify | [art_arv_a1_combo]='17' | Yes |
| art_arv_a2_combo | radio | 1‑15 | [art_arv_regimen]='2' | Yes |
| art_arv_a2reg_other | text | Other | [art_arv_a2_combo]='15' | Yes |
| art_arv_a3_combo | radio | 1=RAL/DRV/RTV,2=Other | [art_arv_regimen]='3' | Yes |
| art_arv_a3reg_other | text | Other | [art_arv_a3_combo]='2' | Yes |
| art_arv_c1_combo | radio | 1‑10 | [art_arv_regimen]='3' | Yes |
| art_arv_c1reg_other | text | Other | [art_arv_c1_combo]='10' | Yes |
| art_arv_c2_combo | radio | 1‑12 | [art_arv_regimen]='3' | Yes |
| art_arv_c2reg_other | text | Other | [art_arv_c2_combo]='12' | Yes |
| art_arv_c3_combo | radio | 1=RAL/DRV/RTV,2=DTG+DRV+2NRTIs,3=Other | [art_arv_regimen]='3' | Yes |
| art_arv_c3reg_other | text | Other | [art_arv_c3_combo]='3' | Yes |
| art_arv_medicine | yesno | Did client receive ARV medicine? | [art_access]='Y' | Yes |
| art_arv_clinical | text | ARV Clinical Medicine (duration) | [art_arv_medicine]='1' | Yes |
| art_arv_adherence | text | Adherence (%) | [art_arv_medicine]='1' | |
| art_arv_pharmacy | text | Pharmacy ARV Medicine (quantity) | [art_arv_medicine]='1' | |
| art_viral_load | yesno | VL samples collected? | [art_access]='Y' | Yes |
| art_vl_collect_date | text | Collection date | [art_viral_load]='1' | Yes |
| art_vl_detected | yesno | VL detected? | [art_viral_load]='1' | Yes |
| art_vl_nd | notes | Why not done? | [art_viral_load]='0' | Yes |
| art_vl_received_date | text | Received date | [art_viral_load]='1' | Yes |
| art_vl_result | text | VL result (copies/ml) | [art_vl_detected]='1' | Yes |
| art_cerv_cancer | yesno | Cervical cancer screening done? | [art_access]='Y' | Yes |
| art_cerv_cancer_type | checkbox | 1=HPV Test,2=VIAC | [art_cerv_cancer]='1' | Yes |
| art_hpv_test_result | checkbox | P/N | [art_cerv_cancer_type(1)]='1' | Yes |
| art_viac_result | checkbox | P/N | [art_cerv_cancer_type(2)]='1' | Yes |
| art_cerv_treatment | yesno | Cervical cancer treatment administered? | [art_cerv_cancer]='1' | Yes |
| art_cerv_treat_nd | notes | Why not? | [art_cerv_treatment]='0' | Yes |
| art_cerv_treatment_type | radio | 1=VIAC Pos Cryotherapy,2=VIAC Pos Thermal Ablation,3=VIAC Pos Leep,4=Suspected Cancer,5=Hysterectomy,6=Refer for further investigation | [art_cerv_treatment]='1' | Yes |
| art_cd4 | yesno | CD4 done? | [art_access]='Y' | Yes |
| art_cd4_result | text | CD4 result | [art_cd4]='1' | Yes |
| art_cd4_nd | notes | Why not done? | [art_cd4]='0' | Yes |
| art_cd4_date | text | CD4 date | [art_cd4]='1' | Yes |
| art_cd4_perc | yesno | CD4% done? | [art_access]='Y' | Yes |
| art_cd4_perc_result | text | CD4% result | [art_cd4_perc]='1' | Yes |
| art_cd4_perc_date | text | CD4% date | [art_cd4_perc]='1' | Yes |
| art_next_review_date | text | Next ART review date | [art_access]='Y' | Yes |
| art_visit_status | radio | 1=Earlier,2=On Time,3=Late,4=Default <28d | [art_access]='Y' | Yes |
| art_final_outcome | radio | 1=Active on treatment,2=Missing appointments,3=LTFU,4=Transfer Out,5=Died,6=Opted Out,7=Other | [art_access]='Y' | Yes |
| art_final_outcome_oth | notes | Other outcome | [art_final_outcome]='7' | Yes |
| art_clinician_name | text | Name of clinician (initials) | | |

---

## OPD Sub‑Project
**Instruments:** Demographics (shared), STI Register, PrEP Initial, PrEP Follow‑ups, Mental Health, Health Education, Counselling, Peer Support, HTS, and **Outpatient**.

### Outpatient (OPD)
**Instrument Name:** outpatient  
**Access:** opd_service = 'Y'  
**Fields:**

| Field Name | Type | Label / Choices | Branching | Required |
|------------|------|-----------------|-----------|----------|
| opd_service | radio | Did client access this service? Y/N/UN | | Yes |
| opd_date | text | OPD Date | [opd_service]='Y' | Yes |
| op_a1 | checkbox | 1=Notifiable diseases,2=Diarrhea,3=Dysentery,4=Malaria,5=Ear Condition,6=Acute Respiratory Infections,7=Bilharzia,8=Acute Mental disorders,9=Diseases of the eye,10=Skin diseases,11=Injuries,12=Poisoning,13=Dental referrals,14=Nutritional Deficiencies,15=Anemia,16=Other,17=NIL | [opd_service]='Y' | Yes |
| op_notifiables_categ | radio | 1=Immunisable,2=Non‑immunisable,3=NIL | [op_a1(1)]='1' | Yes |
| op_notifiable_type | radio | 1=Rabies,2=TB,3=Anthrax,4=Cholera,5=Diphtheria,6=Other,7=NIL | [op_a1(1)]='1' | Yes |
| op_other_notifiables | text | Specify other | [op_notifiable_type]='6' | |
| op_diarrhea_type | radio | 1=No dehydration,2=With dehydration,3=NIL | [op_a1(2)]='1' | Yes |
| op_malaria_case | radio | 1=Suspected,2=Suspected tested,3=Confirmed positive,4=NIL | [op_a1(4)]='1' | Yes |
| op_acute_resp | radio | 1=Mild,2=Moderate (Pneumonia),3=Severe,4=NIL | [op_a1(6)]='1' | Yes |
| op_mental_disorder | notes | Comment on acute mental disorders | [op_a1(8)]='1' | Yes |
| op_eye_disease | radio | 1=Cataracts,2=Glaucoma,3=Refractive errors,4=All other,5=NIL | [op_a1(9)]='1' | Yes |
| op_eye_disease_other | text | Specify other | [op_eye_disease]='4' | |
| op_skin_disease | radio | 1=Chicken pox,2=Herpes zoster,3=Scabies,4=Other,5=NIL | [op_a1(10)]='1' | Yes |
| op_skin_disease_other | text | Specify other | [op_skin_disease]='4' | |
| op_injuries | radio | 1=Burns,2=Road traffic,3=Assaults,4=Occupational,5=All other,6=NIL | [op_a1(11)]='1' | Yes |
| op_injuries_other | text | Specify other | [op_injuries]='5' | |
| op_nutrition_deficiencies | radio | 1=Acute Malnutrition,2=Pellagra,3=NIL | [op_a1(14)]='1' | Yes |
| op_anemia | radio | 1=Mild,2=Moderate,3=Severe,4=NIL | [op_a1(15)]='1' | Yes |
| op_a1_other | notes | Details for other diagnosis | [op_a1(16)]='1' | Yes |
| op_hb_level | text | HB level | [op_anemia] in (1,2,3) | |
| opd_ncd | yesno | Does patient have any NCD condition? | | Yes |
| op_2 | checkbox | 1=Diabetes Type I,2=Diabetes Type II,3=Asthma,4=Leprosy,5=Hypertension,6=CVA/Stroke,7=Acquired Heart,8=Rheum‑Heart,9=Chron‑Heart Failure,10=Chron‑Renal Failure,11=Sickle Cell,12=Nephrotic Syndrome,13=Ischemia,14=NIL | [opd_ncd]='1' | |
| op_screen | descriptive | “After completing this form, screen for Mental Health, Health education and counselling.” | | |

---

## Relationship Between Instruments
- **Demographics** is shared across all three sub‑projects; each record has one demographic record.
- The three sub‑projects are FCH (`project_id` 76), OI/ART (`project_id` 78), and OPD (`project_id` 79).
- Common instruments across all three projects are Demographics (`demog_*`), STI Register (`sti_*`), PrEP Initial Register (`prepr_*`), PrEP Follow-ups (`prep_*`), Mental Health (`mh_*`), Health Education (`he_*`), Counselling (`couns_*`), Peer Support Register (`pls_*`), and HIV Testing Services Register (`hts_*`).
- FCH additionally contains Family Planning (`fp_*`), ANC Initial Register (`ancr_*`), ANC Booking Follow-ups (`anc_*`), Mother-Baby Pair Initial Register Per Baby (`pncr_*`), Mother Follow-ups (`pncm_*`), and Baby Follow-ups (`pncb_*`).
- OI/ART additionally contains OI/ART Initial Register (`artr_*`), OI/ART Initial Baseline (`artib_*`), and OI/ART Follow-ups (`art_*`).
- OPD additionally contains Outpatient (`opd_*`). Its visits are identified by `instance` and/or `opd_date`.
- Service‑specific instruments (STI, FP, ANC, PNCR, PrEP, ART, OPD, etc.) are filled per client visit or encounter.
- Branching logic ensures that only relevant fields appear (e.g., contraception details only if fp_access='Y').
- Some instruments are referenced by others (e.g., `sti_register` is recommended when PrEP STI screening is positive).
- For ART, the `artr_initial_register` and `artib_initial_baseline` capture initial data, while `oiart_follow_ups` captures subsequent visits.
- Mother‑baby pair tracking uses a common `Mother‑Baby Number` across PNCR, PNCM, and PNCB forms.

### Registration and Follow-up Conventions

For one REDCap record, the following prefixes identify the lifecycle of a service:

| Workflow | Registration / initial form | Follow-up form(s) | Rule |
|----------|-----------------------------|-------------------|------|
| ANC | `ancr_*` (ANC Initial Register) | `anc_*` (ANC follow-ups) | `ancr_*` is collected once when the client registers for ANC. Later visits use `anc_*`. |
| Mother-baby | `pncr_*` (mother-baby pair initial register) | `pncm_*` (mother) and `pncb_*` (baby) | `pncr_mother_baby` is the unique mother-baby number. Each baby has its own mother-baby registration and number; subsequent mother and baby visits use their respective follow-up prefixes. |
| PrEP | `prepr_*` (PrEP Initial Register) | `prep_*` (PrEP follow-ups) | `prepr_*` is collected once for the initial registration; later visits use `prep_*`. |
| OI/ART | `artr_*` (OI/ART Initial Register), then `artib_*` (OI/ART Initial Baseline) | `art_*` (OI/ART follow-ups) | `artr_*` registers the client, `artib_*` captures the initial baseline, and later visits use `art_*`. The follow-up visit date is `art_review_date`; `instance` remains the repeat discriminator when available. |

The following instruments use one field-prefix family for both initial and repeat visits. Repeat visits are identified as follows:

| Instrument | Field prefix | Repeat-visit rule |
|------------|--------------|------------------|
| STI Register | `sti_*` | Prefer `instance`; use `sti_visit_date` (and `sti_date` where applicable) as the visit date and fallback repeat key when no usable instance exists. |
| Family Planning | `fp_*` | Prefer `instance`; use `fp_date` as the encounter date and fallback repeat key when no usable instance exists. |
| Mental Health | `mh_*` | Use `instance`. |
| Health Education | `he_*` | Use `instance`. |
| Counselling | `couns_*` | Use `instance`. |
| Peer Support Register | `pls_*` | Use `instance`. |
| HIV Testing Services | `hts_*` | Use `instance`. |
| OI/ART follow-ups | `art_*` | Prefer `instance`; use `art_review_date` as the business visit date for ordering, validation, and reporting. |
| OPD | `opd_*` | Prefer `instance`; use `opd_date` as the business visit date and fallback repeat key when no usable instance exists. |

The date fields describe the business date of an encounter. When an `instance` exists, it must be retained as the primary repeat discriminator for that form and event, with dates used for ordering, validation, and reporting. If `instance` is absent or unusable for STI or Family Planning, the relevant date may provide a provisional date-derived repeat key. Duplicate or conflicting dates should be flagged for data quality review.

The generic term `pnc_*` can describe the PNC family, but fields must be interpreted using the concrete prefixes `pncm_*` and `pncb_*` so mother and baby follow-ups remain distinct.

In `redcap_data6`, each field is a separate long-format row. Registration and follow-up rows must be grouped by `project_id`, `record`, `event_id`, form/instrument, and `instance`. `event_id` links to `redcap_events_metadata.event_id`, whose `arm_id` links the event to its arm. A repeating instrument can have multiple instances within one event. The source instance value must be preserved; reporting code may additionally normalize `NULL`/blank (or an exported `0`) to the first occurrence, without treating instance numbers from different instruments as the same visit.

### Cross-Project Patient and Service Tracking

The immutable REDCap source identity is `project_id + record`. It is retained separately from the application-level canonical patient ID. A record from FCH (`76`), OI/ART (`78`), or OPD (`79`) is not merged automatically with another project record only because names are similar. Any cross-project link retains its match method, confidence, review status, and audit information.

The application read model contains source records, canonical patients, patient-to-source-record links, and encounters. An encounter represents one service occurrence and is grouped by `project_id + record + event_id + form_name + normalized_instance`; it stores the service, subject type, raw instance, normalized instance, business date, and references to the original `redcap_data6` rows. Mother-baby records retain separate mother and baby subjects, with `pncr_mother_baby` stored as the pair relationship identifier.

---

