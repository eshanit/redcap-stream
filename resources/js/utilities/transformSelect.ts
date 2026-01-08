const transformSelect = (elementEnum: string) => {

    const elementEnumArray = elementEnum.split('<br />')

    const outputArray = elementEnumArray.map(item => {
        let final = {};
        const [value, key] = item.split(', ');
        if (typeof key == 'string') {
            final = { [key.charAt(0).toUpperCase() + key.slice(1).toLowerCase()]: value }
        } else {
            final = { [key]: value }
        }
        return final;
    });

    //transform into a usable array

    const finalArray = outputArray.map(outputObj => {
        const outputName = Object.keys(outputObj)[0];
        const outputValue = outputObj[outputName];
        return { name: outputName, value: outputValue };
      });

    return finalArray

}

export default transformSelect