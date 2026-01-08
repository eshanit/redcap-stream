const sumArray = (a: Array<any>, b: string) => {
    var total = a.reduce((prev, curr) =>{
   
        return parseFloat(prev) + parseFloat(curr[b])
    },0)
    console.log('curr:',b)
    return total
}

export default sumArray