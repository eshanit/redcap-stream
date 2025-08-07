export default function useCalculateDifferences (counts: Record<string, number>) {
    const keys = Object.keys(counts);
    const differences: Record<string, Record<string, number>> = {};
  
    keys.forEach((key1) => {
      differences[key1] = {};
      keys.forEach((key2) => {
        differences[key1][key2] = key1 === key2 ? 0 : counts[key1] - counts[key2];
      });
    });
  
    return differences;
}