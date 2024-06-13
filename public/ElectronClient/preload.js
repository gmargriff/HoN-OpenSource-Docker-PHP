const { contextBridge, ipcRenderer } = require("electron");

let indexBridge = {
    openHoNRegister: async () => {
        await ipcRenderer.invoke("openHoNRegister");
    },
    openHonClient: async () => {
        await ipcRenderer.invoke("openHonClient");
    },
    handleLogin: async (params) => {
        await ipcRenderer.invoke("handleLogin", params);
    }
}

contextBridge.exposeInMainWorld("indexBridge", indexBridge);