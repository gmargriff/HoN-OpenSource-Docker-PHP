const { contextBridge, ipcRenderer } = require("electron");

let indexBridge = {
    openHoNRegister: async () => {
        await ipcRenderer.invoke("openHoNRegister");
    },
    openHonClient: async (params) => {
        await ipcRenderer.invoke("openHonClient", params);
    },
    submitGameLogs: async () => {
        await ipcRenderer.invoke("submitGameLogs");
    }
}

contextBridge.exposeInMainWorld("indexBridge", indexBridge);