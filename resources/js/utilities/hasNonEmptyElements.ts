const hasNonEmptyElements = ( arr: Array<any> ) => {
        // Iterate through each object in the array
        for (const obj of arr) {
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    // Check if the value associated with the key has a length greater than zero
                    if (obj[key].length > 0) {
                        return false; // Found a non-empty element, return false
                    }
                }
            }
        }
        return true;
}

export default hasNonEmptyElements