import { steadfastBulkOrderCreate } from "@/remoteApi";
import { getSteadFastPayload, getSteadFastResponsePayload } from "./payload";
import { storeBulkRecordsInToOrdersMeta } from "@/api/courier";

export const manageCourier = async (selectedOrders: object, courierPartner:  string, cb: Function) => {
    switch(courierPartner) {
        case "steadfast":
            await handleSteadfast(selectedOrders, cb)
        break
        case "pathao":
            await handlePathao(selectedOrders, cb)
        break
        case "paperfly":
            await handlePaperfly(selectedOrders, cb)
        break
        case "redx":
            await handleRedx(selectedOrders, cb)
        break
    }
}

const handleSteadfast = async (selectedOrders: object, cb: Function) => {
    const payload = getSteadFastPayload([...selectedOrders.value])
    const { data, status } = await steadfastBulkOrderCreate(payload)
    if(status){
        const responsePayload = getSteadFastResponsePayload(data)
        await storeBulkRecordsInToOrdersMeta(responsePayload);
        if(cb){
            await cb()
        }
    }
}


const handlePathao = (selectedOrders: object, cb: Function) => {
    console.log('handlePathao')
}
const handlePaperfly = (selectedOrders: object, cb: Function) => {
    console.log('handlePaperfly')
}
const handleRedx = (selectedOrders: object, cb: Function) => {
    console.log('handleRedX')
}