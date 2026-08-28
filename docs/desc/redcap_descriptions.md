# Project description

- the project has 3 sub-projects FCH , OIART and OPD.

# FCH
FCH has 15 instruments, namely:
     1. Demographics
       Here patient data is collected once, 15 data-items are collected for the patient. Once collected this will be used on all other instruments within FCH. The 15 collected data items are:
         1.1 record_id
            used to track the patient and is unique

         1.2 demog_district
            used to determine which district the patient comes from
            
        1.3 demog_facility
            used to determine which facility the patient comes from
        
        1.4 demog_firstname
            the patient's firstname

        1.5 demog_surname
            thie patient's surname

        1.6 demog_dateofbirth
            this is patient's date of birth in format YYYY-MM-DD

        1.7 demog_age
            patient's age precalculated from demog_dateofbirth (in years)
           
        1.8 demog_gender
            patient's gender

        1.9 demog_address
            patient's address

        1.10 demog_have_contact
            asks whether client has a contact phone number or not

        1.11 demog_contact
            if answer is yes on demog_have_contact then the number should be filled in here
        
        1.12 demog_marital_status
            patient marital status

        1.13 demog_education
            patient level of education

        1.14 demog_client_profile
            where patient falls in terms of category e.g (sex worker, transgender etc)

        1.15 demof_client_profile_other
            filled in text when demog_client_profile is other


    2. STI register
        if a registered patient accesses or has an STI register the data is recorded here, this register has 24 data items:
        2.1 sti_access
            whether patient accessed STI services or not
        
        2.2 sti_visit_date
            when the service was accessed by patient (YYY-MM-DD format)

        2.3 sti_date
            the date if the patient came in for a review (YYY-MM-DD format)

        2.4 sti_client_history
            client history

        2.5 sti_repeat
            if this is a new or repeat sti episode

        2.6 sti_syndrome
            sti type
        
        2.7 sti_syndrome_other
            if answer is other for sti_syndrome then this field comes up

        2.8 sti_syphilis_test
            if the test was done 
        
        2.9 sti_syphilis_test_why 
            purpose why test was done

        2.10 sti_syph_test_why_other
            text if 2.9 is other

        2.11 sti_syphilis_result
            results for the test

        2.12 sti_syphilis_test_nd
            reason if test was not done

        2.13 sti_gonorrhea_test
            if test was done

        2.14 sti_gonorrhea_nd
            why not done

        2.15 sti_gonorrhea_test_why
            purpose of test

        2.16 sti_gonorr_test_why_other
            if response is other on 2.15

        2.17 sti_gonorrhea_result
            results of the test

        2.18 sti_patient_treated
            if patient was treated

        2.19 sti_treatment 
            what treatment was given

        2.20 sti_hiv_test
            if patient was hiv tested

        2.21 sti_hiv_test_result
            the results
        
        2.22 sti_hiv_test_nd
            reason if test was not done

        2.23 sti_contact_recieved
        if sti contact slips were given
    
        2.24 sti_contact_treated
        if sti contact was treated?

    3. Family Planning
    gets filled in if the patient accessed this service. It has 22 fields:

        3.1 fp_access
        if patient accessed this service

        3.2 fb_date
        date service was accessed (YYYY-MM-DD)

        3.3 fb_client_category
        which category patient falls into

        3.4 fp_on_contra
        if patient is on any contraceptive

        3.5 fp_no_contra_why
        why not on contraceptives

        3.6 fp_want_contra
        if they want contraceptive

        3.7 fp_not_want_contra_why
        if not (3.6), why not

        3.8 fp_method
        fp method patient came for

        3.9 fb_progesto_status
        For Progestogen only Pills, is the client a new user or a repeat user?

        3.10 fb_combinedoral_status
        For Combined Oral Pills, is the client a new user or a repeat user?

        3.11 fb_emcontra_status
        For Emergency Contraceptives is the client a new user or a repeat user?

        3.12 fb_injectables_type
        Which type of Injectables is the client taking?

        3.13 fp_intramuscular_status
        For DMPA Intramuscular, is the client a new user or a repeat user?

        3.14 fp_subcutaneous_status
        For DMPA Subcutaneous, is the client a new user or a repeat user?

        3.15 fp_implant_period
        For Implants, which period is it for?

        3.16 fp_3_status
        For 3 year implant is it a new insertion , repeat insertion or for removal?


        



