interface QueryItem {
    name: string;
    event_id: number;
    query: {
        collection: string[] | { min: string; max: string }[];
        operator: string;
    };
}

interface TransformedItem {
    index: string
    field_name: string;
    event_name: string;
    event_id: number;
    values: any[];
    operator: string;
    search?: string;
    sort?: string;
}

const transformQueryArray = (arr: Array<QueryItem>): TransformedItem[] => {

    const outputArray: TransformedItem[] = [];

    arr.forEach((event) => {
        const field = Object.keys(event)[0];
        event[field].forEach((eventData) => {
            const transformedItem: TransformedItem = {
                index: `Condition ${outputArray.length + 1}`,
                field_name: field,
                event_name: eventData.name,
                event_id: eventData.event_id,
                values: eventData.query.collection,
                operator: eventData.query.operator,
                search: null,
                sort: null,
            };
            outputArray.push(transformedItem);

        })
    })

    return outputArray
}

export default transformQueryArray