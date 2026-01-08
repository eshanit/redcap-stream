import _ from "lodash"
import { max, min } from 'simple-statistics';

const chunkArrayDescription = (arr: any[],chunks: number) => {

   const sortedArray = _.sortBy(arr)

 //step 2:  chunk

 const chunkedArray = _.chunk(sortedArray, chunks)

//step 3: find max, min and count of each chunk

const descriptArray:any[] = []

 chunkedArray.forEach(chunk => {

    descriptArray.push({
        min: min(chunk),
        max: max(chunk),
        count: chunk.length
    })

 })

/// return 

 return descriptArray

}

export default chunkArrayDescription