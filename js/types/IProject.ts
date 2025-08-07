interface Project {
    project_id: number
    project_name: string
    app_title: string
    creation_time: string
    production_time: string
    status: number
}

type IProject = Readonly<Project>

///

interface Enrollment {
  record: string;
  status: string | null;
  latest_instance: string | null;
  next_appo_date: string | null;
  dm_enrollment_date: string | null;
}

type IEnrollment = Readonly<Enrollment>
//

interface Metadata {
    project_id: number;
    field_name: string;
    field_phi?: string;
    form_name: string;
    form_menu_description: string;
    field_order: number;
    field_units?: string;
    element_preceding_header?: string;
    element_type: string;
    element_label: string;
    element_enum?:string;
    element_note?: string;
    element_validation_type?: string;
    element_validation_min?: number;
    element_validation_max?: number;
    element_validation_checktype: string;
    branching_logic?: string;
    field_req: number;
    edoc_id?: string;
    edoc_display_img: number;
    custom_alignment?: string;
    stop_actions?: string;
    question_num?: string;
    grid_name?: string;
    grid_rank:number;
    misc?: string;
    video_url?: string;
    video_display_inline: number
}

type IMetadata = Readonly<Metadata>
///

interface ProjectData {
    project_id: number;
    event_id: string;
    record: string;
    field_name: string;
    value: string;
    instance: number,
    element_type?: string,
    element_enum?: string
    
}

type IProjectData = Readonly<ProjectData>

///
export type { IProject }
export type { IMetadata }
export type { IProjectData }
export type { IEnrollment }