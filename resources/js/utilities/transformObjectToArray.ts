const transformObjectToArray = (inputObject: object) => {
    const resultArray = [];

    for (const key in inputObject) {
        if (inputObject.hasOwnProperty(key)) {
            resultArray.push({ name: key, value: inputObject[key] });
        }
    }

    return resultArray;
}

export default transformObjectToArray