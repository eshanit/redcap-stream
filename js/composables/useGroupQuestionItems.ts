const useGroupQuestionItems = (metaData: any[]): Array<{
    form_name: string,
    fields: Array<any>
}> => {

    const groupedByFormName = metaData.reduce((accumulator: { [x: string]: any[]; }, current) => {
        const formName = current.form_name;
    
        // Initialize the array for this form_name if it doesn't exist
        if (!accumulator[formName]) {
            accumulator[formName] = [];
        }
    
        // Add the current object to the appropriate array
        accumulator[formName].push(current);
    
        return accumulator;
    }, {});
    
    // Convert the grouped object into an array of entries
    const groupedArray = Object.entries(groupedByFormName).map(([key, value]) => ({
        form_name: key,
        fields: value
    }));


    return groupedArray

} 

export default useGroupQuestionItems