const { contextBridge, ipcRenderer } = require("electron");

let indexBridge = {
    openHoNRegister: async () => {
        await ipcRenderer.invoke("openHoNRegister");
    },
    openHonClient: async (params) => {
        await ipcRenderer.invoke("openHonClient", params);
    }
}

contextBridge.exposeInMainWorld("indexBridge", indexBridge);