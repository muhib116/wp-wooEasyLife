export const getAnalyzeAddressPrompt = (address: string) => {
    return `Extract the following details from the given address:
            - Police station (thana)
            - District
            - Division
            - Area/Locality
            Also, return an "accuracy" score in percentage based on confidence.
            Address: "${address}".
            Response format: JSON with keys: police_station, district, division, area, accuracy.`
}