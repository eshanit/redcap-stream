import { type IProjectData } from '@/types/IProject'

export default function useDownloadData(dataPoints: IProjectData[], field_name: string|undefined) {

    const headers = [
        "Record",
        "Field Name",
        "Value",
        "Instance",
    ];

    const trueHeaders = [
        "record",
        "field_name",
        "value",
        "instance"
    ]


    const headerString = headers.join(",");
    // handle null or undefined values here
    const replacer = (key: any, value: any) => value ?? "";
    const rowItems = dataPoints.map((row: { [x: string]: any }) =>
        trueHeaders
            .map((fieldName) => JSON.stringify(row[fieldName], replacer))
            .join(",")
    );
    // join header and body, and break into separate lines
    const csvContent = [headerString, ...rowItems].join("\r\n");

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `${field_name}`+'.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

}