const { contextBridge, ipcRenderer } = require("electron");
const { app } = require('@electron/remote');
const fs = require("node:fs");
const os = require('node:os');
const path = require("node:path");
const commandExistsSync = require('command-exists').sync;

let masterserver_url = "";
let user_OS = "";

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

const loadClientConfigs = async () => {
    let parameters = false;
    // Get the current environment documents folder and check if HoN folder exists.
    // Creates if it doesn't
    let userlogin_folder = path.join(app.getPath("documents"), "Heroes of Newerth x64", "game");

    if (!fs.existsSync(userlogin_folder)) {
        fs.mkdirSync(userlogin_folder, { recursive: true });
    }

    // Check if a user.cfg file exists in HoN folder and load it's parameters it it does
    if (fs.existsSync(path.join(userlogin_folder, "user.cfg"))) {
        parameters = JSON.parse(fs.readFileSync(path.join(userlogin_folder, "user.cfg"), { encoding: "utf8" }))
    }

    // If no parameters have been set, create a new config
    if (!parameters) {
        parameters = {
            WINE_PATH: os.platform() !== "win32" && commandExistsSync('wine') ? "wine" : false,
            WINEPREFIX: false,
            HON_EXE: false,
            MASTERSERVER: "127.0.0.1:8080"
        }
    }

    // Writes the current configs to user.cfg
    fs.writeFileSync(path.join(userlogin_folder, "user.cfg"), JSON.stringify(parameters));
    masterserver_url = parameters.MASTERSERVER;
    user_OS = os.platform();
}

window.addEventListener('DOMContentLoaded', loadClientConfigs());

contextBridge.exposeInMainWorld("masterserver", masterserver_url);
contextBridge.exposeInMainWorld("user_OS", user_OS);
contextBridge.exposeInMainWorld("indexBridge", indexBridge);