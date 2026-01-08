const handleQueryArrays = (arr: any[]) => {
    if (!Array.isArray(arr)) {
        return ''; // Handle non-array input gracefully
    }

    if (arr.length === 1) {
        return arr[0].toString(); // Single-element array
    } else {
        const formattedElements = arr.map(el => el.toString());
        const lastElement = formattedElements.pop();
        return `${formattedElements.join(', ')} and ${lastElement}`;
    }
}

export default handleQueryArrays